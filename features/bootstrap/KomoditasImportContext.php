<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KomoditasImportContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    protected $endpoint = '/api/v1/komoditas/import';
    
    protected $tempFiles = []; 

    /**
     * @Given Pengguna dengan peran sekretariat login untuk import komoditas
     */
    public function penggunaDenganPeranSekretariatLoginUntukImportKomoditas()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * Helper untuk membuat file UploadedFile palsu yang terbaca oleh sistem
     */
    protected function createTempUploadedFile($fileName, $content, $mimeType)
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $fileName;
        file_put_contents($tempPath, $content);
        $this->tempFiles[] = $tempPath;

        return new UploadedFile(
            $tempPath,
            $fileName,
            $mimeType,
            null,
            true
        );
    }

    /**
     * Eksekutor Request Upload File
     */
    protected function uploadFileRequest($file)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        
        $request = Request::create(
            $this->endpoint, 
            'POST', 
            [],
            [],
            ['file' => $file]
        );

        $request->headers->set('Accept', 'application/json');
        
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @When Pengguna mengirim file excel data Komoditas
     */
    public function penggunaMengirimFileExcelDataKomoditas()
    {
        $export = new class implements \Maatwebsite\Excel\Concerns\FromArray {
            public function array(): array {
                return [
                    ['kode', 'nama_komoditas', 'jenis_komoditas', 'komoditas_kehutanan'],
                    ['KMD-XLS', 'Kakao Excel', 'Perkebunan', '0'],
                    ['PSG-01', 'Pisang', 'Perkebunan', '1']
                ];
            }
        };

        $content = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        $file = $this->createTempUploadedFile(
            'data_valid.xlsx', 
            $content, 
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        // 4. Kirim request
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Komoditas
     */
    public function penggunaMengirimFileCsvDataKomoditas()
    {
        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan\n" .
                      "KMD-01,Kakao,Perkebunan,false\n" .
                      "KMD-02,Madu Hutan,HHBK,true";
                      
        $file = $this->createTempUploadedFile('data_valid.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file txt data Komoditas
     */
    public function penggunaMengirimFileTxtDataKomoditas()
    {
        $file = $this->createTempUploadedFile('data_salah.txt', 'ini teks biasa', 'text/plain');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Komoditas yang ukuran lebih besar dari 5MB
     */
    public function penggunaMengirimFileCsvDataKomoditasYangUkuranLebihBesarDari5Mb()
    {
        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan\n";
        $csvContent .= str_repeat("A", 6 * 1024 * 1024); 
        
        $file = $this->createTempUploadedFile('data_besar.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Komoditas dengan struktur header yang duplikat
     */
    public function penggunaMengirimFileCsvDataKomoditasDenganStrukturHeaderYangDuplikat()
    {
        $csvContent = "kode,kode,jenis_komoditas,komoditas_kehutanan\n" .
                      "KMD-01,Kakao,Perkebunan,false";
                      
        $file = $this->createTempUploadedFile('data_header_duplikat.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Komoditas dengan struktur header tidak lengkap
     */
    public function penggunaMengirimFileCsvDataKomoditasDenganStrukturHeaderTidakLengkap()
    {
        $csvContent = "kode,nama_komoditas,komoditas_kehutanan\n" .
                      "KMD-01,Kakao,false";
                      
        $file = $this->createTempUploadedFile('data_header_tidak_lengkap.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Komoditas dan terdapat data yang tidak valid
     */
    public function penggunaMengirimFileCsvDataKomoditasDanTerdapatDataYangTidakValid()
    {
        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan\n" .
                      "APM-01,Apel Manis,Buah,false\n" .
                      "KMD-03,Karet,Ini Adalah Jenis Komoditas Yang Namanya Terlalu Panjang Dan Melebihi Batas Tiga Puluh Karakter,true\n" .
                      "KMD-04,Sawit,Perkebunan,BukanBoolean";
                      
        $file = $this->createTempUploadedFile('data_baris_salah.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @Then Sistem memberikan pesan sukses import komoditas
     */
    public function sistemMemberikanPesanSuksesImportKomoditas()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diimport', $message);
        Assert::assertEquals(200, $status, "Gagal import. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Data Komoditas sudah berada di database
     */
    public function dataKomoditasSudahBeradaDiDatabase()
    {
        $exists = DB::table('komoditas')->whereIn('kode', ['KMD-01', 'KMD-02', 'PSG-01', 'KMD-XLS'])->exists();
        Assert::assertTrue($exists, 'Data komoditas tidak ditemukan');
    }

    /**
     * @Then Sistem memberikan pesan error terkait ekstensi file komoditas
     * @Then Sistem memberikan pesan error terkait ukuran file komoditas
     */
    public function sistemMemberikanPesanErrorTerkaitFile()
    {
        $status = $this->response->getStatusCode();
        Assert::assertEquals(422, $status, "Sistem tidak memblokir file yang tidak valid. Status: $status");
        
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertArrayHasKey('file', $errors, "Tidak ada pesan error spesifik untuk field 'file'");
    }

    /**
     * @Then Sistem memberikan pesan error terkait header duplikat file komoditas
     */
    public function sistemMemberikanPesanErrorTerkaitHeaderDuplikatFileKomoditas()
    {
        $status = $this->response->getStatusCode();
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if ($status !== 422) {
            throw new \Exception("Ekspektasi status 422, tapi mendapat $status.\nIsi Response: $responseString");
        }

        $hasError = (stripos($responseString, 'tidak valid') !== false) || 
                     (stripos($responseString, 'duplikat') !== false);

        if (!$hasError) {
            throw new \Exception("Pesan error terkait header duplikat tidak ditemukan di respons.\nIsi Response: $responseString");
        }
    }

    /**
     * @Then Sistem memberikan pesan error terkait struktur header tidak sesuai file komoditas
     */
    public function sistemMemberikanPesanErrorTerkaitStrukturHeaderTidakSesuaiFileKomoditas()
    {
        $status = $this->response->getStatusCode();
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if ($status !== 422) {
            throw new \Exception("Ekspektasi status 422, tapi mendapat $status.\nIsi Response: $responseString");
        }

        if (stripos($responseString, 'tidak sesuai') === false) {
            throw new \Exception("Pesan error terkait header tidak sesuai tidak ditemukan di respons.\nIsi Response: $responseString");
        }
    }

    /**
     * @Then Sistem memberikan pesan error terkait data pada file komoditas yang tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitDataFileYangTidakValid()
    {
        $status = $this->response->getStatusCode();
        Assert::assertEquals(422, $status);
        
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertNotEmpty($errors, 'Array errors dari baris excel kosong.');
        
        $errorString = json_encode($errors);
        Assert::assertStringContainsStringIgnoringCase('sudah ada', $errorString);
    }

    /**
     * @AfterScenario
     */
    public function cleanUpTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
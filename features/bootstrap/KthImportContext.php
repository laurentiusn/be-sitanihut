<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KthImportContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    protected $endpoint = '/api/v1/kth/import';
    
    protected $tempFiles = []; 
    
    // Properti untuk menyimpan ID referensi validasi
    protected $validKphId;
    protected $validPenyuluhId;
    protected $invalidPenyuluhId; // Penyuluh yang ada di KPH lain

    /**
     * Helper untuk menyiapkan KPH dan Penyuluh yang valid di database
     */
    protected function setupKphAndPenyuluh()
    {
        if ($this->validKphId) return;

        $this->validKphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'       => $this->validKphId, 
            'nama_kph' => 'KPH Import Valid',
            'wilayah'  => 'Wilayah Barat',
            'alamat'   => 'Jalan Hutan Produksi No. 1',
            'nomor_wa' => '081122334455',
            'jenis'    => 'Produksi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penyuluhValid = User::factory()->penyuluh()->create([
            'id_kph' => $this->validKphId
        ]);
        $this->validPenyuluhId = $penyuluhValid->id;

        $kphLainId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'       => $kphLainId, 
            'nama_kph' => 'KPH Import Invalid',
            'wilayah'  => 'Wilayah Timur',
            'alamat'   => 'Jalan Hutan Lindung No. 2',
            'nomor_wa' => '089988776655',
            'jenis'    => 'Lindung',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penyuluhInvalid = User::factory()->penyuluh()->create([
            'id_kph' => $kphLainId
        ]);
        $this->invalidPenyuluhId = $penyuluhInvalid->id;
    }

    /**
     * @Given Pengguna dengan peran sekretariat login untuk import kth
     */
    public function penggunaDenganPeranSekretariatLoginUntukImportKth()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * Helper untuk File Temp
     */
    protected function createTempUploadedFile($fileName, $content, $mimeType)
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $fileName;
        file_put_contents($tempPath, $content);
        $this->tempFiles[] = $tempPath;

        return new UploadedFile($tempPath, $fileName, $mimeType, null, true);
    }

    protected function uploadFileRequest($file)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create($this->endpoint, 'POST', [], [], ['file' => $file]);
        $request->headers->set('Accept', 'application/json');
        
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @When Pengguna mengirim file excel data KTH
     */
    public function penggunaMengirimFileExcelDataKth()
    {
        $this->setupKphAndPenyuluh();

        $export = new class($this->validPenyuluhId, $this->validKphId) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $penyuluh, $kph;
            public function __construct($p, $k) { $this->penyuluh = $p; $this->kph = $k; }
            
            public function array(): array {
                return [
                    ['nama_kth', 'desa', 'kecamatan', 'kabupaten', 'koordinator', 'nomor_wa', 'id_penyuluh', 'id_kph'],
                    ['KTH Excel Baru', 'Desa A', 'Kec A', 'Kab A', 'Koor A', '08123456789', $this->penyuluh, $this->kph],
                ];
            }
        };

        $content = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $file = $this->createTempUploadedFile('data_kth.xlsx', $content, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data KTH
     */
    public function penggunaMengirimFileCsvDataKth()
    {
        $this->setupKphAndPenyuluh();

        $csvContent = "nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" .
                      "KTH CSV Baru,Desa B,Kec B,Kab B,Koor B,08987654321,{$this->validPenyuluhId},{$this->validKphId}";
                      
        $file = $this->createTempUploadedFile('data_kth.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file txt data KTH
     */
    public function penggunaMengirimFileTxtDataKth()
    {
        $file = $this->createTempUploadedFile('salah.txt', 'Teks Acak', 'text/plain');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data KTH yang ukuran lebih besar dari 5MB
     */
    public function penggunaMengirimFileCsvDataKthYangUkuranLebihBesarDari5Mb()
    {
        $csvContent = "nama_kth,desa\n" . str_repeat("A", 6 * 1024 * 1024); 
        $file = $this->createTempUploadedFile('besar.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data KTH dengan struktur header yang duplikat
     */
    public function penggunaMengirimFileCsvDataKthDenganStrukturHeaderYangDuplikat()
    {
        $this->setupKphAndPenyuluh();
        $csvContent = "nama_kth,nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" .
                      "KTH A,KTH A,Desa A,Kec A,Kab A,Koor A,0812,{$this->validPenyuluhId},{$this->validKphId}";
        $file = $this->createTempUploadedFile('header_duplikat.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data KTH dengan struktur header tidak lengkap
     */
    public function penggunaMengirimFileCsvDataKthDenganStrukturHeaderTidakLengkap()
    {
        $this->setupKphAndPenyuluh();
        $csvContent = "nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh\n" .
                      "KTH A,Desa A,Kec A,Kab A,Koor A,0812,{$this->validPenyuluhId}";
        $file = $this->createTempUploadedFile('header_tidak_lengkap.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data KTH dan terdapat data yang tidak valid
     */
    public function penggunaMengirimFileCsvDataKthDanTerdapatDataYangTidakValid()
    {
        $this->setupKphAndPenyuluh();
        $uuidNgawur = Str::uuid()->toString();
        $namaPanjang = str_repeat('A', 40);

        $csvContent = "nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" .
                      "{$namaPanjang},Desa,Kec,Kab,Koor,0812,{$this->validPenyuluhId},{$this->validKphId}\n" .
                      "KTH A,Desa,Kec,Kab,Koor,0812,{$this->validPenyuluhId},{$uuidNgawur}\n" .
                      "KTH B,Desa,Kec,Kab,Koor,0812,{$this->invalidPenyuluhId},{$this->validKphId}";
                      
        $file = $this->createTempUploadedFile('data_invalid.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @Then Sistem memberikan pesan sukses import kth
     */
    public function sistemMemberikanPesanSuksesImportKth()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diimport', $message);
        Assert::assertEquals(200, $status, "Gagal! Status: $status. Pesan: " . json_encode($this->responseContent));
    }

    /**
     * @Then Data KTH sudah berada di database
     */
    public function dataKthSudahBeradaDiDatabase()
    {
        $exists = DB::table('kth')->whereIn('nama_kth', ['KTH Excel Baru', 'KTH CSV Baru'])->exists();
        Assert::assertTrue($exists, 'Data KTH dari file import tidak ditemukan di database.');
    }

    /**
     * @Then Sistem memberikan pesan error terkait ekstensi file kth
     * @Then Sistem memberikan pesan error terkait ukuran file kth
     */
    public function sistemMemberikanPesanErrorTerkaitEkstensiUkuranFileKth()
    {
        Assert::assertEquals(422, $this->response->getStatusCode());
        Assert::assertArrayHasKey('file', $this->responseContent['errors'] ?? []);
    }

    /**
     * @Then Sistem memberikan pesan error terkait header duplikat file kth
     */
    public function sistemMemberikanPesanErrorTerkaitHeaderDuplikatFileKth()
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
     * @Then Sistem memberikan pesan error terkait struktur header tidak sesuai file kth
     */
    public function sistemMemberikanPesanErrorTerkaitStrukturHeaderTidakSesuaiFileKth()
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
     * @Then Sistem memberikan pesan error terkait data pada file kth yang tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitDataPadaFileKthYangTidakValid()
    {
        Assert::assertEquals(422, $this->response->getStatusCode());
        $errors = json_encode($this->responseContent['errors'] ?? []);
        
        Assert::assertStringContainsStringIgnoringCase('35 karakter', $errors);
        Assert::assertStringContainsStringIgnoringCase('tidak terdaftar di KPH yang dipilih', $errors);
    }

    /**
     * @AfterScenario
     */
    public function cleanUpTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
}
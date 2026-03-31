<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserImportContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    protected $endpoint = '/api/v1/users/import';
    
    protected $tempFiles = []; 
    
    // Properti Data Referensi
    protected $rolePetaniId;
    protected $kphId;
    protected $kthId;

    /**
     * Menyiapkan dependensi (Role, KPH dari Seeder, dan buat KTH dummy)
     */
    protected function setupDependencies()
    {
        if ($this->rolePetaniId) return;

        // 1. Ambil Role Petani dari Seeder
        $this->rolePetaniId = DB::table('role')->where('slug', 'petani')->value('id');

        // 2. Ambil KPH pertama dari Seeder (Tahura WAR, dll)
        $this->kphId = DB::table('kph')->first()->id;

        // 3. Buat KTH dummy yang bernaung di KPH tersebut
        $this->kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id'          => $this->kthId,
            'nama_kth'    => 'KTH Import User',
            'desa'        => 'Desa Uji',
            'kecamatan'   => 'Kec Uji',
            'kabupaten'   => 'Kab Uji',
            'koordinator' => 'Koor Uji',
            'nomor_wa'    => '081122334455',
            'id_kph'      => $this->kphId,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    /**
     * @Given Pengguna dengan peran sekretariat login untuk import akun
     */
    public function penggunaDenganPeranSekretariatLoginUntukImportAkun()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

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
     * @When Pengguna mengirim file excel data Akun
     */
    public function penggunaMengirimFileExcelDataAkun()
    {
        $this->setupDependencies();

        $export = new class($this->rolePetaniId, $this->kphId, $this->kthId) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $role, $kph, $kth;
            public function __construct($role, $kph, $kth) { $this->role = $role; $this->kph = $kph; $this->kth = $kth; }
            
            public function array(): array {
                return [
                    ['email', 'password', 'id_role', 'id_kph', 'id_kth', 'nama_user', 'nomor_induk', 'jenis_kelamin', 'alamat', 'nomor_wa', 'pendidikan_terakhir', 'pekerjaan_sampingan', 'luas_lahan', 'jabatan'],
                    ['excel@petani.com', 'password123', $this->role, $this->kph, $this->kth, 'Petani Excel', 'NIK-XLS-001', 'pria', 'Jalan Excel', '0811', 'SMA', 'Dagang', '2.5', 'Anggota']
                ];
            }
        };

        $content = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        $file = $this->createTempUploadedFile('data_akun.xlsx', $content, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Akun
     */
    public function penggunaMengirimFileCsvDataAkun()
    {
        $this->setupDependencies();

        $csvContent = "email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n" .
                      "csv@petani.com,password123,{$this->rolePetaniId},{$this->kphId},{$this->kthId},Petani CSV,NIK-CSV-001,wanita,Jalan CSV,0822,S1,Guru,1.5,Ketua";
                      
        $file = $this->createTempUploadedFile('data_akun.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file txt data Akun
     */
    public function penggunaMengirimFileTxtDataAkun()
    {
        $file = $this->createTempUploadedFile('salah.txt', 'Ini file txt', 'text/plain');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Akun yang ukuran lebih besar dari 5MB
     */
    public function penggunaMengirimFileCsvDataAkunYangUkuranLebihBesarDari5Mb()
    {
        $csvContent = "email,password\n" . str_repeat("A", 6 * 1024 * 1024); 
        $file = $this->createTempUploadedFile('besar.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }



    /**
     * @When Pengguna mengirim file csv data Akun dengan struktur header yang duplikat
     */
    public function penggunaMengirimFileCsvDataAkunDenganStrukturHeaderYangDuplikat()
    {
        $this->setupDependencies();
        $csvContent = "email,email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n" .
                      "csv@petani.com,csv@petani.com,password123,{$this->rolePetaniId},{$this->kphId},{$this->kthId},Petani CSV,NIK-CSV-001,wanita,Jalan CSV,0822,S1,Guru,1.5,Ketua";
        $file = $this->createTempUploadedFile('header_duplikat.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Akun dengan struktur header tidak lengkap
     */
    public function penggunaMengirimFileCsvDataAkunDenganStrukturHeaderTidakLengkap()
    {
        $this->setupDependencies();
        $csvContent = "email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan\n" .
                      "csv@petani.com,password123,{$this->rolePetaniId},{$this->kphId},{$this->kthId},Petani CSV,NIK-CSV-001,wanita,Jalan CSV,0822,S1,Guru,1.5";
        $file = $this->createTempUploadedFile('header_tidak_lengkap.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @When Pengguna mengirim file csv data Akun dan terdapat data yang tidak valid
     */
    public function penggunaMengirimFileCsvDataAkunDanTerdapatDataYangTidakValid()
    {
        $this->setupDependencies();

        $emailDummy = "petani@demo.com";
        $user = User::factory()->petani()->withDetail()->create(['email' => $emailDummy]);
        $nomorInduk = $user->userDetail->nomor_induk;

        $csvContent = "email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n" .
                      "invalid@petani.com,123,{$this->rolePetaniId},,,User Invalid,22151115555,alien,Jalan,,,,,\n" .
                      "invalid@petani.com,123,{$this->rolePetaniId},,,User Invalid,22151115555,alien,Jalan,,,,,\n" .
                      "{$emailDummy},123,{$this->rolePetaniId},,,User Invalid,{$nomorInduk},alien,Jalan,,,,,";
                      
        $file = $this->createTempUploadedFile('data_invalid.csv', $csvContent, 'text/csv');
        $this->uploadFileRequest($file);
    }

    /**
     * @Then Sistem memberikan pesan sukses import akun
     */
    public function sistemMemberikanPesanSuksesImportAkun()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diimport', $message);
        Assert::assertEquals(200, $status, "Gagal import! Status: $status. Pesan: " . json_encode($this->responseContent));
    }

    /**
     * @Then Data User sudah berada di database
     */
    public function dataUserSudahBeradaDiDatabase()
    {
        $existsUser = DB::table('users')->whereIn('email', ['excel@petani.com', 'csv@petani.com'])->exists();
        Assert::assertTrue($existsUser, 'Email user tidak ditemukan di database.');

        $existsDetail = DB::table('user_details')->whereIn('nomor_induk', ['NIK-XLS-001', 'NIK-CSV-001'])->exists();
        Assert::assertTrue($existsDetail, 'Detail user (nomor_induk) tidak ditemukan di database.');
    }

    /**
     * @Then Sistem memberikan pesan error terkait ekstensi file akun
     * @Then Sistem memberikan pesan error terkait ukuran file akun
     */
    public function sistemMemberikanPesanErrorTerkaitEkstensiUkuranFileAkun()
    {
        Assert::assertEquals(422, $this->response->getStatusCode());
        Assert::assertArrayHasKey('file', $this->responseContent['errors'] ?? []);
    }

    /**
     * @Then Sistem memberikan pesan error terkait header duplikat file akun
     */
    public function sistemMemberikanPesanErrorTerkaitHeaderDuplikatFileAkun()
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
     * @Then Sistem memberikan pesan error terkait struktur header tidak sesuai file akun
     */
    public function sistemMemberikanPesanErrorTerkaitStrukturHeaderTidakSesuaiFileAkun()
    {
        $status = $this->response->getStatusCode();
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if ($status !== 422) {
            throw new \Exception("Ekspektasi status 422, tapi mendapat $status.\nIsi Response: $responseString");
        }

        if (stripos($responseString, 'penulisannya salah (typo)') === false) {
            throw new \Exception("Pesan error terkait header tidak sesuai tidak ditemukan di respons.\nIsi Response: $responseString");
        }
    }

    /**
     * @Then Sistem memberikan pesan error terkait data pada file akun yang tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitDataPadaFileAkunYangTidakValid()
    {
        $status = $this->response->getStatusCode();
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if ($status !== 422) {
            throw new \Exception("Ekspektasi status 422, tapi mendapat $status.\nIsi Response: $responseString");
        }

        // Gunakan implode untuk menghindari escape karakter (\") dari json_encode
        $errors = implode(" ", $this->responseContent['errors'] ?? []);

        if (stripos($errors, 'terdeteksi ganda') === false) {
            throw new \Exception("Error 'terdeteksi ganda' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'sudah terdaftar di sistem') === false) {
            throw new \Exception("Error 'sudah terdaftar di sistem' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'sudah digunakan oleh user lain') === false) {
            throw new \Exception("Error 'sudah digunakan oleh user lain' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'Password minimal 8 karakter') === false) {
            throw new \Exception("Error 'Password minimal 8 karakter' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'harus berupa "pria" atau "wanita"') === false) {
            throw new \Exception("Error validasi 'jenis kelamin' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'kolom KPH tidak boleh kosong') === false) {
            throw new \Exception("Error 'kolom KPH tidak boleh kosong' TIDAK ADA.\nIsi Response: $responseString");
        }

        if (stripos($errors, 'kolom KTH tidak boleh kosong') === false) {
            throw new \Exception("Error 'kolom KTH tidak boleh kosong' TIDAK ADA.\nIsi Response: $responseString");
        }

        Assert::assertTrue(true);
    }

    /**
     * Hapus semua file temporary setelah scenario selesai
     * @AfterScenario
     */
    public function cleanUpTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
    }
}
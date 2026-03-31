<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ImportController;
use App\Http\Requests\Api\Import\UserImportRequest;
use App\Helpers\RoleHelper;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class ImportUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setDataDummy(): array
    {
        $this->seed(RoleSeeder::class);
        $rolePetaniId = RoleHelper::id('petani');

        $kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'       => $kphId,
            'nama_kph' => 'KPH Import User Valid',
            'wilayah'  => 'Wilayah Test',
            'alamat'   => 'Alamat Test',
            'nomor_wa' => '081122334455',
            'jenis'    => 'Produksi',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id'          => $kthId,
            'nama_kth'    => 'KTH Import User',
            'desa'        => 'Desa Uji',
            'kecamatan'   => 'Kec Uji',
            'kabupaten'   => 'Kab Uji',
            'koordinator' => 'Koor Uji',
            'nomor_wa'    => '081122334455',
            'id_kph'      => $kphId,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        return [
            'rolePetaniId' => $rolePetaniId,
            'kphId'        => $kphId,
            'kthId'        => $kthId
        ];
    }

    public function test_controller_user_berhasil_import_file_excel_untuk_membuat_banyak_akun()
    {
        $refs = $this->setDataDummy();

        $export = new class($refs['rolePetaniId'], $refs['kphId'], $refs['kthId']) implements \Maatwebsite\Excel\Concerns\FromArray {
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
        $file = UploadedFile::fake()->createWithContent('data_akun.xlsx', $content);

        $payload = ['file' => $file];

        $request = UserImportRequest::create('/api/import/user', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->userImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $existsUser = DB::table('users')->where('email', 'excel@petani.com')->exists();
        $this->assertTrue($existsUser, 'Email user tidak ditemukan di database.');

        $existsDetail = DB::table('user_details')->where('nomor_induk', 'NIK-XLS-001')->exists();
        $this->assertTrue($existsDetail, 'Detail user (nomor_induk) tidak ditemukan di database.');
    }

    public function test_controller_user_berhasil_import_file_csv_untuk_membuat_banyak_akun()
    {
        $refs = $this->setDataDummy();

        $csvContent = "email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n" .
                      "csv@petani.com,password123,{$refs['rolePetaniId']},{$refs['kphId']},{$refs['kthId']},Petani CSV,NIK-CSV-001,wanita,Jalan CSV,0822,S1,Guru,1.5,Ketua";
                      
        $file = UploadedFile::fake()->createWithContent('data_akun.csv', $csvContent);

        $payload = ['file' => $file];

        $request = UserImportRequest::create('/api/import/user', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->userImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $existsUser = DB::table('users')->where('email', 'csv@petani.com')->exists();
        $this->assertTrue($existsUser, 'Email user tidak ditemukan di database.');

        $existsDetail = DB::table('user_details')->where('nomor_induk', 'NIK-CSV-001')->exists();
        $this->assertTrue($existsDetail, 'Detail user (nomor_induk) tidak ditemukan di database.');
    }

    public function test_request_gagal_ekstensi_file_akun_tidak_sesuai()
    {
        $file = UploadedFile::fake()->createWithContent('salah.txt', 'Ini file txt');

        $request = new UserImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_request_gagal_ukuran_file_akun_terlalu_besar()
    {
        $file = UploadedFile::fake()->create('besar.csv', 6000, 'text/csv');

        $request = new UserImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_controller_user_gagal_struktur_header_duplikat_pada_file_akun()
    {
        $refs = $this->setDataDummy();

        $csvContent = "email,password,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n".
                      "csv@petani.com,password123,{$refs['rolePetaniId']},{$refs['kphId']},{$refs['kthId']},Petani CSV,NIK-CSV-001,wanita,Jalan CSV,0822,S1,Guru,1.5,Ketua";
        $file = UploadedFile::fake()->createWithContent('header_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = UserImportRequest::create('/api/import/user', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->userImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Terdapat judul kolom duplikat', json_encode($response->json()));
    }

    public function test_controller_user_gagal_struktur_header_tidak_lengkap_file_akun()
    {
        $this->setDataDummy();
        $csvContent = "email_salah,password,id_role\n".
                      "admin@a.com,12345678,123";
        $file = UploadedFile::fake()->createWithContent('header_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = UserImportRequest::create('/api/import/user', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->userImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Kolom berikut wajib ada pada baris pertama atau penulisannya salah', json_encode($response->json()));
    }

    public function test_controller_user_gagal_terdapat_data_akun_yang_tidak_valid()
    {
        $refs = $this->setDataDummy();

        $emailDummy = "petani@demo.com";
        $userObj = User::factory()->petani()->withDetail()->create(['email' => $emailDummy]);
        $nomorInduk = $userObj->userDetail->nomor_induk;
        $uuidAsal = Str::uuid()->toString();

        $csvContent = "email,password,id_role,id_kph,id_kth,nama_user,nomor_induk,jenis_kelamin,alamat,nomor_wa,pendidikan_terakhir,pekerjaan_sampingan,luas_lahan,jabatan\n" .
                      "invalid@petani.com,123,{$refs['rolePetaniId']},,,User Invalid,22151115555,alien,Jalan,,,,,\n" .
                      "invalid@petani.com,123,{$refs['rolePetaniId']},{$uuidAsal},{$refs['kthId']},User Invalid,22151115555,alien,Jalan,,,,,\n" .
                      "{$emailDummy},123,{$refs['rolePetaniId']},{$refs['kphId']},{$uuidAsal},User Invalid,{$nomorInduk},alien,Jalan,,,,,";
                      
        $file = UploadedFile::fake()->createWithContent('data_invalid.csv', $csvContent);

        $payload = ['file' => $file];

        $request = UserImportRequest::create('/api/import/user', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->userImport($request));

        $response->assertStatus(422);
        
        $errorsString = implode(" ", $response->json('errors') ?? []);

        $this->assertStringContainsStringIgnoringCase('terdeteksi ganda', $errorsString);
        $this->assertStringContainsStringIgnoringCase('sudah terdaftar di sistem', $errorsString);
        $this->assertStringContainsStringIgnoringCase('sudah digunakan oleh user lain', $errorsString);
        $this->assertStringContainsStringIgnoringCase('Password minimal 8 karakter', $errorsString);
        $this->assertStringContainsStringIgnoringCase('harus berupa "pria" atau "wanita"', $errorsString);
        $this->assertStringContainsStringIgnoringCase('kolom KPH tidak boleh kosong', $errorsString);
        $this->assertStringContainsStringIgnoringCase('kolom KTH tidak boleh kosong', $errorsString);
        $this->assertStringContainsStringIgnoringCase('KTH yang dipilih tidak terdaftar di KPH', $errorsString);
    }
}
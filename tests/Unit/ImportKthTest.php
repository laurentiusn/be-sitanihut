<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ImportController;
use App\Http\Requests\Api\Import\KthImportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class ImportKthTest extends TestCase
{
    use RefreshDatabase;

    protected function setupKphAndPenyuluh(): array
    {
        $validKphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'       => $validKphId, 
            'nama_kph' => 'KPH Import Valid',
            'wilayah'  => 'Wilayah Barat',
            'alamat'   => 'Jalan Hutan Produksi No. 1',
            'nomor_wa' => '081122334455',
            'jenis'    => 'Produksi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $penyuluhValid = User::factory()->penyuluh()->create([
            'id_kph' => $validKphId
        ]);

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

        return [
            'validKphId' => $validKphId,
            'validPenyuluhId' => $penyuluhValid->id,
            'invalidPenyuluhId' => $penyuluhInvalid->id
        ];
    }

    public function test_controller_kth_berhasil_import_file_excel_untuk_membuat_banyak_kth()
    {
        $refs = $this->setupKphAndPenyuluh();

        $export = new class($refs['validPenyuluhId'], $refs['validKphId']) implements \Maatwebsite\Excel\Concerns\FromArray {
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
        $file = UploadedFile::fake()->createWithContent('data_kth.xlsx', $content);
        
        $payload = ['file' => $file];

        $request = KthImportRequest::create('/api/import/kth', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->kthImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $exists = DB::table('kth')->where('nama_kth', 'KTH Excel Baru')->exists();
        $this->assertTrue($exists, 'Data KTH dari file Excel import tidak ditemukan di database.');
    }

    public function test_controller_kth_berhasil_import_file_csv_untuk_membuat_banyak_kth()
    {
        $refs = $this->setupKphAndPenyuluh();

        $csvContent = "nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" .
                      "KTH CSV Baru,Desa B,Kec B,Kab B,Koor B,08987654321,{$refs['validPenyuluhId']},{$refs['validKphId']}";
                      
        $file = UploadedFile::fake()->createWithContent('data_kth.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KthImportRequest::create('/api/import/kth', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->kthImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $exists = DB::table('kth')->where('nama_kth', 'KTH CSV Baru')->exists();
        $this->assertTrue($exists, 'Data KTH dari file CSV import tidak ditemukan di database.');
    }

    public function test_request_gagal_ekstensi_file_kth_tidak_sesuai()
    {
        $file = UploadedFile::fake()->createWithContent('salah.txt', 'Teks Acak');

        $request = new KthImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_request_gagal_ukuran_file_kth_terlalu_besar()
    {
        $file = UploadedFile::fake()->create('besar.csv', 6000, 'text/csv');

        $request = new KthImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_controller_kth_gagal_struktur_header_duplikat_pada_file_kth()
    {
        $refs = $this->setupKphAndPenyuluh();

        $csvContent = "nama_kth,nama_kth,desa,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" . 
                      "KTH CSV Baru,Desa B,Kec B,Kab B,Koor B,08987654321,{$refs['validPenyuluhId']},{$refs['validKphId']}";

        $file = UploadedFile::fake()->createWithContent('header_duplikat.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KthImportRequest::create('/api/import/kth', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->kthImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Terdapat judul kolom duplikat', json_encode($response->json()), 'Tidak ada pesan duplikat');
    }

    public function test_controller_kth_gagal_struktur_header_file_tidak_lengkap_pada_file_kth()
    {
        $csvContent = "nama_kth_salah,desa,kecamatan\n".
                      "KTH A,Desa A,Kec A";
        $file = UploadedFile::fake()->createWithContent('header_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KthImportRequest::create('/api/import/kth', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->kthImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Kolom berikut wajib ada pada baris pertama atau penulisannya salah', json_encode($response->json()));
    }

    public function test_controller_kth_gagal_terdapat_data_kth_yang_tidak_valid()
    {
        $refs = $this->setupKphAndPenyuluh();
        $uuidNgawur = Str::uuid()->toString();
        $namaPanjang = str_repeat('A', 40);

        $csvContent = "nama_kth,desa,kecamatan,kabupaten,koordinator,nomor_wa,id_penyuluh,id_kph\n" .
                      "{$namaPanjang},{$namaPanjang},{$namaPanjang},{$namaPanjang},{$namaPanjang},{$namaPanjang},{$refs['validPenyuluhId']},{$refs['validKphId']}\n" .
                      "KTH A,Desa,Kec,Kab,Koor,0812,{$refs['validPenyuluhId']},{$uuidNgawur}\n" .
                      "KTH B,Desa,Kec,Kab,Koor,0812,{$refs['invalidPenyuluhId']},{$refs['validKphId']}";
                      
        $file = UploadedFile::fake()->createWithContent('data_invalid.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KthImportRequest::create('/api/import/kth', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->kthImport($request));

        $response->assertStatus(422);
        
        $errorsString = json_encode($response->json('errors') ?? []);
        $this->assertStringContainsStringIgnoringCase('tidak boleh lebih dari 15 karakter', $errorsString);
        $this->assertStringContainsStringIgnoringCase('tidak boleh lebih dari 35 karakter', $errorsString);
        $this->assertStringContainsStringIgnoringCase('tidak terdaftar di KPH yang dipilih', $errorsString);
        $this->assertStringContainsStringIgnoringCase('tidak ditemukan', $errorsString);
    }
}
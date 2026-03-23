<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\ImportController;
use App\Http\Requests\Api\Import\KomoditasImportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Str;

class ImportKomoditasTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_komoditas_berhasil_import_file_excel_untuk_membuat_banyak_komoditas()
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
        $file = UploadedFile::fake()->createWithContent('data_valid.xlsx', $content);

        $payload = ['file' => $file];

        $request = KomoditasImportRequest::create('/api/import/komoditas', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->komoditasImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $exists = DB::table('komoditas')->whereIn('kode', ['KMD-XLS', 'PSG-01'])->exists();
        $this->assertTrue($exists, 'Data komoditas dari Excel tidak ditemukan di database.');
    }

    public function test_controller_komoditas_berhasil_import_file_csv_untuk_membuat_banyak_komoditas()
    {
        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan\n" .
                      "KMD-01,Kakao,Perkebunan,false\n" .
                      "KMD-02,Madu Hutan,HHBK,true";
                      
        $file = UploadedFile::fake()->createWithContent('data_valid.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KomoditasImportRequest::create('/api/import/komoditas', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->komoditasImport($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diimport', json_encode($response->json('message')));

        $exists = DB::table('komoditas')->whereIn('kode', ['KMD-01', 'KMD-02'])->exists();
        $this->assertTrue($exists, 'Data komoditas dari CSV tidak ditemukan di database.');
    }

    public function test_request_gagal_ekstensi_file_komoditas_tidak_sesuai()
    {
        $file = UploadedFile::fake()->createWithContent('data_salah.txt', 'ini teks biasa');
        
        $request = new KomoditasImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_request_gagal_ukuran_file_komoditas_terlalu_besar()
    {
        $file = UploadedFile::fake()->create('data_besar.csv', 6000, 'text/csv');
        
        $request = new KomoditasImportRequest();
        $validator = Validator::make(['file' => $file], $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('file', $errors);
    }

    public function test_controller_komoditas_gagal_struktur_header_duplikat_pada_file_komoditas()
    {
        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan,kode\n" .
                      "KMD-01,Kakao,Perkebunan,false";
        $file = UploadedFile::fake()->createWithContent('data_header_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KomoditasImportRequest::create('/api/import/komoditas', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->komoditasImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Terdapat judul kolom duplikat', json_encode($response->json()));
    }

    public function test_controller_komoditas_gagal_struktur_header_tidak_lengkap_pada_file_komoditas()
    {
        $csvContent = "kode_salah,nama_komoditas,jenis\n" .
                      "KMD-01,Kakao,Perkebunan";
        $file = UploadedFile::fake()->createWithContent('data_header_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KomoditasImportRequest::create('/api/import/komoditas', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->komoditasImport($request));

        $response->assertStatus(422);
        $this->assertStringContainsStringIgnoringCase('Kolom berikut wajib ada pada baris pertama atau penulisannya salah', json_encode($response->json()));
    }

    public function test_controller_komoditas_gagal_terdapat_data_komoditas_yang_tidak_valid()
    {
        DB::table('komoditas')->insert([
            'id' => Str::uuid()->toString(),
            'kode' => 'APM-01',
            'nama_komoditas' => 'Apel Manis',
            'jenis_komoditas' => 'Buah'
        ]);

        $csvContent = "kode,nama_komoditas,jenis_komoditas,komoditas_kehutanan\n" .
                      "APM-01,Apel Manis,Buah,false\n" .
                      "KMD-03,Karet,Ini Adalah Jenis Komoditas Yang Namanya Terlalu Panjang Dan Melebihi Batas Tiga Puluh Karakter,true\n" .
                      "KMD-04,Sawit,Perkebunan,BukanBoolean\n" .
                      "KMD-04,Sawit,Perkebunan,BukanBoolean";
                      
        $file = UploadedFile::fake()->createWithContent('data_baris_salah.csv', $csvContent);

        $payload = ['file' => $file];

        $request = KomoditasImportRequest::create('/api/import/komoditas', 'POST', [], [], $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(ImportController::class);
        $response = TestResponse::fromBaseResponse($controller->komoditasImport($request));

        $response->assertStatus(422);
        
        $errors = $response->json('errors');
        $this->assertNotEmpty($errors, 'Array errors dari baris excel kosong.');
        $this->assertStringContainsStringIgnoringCase('sudah ada', json_encode($errors), 'Error duplikasi "sudah ada" tidak ditemukan.');
        $this->assertStringContainsStringIgnoringCase('terdeteksi ganda', json_encode($errors), 'Error duplikasi "terdeteksi ganda" tidak ditemukan.');
        $this->assertStringContainsStringIgnoringCase('tidak boleh lebih dari', json_encode($errors), 'Error duplikasi "tidak boleh lebih dari" tidak ditemukan.');
        $this->assertStringContainsStringIgnoringCase('harus bernilai true atau false', json_encode($errors), 'Error duplikasi "harus bernilai true atau false" tidak ditemukan.');
    }
}
<?php

namespace Tests\Unit;

use App\Models\Laporan;
use App\Models\Komoditas;
use App\Models\User;
use App\Http\Controllers\Api\RekapNteController;
use App\Http\Requests\Api\Rekap\RekapExportRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Smalot\PdfParser\Parser;
use App\Services\RekapNteService;

class RekapExportTest extends TestCase
{
    use RefreshDatabase;

    private function setupDummyData()
    {
        $komoditas = [
            ['kode' => 'PDI-01', 'nama_komoditas' => 'Padi Sawah', 'jenis_komoditas' => 'Pangan'],
        ];

        foreach ($komoditas as $item) {
            Komoditas::updateOrCreate(['kode' => $item['kode']], $item);
        }

        $kph = DB::table('kph')->first();
        
        if (!$kph) {
            $kphId = Str::uuid()->toString();
            DB::table('kph')->insert([
                'id' => $kphId,
                'nama_kph' => 'KPH Dummy',
                'wilayah'  => 'Wilayah Dummy',
                'alamat'   => 'Alamat Wilayah',
                'nomor_wa' => '081367898876',
                'jenis'    => 'KPHK'
            ]);
        } else {
            $kphId = $kph->id;
            DB::table('kph')->where('id', $kphId)->update([
                'wilayah'  => 'Wilayah Dummy',
                'alamat'   => 'Alamat Wilayah',
                'jenis'    => 'KPHK'
            ]);
        }

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id' => $kthId,
            'nama_kth' => 'KTH Testing Rekap',
            'desa' => 'Desa Rekap', 
            'kecamatan' => 'Kec Rekap', 
            'kabupaten' => 'Kab Rekap', 
            'koordinator' => 'Koordinator Rekap', 
            'nomor_wa' => '08123456789',
            'id_kph' => $kphId
        ]);

        $petani = User::factory()->petani()->withDetail()->create([
            'id_kph' => $kphId,
            'id_kth' => $kthId
        ]);

        Laporan::factory()
            ->status('disetujui')
            ->withMasaTanamPanen(2)
            ->create([
                'id_user' => $petani->id,
                'periode' => 2025
            ]);
    }

    public function test_controller_export_data_rekap_nte_tiap_kph_menjadi_file_pdf()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'kph',
            'export' => 'pdf',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertDownload('rekap_nte_kph_2025.pdf');

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType, 'Sistem gagal mengirimkan file PDF');

        $content = $response->getContent();
        $parser = new Parser();
        $pdfDocument = $parser->parseContent($content);
        $pdfText = $pdfDocument->getText(); 

        $this->assertStringContainsString('No', $pdfText, 'Gagal: Header No tidak ada');
        $this->assertStringContainsString('Nama KPH', $pdfText, 'Gagal: Header Nama KPH tidak ada');
        $this->assertStringContainsString('Produksi', $pdfText, 'Gagal: Header Produksi tidak ada');
        $this->assertStringContainsString('Total NTE', $pdfText, 'Gagal: Header Total NTE tidak ada');

        $this->assertStringContainsString('KPH Dummy', $pdfText, 'Gagal: Nama KPH Dummy tidak di-render di PDF');
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $pdfText);

        // Cleanup
        unset($content, $parser, $pdfDocument, $pdfText);
        gc_collect_cycles();
    }

    public function test_controller_export_data_rekap_nte_tiap_kph_menjadi_file_csv()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'kph',
            'export' => 'csv',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertStreamed();

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('REKAP NILAI TRANSAKSI EKONOMI', $csv);
        $this->assertStringContainsString('Nama KPH', $csv);
        $this->assertStringContainsString('Produksi', $csv);
        $this->assertStringContainsString('Total NTE', $csv);
        
        $this->assertStringContainsString('KPH Dummy', $csv);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $csv);
    }

    public function test_controller_export_data_rekap_nte_tiap_kth_menjadi_file_pdf()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'kth',
            'export' => 'pdf',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertDownload('rekap_nte_kth_2025.pdf');

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType, 'Sistem gagal mengirimkan file PDF');

        $content = $response->getContent();
        $parser = new Parser();
        $pdfDocument = $parser->parseContent($content);
        $pdfText = $pdfDocument->getText(); 

        $this->assertStringContainsString('No', $pdfText, 'Gagal: Header No tidak ada');
        $this->assertStringContainsString('Nama KPH', $pdfText, 'Gagal: Header Nama KPH tidak ada');
        $this->assertStringContainsString('Nama KTH', $pdfText, 'Gagal: Header Nama KTH tidak ada');
        $this->assertStringContainsString('Produksi', $pdfText, 'Gagal: Header Produksi tidak ada');
        $this->assertStringContainsString('Total NTE', $pdfText, 'Gagal: Header Total NTE tidak ada');

        $this->assertStringContainsString('KTH Testing Rekap', $pdfText, 'Gagal: Nama KTH Testing Rekap tidak di-render di PDF');
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $pdfText);

        // Cleanup
        unset($content, $parser, $pdfDocument, $pdfText);
        gc_collect_cycles();
    }

    public function test_controller_export_data_rekap_nte_tiap_kth_menjadi_file_csv()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'kth',
            'export' => 'csv',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertStreamed();

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('REKAP NILAI TRANSAKSI EKONOMI', $csv);
        $this->assertStringContainsString('Nama KPH', $csv);
        $this->assertStringContainsString('Nama KTH', $csv);
        $this->assertStringContainsString('Produksi', $csv);
        $this->assertStringContainsString('Total NTE', $csv);

        $this->assertStringContainsString('KTH Testing Rekap', $csv);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $csv);
    }

    public function test_controller_export_data_rekap_nte_tiap_petani_menjadi_file_pdf()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'petani',
            'export' => 'pdf',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertDownload('rekap_nte_petani_2025.pdf');

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType, 'Sistem gagal mengirimkan file PDF');

        $content = $response->getContent();
        $this->assertNotEmpty($content, 'File PDF yang di-generate kosong');

        $parser = new Parser();
        $pdfDocument = $parser->parseContent($content);
        $pdfText = $pdfDocument->getText(); 

        $this->assertStringContainsString('No', $pdfText, 'Gagal: Header No tidak ada');
        $this->assertStringContainsString('Nama KPH', $pdfText, 'Gagal: Header Nama KPH tidak ada');
        $this->assertStringContainsString('Nama KTH', $pdfText, 'Gagal: Header Nama KTH tidak ada');
        $this->assertStringContainsString('Nama Petani', $pdfText, 'Gagal: Header Nama Petani tidak ada');
        $this->assertStringContainsString('Produksi', $pdfText, 'Gagal: Header Produksi tidak ada');
        $this->assertStringContainsString('Total NTE', $pdfText, 'Gagal: Header Total NTE tidak ada');

        $this->assertStringContainsString('KPH Dummy', $pdfText, 'Gagal: Nama KPH Dummy tidak di-render di PDF');
        $this->assertStringContainsString('Padi Sawah', $pdfText, 'Gagal: Komoditas Padi Sawah tidak di-render');
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $pdfText);

        // Cleanup
        unset($content, $parser, $pdfDocument, $pdfText);
        gc_collect_cycles();
    }

    public function test_controller_export_data_rekap_nte_tiap_petani_menjadi_file_csv()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'petani',
            'export' => 'csv',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertStreamed();

        $csv = $response->streamedContent();

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('REKAP NILAI TRANSAKSI EKONOMI', $csv);
        $this->assertStringContainsString('Nama KPH', $csv);
        $this->assertStringContainsString('Nama KTH', $csv);
        $this->assertStringContainsString('Nama Petani', $csv);
        $this->assertStringContainsString('Produksi', $csv);
        $this->assertStringContainsString('Total NTE', $csv);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $csv);
    }

    public function test_controller_export_data_rekap_nte_tiap_komoditas_menjadi_file_pdf()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'komoditas',
            'export' => 'pdf',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertDownload('rekap_nte_komoditas_2025.pdf');

        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/pdf', $contentType, 'Sistem gagal mengirimkan file PDF');

        $content = $response->getContent();
        $parser = new Parser();
        $pdfDocument = $parser->parseContent($content);
        $pdfText = $pdfDocument->getText(); 

        $this->assertStringContainsString('No', $pdfText, 'Gagal: Header No tidak ada');
        $this->assertStringContainsString('Nama Komoditas', $pdfText, 'Gagal: Header Nama Komoditas tidak ada');
        $this->assertStringContainsString('Produksi', $pdfText, 'Gagal: Header Produksi tidak ada');
        $this->assertStringContainsString('Total NTE', $pdfText, 'Gagal: Header Total NTE tidak ada');

        $this->assertStringContainsString('Padi Sawah', $pdfText, 'Gagal: Komoditas Padi Sawah tidak di-render di PDF');
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $pdfText);

        // Cleanup
        unset($content, $parser, $pdfDocument, $pdfText);
        gc_collect_cycles();
    }

    public function test_controller_export_data_rekap_nte_tiap_komoditas_menjadi_file_csv()
    {
        $this->setupDummyData();

        $payload = [
            'type' => 'komoditas',
            'export' => 'csv',
            'periode' => 2025
        ];

        $request = RekapExportRequest::create('/api/rekap-nte/export', 'GET', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->export($request));

        $response->assertStatus(200);
        $response->assertStreamed();

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('REKAP NILAI TRANSAKSI EKONOMI', $csv);
        $this->assertStringContainsString('Komoditas', $csv);
        $this->assertStringContainsString('Produksi', $csv);
        $this->assertStringContainsString('Total NTE', $csv);
        
        $this->assertStringContainsString('Padi Sawah', $csv);
        $this->assertStringContainsString('GRAND TOTAL KESELURUHAN', $csv);
    }

    public function test_request_gagal_parameter_type_tidak_valid()
    {
        $request = new RekapExportRequest();
        $payload = [
            'type' => 'tipe_salah',
            'export' => 'pdf',
            'periode' => '2025'
        ];
        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('type', $errors);
    }

    public function test_service_handle_export_tipe_export_invalid()
    {
        $this->setupDummyData();
        $filter = [
            'type' => 'kph',
            'export' => 'jpg',
            'periode' => 2025
        ];

        $service = app(RekapNteService::class);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipe ekspor tidak valid');

        $service->handleExport($filter);
    }
}
<?php

namespace Tests\Unit;

use App\Models\Komoditas;
use App\Models\User;
use App\Http\Controllers\Api\LaporanBulkController;
use App\Http\Requests\Api\Laporan\CreateBulkLaporanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\TestResponse;
use App\Services\LaporanBulkService;
use Illuminate\Auth\Access\AuthorizationException;

class LaporanBulkCreateTest extends TestCase
{
    use RefreshDatabase;

    private function getKomoditasId(): string
    {
        $komoditas = Komoditas::first();

        if (!$komoditas) {
            $komoditas = Komoditas::create([
                'kode' => 'TEST-01',
                'nama_komoditas' => 'Komoditas Dummy',
                'jenis_komoditas' => 'Pangan'
            ]);
        }

        return $komoditas->id;
    }

    private function getValidLaporanPayload(): array
    {
        $idKomoditas = $this->getKomoditasId();

        return [
            'laporan' => [
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'oktober',
                    'modal' => 5000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa tanam pertama',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2024-10-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 150,
                            'usia_tanam' => 14
                        ]
                    ],
                    'lampiran' => [
                        UploadedFile::fake()->create('dokumen_lampiran_1.pdf', 100),
                        UploadedFile::fake()->create('dokumen_lampiran_2.pdf', 200),
                        UploadedFile::fake()->image('gambar_1.jpg')->size(500),
                        UploadedFile::fake()->create('dokumen_lampiran_3.pdf', 200),
                    ]
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'november',
                    'modal' => 2000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa panen',
                    'masa_panen' => [
                        [
                            'tanggal' => '2024-11-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 500,
                            'harga_satuan' => 12000
                        ]
                    ]
                ]
            ]
        ];
    }

    public function test_controller_store_berhasil_membuat_banyak_laporan_dalam_satu_requests()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');
        $payload = $this->getValidLaporanPayload();

        $request = CreateBulkLaporanRequest::create('/api/laporan/bulk', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(LaporanBulkController::class);

        $response = TestResponse::fromBaseResponse($controller->store($request));

        $response->assertStatus(201);
        $this->assertStringContainsString('massal berhasil dibuat', $response->json('message'));

        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][0]['id']]);
        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][1]['id']]);
    }

    public function test_request_gagal_tidak_ada_laporan_pada_request()
    {
        $request = new CreateBulkLaporanRequest();
        $payload = ['laporan' => []];
        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan', $errors);
    }

    public function test_request_gagal_data_tidak_valid()
    {
        $request = new CreateBulkLaporanRequest();
        $payload = $this->getValidLaporanPayload();
        $payload['laporan'][0]['bulan'] = 'bulan';
        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan.0.bulan', $errors);
    }

    public function test_request_gagal_data_lampiran_melebihi_aturan()
    {
        $request = new CreateBulkLaporanRequest();
        $payload = $this->getValidLaporanPayload();

        $banyakFile = [];
        for ($i = 1; $i <= 11; $i++) {
            $banyakFile[] = UploadedFile::fake()->create("dokumen_{$i}.pdf", 100);
        }
        $payload['laporan'][0]['lampiran'] = $banyakFile;

        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan.0.lampiran', $errors);
    }

    public function test_service_create_bulk_berhasil_membuat_banyak_laporan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');
        $payload = $this->getValidLaporanPayload();

        $service = app(LaporanBulkService::class);
        $hasil = $service->createBulk($payload['laporan'], $user, $payload['laporan']);

        $this->assertIsArray($hasil);
        $this->assertArrayHasKey('sukses', $hasil);
        $this->assertCount(2, $hasil['sukses']);
        $this->assertCount(0, $hasil['gagal']);

        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][0]['id']]);
        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][1]['id']]);
    }

    public function test_service_create_bulk_gagal_role_tidak_ada_akses()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->auditor()->create();
        $this->actingAs($user, 'api');
        $payload = $this->getValidLaporanPayload();

        $this->expectException(AuthorizationException::class);

        $service = app(LaporanBulkService::class);
        $service->createBulk($payload['laporan'], $user, $payload['laporan']);
    }
}
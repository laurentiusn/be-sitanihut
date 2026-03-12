<?php

namespace Tests\Unit;

use App\Models\Laporan;
use App\Models\Komoditas;
use App\Models\User;
use App\Http\Controllers\Api\RekapNteController;
use App\Http\Requests\Api\Rekap\RekapNteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class RekapProduksiTest extends TestCase
{
    use RefreshDatabase;

    private function setupDummyData()
    {
        $komoditas = [
            ['kode' => 'PDI-01', 'nama_komoditas' => 'Padi Sawah', 'jenis_komoditas' => 'Pangan'],
            ['kode' => 'JGN-01', 'nama_komoditas' => 'Jagung Hibrida', 'jenis_komoditas' => 'Pangan'],
            ['kode' => 'KDL-01', 'nama_komoditas' => 'Kedelai', 'jenis_komoditas' => 'Pangan'],
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

    public function test_controller_index_melihat_data_produksi_tiap_kph()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $this->setupDummyData();

        $payload = [
            'type' => 'kph',
            'periode' => '2025'
        ];

        $request = RekapNteRequest::create('/api/rekap-nte', 'GET', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rekap' => [
                    'data' => [
                        '*' => [
                            'nama_kph',
                            'produksi' => [
                                '*' => [
                                    'komoditas',
                                    'jumlah'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->json('data.rekap.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data rekap tidak boleh kosong');
        
        $this->assertArrayHasKey('nama_kph', $data[0], "Key 'nama_kph' tidak ditemukan.");
        $this->assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan.");
    }

    public function test_controller_index_melihat_data_produksi_tiap_kth()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->auditor()->create();
        $this->actingAs($user, 'api');

        $this->setupDummyData();

        $payload = [
            'type' => 'kth',
            'periode' => '2025'
        ];

        $request = RekapNteRequest::create('/api/rekap-nte', 'GET', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rekap' => [
                    'data' => [
                        '*' => [
                            'nama_kth',
                            'asal_kph',
                            'produksi' => [
                                '*' => [
                                    'komoditas',
                                    'jumlah'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->json('data.rekap.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data rekap tidak boleh kosong');
    }

    public function test_controller_index_melihat_data_produksi_tiap_petani()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $this->setupDummyData();

        $payload = [
            'type' => 'petani',
            'periode' => '2025',
            'search' => 'KTH Testing Rekap'
        ];

        $request = RekapNteRequest::create('/api/rekap-nte', 'GET', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rekap' => [
                    'data' => [
                        '*' => [
                            'nama_petani',
                            'asal_kth',
                            'asal_kph',
                            'produksi' => [
                                '*' => [
                                    'komoditas',
                                    'jumlah'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->json('data.rekap.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data rekap tidak boleh kosong');
    }

    public function test_controller_index_melihat_data_produksi_tiap_komoditas()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $this->setupDummyData();

        $payload = [
            'type' => 'komoditas',
            'periode' => '2025'
        ];

        $request = RekapNteRequest::create('/api/rekap-nte', 'GET', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(RekapNteController::class);

        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'rekap' => [
                    'data' => [
                        '*' => [
                            'komoditas',
                            'produksi'
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->json('data.rekap.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data rekap tidak boleh kosong');
        
        $this->assertArrayHasKey('komoditas', $data[0], "Key 'komoditas' tidak ditemukan.");
        $this->assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan.");
    }
}
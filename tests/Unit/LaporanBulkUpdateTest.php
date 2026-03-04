<?php

namespace Tests\Unit;

use App\Models\Laporan;
use App\Models\User;
use App\Models\Komoditas;
use App\Http\Controllers\Api\LaporanBulkController;
use App\Http\Requests\Api\Laporan\UpdateBulkLaporanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\LaporanBulkService;

class LaporanBulkUpdateTest extends TestCase
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

    public function test_controller_update_berhasil_memperbarui_banyak_laporan_dalam_satu_requests()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');
        $idKomoditas = $this->getKomoditasId();
        
        $laporans = Laporan::factory()->count(2)
            ->withMasaTanamPanen(1)
            ->withLampiran(1)
            ->create([
                'id_user' => $user->id,
                'status' => 'belum diajukan'
            ]);
        
        $payload = [
            'laporan' => [
                [
                    'id'                => $laporans[0]->id,
                    'modal'             => 5000000, 
                    'catatan_petani'    => 'Revisi modal laporan pertama',
                    'periode'           => '2025',
                    'bulan'             => 'juni',
                    'masa_tanam'        => [
                        [
                            'tanggal' => '2025-06-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 120,
                            'usia_tanam' => 2
                        ]
                    ],
                    'masa_panen'        => [
                        [
                            'tanggal' => '2025-06-15',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 300,
                            'harga_satuan' => 15000
                        ]
                    ],
                    'lampiran_existing' => [],
                    'lampiran_new'      => [
                        UploadedFile::fake()->create('laporan1_baru.pdf', 200)
                    ]
                ],
                [
                    'id'                => $laporans[1]->id,
                    'modal'             => 8500000, 
                    'catatan_petani'    => 'Ada tambahan pupuk di laporan kedua',
                    'periode'           => '2025',
                    'bulan'             => 'agustus',
                    'masa_tanam'        => [
                        [
                            'tanggal' => '2025-08-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 200,
                            'usia_tanam' => 3
                        ]
                    ],
                    'masa_panen'        => [
                        [
                            'tanggal' => '2025-08-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 400,
                            'harga_satuan' => 18000
                        ]
                    ],
                    'lampiran_existing' => [],
                    'lampiran_new'      => [
                        UploadedFile::fake()->create('laporan2_baru.pdf', 250)
                    ]
                ]
            ]
        ];

        $request = UpdateBulkLaporanRequest::create('/api/laporan/bulk', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(LaporanBulkController::class);

        $response = TestResponse::fromBaseResponse($controller->update($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('Update laporan massal selesai', $response->json('message'));

        $sukses = $response->json('data.sukses');
        $ditolak = $response->json('data.ditolak');

        $this->assertCount(2, $sukses, 'Harusnya ada 2 laporan yang berhasil diperbarui');
        $this->assertEmpty($ditolak, 'Harusnya kosong');
        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][0]['id']]);
        $this->assertDatabaseHas('laporan', ['id' => $payload['laporan'][1]['id']]);
    }

    public function test_request_gagal_tidak_menyertakan_laporan_pada_request()
    {
        $request = new UpdateBulkLaporanRequest();
        $payload = ['laporan' => []];
        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan', $errors);
    }

    public function test_request_gagal_data_laporan_tidak_valid()
    {
        $user = User::factory()->petani()->create();
        $this->getKomoditasId();
        $laporans = Laporan::factory()->count(2)
            ->withMasaTanamPanen(1)
            ->withLampiran(1)
            ->create([
                'id_user' => $user->id,
                'status' => 'belum diajukan'
            ]);

        $existingLampiran = $laporans[0]->lampiran()->first()->id;
        $newLampiran = [];
        for ($i = 0; $i < 10; $i++) {
            $newLampiran[] = UploadedFile::fake()->create("file_baru_{$i}.pdf", 100);
        }
        $payload = [
            'laporan' => [
                [
                    'id'    => $laporans[0]->id,
                    'bulan' => 'bulan_rahasia',
                    'modal' => -500,
                    'lampiran_existing' => [$existingLampiran],
                    'lampiran_new' => $newLampiran
                ],
                [
                    'id'    => $laporans[1]->id,
                    'bulan' => 'juli',
                    'modal' => 1000000
                ]
            ]
        ];

        $request = new UpdateBulkLaporanRequest();
        $request->replace(['laporan' => $payload['laporan']]);
        $validator = Validator::make($payload, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan.0.bulan', $errors, 'Validasi bulan harusnya gagal');
        $this->assertArrayHasKey('laporan.0.modal', $errors, 'Validasi modal harusnya gagal');

        $this->assertArrayHasKey('laporan.0.lampiran', $errors, 'Validasi total lampiran harusnya gagal');
        
        $this->assertStringContainsString('Total lampiran tidak boleh lebih dari 10', $errors['laporan.0.lampiran'][0]);

        $this->assertArrayNotHasKey('laporan.1.bulan', $errors);
        $this->assertArrayNotHasKey('laporan.1.modal', $errors);
    }

    public function test_service_update_bulk_gagal_petani_tidak_bisa_memperbarui_laporan_petani_lain()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');
        $this->getKomoditasId();

        $petaniLain = User::factory()->petani()->create();
        $laporanOrangLain = Laporan::factory()->count(2)->create([
            'id_user' => $petaniLain->id,
            'status' => 'belum diajukan',
            'bulan' => 'juni',
            'modal' => 1000000
        ]);

        $payload = [
            'laporan' => [
                [
                    'id'    => $laporanOrangLain[0]->id,
                    'modal' => 2000000,
                    'bulan' => 'juli',
                ],
                [
                    'id'    => $laporanOrangLain[1]->id,
                    'modal' => 120000,
                    'bulan' => 'agustus',
                ]
            ]
        ];
        $service = app(LaporanBulkService::class);
        $hasil = $service->updateBulk($payload['laporan'], $user, []);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertCount(2, $hasil['ditolak']);

        $this->assertDatabaseHas('laporan',[
            'id' => $laporanOrangLain[0]->id,
            'modal' => $laporanOrangLain[0]->modal,
            'bulan' => $laporanOrangLain[0]->bulan
        ]);
        $this->assertDatabaseHas('laporan',[
            'id' => $laporanOrangLain[1]->id,
            'modal' => $laporanOrangLain[1]->modal,
            'bulan' => $laporanOrangLain[1]->bulan
        ]);
    }

    public function test_service_update_bulk_gagal_memperbarui_laporan_yang_tidak_ada()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');
        $this->getKomoditasId();
        $laporanId1 = Str::uuid()->toString();
        $laporanId2 = Str::uuid()->toString();

        $payload = [
            'laporan' => [
                [
                    'id'    => $laporanId1,
                    'modal' => 2000000
                ],
                [
                    'id'    => $laporanId2,
                    'modal' => 120000
                ]
            ]
        ];

        $service = app(LaporanBulkService::class);
        $hasil = $service->updateBulk($payload['laporan'], $user, []);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertCount(2, $hasil['ditolak']);

        $this->assertDatabaseMissing('laporan', ['id' => $laporanId1]);
        $this->assertDatabaseMissing('laporan', ['id' => $laporanId2]);
    }

    public function test_service_update_bulk_gagal_status_laporan_tidak_belum_diajukan_atau_ditolak()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        // And: Laporan yang ingin diperbarui berstatus selain 'belum diajukan' atau 'ditolak'
        $laporanDisetujui = Laporan::factory()->create([
            'id_user'   => $user->id,
            'status'    => 'disetujui'
        ]);

        $laporanMenunggu = Laporan::factory()->create([
            'id_user'   => $user->id,
            'status'    => 'menunggu'
        ]);

        $payload = [
            'laporan' => [
                [
                    'id'    => $laporanDisetujui->id,
                    'modal' => 2000000
                ],
                [
                    'id'    => $laporanMenunggu->id,
                    'modal' => 5000
                ]
            ]
        ];

        $service = app(LaporanBulkService::class);
        $hasil = $service->updateBulk($payload['laporan'], $user, []);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertCount(2, $hasil['ditolak']);
    }

    public function test_service_update_bulk_terdapat_laporan_yang_berhasil_diperbarui_dan_tidak_berhasil_diperbarui()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $laporanValid = Laporan::factory()->create([
            'id_user'   => $user->id,
            'status'    => 'belum diajukan'
        ]);

        $laporanInvalidStatus = Laporan::factory()->create([
            'id_user'   => $user->id,
            'status'    => 'disetujui'
        ]);

        $payload = [
            'laporan' => [
                [
                    'id'    => $laporanValid->id,
                    'modal' => 1500000
                ],
                [
                    'id'    => $laporanInvalidStatus->id,
                    'modal' => 2000000
                ]
            ]
        ];

        $service = app(LaporanBulkService::class);
        $hasil = $service->updateBulk($payload['laporan'], $user, []);

        $this->assertCount(1, $hasil['sukses']);
        $this->assertCount(1, $hasil['ditolak']);
    }
}
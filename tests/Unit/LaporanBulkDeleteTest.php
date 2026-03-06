<?php

namespace Tests\Unit;

use App\Models\Laporan;
use App\Models\User;
use App\Http\Controllers\Api\LaporanBulkController;
use App\Http\Requests\Api\Laporan\DestroyBulkLaporanRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\LaporanBulkService;

class LaporanBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_destroy_berhasil_menghapus_banyak_laporan_dalam_satu_request()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $laporans = Laporan::factory()->count(2)->create([
            'id_user' => $user->id,
            'status' => 'belum diajukan'
        ]);

        $payload = [
            'laporan_ids' => [
                $laporans[0]->id,
                $laporans[1]->id
            ]
        ];

        $request = DestroyBulkLaporanRequest::create('/api/laporan/bulk', 'DELETE', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(LaporanBulkController::class);

        $response = TestResponse::fromBaseResponse($controller->destroy($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('Penghapusan laporan massal selesai', $response->json('message'));

        $sukses = $response->json('data.sukses');
        $this->assertIsArray($sukses);
        $this->assertCount(2, $sukses, 'Harusnya ada 2 laporan yang berhasil dihapus');
    }

    public function test_request_gagal_tidak_menyertakan_laporan_pada_request()
    {
        $request = new DestroyBulkLaporanRequest();
        $payload = ['laporan_ids' => []];
        $validator = Validator::make($payload, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('laporan_ids', $errors);
    }

    public function test_service_delete_bulk_gagal_menghapus_laporan_yang_tidak_ada()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $idLaporanPalsu = Str::uuid()->toString();
        $laporanIds = [$idLaporanPalsu];

        $service = app(LaporanBulkService::class);
        $hasil = $service->deleteBulk($laporanIds, $user);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertContains(
            $idLaporanPalsu,
            $hasil['tidak_ditemukan'],
            "ID {$idLaporanPalsu} seharusnya masuk ke dalam array 'tidak_ditemukan'"
        );
    }

    public function test_service_delete_bulk_gagal_petani_tidak_bisa_menghapus_laporan_petani_lain()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $petaniLain = User::factory()->petani()->create();
        $laporanOrangLain = Laporan::factory()->create([
            'id_user' => $petaniLain->id,
            'status' => 'belum diajukan'
        ]);

        $laporanIds = [$laporanOrangLain->id];

        $service = app(LaporanBulkService::class);
        $hasil = $service->deleteBulk($laporanIds, $user);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertNotEmpty($hasil['ditolak']);
        $this->assertEquals($laporanOrangLain->id, $hasil['ditolak'][0]['id'], 'Id Laporan tidak sama');
        $this->assertEquals("Akses ditolak: Laporan ini milik petani lain.", $hasil['ditolak'][0]['alasan'], 'Pesan penolakan tidak sesuai');
    }

    public function test_service_delete_bulk_gagal_menghapus_laporan_yang_sudah_dihapus_sebelumnya()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $laporan = Laporan::factory()->create([
            'id_user' => $user->id,
            'status' => 'belum diajukan'
        ]);
        $laporan->delete();

        $laporanIds = [$laporan->id];

        $service = app(LaporanBulkService::class);
        $hasil = $service->deleteBulk($laporanIds, $user);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertContains(
            $laporan->id,
            $hasil['sudah_dihapus_sebelumnya'],
            "ID laporan yang sudah di-soft-delete harusnya masuk ke array 'sudah_dihapus_sebelumnya'"
        );
    }

    public function test_service_delete_bulk_gagal_status_laporan_tidak_belum_diajukan_atau_ditolak()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $laporanDiproses = Laporan::factory()->create([
            'id_user' => $user->id,
            'status' => 'menunggu'
        ]);

        $laporanIds = [$laporanDiproses->id];

        $service = app(LaporanBulkService::class);
        $hasil = $service->deleteBulk($laporanIds, $user);

        $this->assertCount(0, $hasil['sukses']);
        $this->assertIsArray($hasil['ditolak']);
        $this->assertGreaterThanOrEqual(1, count($hasil['ditolak']), "Harusnya ada laporan yang masuk ke dalam array ditolak");
    }

    public function test_service_delete_bulk_terdapat_laporan_yang_berhasil_dihapus_dan_tidak_dihapus()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->petani()->create();
        $this->actingAs($user, 'api');

        $laporanValid = Laporan::factory()->create([
            'id_user' => $user->id,
            'status' => 'belum diajukan'
        ]);

        $laporanInvalidStatus = Laporan::factory()->create([
            'id_user' => $user->id,
            'status' => 'menunggu'
        ]);

        $idLaporanPalsu = Str::uuid()->toString();

        $laporanIds = [
            $laporanValid->id,
            $laporanInvalidStatus->id,
            $idLaporanPalsu
        ];

        $service = app(LaporanBulkService::class);
        $hasil = $service->deleteBulk($laporanIds, $user);

        $this->assertGreaterThanOrEqual(1, count($hasil['sukses']), "Harusnya ada yang berhasil");
        $this->assertGreaterThanOrEqual(1, count($hasil['ditolak']), "Harusnya ada yang ditolak karena status");
        $this->assertGreaterThanOrEqual(1, count($hasil['tidak_ditemukan']), "Harusnya ada yang tidak ditemukan");
    }
}
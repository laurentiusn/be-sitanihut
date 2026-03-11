<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Kth;
use App\Http\Controllers\Api\KthController;
use App\Http\Requests\Api\Kth\CreateKthRequest;
use App\Http\Requests\Api\Kth\UpdateKthRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use App\Services\KthService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class KthPembagianPenyuluhTest extends TestCase
{
    use RefreshDatabase;

    private function getKph()
    {
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
            $kph = DB::table('kph')->where('id', $kphId)->first();
        }
        return $kph;
    }

    private function createAnotherKph()
    {
        $kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id' => $kphId,
            'nama_kph' => 'KPH Dummy B',
            'wilayah'  => 'Wilayah Dummy B',
            'alamat'   => 'Alamat Wilayah',
            'nomor_wa' => '081367898877',
            'jenis'    => 'KPHK'
        ]);
        return DB::table('kph')->where('id', $kphId)->first();
    }

    private function getBaseKthPayload(): array
    {
        return [
            'nama_kth'      => 'KTH Suka Makmur',
            'desa'          => 'Suka Maju',
            'kecamatan'     => 'Way Lima',
            'kabupaten'     => 'Pesawaran',
            'koordinator'   => 'Bapak Budi',
            'nomor_wa'      => '08123456789'
        ];
    }

    public function test_controller_store_sekretariat_membuat_kth_sekaligus_menentukan_pengawas()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kph = $this->getKph();
        $penyuluh = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        
        $payload = array_merge($this->getBaseKthPayload(), [
            'id_kph' => $kph->id,
            'id_penyuluh' => $penyuluh->id
        ]);

        $request = CreateKthRequest::create('/api/kth', 'POST', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KthController::class);

        $response = TestResponse::fromBaseResponse($controller->store($request));

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id_penyuluh',
                'nama_penyuluh',
            ]
        ]);

        $this->assertNotNull($response->json('data.id_penyuluh'));
        $this->assertEquals($penyuluh->id, $response->json('data.id_penyuluh'));
    }

    public function test_controller_update_sekretariat_menentukan_penyuluh_sebagai_pengawas_pada_suatu_kth()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kph = $this->getKph();
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'nama_kth' => 'KTH Lama',
            'id_kph' => $kph->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $penyuluh = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $payload = [
            'nama_kth' => 'KTH Diperbarui',
            'id_kph' => $kph->id,
            'id_penyuluh' => $penyuluh->id
        ];

        $request = UpdateKthRequest::create('/api/kth/' . $kthId, 'PUT', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KthController::class);

        $result = $controller->update($request, $kthModel);
        $response = TestResponse::fromBaseResponse($result->response());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id_penyuluh',
                'nama_penyuluh',
            ]
        ]);

        $this->assertNotNull($response->json('data.id_penyuluh'));
        $this->assertEquals($penyuluh->id, $response->json('data.id_penyuluh'));
    }

    public function test_service_create_check_gagal_karena_penyuluh_tidak_ada()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kph = $this->getKph();
        User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $payload = array_merge($this->getBaseKthPayload(), [
            'id_kph' => $kph->id,
            'id_penyuluh' => Str::uuid()->toString()
        ]);

        $this->expectException(HttpResponseException::class);

        $service = app(KthService::class);
        $service->createCheck($payload, $user);
    }

    public function test_service_update_check_gagal_karena_penyuluh_tidak_ada()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kph = $this->getKph();
        User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'id_kph' => $kph->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $payload = [
            'id_kph' => $kph->id,
            'id_penyuluh' => Str::uuid()->toString()
        ];

        $this->expectException(HttpResponseException::class);

        $service = app(KthService::class);
        $service->updateCheck($payload, $user, $kthModel);
    }

    public function test_service_create_check_gagal_karena_penyuluh_dan_kph_tidak_sesuai()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kphA = $this->getKph();
        $kphB = $this->createAnotherKph();
        
        $penyuluhDiKphB = User::factory()->penyuluh()->create(['id_kph' => $kphB->id]);

        $payload = array_merge($this->getBaseKthPayload(), [
            'id_kph' => $kphA->id,
            'id_penyuluh' => $penyuluhDiKphB->id
        ]);

        $this->expectException(HttpResponseException::class);

        $service = app(KthService::class);
        $service->createCheck($payload, $user);
    }

    public function test_service_update_check_gagal_karena_penyuluh_dan_kph_tidak_sesuai()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $kphA = $this->getKph();
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'id_kph' => $kphA->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $kphB = $this->createAnotherKph();
        $penyuluhDiKphB = User::factory()->penyuluh()->create(['id_kph' => $kphB->id]);

        $payload = [
            'id_kph' => $kphA->id,
            'id_penyuluh' => $penyuluhDiKphB->id
        ];

        $this->expectException(HttpResponseException::class);

        $service = app(KthService::class);
        $service->updateCheck($payload, $user, $kthModel);
    }

    public function test_controller_store_penyuluh_membuat_kth()
    {
        $kph = $this->getKph();
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $this->actingAs($user, 'api');

        $payload = $this->getBaseKthPayload();
        
        $request = CreateKthRequest::create('/api/kth', 'POST', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KthController::class);

        $response = TestResponse::fromBaseResponse($controller->store($request));

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id_penyuluh',
                'nama_penyuluh',
            ]
        ]);

        $this->assertEquals($user->id, $response->json('data.id_penyuluh'));
    }

    public function test_controller_update_penyuluh_memperbarui_kth_yang_diawasi()
    {
        $kph = $this->getKph();
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $this->actingAs($user, 'api');

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'nama_kth' => 'KTH Binaan Saya',
            'id_penyuluh' => $user->id,
            'id_kph' => $kph->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $payload = ['nama_kth' => 'KTH Binaan Saya (Update)'];

        $request = UpdateKthRequest::create('/api/kth/' . $kthId, 'PUT', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KthController::class);

        $result = $controller->update($request, $kthModel);
        $response = TestResponse::fromBaseResponse($result->response());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id_penyuluh',
                'nama_penyuluh',
            ]
        ]);
    }

    public function test_controller_index_penyuluh_melihat_list_kth_yang_diawasi()
    {
        $kph = $this->getKph();
        /** @var \App\Models\User $user */        
        $user = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $this->actingAs($user, 'api');

        $penyuluhLain = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);

        Kth::factory()->count(2)->create([
            'id_penyuluh' => $user->id,
            'id_kph' => $kph->id
        ]);

        Kth::factory()->count(3)->create([
            'id_penyuluh' => $penyuluhLain->id,
            'id_kph' => $kph->id
        ]);

        $request = Request::create('/api/kth', 'GET');
        $request->setUserResolver(fn() => $user);
        $request->headers->set('Accept', 'application/json');

        $controller = app(KthController::class);

        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data.data'));
        $response->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id_penyuluh',
                        'nama_penyuluh',
                    ]
                ]
            ]
        ]);

        foreach ($response->json('data.data') as $kth) {
            $this->assertEquals($user->id, $kth['id_penyuluh']);
        }
    }

    public function test_service_update_check_gagal_penyuluh_ingin_mengubah_pengawas_dan_kph()
    {
        $kph = $this->getKph();
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $this->actingAs($user, 'api');

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'id_penyuluh' => $user->id,
            'id_kph' => $kph->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $kphBaru = $this->createAnotherKph();
        $penyuluhBaru = User::factory()->penyuluh()->create(['id_kph' => $kphBaru->id]);

        $payload = [
            'id_penyuluh' => $penyuluhBaru->id,
            'id_kph' => $kphBaru->id
        ];

        $this->expectException(HttpResponseException::class);

        $service = app(KthService::class);
        $service->updateCheck($payload, $user, $kthModel);
    }

    public function test_controller_destroy_penyuluh_gagal_menghapus_kth_yang_tidak_diawasi_olehnya()
    {
        $kph = $this->getKph();
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $this->actingAs($user, 'api');

        $penyuluhLain = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert(array_merge($this->getBaseKthPayload(), [
            'id' => $kthId,
            'id_penyuluh' => $penyuluhLain->id,
            'id_kph' => $kph->id
        ]));
        $kthModel = Kth::findOrFail($kthId);

        $controller = app(KthController::class);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $controller->destroy($kthModel);
    }
}
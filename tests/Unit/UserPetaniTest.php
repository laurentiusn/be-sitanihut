<?php

namespace Tests\Unit;

use App\Helpers\RoleHelper;
use App\Models\User;
use App\Http\Controllers\Api\UserPetaniController;
use App\Http\Middleware\PJ_Penyuluh;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use App\Exception\PostException;
use Illuminate\Http\Request;

class UserPetaniTest extends TestCase
{
    use RefreshDatabase;

    protected function setDataDummy(): array
    {
        $kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $kphId, 
            'nama_kph'   => 'KPH Pengujian Petani',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 99, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999),
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $penyuluhUser = User::factory()->penyuluh()->withDetail()->create([
            'id_kph' => $kphId
        ]);

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id'          => $kthId,
            'nama_kth'    => 'KTH Pengujian',
            'desa'        => 'Desa Pengujian',
            'kecamatan'   => 'Kecamatan Testing',
            'kabupaten'   => 'Kabupaten Test',
            'koordinator' => 'Bapak Tester',
            'nomor_wa'    => '089988776655',
            'id_kph'      => $kphId,
            'id_penyuluh' => $penyuluhUser->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        $petani = User::factory()->petani()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user' => 'Zainal Petani Pengujian'
        ])->create([
            'id_kph' => $kphId,
            'id_kth' => $kthId,
        ]);

        return [
            'kphId' => $kphId,
            'kthId' => $kthId,
            'penyuluh' => $penyuluhUser,
            'petaniId' => $petani->id
        ];
    }

    public function test_controller_index_sekretariat_berhasil_melihat_list_petani()
    {
        $this->setDataDummy();
        $user = User::factory()->sekretariat()->withDetail()->create();

        $request = Request::create('/api/user/petani', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPetaniController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $this->assertTrue(collect($data)->every(fn ($petani) => $petani['slug'] === 'petani'));
    }

    public function test_controller_index_auditor_berhasil_melihat_list_petani_dengan_filter()
    {
        $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search'            => 'Zainal',
            'jenis_kelamin'     => 'pria',
            'sort_by'           => 'nama_user',
            'sort_direction'    => 'desc'
        ];

        $request = Request::create('/api/user/petani', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPetaniController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        $this->assertTrue(collect($data)->every(fn ($petani) => $petani['slug'] === 'petani'));
    }

    public function test_controller_index_penanggung_jawab_berhasil_melihat_list_petani()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $refs['kphId']
        ]);

        $request = Request::create('/api/user/petani', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPetaniController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($petani) => $petani['slug'] === 'petani'));

        foreach ($data as $petani) {
            $this->assertEquals($user->id_kph, $petani['id_kph'], 'Terjadi kebocoran data! Penanggung Jawab melihat Petani dari KPH lain.');
        }
    }

    public function test_controller_index_penyuluh_berhasil_melihat_list_petani()
    {
        $refs = $this->setDataDummy();
        $user = clone $refs['penyuluh'];

        $request = Request::create('/api/user/petani', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPetaniController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($petani) => $petani['slug'] === 'petani'));

        $found = collect($data)->contains('id', $refs['petaniId']);
        $this->assertTrue($found, 'Penyuluh tidak dapat melihat Petani di KTH binaannya sendiri.');
    }

    public function test_controller_show_sekretariat_berhasil_melihat_detail_petani()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->sekretariat()->withDetail()->create();
        $idRolePetani = RoleHelper::id('petani');
        $petaniModel = User::findOrFail($refs['petaniId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPetaniController::class);
        $responseResource = $controller->show($petaniModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);

        $response->assertJsonPath('data.id_role', $idRolePetani);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id_user', $data, "Detail petani tidak memiliki key 'id_user'.");
    }

    public function test_controller_show_auditor_berhasil_melihat_detail_petani()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();
        $idRolePetani = RoleHelper::id('petani');
        $petaniModel = User::findOrFail($refs['petaniId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPetaniController::class);
        $responseResource = $controller->show($petaniModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $response->assertJsonPath('data.id_role', $idRolePetani);
        $data = $response->json('data');
        $this->assertArrayHasKey('id_user', $data);
    }

    public function test_service_authorize_detail_view_penanggung_jawab_berhasil_melihat_detail_petani()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $refs['kphId']
        ]);
        $petaniModel = User::findOrFail($refs['petaniId']);

        $service = app(UserService::class);
        
        $result = $service->authorizeDetailView($petaniModel, clone $user, 'petani');
        $this->assertTrue($result);
    }

    public function test_service_authorize_detail_view_penyuluh_berhasil_melihat_detail_petani()
    {
        $refs = $this->setDataDummy();
        $user = clone $refs['penyuluh'];
        $petaniModel = User::findOrFail($refs['petaniId']);

        $service = app(UserService::class);

        $result = $service->authorizeDetailView($petaniModel, clone $user, 'petani');
        $this->assertTrue($result);
    }

    public function test_controller_index_gagal_melihat_list_petani_karena_filter_tidak_valid()
    {
        $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search'            => str_repeat('A', 105),
            'jenis_kelamin'     => 'alien',
            'sort_by'           => 'nomor_induk',
            'sort_direction'    => 'acak'
        ];

        $request = Request::create('/api/user/petani', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPetaniController::class);
        
        $this->expectException(ValidationException::class);

        $controller->index($request);
    }

    public function test_middleware_gagal_melihat_list_petani_karena_tidak_ada_hak_akses()
    {
        $this->setDataDummy();
        $user = User::factory()->petani()->withDetail()->create();

        $request = Request::create('/api/user/petani', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = app(PJ_Penyuluh::class);

        $this->expectException(PostException::class);
        $this->expectExceptionMessage('Unauthorized');

        $middleware->handle($request, function () {});
    }

    public function test_service_authorize_detail_view_gagal_melihat_detail_petani_karena_tidak_ada_hak_akses()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->petani()->withDetail()->create();
        $petaniModel = User::findOrFail($refs['petaniId']);

        $service = app(UserService::class);

        $result = $service->authorizeDetailView($petaniModel, clone $user, 'petani');
        $this->assertFalse($result);
    }
}
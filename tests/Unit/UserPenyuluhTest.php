<?php

namespace Tests\Unit;

use App\Helpers\RoleHelper;
use App\Models\User;
use App\Http\Controllers\Api\UserPenyuluhController;
use App\Http\Middleware\PJ;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use App\Exception\PostException;
use Illuminate\Http\Request;

class UserPenyuluhTest extends TestCase
{
    use RefreshDatabase;

    protected function setDataDummy(): array
    {
        $kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $kphId, 
            'nama_kph'   => 'KPH Pengujian Penyuluh',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 88, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999), 
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $penyuluh = User::factory()->penyuluh()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user'     => 'Tirta Penyuluh Pengujian'
        ])->create([
            'id_kph' => $kphId
        ]);

        return [
            'kphId' => $kphId,
            'penyuluhId' => $penyuluh->id
        ];
    }

    public function test_controller_index_sekretariat_berhasil_melihat_list_penyuluh()
    {
        $this->setDataDummy();
        $user = User::factory()->sekretariat()->withDetail()->create();

        $request = Request::create('/api/user/penyuluh', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPenyuluhController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data list penyuluh kosong padahal ekspektasinya ada.');
        $this->assertTrue(collect($data)->every(fn ($penyuluh) => $penyuluh['slug'] === 'penyuluh'));
    }

    public function test_controller_index_auditor_berhasil_melihat_list_penyuluh_dengan_filter()
    {
        $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search' => 'Tirta',
            'jenis_kelamin' => 'pria',
            'sort_by' => 'nama_user',
            'sort_direction' => 'desc'
        ];

        $request = Request::create('/api/user/penyuluh', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPenyuluhController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($penyuluh) => $penyuluh['slug'] === 'penyuluh'));
    }

    public function test_controller_index_penanggung_jawab_berhasil_melihat_list_penyuluh()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $refs['kphId']
        ]);

        $request = Request::create('/api/user/penyuluh', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPenyuluhController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($penyuluh) => $penyuluh['slug'] === 'penyuluh'));

        foreach ($data as $penyuluh) {
            $this->assertEquals($user->id_kph, $penyuluh['id_kph'], 'Terjadi kebocoran data! Penanggung Jawab melihat Penyuluh dari KPH lain.');
        }
    }

    public function test_controller_show_sekretariat_berhasil_melihat_detail_penyuluh()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->sekretariat()->withDetail()->create();
        $idRolePenyuluh = RoleHelper::id('penyuluh');
        $penyuluhModel = User::findOrFail($refs['penyuluhId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPenyuluhController::class);
        $responseResource = $controller->show($penyuluhModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $response->assertJsonPath('data.id_role', $idRolePenyuluh);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id_user', $data, "Detail penyuluh tidak memiliki key 'id_user'.");
    }

    public function test_controller_show_auditor_berhasil_melihat_detail_penyuluh()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();
        $idRolePenyuluh = RoleHelper::id('penyuluh');
        $penyuluhModel = User::findOrFail($refs['penyuluhId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPenyuluhController::class);
        $responseResource = $controller->show($penyuluhModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $response->assertJsonPath('data.id_role', $idRolePenyuluh);
        $data = $response->json('data');
        $this->assertArrayHasKey('id_user', $data);
    }

    public function test_service_authorize_detail_view_penanggung_jawab_berhasil_melihat_detail_penyuluh()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $refs['kphId']
        ]);
        $penyuluhModel = User::findOrFail($refs['penyuluhId']);

        $service = app(UserService::class);

        $result = $service->authorizeDetailView($penyuluhModel, clone $user, 'penyuluh');
        $this->assertTrue($result);
    }

    public function test_controller_index_gagal_melihat_list_penyuluh_karena_filter_tidak_valid()
    {
        $this->setDataDummy();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search' => str_repeat('A', 105),
            'jenis_kelamin' => 'alien',
            'sort_by' => 'nomor_induk',
            'sort_direction' => 'acak'
        ];

        $request = Request::create('/api/user/penyuluh', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPenyuluhController::class);
        
        $this->expectException(ValidationException::class);

        $controller->index($request);
    }

    public function test_middleware_gagal_melihat_list_penyuluh_karena_tidak_ada_hak_akses()
    {
        $this->setDataDummy();
        $user = User::factory()->penyuluh()->withDetail()->create();

        $request = Request::create('/api/user/penyuluh', 'GET');
        $request->setUserResolver(fn() => $user);

        $middleware = app(PJ::class);

        $this->expectException(PostException::class);
        $this->expectExceptionMessage('Unauthorized');

        $middleware->handle($request, function () {});
    }

    public function test_service_authorize_detail_view_gagal_melihat_detail_penyuluh_karena_tidak_ada_hak_akses()
    {
        $refs = $this->setDataDummy();
        $user = User::factory()->petani()->withDetail()->create();
        $penyuluhModel = User::findOrFail($refs['penyuluhId']);

        $service = app(UserService::class);

        $result = $service->authorizeDetailView($penyuluhModel, clone $user, 'penyuluh');
        $this->assertFalse($result);
    }
}
<?php

namespace Tests\Unit;

use App\Helpers\RoleHelper;
use App\Models\User;
use App\Http\Controllers\Api\UserPJController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class UserPJTest extends TestCase
{
    use RefreshDatabase;

    protected function setupEcosystem(): array
    {
        $kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $kphId, 
            'nama_kph'   => 'KPH Pengujian PJ',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 77, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999), 
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $pj = User::factory()->penanggungJawab()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user'     => 'Zainal PJ Pengujian'
        ])->create([
            'id_kph' => $kphId
        ]);

        return [
            'kphId' => $kphId,
            'pjId'  => $pj->id
        ];
    }

    public function test_controller_index_sekretariat_berhasil_melihat_list_penanggung_jawab()
    {
        $this->setupEcosystem();
        $user = User::factory()->sekretariat()->withDetail()->create();

        $request = Request::create('/api/user/penanggung-jawab', 'GET');
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPJController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data list penanggung jawab kosong padahal ekspektasinya ada.');
        $this->assertTrue(collect($data)->every(fn ($pj) => $pj['slug'] === 'penanggung-jawab'));
    }

    public function test_controller_index_auditor_berhasil_melihat_list_penanggung_jawab_dengan_filter()
    {
        $this->setupEcosystem();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search' => 'Zainal',
            'jenis_kelamin' => 'pria',
            'sort_by' => 'nama_user',
            'sort_direction' => 'desc'
        ];

        $request = Request::create('/api/user/penanggung-jawab', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPJController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->every(fn ($pj) => $pj['slug'] === 'penanggung-jawab'));
    }

    public function test_controller_show_sekretariat_berhasil_melihat_detail_penanggung_jawab()
    {
        $refs = $this->setupEcosystem();
        $user = User::factory()->sekretariat()->withDetail()->create();
        $idRolePJ = RoleHelper::id('penanggung-jawab');
        $pjModel = User::findOrFail($refs['pjId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPJController::class);
        $responseResource = $controller->show($pjModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $response->assertJsonPath('data.id_role', $idRolePJ);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id_user', $data, "Detail penanggung jawab tidak memiliki key 'id_user'.");
    }

    public function test_controller_show_auditor_berhasil_melihat_detail_penanggung_jawab()
    {
        $refs = $this->setupEcosystem();
        $user = User::factory()->auditor()->withDetail()->create();
        $idRolePJ = RoleHelper::id('penanggung-jawab');
        $pjModel = User::findOrFail($refs['pjId']);

        request()->setUserResolver(fn () => $user);

        $controller = app(UserPJController::class);
        $responseResource = $controller->show($pjModel);
        
        $response = TestResponse::fromBaseResponse($responseResource->response());

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $response->assertJsonPath('data.id_role', $idRolePJ);
        $data = $response->json('data');
        $this->assertArrayHasKey('id_user', $data);
    }

    public function test_controller_index_gagal_melihat_list_penanggung_jawab_karena_filter_tidak_valid()
    {
        $this->setupEcosystem();
        $user = User::factory()->auditor()->withDetail()->create();

        $payload = [
            'search' => str_repeat('A', 105),
            'jenis_kelamin' => 'alien',
            'sort_by' => 'nomor_induk',
            'sort_direction' => 'acak'
        ];

        $request = Request::create('/api/user/penanggung-jawab', 'GET', $payload);
        $request->setUserResolver(fn() => $user);

        $controller = app(UserPJController::class);
        
        $this->expectException(ValidationException::class);

        $controller->index($request);
    }
}
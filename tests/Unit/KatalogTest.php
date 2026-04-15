<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\KatalogController;
use App\Http\Requests\Api\Katalog\CreateKatalogRequest;
use App\Http\Requests\Api\Katalog\UpdateKatalogRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Katalog;
use App\Http\Middleware\Sekretariat_Penyuluh;
use Tests\TestCase;
use App\Exception\PostException;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class KatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    protected function setDummyKatalog($userId = null): string
    {
        Katalog::factory()->count(3)->create();

        $katalogId = Str::uuid()->toString();
        DB::table('catalog')->insert([
            'id'          => $katalogId,
            'name'        => 'Katalog Original',
            'description' => 'Deskripsi Katalog Original',
            'location'    => 'Bandar Lampung',
            'kth'         => 'KTH Pengujian',
            'cp1'         => '081122334455',
            'price'       => 50000,
            'is_active'   => 1,
            'id_user'     => $userId,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        return $katalogId;
    }

    public function test_controller_store_sekretariat_berhasil_membuat_katalog()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $image = UploadedFile::fake()->image('test_katalog.png');
        $payload = [
            'name'        => 'Katalog Baru Test',
            'description' => 'Katalog ini dibuat dari test',
            'kth'         => 'KTH Baru',
            'cp1'         => '0812345678',
            'price'       => 100000,
            'location'    => 'Jakarta',
            'is_active'   => 1,
        ];

        $request = CreateKatalogRequest::create('/api/katalog', 'POST', $payload, [], ['images' => [$image]]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->store($request));

        $response->assertStatus(201);
        $this->assertStringContainsString('berhasil ditambah', json_encode($response->json('message')));
    }

    public function test_controller_store_penyuluh_berhasil_membuat_katalog()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');

        $image = UploadedFile::fake()->image('test_katalog_penyuluh.png');
        $payload = [
            'name'        => 'Katalog Penyuluh Test',
            'description' => 'Katalog ini dibuat oleh penyuluh',
            'kth'         => 'KTH Binaan',
            'cp1'         => '0812345678',
            'price'       => 50000,
            'location'    => 'Lampung',
            'is_active'   => 1,
        ];

        $request = CreateKatalogRequest::create('/api/katalog', 'POST', $payload, [], ['images' => [$image]]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->store($request));

        $response->assertStatus(201);
        $this->assertStringContainsString('berhasil ditambah', json_encode($response->json('message')));
    }

    public function test_controller_update_penyuluh_memperbarui_katalog()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');
        
        $katalogId = $this->setDummyKatalog($user->id);
        $katalogModel = Katalog::findOrFail($katalogId);

        $image = UploadedFile::fake()->image('update_katalog.png');
        $payload = [
            'name'       => 'Katalog Diperbarui',
            'price'      => 75000,
            '_method'    => 'PUT',
        ];

        $request = UpdateKatalogRequest::create('/api/katalog/' . $katalogId, 'POST', $payload, [], ['images_new' => [$image]]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->update($request, $katalogModel));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diupdate', json_encode($response->json('message')));
        
        $updatedResponse = TestResponse::fromBaseResponse($controller->show($katalogModel));
        $updatedName = $updatedResponse->json('data.name');
        $this->assertEquals('Katalog Diperbarui', $updatedName);
    }

    public function test_controller_destroy_sekretariat_berhasil_menghapus_katalog()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');
        $katalogId = $this->setDummyKatalog();
        $katalogModel = Katalog::findOrFail($katalogId);

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->destroy($katalogModel));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil dihapus', json_encode($response->json('message')));
    }

    public function test_controller_index_penyuluh_melihat_list_katalog()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');

        $katalogId = Str::uuid()->toString();
        DB::table('catalog')->insert([
            'id'          => $katalogId,
            'name'        => 'Katalog Penyuluh',
            'description' => 'Deskripsi',
            'location'    => 'Lampung',
            'kth'         => 'KTH Test',
            'cp1'         => '081234567890',
            'price'       => 10000,
            'is_active'   => 1,
            'id_user'     => $user->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        $request = Request::create('/api/katalog', 'GET', ['search' => 'KTH Test', 'lokasi' => 'Lampung']);
        $request->setUserResolver(fn() => $user);

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertNotEmpty($data);
        
        $returnedIds = array_column($data, 'id');
        $count = DB::table('catalog')
            ->whereIn('id', $returnedIds)
            ->where('id_user', $user->id)
            ->count();
            
        $this->assertEquals(count($returnedIds), $count);
    }

    public function test_controller_index_pengguna_yang_tidak_login_melihat_list_katalog()
    {
        $this->setDummyKatalog();

        $request = Request::create('/api/katalog', 'GET');
        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_controller_show_pengguna_yang_tidak_login_melihat_detail_katalog()
    {
        $katalogId = $this->setDummyKatalog();
        $katalogModel = Katalog::findOrFail($katalogId);

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->show($katalogModel));

        $response->assertStatus(200);
        $this->assertStringContainsString('berhasil diambil', json_encode($response->json('message')));

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
    }

    public function test_controller_show_penyuluh_gagal_melihat_detail_katalog_bukan_miliknya()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');
        
        $otherUser = User::factory()->penyuluh()->create();
        $katalogId = $this->setDummyKatalog($otherUser->id);
        $katalogModel = Katalog::findOrFail($katalogId);

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->show($katalogModel));

        $response->assertStatus(403);
        $this->assertStringContainsString('Akses ditolak', json_encode($response->json('message')));
    }

    public function test_controller_update_penyuluh_gagal_memperbarui_katalog_yang_bukan_miliknya()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');
        
        $otherUser = User::factory()->penyuluh()->create();
        $katalogId = $this->setDummyKatalog($otherUser->id);
        $katalogModel = Katalog::findOrFail($katalogId);

        $payload = [
            'price' => 75000,
        ];

        $request = UpdateKatalogRequest::create('/api/katalog/' . $katalogId, 'PUT', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->update($request, $katalogModel));

        $response->assertStatus(403);
        $this->assertStringContainsString('Akses ditolak', json_encode($response->json('message')));
    }

    public function test_controller_destroy_penyuluh_gagal_menghapus_katalog_yang_bukan_miliknya()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');
        
        $otherUser = User::factory()->penyuluh()->create();
        $katalogId = $this->setDummyKatalog($otherUser->id);
        $katalogModel = Katalog::findOrFail($katalogId);

        $controller = app(KatalogController::class);
        $response = TestResponse::fromBaseResponse($controller->destroy($katalogModel));

        $response->assertStatus(403);
        $this->assertStringContainsString('Akses ditolak', json_encode($response->json('message')));
    }

    public function test_storerequest_gagal_membuat_katalog_karena_data_tidak_diisi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $request = CreateKatalogRequest::create('/api/katalog', 'POST', []);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }

    public function test_storerequest_gagal_membuat_katalog_karena_data_tidak_sesuai_aturan()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $invalidFiles = [];
        for ($i = 0; $i < 6; $i++) {
            $invalidFiles[] = UploadedFile::fake()->create("dokumen_ilegal_{$i}.pdf", 6000, 'application/pdf');
        }

        $payload = [
            'name'        => str_repeat('A', 105), 
            'description' => ['Ini', 'Bukan', 'String'],
            'kth'         => str_repeat('B', 55),
            'cp1'         => str_repeat('C', 55),
            'cp2'         => str_repeat('D', 55),
            'price'       => -10000,
            'location'    => ['Bukan', 'String'],
            'is_active'   => 'bukan_boolean_valid',
            'id_user'     => 'ini-jelas-bukan-format-uuid-yang-benar',
        ];

        $request = CreateKatalogRequest::create('/api/katalog', 'POST', $payload, [], ['images' => $invalidFiles]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }

    public function test_updaterequest_gagal_memperbarui_katalog_karena_data_tidak_valid()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->penyuluh()->create();
        $this->actingAs($user, 'api');
        
        $katalogId = $this->setDummyKatalog($user->id);

        $payload = [
            'price'   => -5000,
        ];

        $request = UpdateKatalogRequest::create('/api/katalog/' . $katalogId, 'PUT', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }

    public function test_middleware_gagal_mengelola_katalog_karena_tidak_memiliki_hak_akses()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->auditor()->create();

        $middleware = app(Sekretariat_Penyuluh::class);
        
        $request = Request::create('/api/katalog', 'POST');
        $request->setUserResolver(fn () => $user);

        $this->expectException(PostException::class);
        $this->expectExceptionMessage('Unauthorized access');

        $middleware->handle($request, function () {});
    }
}
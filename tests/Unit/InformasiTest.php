<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Informasi;
use App\Http\Controllers\Api\InformasiController;
use App\Http\Requests\Api\Informasi\CreateInformasiRequest;
use App\Http\Requests\Api\Informasi\UpdateInformasiRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class InformasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setDummyInformasi(?string $namaImage = null, ?string $pathImage = null): string
    {
        $informasiId = Str::uuid()->toString();

        DB::table('informasi')->insert([
            'id'         => $informasiId,
            'title'      => 'Informasi Original Test',
            'desc'       => '<p>Deskripsi original dari database.</p>',
            'date'       => '2026-05-05',
            'loc'        => 'Bandar Lampung',
            'organizer'  => 'Sekretariat Pusat',
            'nama_image' => $namaImage,
            'path_image' => $pathImage,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if ($pathImage) {
            \Illuminate\Support\Facades\Storage::disk('public')->put($pathImage, 'dummy content');
        }

        return $informasiId;
    }

    public function test_controller_store_sekretariat_berhasil_membuat_informasi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');

        $image = UploadedFile::fake()->image('test_informasi.png');
        $payload = [
            'title'     => 'Berita Baru Test',
            'desc'      => '<p>Ini adalah isi dari berita terbaru.</p>',
            'date'      => '2026-10-10',
            'loc'       => 'Jakarta Selatan',
            'organizer' => 'Panitia Event',
        ];

        $request = CreateInformasiRequest::create('/api/informasi', 'POST', $payload, [], ['image' => $image]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->store($request));

        $this->assertTrue(in_array($response->getStatusCode(), [200, 201]), "Ekspektasi 200 atau 201. Didapat: " . $response->getStatusCode());
        $message = $response->json('message') ?? '';
        $this->assertStringContainsStringIgnoringCase('berhasil', $message);
    }

    public function test_controller_update_sekretariat_memperbarui_informasi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');
        \Illuminate\Support\Facades\Storage::fake('public');

        $informasiId = $this->setDummyInformasi('dummy_informasi.png', 'informasi/dummy_informasi.png');
        $informasiModel = Informasi::findOrFail($informasiId);

        $image = UploadedFile::fake()->image('updated_informasi.png');
        $payload = [
            'title'        => 'Informasi Diperbarui',
            'loc'          => 'Bandung',
            '_method'      => 'PUT',
        ];

        $request = UpdateInformasiRequest::create('/api/informasi/' . $informasiId, 'POST', $payload, [], ['image' => $image]);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->update($request, $informasiModel));

        $response->assertStatus(200);
        $message = $response->json('message') ?? '';
        $this->assertStringContainsStringIgnoringCase('berhasil', $message);

        $informasi = Informasi::findOrFail($informasiId);
        $this->assertEquals('Informasi Diperbarui', $informasi->title);
        $this->assertEquals('Bandung', $informasi->loc);
        $this->assertEquals('updated_informasi.png', $informasi->nama_image);
    }

    public function test_controller_update_sekretariat_menghapus_gambar_pada_informasi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');
        \Illuminate\Support\Facades\Storage::fake('public');

        $informasiId = $this->setDummyInformasi('dummy_informasi.png', 'informasi/dummy_informasi.png');
        $informasiModel = Informasi::findOrFail($informasiId);

        $payload = [
            'remove_image' => true,
        ];

        $request = UpdateInformasiRequest::create('/api/informasi/' . $informasiId, 'PUT', $payload);
        $request->setUserResolver(fn() => $user);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));
        $request->validateResolved();

        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->update($request, $informasiModel));

        $response->assertStatus(200);
        $message = $response->json('message') ?? '';
        $this->assertStringContainsStringIgnoringCase('berhasil', $message);

        $informasi = Informasi::findOrFail($informasiId);
        $this->assertNull($informasi->nama_image, "Image nama_image tidak terhapus!");
        $this->assertNull($informasi->path_image, "Image path_image tidak terhapus!");
    }

    public function test_controller_destroy_sekretariat_berhasil_menghapus_informasi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $this->actingAs($user, 'api');
        \Illuminate\Support\Facades\Storage::fake('public');

        $informasiId = $this->setDummyInformasi('dummy_informasi.png', 'informasi/dummy_informasi.png');
        $informasiModel = Informasi::findOrFail($informasiId);

        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->destroy($informasiModel));

        $message = $response->json('message');
        $this->assertStringContainsString('berhasil dihapus', $message);

        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists('informasi/dummy_informasi.png'));
    }

    public function test_controller_index_pengguna_yang_tidak_login_melihat_list_informasi()
    {
        $this->setDummyInformasi();

        $request = Request::create('/api/informasi', 'GET', ['search' => 'test']);
        
        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->index($request));

        $response->assertStatus(200);

        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data, 'Data list informasi kosong.');
    }

    public function test_controller_show_pengguna_yang_tidak_login_melihat_detail_informasi()
    {
        $informasiId = $this->setDummyInformasi();
        $informasiModel = Informasi::findOrFail($informasiId);

        $controller = app(InformasiController::class);
        $response = TestResponse::fromBaseResponse($controller->show($informasiModel));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data, "Detail informasi (key 'id') tidak ditemukan.");
    }

    public function test_createrequest_gagal_membuat_informasi_karena_beberapa_data_tidak_diisi()
    {
        $payload = [];

        $request = CreateInformasiRequest::create('/api/informasi', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }

    public function test_createrequest_gagal_membuat_informasi_karena_data_melewati_batas_aturan()
    {
        $image = UploadedFile::fake()->create('test_informasi.pdf', 100, 'application/pdf');
        $payload = [
            'title'     => str_repeat('A', 260),
            'desc'      => ['array_bukan_string'],
            'date'      => 'sabtu, 10 Oktober 2026',
            'loc'       => str_repeat('B', 260),
            'organizer' => str_repeat('C', 260),
        ];

        $request = CreateInformasiRequest::create('/api/informasi', 'POST', $payload, [], ['image' => $image]);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }

    public function test_updaterequest_gagal_memperbarui_informasi_karena_data_tidak_valid()
    {
        $informasiId = $this->setDummyInformasi();

        $payload = [
            'date'    => 'bukan-tanggal',
        ];

        $request = UpdateInformasiRequest::create('/api/informasi/' . $informasiId, 'PUT', $payload);
        $request->setContainer(app());
        $request->setRedirector(app(\Illuminate\Routing\Redirector::class));

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->validateResolved();
    }
}
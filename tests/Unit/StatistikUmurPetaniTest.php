<?php

namespace Tests\Unit;

use App\Models\User;
use App\Http\Controllers\Api\StatistikController;
use App\Services\StatistikService;
use App\Helpers\RoleHelper;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Testing\TestResponse;

class StatistikUmurPetaniTest extends TestCase
{
    use RefreshDatabase;

    protected function insertDummyPetaniWithNik(string $nik)
    {
        return User::factory()
            ->petani()
            ->withDetail([
                'nomor_induk' => $nik,
                'jenis_kelamin' => 'pria'
            ])
            ->create();
    }

    public function test_controller_index_berhasil_melihat_informasi_umur_petani()
    {
        $this->seed(RoleSeeder::class);
        User::factory()->petani()->count(10)->withDetail()->create();

        $controller = app(StatistikController::class);
        $response = TestResponse::fromBaseResponse($controller->index());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'range_umur_petani'
            ]
        ]);
    }

    public function test_service_petani_baru_ditambahkan_dengan_nomor_induk_yang_valid()
    {
        $this->seed(RoleSeeder::class);
        User::factory()->petani()->count(10)->withDetail()->create();

        $rolePetaniId = RoleHelper::id('petani');
        $service = app(StatistikService::class);
        
        $initialRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->insertDummyPetaniWithNik('1801011508900001');

        $newRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->assertNotEquals($initialRangeData, $newRangeData);
    }

    public function test_service_petani_baru_ditambahkan_dengan_nomor_induk_yang_tidak_lengkap()
    {
        $this->seed(RoleSeeder::class);
        User::factory()->petani()->count(10)->withDetail()->create();

        $rolePetaniId = RoleHelper::id('petani');
        $service = app(StatistikService::class);

        $initialRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->insertDummyPetaniWithNik('12345678');

        $newRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->assertEquals($initialRangeData, $newRangeData);
    }

    public function test_service_petani_baru_ditambahkan_serta_nomor_induk_dengan_tanggal_lahir_yang_tidak_sesuai()
    {
        $this->seed(RoleSeeder::class);
        User::factory()->petani()->count(10)->withDetail()->create();

        $rolePetaniId = RoleHelper::id('petani');
        $service = app(StatistikService::class);

        $initialRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->insertDummyPetaniWithNik('1801019999900001');

        $newRangeData = $service->getRangeUmurPetani($rolePetaniId);

        $this->assertEquals(
            $initialRangeData, 
            $newRangeData, 
            "Ekspektasi rentang umur TIDAK berubah karena NIK tanggal lahir tidak valid, namun nilainya malah berubah."
        );
    }
}
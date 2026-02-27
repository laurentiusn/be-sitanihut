<?php

namespace Database\Factories;

use App\Models\Kth;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kth>
 */
class KthFactory extends Factory
{
    protected $model = Kth::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $kph = DB::table('kph')->inRandomOrder()->first();
        
        // Ambil penyuluh secara acak, atau buat baru jika belum ada
        $penyuluh = DB::table('users')
            ->join('role', 'users.id_role', '=', 'role.id')
            ->where('role.slug', 'penyuluh')
            ->select('users.id')
            ->inRandomOrder()
            ->first();
            
        $idPenyuluh = $penyuluh ? $penyuluh->id : User::factory()->penyuluh()->create()->id;

        $faker = fake('id_ID');

        $namaDesa = preg_replace('/^(Kab\.\s|Kota\s)/i', '', $faker->city());

        return [
            'id' => Str::uuid()->toString(),
            'nama_kth' => 'KTH ' . $namaDesa,
            'desa' => $namaDesa,
            'kecamatan' => preg_replace('/^(Kab\.\s|Kota\s)/i', '', $faker->city()),
            'kabupaten' => $faker->city(),
            'koordinator' => $faker->name(),
            'nomor_wa' => $faker->phoneNumber(),
            'id_penyuluh' => $idPenyuluh,
            'id_kph' => $kph ? $kph->id : null,
        ];
    }
}
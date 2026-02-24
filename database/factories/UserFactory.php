<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Helper privat untuk memastikan Role ada di DB.
     * Mencegah duplikasi kode di setiap state.
     */
    private function ensureRoleExists(string $slug, string $namaRole): string
    {
        $role = DB::table('role')->where('slug', $slug)->first();

        if (!$role) {
            $id = Str::uuid()->toString();
            DB::table('role')->insert([
                'id' => $id,
                'nama_role' => $namaRole,
                'slug' => $slug,
            ]);
            return $id;
        }

        return $role->id;
    }

    public function definition(): array
    {
        // Default role: Petani (atau bisa diubah sesuai kebutuhan)
        return [
            'id' => Str::uuid()->toString(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'id_role' => $this->ensureRoleExists('petani', 'Petani'),
            'id_kph' => null,
            'id_kth' => null,
        ];
    }

    // --- STATES UNTUK ROLE ---

    public function petani(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_role' => $this->ensureRoleExists('petani', 'Petani'),
        ]);
    }

    public function penyuluh(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_role' => $this->ensureRoleExists('penyuluh', 'Penyuluh'),
        ]);
    }

    public function sekretariat(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_role' => $this->ensureRoleExists('sekretariat', 'Sekretariat'),
        ]);
    }

    public function auditor(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_role' => $this->ensureRoleExists('auditor', 'Auditor'),
        ]);
    }

    public function penanggungJawab(): static
    {
        return $this->state(fn (array $attributes) => [
            'id_role' => $this->ensureRoleExists('penanggung-jawab', 'Penanggung Jawab'),
        ]);
    }

    // --- STATE UNTUK DETAIL USER ---

    public function withDetail(array $customDetail = []): static
    {
        return $this->afterCreating(function (User $user) use ($customDetail) {
            $tglLahir = fake()->dateTimeBetween('-60 years', '-18 years')->format('dmy');
            $nikDummy = fake()->numerify("######{$tglLahir}####");

            DB::table('user_details')->insert([
                'id' => Str::uuid()->toString(),
                'id_user' => $user->id,
                'nama_user' => $customDetail['nama_user'] ?? fake()->name(),
                'nomor_induk' => $customDetail['nomor_induk'] ?? $nikDummy,
                'jenis_kelamin' => $customDetail['jenis_kelamin'] ?? fake()->randomElement(['pria', 'wanita']),
                'alamat' => fake()->address(),
                'nomor_wa' => fake()->phoneNumber(),
                'luas_lahan' => fake()->randomFloat(2, 0, 100),
                'pendidikan_terakhir' => 'SMA',
                'pekerjaan_sampingan' => 'Wiraswasta',
                'jabatan' => 'Anggota',
            ]);
        });
    }
}
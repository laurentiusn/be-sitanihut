<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nama_role' => 'Sekretariat',
                'slug'      => 'sekretariat'
            ],
            [
                'nama_role' => 'Penanggung Jawab',
                'slug'      => 'penanggung-jawab'
            ],
            [
                'nama_role' => 'Penyuluh',
                'slug'      => 'penyuluh'
            ],
            [
                'nama_role' => 'Petani',
                'slug'      => 'petani'
            ],
            [
                'nama_role' => 'Auditor',
                'slug'      => 'auditor'
            ]
        ];
        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug'      => $role['slug']],
                ['nama_role' => $role['nama_role']]
            );
        }
    }
}

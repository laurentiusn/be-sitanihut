<?php

namespace Database\Seeders;

use App\Models\Kph;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $detailsData = [
            [
                'user_email' => 'sekretariat@demo.com',
                'details' => [
                    'nama_user' => 'Aldo Wijaya',
                    'nomor_induk' => '1801132709010001',
                    'jenis_kelamin' => 'pria',
                    'nomor_wa' => '082180228516',
                ]
            ]
        ];


        foreach ($detailsData as $data) {
            $user = User::where('email', $data['user_email'])->first();

            if ($user) {
                UserDetail::updateOrCreate(
                    ['id_user' => $user->id],
                    $data['details']
                );
            }
        }
    }
}

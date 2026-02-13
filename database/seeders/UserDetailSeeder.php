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
                'user_email' => 'aldowijayasept27@gmail.com',
                'details' => [
                    'nama_user' => 'Aldo Wijaya',
                    'nomor_induk' => '1801132709010001',
                    'jenis_kelamin' => 'pria',
                    'nomor_wa' => '082180228516',
                ]
            ],
            // [
            //     'user_email' => 'pj@example.com',
            //     'details' => [
            //         'nama_user' => 'Andi Andhika',
            //         'nomor_induk' => '200109272025041003',
            //         'jenis_kelamin' => 'pria',
            //         'nomor_wa' => '0821716425433',
            //     ]
            // ],
            // [
            //     'user_email' => 'auditor@example.com',
            //     'details' => [
            //         'nama_user' => 'Auditor 1',
            //         'nomor_induk' => '1804031206651200',
            //         'jenis_kelamin' => 'pria',
            //         'nomor_wa' => '0821716425432',
            //     ]
            // ],
            // [
            //     'user_email' => 'penyuluh@example.com',
            //     'details' => [
            //         'nama_user' => 'Penyuluh Satu',
            //         'nomor_induk' => '1804031806651200',
            //         'jenis_kelamin' => 'pria',
            //         'nomor_wa' => '082188732234',
            //         'alamat' => 'Jl. Ambaruwo No 9',
            //         'jabatan' => 'Penyuluh Kehutanan Ahli',
            //     ]
            // ],
            // [
            //     'user_email' => 'penyuluh2@example.com',
            //     'details' => [
            //         'nama_user' => 'Penyuluh Dua',
            //         'nomor_induk' => '1804031506651200',
            //         'jenis_kelamin' => 'wanita',
            //         'nomor_wa' => '0813923234352',
            //         'alamat' => 'Jl. Ketamban No 11',
            //         'jabatan' => 'Penyuluh Kehutanan Ahli',
            //     ]
            // ],
            // [
            //     'user_email' => 'ardora.334@gmail.com',
            //     'details' => [
            //         'nama_user' => 'Arnora',
            //         'nomor_induk' => '1804031206651210',
            //         'jenis_kelamin' => 'pria',
            //         'nomor_wa' => '081298349292',
            //         'alamat' => 'Jl. Tukam No 67',
            //         'pendidikan_terakhir' => 'SMA',
            //         'pekerjaan_sampingan' => 'Berkebun',
            //         'luas_lahan' => 10,
            //     ]
            // ],
            // [
            //     'user_email' => 'petani2@example.com',
            //     'details' => [
            //         'nama_user' => 'Petani Dua',
            //         'nomor_induk' => '1871136767690001',
            //         'jenis_kelamin' => 'wanita',
            //         'nomor_wa' => '081369673000',
            //         'alamat' => 'Jl. Rusdiyawan No 10',
            //         'pendidikan_terakhir' => 'S1',
            //         'pekerjaan_sampingan' => 'Ternak Lele',
            //         'luas_lahan' => 5,
            //     ]
            // ],
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

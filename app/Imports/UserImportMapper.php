<?php

namespace App\Imports;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Class UserImportMapper
 * @package App\Imports
 */
class UserImportMapper
{
    private array $passwordCache = [];

    /** Mapper insert tabel users
     * @param array $row Data satu baris Excel
     * @param string $userId UUID untuk entitas User
     * @return array Array mapped data users
     */
    public function mapUserInsertData(array $row, string $userId): array
    {
        $rawPassword = $row['password'];
        if (!isset($this->passwordCache[$rawPassword])) {
            $this->passwordCache[$rawPassword] = Hash::make($rawPassword);
        }

        return [
            'id'       => $userId,
            'email'    => $row['email'],
            'password' => $this->passwordCache[$rawPassword],
            'id_role'  => $row['id_role'],
            'id_kph'   => $row['id_kph'] ?? null,
            'id_kth'   => $row['id_kth'] ?? null
        ];
    }

    /** Mapper insert tabel user_details
     * @param array $row Data satu baris Excel
     * @param string $userId UUID referensi ke tabel users
     * @return array Array mapped data user_details
     */
    public function mapUserDetailInsertData(array $row, string $userId): array
    {
        return [
            'id'                  => Str::uuid()->toString(),
            'id_user'             => $userId,
            'nama_user'           => $row['nama_user'],
            'nomor_induk'         => (string) $row['nomor_induk'],
            'jenis_kelamin'       => strtolower($row['jenis_kelamin']),
            'alamat'              => $row['alamat'] ?? null,
            'nomor_wa'            => isset($row['nomor_wa']) ? (string) $row['nomor_wa'] : null,
            'pendidikan_terakhir' => $row['pendidikan_terakhir'] ?? null,
            'pekerjaan_sampingan' => $row['pekerjaan_sampingan'] ?? null,
            'luas_lahan'          => $row['luas_lahan'] ?? null,
            'jabatan'             => $row['jabatan'] ?? null
        ];
    }
}
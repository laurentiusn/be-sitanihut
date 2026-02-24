<?php

namespace App\Repository;

use App\Models\RefreshToken;

/** Class RefreshTokenRepository
 * @package App\Repository
 */
class RefreshTokenRepository
{
    /** Mencari refresh token berdasarkan device
     * @param string $userId ID pengguna
     * @param string $deviceUnq Identifier unik perangkat
     * @return RefreshToken|null
     */
    public function findByDevice(string $userId, string $deviceUnq)
    {
        return RefreshToken::withTrashed()
            ->where('id_user', $userId)
            ->where('device_unq', $deviceUnq)
            ->first();
    }

    /** Mencari refresh token berdasarkan hash
     * @param string $hashToken Token hash yang dicari
     * @return RefreshToken|null
     */
    public function findByHashToken(string $hashToken)
    {
        return RefreshToken::where('token', $hashToken)->first();
    }

    /** Membuat data refresh token baru
     * @param array $data Data yang akan disimpan
     * @return RefreshToken
     */
    public function create(array $data)
    {
        return RefreshToken::create($data);
    }
    
    /** Memperbarui refresh token yang terhapus (restore)
     * @param RefreshToken $token Objek model RefreshToken
     * @param array $data Data untuk memperbarui token
     * @return RefreshToken
     */
    public function update(RefreshToken $token, array $data)
    {
        $token->restore();
        $token->update($data);
        return $token;
    }

    /** Menghapus (soft-delete) refresh token
     * @param RefreshToken $token Objek model RefreshToken
     * @return bool|null
     */
    public function delete(RefreshToken $token)
    {
        return $token->delete();
    }

    /** Menghapus refresh token berdasarkan hash
     * @param string $userId ID pengguna
     * @param string $hashToken Token hash yang akan dihapus
     * @return int Jumlah baris yang dihapus
     */
    public function deleteByUserAndHash(string $userId, string $hashToken)
    {
        return RefreshToken::where('id_user', $userId)
            ->where('token', $hashToken)
            ->delete();
    }
}
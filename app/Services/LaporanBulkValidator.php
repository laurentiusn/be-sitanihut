<?php

namespace App\Services;

use App\Models\User;

/**
 * Class LaporanBulkValidator
 * 
 * Memeriksa hak akses (*Authorization*) spesifik tiap item laporan di operasi Massal (Update & Delete).
 * Menolak pemrosesan jika laporan milik entitas lain atau statusnya sudah diverifikasi oleh pusat.
 *
 * @package App\Services
 */
class LaporanBulkValidator
{
    /**
     * Validasi Hak Akses untuk Update Laporan.
     * 
     * @param string $laporanId UUID target laporan
     * @param \Illuminate\Database\Eloquent\Collection|array $laporansPusat Koleksi laporan dari database
     * @param User $user Pengguna yang melakukan update
     * @return array|null Mengembalikan null jika lolos, atau array alasan ditolak.
     */
    public function validateUpdateAccess(string $laporanId, $laporansPusat, User $user): ?array
    {
        if (!$laporansPusat->has($laporanId)) {
            return [
                'id'           => $laporanId, 
                'status_pusat' => 'tidak diketahui', 
                'alasan'       => 'Data tidak ditemukan atau bukan milik Anda.'
            ];
        }

        $lapPusat = $laporansPusat[$laporanId];
        
        if ($user->cannot('updateBulk', $lapPusat)) {
            return [
                'id'           => $laporanId, 
                'status_pusat' => $lapPusat->status, 
                'alasan'       => 'Akses ditolak: Laporan sedang diproses atau sudah diverifikasi.'
            ];
        }

        return null;
    }

    /**
     * Memvalidasi apakah laporan tunggal di dalam Bulk bisa dihapus oleh user aktif.
     * 
     * @param string $laporanId UUID target laporan
     * @param \Illuminate\Database\Eloquent\Collection|array $laporanDiServer Koleksi laporan target
     * @param User $user Pengguna yang melakukan penghapusan
     * @return array|null Mengembalikan null jika lolos, atau array alasan ditolak.
     */
    public function validateDeleteAccess(string $laporanId, $laporanDiServer, User $user): ?array
    {
        if (!$laporanDiServer->has($laporanId)) {
            return ['tipe' => 'tidak_ditemukan', 'data' => $laporanId];
        }

        $laporan = $laporanDiServer[$laporanId];

        if ($user->role->slug === 'petani' && $laporan->id_user !== $user->id) {
            return [
                'tipe' => 'ditolak',
                'data' => [
                    'id'           => $laporanId,
                    'status_pusat' => 'tidak diketahui',
                    'alasan'       => 'Akses ditolak: Laporan ini milik petani lain.'
                ]
            ];
        }

        if ($laporan->trashed()) {
            return [
                'tipe' => 'sudah_dihapus_sebelumnya',
                'data' => $laporanId];
        }

        if ($user->cannot('delete', $laporan)) {
            return [
                'tipe' => 'ditolak',
                'data' => [
                    'id'           => $laporanId,
                    'status_pusat' => $laporan->status,
                    'alasan'       => 'Akses ditolak: laporan sedang diproses'
                ]
            ];
        }

        return null;
    }
}

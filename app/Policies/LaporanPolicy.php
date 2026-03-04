<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\User;

/**
 * Policy untuk mengatur hak akses terhadap entitas Laporan.
 */
class LaporanPolicy
{
    /**
     * Menentukan apakah user dapat melihat daftar laporan apapun.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['sekretariat', 'penanggung-jawab', 'auditor', 'penyuluh', 'petani']);
    }

    /**
     * Menentukan apakah user dapat melihat detail suatu laporan.
     */
    public function view(User $user, Laporan $laporan): bool
    {
        return match ($user->role->slug) {
            'sekretariat'      => true,
            'auditor'          => $laporan->status === 'disetujui',
            'penanggung-jawab' => $this->isSameKph($user, $laporan),
            'penyuluh'         => $this->isSameKph($user, $laporan),
            'petani'           => $this->isOwner($user, $laporan),
            default            => false,
        };
    }

    /**
     * Menentukan apakah user dapat membuat laporan baru.
     */
    public function create(User $user): bool
    {
        return $user->role->slug === 'petani';
    }

    /**
     * Menentukan apakah user dapat mengupdate laporan tertentu.
     */
    public function update(User $user, Laporan $laporan): bool
    {
        return match ($user->role->slug) {
            'sekretariat'      => in_array($laporan->status, ['menunggu', 'diverifikasi']),
            'penyuluh'         => $laporan->status === 'menunggu' && $this->isSameKph($user, $laporan),
            'penanggung-jawab' => $laporan->status === 'diverifikasi' && $this->isSameKph($user, $laporan),
            'petani'           => $this->isOwner($user, $laporan) && in_array($laporan->status, ['belum diajukan', 'ditolak']),
            default            => false,
        };
    }

    /**
     * Menentukan apakah user dapat menghapus laporan.
     */
    public function delete(User $user, Laporan $laporan): bool
    {
        return $user->role->slug === 'petani' 
            && $this->isOwner($user, $laporan) 
            && in_array($laporan->status, ['belum diajukan', 'ditolak']);
    }

    /**
     * Menentukan apakah user dapat melakukan update massal (bulk).
     */
    public function canBulkUpdate(User $user): bool
    {
        return $user->role->slug === 'petani';
    }

    /**
     * Menentukan apakah laporan tertentu bisa diikutkan dalam update massal oleh user.
     */
    public function updateBulk(User $user, Laporan $laporan): bool
    {
        // Logikanya identik dengan delete/update untuk petani
        return $user->role->slug === 'petani' 
            && $this->isOwner($user, $laporan) 
            && in_array($laporan->status, ['belum diajukan', 'ditolak']);
    }

    /**
     * Mengecek apakah user dan pembuat laporan berada di KPH yang sama.
     */
    private function isSameKph(User $user, Laporan $laporan): bool
    {
        return $user->id_kph !== null 
            && $laporan->user->id_kph !== null 
            && $user->id_kph === $laporan->user->id_kph;
    }

    /**
     * Mengecek apakah user adalah pemilik (pembuat) laporan tersebut.
     */
    private function isOwner(User $user, Laporan $laporan): bool
    {
        return $user->id === $laporan->id_user;
    }
}
<?php

namespace App\Policies;

use App\Models\Laporan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LaporanPolicy
{

    // public function before(User $user, string $ability): bool|null
    // {
    //     if ($user->role->slug === 'sekretariat') {
    //         return true;
    //     }

    //     return null;
    // }

    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['sekretariat', 'penanggung-jawab', 'auditor', 'penyuluh', 'petani']);
    }


    public function view(User $user, Laporan $laporan): bool
    {
        if ($user->role->slug === 'sekretariat') {
            return true;
        }
        if ($user->role->slug === 'auditor' && $laporan->status === 'disetujui') {
            return true;
        }
        if ($user->role->slug === 'penanggung-jawab') {
            return $user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph;
        }
        if ($user->role->slug === 'penyuluh') {
            return $user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph;
        }

        if ($user->role->slug === 'petani') {
            return $user->id === $laporan->id_user;
        }

        return false;
    }


    public function create(User $user): bool
    {
        return $user->role->slug === 'petani';
    }


    public function update(User $user, Laporan $laporan): bool
    {
        if ($user->role->slug === 'sekretariat') {
            return in_array($laporan->status, ['menunggu', 'diverifikasi']);
        }

        if ($user->role->slug === 'penyuluh') {
            return ($laporan->status === 'menunggu') && ($user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph);
        }
        if ($user->role->slug === 'penanggung-jawab') {
            return ($laporan->status === 'diverifikasi') && ($user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph);
        }
        if ($user->role->slug === 'petani') {
            return ($user->id === $laporan->id_user) && in_array($laporan->status, ['belum diajukan', 'ditolak']);
        }

        return false;
    }


    public function delete(User $user, Laporan $laporan): bool
    {
        if ($user->role->slug === 'petani') {
            return ($user->id === $laporan->id_user) && in_array($laporan->status, ['belum diajukan', 'ditolak']);
        }

        return false;
    }


    // public function ajukanLaporan(User $user, Laporan $laporan): bool
    // {
    //     if ($user->role->slug === 'petani') {
    //         return ($user->id === $laporan->id_user) && ($laporan->status === 'belum diajukan');
    //     }

    //     if ($user->role->slug === 'penyuluh') {
    //          return ($user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph) && ($laporan->status === 'belum diajukan');
    //     }

    //     return false;
    // }


    // public function periksaLaporan(User $user, Laporan $laporan): bool
    // {
    //     if ($user->role->slug === 'penyuluh') {
    //         return ($laporan->status === 'menunggu') && ($user->id_kph && $laporan->user->id_kph && $user->id_kph === $laporan->user->id_kph);
    //     }

    //     return false;
    // }
}

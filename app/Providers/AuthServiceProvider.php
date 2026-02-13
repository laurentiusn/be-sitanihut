<?php

namespace App\Providers;

use App\Models\Kth;
use App\Models\Laporan;
use App\Models\User;
use App\Policies\KthPolicy;
use App\Policies\UserPolicy;
use App\Models\Petani;
use App\Policies\LaporanPolicy;
use App\Policies\PetaniPolicy;
use App\Models\Komoditas;
use App\Policies\KomoditasPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Kth::class => KthPolicy::class,
        User::class => UserPolicy::class,
        Petani::class => PetaniPolicy::class,
        Laporan::class => LaporanPolicy::class,
        Komoditas::class => KomoditasPolicy::class
    ];
}

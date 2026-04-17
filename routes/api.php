<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\KphController;
use App\Http\Controllers\Api\KthController;
use App\Http\Controllers\Api\PenyuluhController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\KomoditasController;
use App\Http\Middleware\Sekretariat;
use App\Http\Controllers\Api\PetaniController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\LogLaporanController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\RekapNteController;
use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LaporanBulkController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Middleware\PJ;
use App\Http\Middleware\PJ_Penyuluh;
use App\Http\Controllers\Api\UserPenyuluhController;
use App\Http\Controllers\Api\UserPetaniController;
use App\Http\Controllers\Api\UserPJController;
use App\Http\Middleware\Sekretariat_Penyuluh;
use App\Http\Controllers\Api\KatalogController;
use App\Http\Controllers\Api\InformasiController;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassMail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/kph', [KphController::class, 'index']);
    Route::post('/refresh', [AuthController::class, 'refreshToken']);
    Route::apiResource('katalog', KatalogController::class)->only(['index', 'show']);
    Route::apiResource('informasi', InformasiController::class)->only(['index', 'show']);

    //Middleware yang telah login
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('laporan', LaporanController::class);
        Route::post('/laporan/bulk-create', [LaporanBulkController::class, 'store']);
        Route::post('/laporan/bulk-update', [LaporanBulkController::class, 'update']);
        Route::post('/laporan/bulk-destroy', [LaporanBulkController::class, 'destroy']);
        Route::apiResource('users', UserController::class);
        Route::apiResource('petani', PetaniController::class);
        Route::get('komoditas', [KomoditasController::class, 'index']);
        Route::get('komoditas/{komoditas}', [KomoditasController::class, 'show']);
        Route::apiResource('kth', KthController::class);
        Route::get('/role', [RoleController::class, 'index']);

        //Sekretariat Middleware
        Route::middleware(Sekretariat::class)->group(function () {
            Route::apiResource('kph', KphController::class)->except(['index', 'show']);
            Route::apiResource('penyuluh', PenyuluhController::class)->except(['index', 'show']);
            Route::apiResource('komoditas', KomoditasController::class)->except(['index', 'show'])->parameters(['komoditas' => 'komoditas']);
            Route::post('/komoditas/import', [ImportController::class, 'komoditasImport']);
            Route::post('/kth/import', [ImportController::class, 'kthImport']);
            Route::post('/users/import', [ImportController::class, 'userImport']);
            Route::apiResource('informasi', InformasiController::class)->except(['index', 'show']);
        });

        //Admin = Sekretariat & Auditor
        Route::middleware(Admin::class)->group(function () {
            Route::apiResource('kph', KphController::class)->only(['show']);
            Route::apiResource('penyuluh', PenyuluhController::class)->only(['index', 'show']);
            Route::get('logs/laporan', [LogLaporanController::class, 'index']);
            Route::get('statistik', [StatistikController::class, 'index']);
            Route::get('rekap/nte', [RekapNteController::class, 'index']);
            Route::get('rekap/export', [RekapNteController::class, 'export']);
            Route::get('user-pj', [UserPJController::class, 'index']);
            Route::get('user-pj/{user}', [UserPJController::class, 'show']);
        });

        //PJ = Admin + PJ
        Route::middleware(PJ::class)->group(function () {
            Route::get('user-penyuluh', [UserPenyuluhController::class, 'index']);
            Route::get('user-penyuluh/{user}', [UserPenyuluhController::class, 'show']);
        });

        //PJ_Penyuluh = Admin + PJ + Penyuluh
        Route::middleware(PJ_Penyuluh::class)->group(function () {
            Route::get('user-petani', [UserPetaniController::class, 'index']);
            Route::get('user-petani/{user}', [UserPetaniController::class, 'show']);
        });

        Route::middleware(Sekretariat_Penyuluh::class)->group(function (){
            Route::apiResource('katalog', KatalogController::class)->except(['index', 'show']);
        });
    });
});
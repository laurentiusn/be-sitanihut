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

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassMail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/kph', [KphController::class, 'index']);
    Route::post('/refresh', [AuthController::class, 'refreshToken']);

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
        // Route::get('rekap/nte', [RekapNteController::class, 'index']);

        //Admin = Sekretariat & Auditor
        Route::middleware(Admin::class)->group(function () {
            Route::apiResource('kph', KphController::class)->only(['show']);
            Route::apiResource('penyuluh', PenyuluhController::class)->only(['index', 'show']);
            Route::get('logs/laporan', [LogLaporanController::class, 'index']);
            Route::get('statistik', [StatistikController::class, 'index']);
            Route::get('rekap/nte', [RekapNteController::class, 'index']);
        });

        //Sekretariat Middleware
        Route::middleware(Sekretariat::class)->group(function () {
            Route::apiResource('kph', KphController::class)->except(['index', 'show']);
            Route::apiResource('penyuluh', PenyuluhController::class)->except(['index', 'show']);
            Route::apiResource('komoditas', KomoditasController::class)->except(['index', 'show'])->parameters(['komoditas' => 'komoditas']);
        });
        Route::apiResource('kth', KthController::class);
        Route::get('/role', [RoleController::class, 'index']);
    });
});

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPassRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\RefreshRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\PostResource;
use App\Mail\ForgotPassMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Services\RefreshTokenService;

/**
 * Class AuthController
 * 
 * Mengelola seluruh alur autentikasi (*Authentication*) pengguna di sisi server.
 * Bertanggung jawab penuh terhadap manajemen siklus hidup token JWT (*Access Token* & *Refresh Token*),
 * proses masuk (login) ganda (email/nomor induk), penyegaran sesi, hingga proses pemulihan kata sandi.
 *
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller
{
    protected RefreshTokenService $refreshTokenService;

    public function __construct(RefreshTokenService $refreshTokenService)
    {
        $this->refreshTokenService = $refreshTokenService;
    }

    /**
     * Menangani proses autentikasi (login) pengguna.
     * Mengizinkan pengguna untuk masuk menggunakan email ataupun nomor induk.
     * Jika berhasil, akan menghasilkan JWT Access Token baru dan Refresh Token.
     *
     * @param \App\Http\Requests\Auth\LoginRequest $request Objek request berisi login (email/nomor induk) dan password
     * @return \Illuminate\Http\JsonResponse Respons JSON yang seragam (PostResource) berisi data Token dan User
     */
    public function login(LoginRequest $request)
    {
        $login = $request->login;
        $password = $request->password;

        // Tentukan apakah input adalah email atau nomor_induk
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nomor_induk';

        // Jika login pakai nomor_induk, ambil email user-nya dulu
        if ($loginField === 'nomor_induk') {
            $user = User::whereHas('userDetail', function ($q) use ($login) {
                $q->where('nomor_induk', $login);
            })->first();

            if (!$user) {
                return (new PostResource('Pengguna tidak ditemukan', 401, []))->response();
            }

            // Auth attempt tetap pakai email
            $credentials = [
                'email' => $user->email,
                'password' => $password
            ];
        } else {
            // Login pakai email
            $credentials = [
                'email' => $login,
                'password' => $password
            ];
        }

        // Auth attempt
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->attempt($credentials);

        if (!$token) {
            return (new PostResource('Data yang anda masukan salah', 401, []))->response();
        }

        $user = User::with(['role', 'userDetail'])->find($guard->id());

        $refreshToken = $this->refreshTokenService->generateFromLogin(
            $user->id,
            $request->platform,
            $request->device_unq,
            $request->device_name
        );

        $data = new AuthResource($token, $refreshToken, $user);
        return (new PostResource('Berhasil masuk', 200, $data))->response();
    }

    /**
     * Memperbarui (refresh) JWT Access Token yang sudah kedaluwarsa.
     * Mengharuskan pengguna memberikan Refresh Token yang valid dan belum dicabut.
     *
     * @param \App\Http\Requests\RefreshRequest $request Objek request berisi refresh_token
     * @return \Illuminate\Http\JsonResponse Respons JSON berisi token baru atau pesan gagal
     */
    public function refreshToken(RefreshRequest $request)
    {
        $request->validated();
        $refreshToken = $request->input('refresh_token');

        $result = $this->refreshTokenService->generateNewToken($refreshToken);

        if (!$result['success']) {
            return (new PostResource($result['message'], $result['code'], []))
                ->response()->setStatusCode($result['code']);
        }

        $data = new AuthResource($result['acc_token'], $result['ref_token'], $result['user']);
        
        return (new PostResource('Success: Token refreshed', 200, $data))
            ->response()->setStatusCode(200);
    }

    public function forgotPassMail(Request $request)
    {
        $email = $request->input('email');
        $fix_mail = htmlspecialchars($email);

        if (!$fix_mail) {
            return (new PostResource(
                'failed',
                'Email is not valid',
                []
            ))->response()->setStatusCode(400);
        }

        $user = User::where('email', $fix_mail)->first();

        if (!$user) {
            return (new PostResource(
                'failed',
                'User not found',
                []
            ))->response()->setStatusCode(404);
        }

        // ✅ Buat token dengan custom claims
        $token = JWTAuth::customClaims([
            'email' => $user->email,
            'purpose' => 'forgot_password',
        ])->fromUser($user);

        // ✅ Buat URL ke frontend reset-password
        $url = env('APP_FE_URL', 'https://sipetahut.palum.id') . '/auth/reset-password?token=' . $token;

        // ✅ Kirim email
        Mail::to($user->email)->send(new ForgotPassMail($url));

        return (new PostResource(
            'success',
            'Email verification is sent successfully',
            []
        ))->response()->setStatusCode(200);
    }

    public function resetPassword(ResetPassRequest $request)
    {
        $token = $request->query('token');
        $fix_token = htmlspecialchars($token);
        if (!$fix_token) {
            return (new PostResource(
                'failed',
                'Token is not found',
                []
            ))->response()->setStatusCode(400);
        }

        $payload = JWTAuth::parseToken($fix_token);
        $payload = $payload->getPayload();

        $validity = JWTAuth::getPayload($fix_token)->get('exp');
        if ($validity < time()) {
            return (new PostResource(
                'failed',
                'Token is expired',
                []
            ))->response()->setStatusCode(404);
        }

        $sub = $payload['sub'];
        $email = $payload['email'];
        $user = User::where('email', $email)
            ->where('id', $sub)
            ->first();
        if (!$user) {
            return (new PostResource(
                'failed',
                'User not found',
                []
            ))->response()->setStatusCode(404);
        }

        if (!$request->password) {
            return (new PostResource(
                'failed',
                'New password is required',
                []
            ))->response()->setStatusCode(400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return (new PostResource(
            'success',
            'Password is reset successfully',
            []
        ))->response()->setStatusCode(200);
    }

    /**
     * Mengakhiri sesi pengguna (logout).
     * Memasukkan JWT Access Token saat ini ke dalam *blacklist* (invalidasi) 
     * dan menonaktifkan Refresh Token terkait di database agar tidak bisa digunakan lagi.
     *
     * @param \App\Http\Requests\LogoutRequest $request Objek request
     * @return \Illuminate\Http\JsonResponse Respons konfirmasi logout berhasil
     */
    public function logout(LogoutRequest $request)
    {
        $request->validated();
        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');

        $refreshToken = $request->input('refresh_token') ?? null;

        $this->refreshTokenService->deactivateToken($guard->id(), $refreshToken);

        $guard->logout(true);

        return (new PostResource('Berhasil keluar', 200, []))->response();
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPassRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\PostResource;
use App\Mail\ForgotPassMail;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
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
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return (new PostResource('Data yang anda masukan salah', 401, []))->response();
        }

        $userId = auth('api')->id();
        $user = User::with(['role', 'userDetail'])->find($userId);

        $data = new AuthResource($token, $user);
        return (new PostResource('Berhasil masuk', 200, $data))->response();
    }


    // public function forgotPassword (ForgotRequest $request){
    //     try{
    //         $email = $request->validated()['email'];
    //         $user = User::where('email', $email)->first();
    //         if($user){
    //             $claims = [
    //                 'exp'     => now()->addMinutes(5)->timestamp,
    //                 'purpose' => 'forgot_password'];

    //             $token = JWTAuth::fromUser($user, $claims);

    //             $url = env('APP_URL') . '/reset-password?token=' . $token;

    //             // Sending an email
    //             Mail::to($user->email)->send(new PasswordResetMail($url));
    //         }

    //         return (new PostResource('Tautan Reset Password telah dikirim', 200, []))->response();

    //     } catch (Throwable $e) {
    //         return (new PostResource($e->getMessage(), 500, []))->response();
    //         // return (new PostResource($e->getMessage(), 500, []))->response();
    //     }

    // }

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

        // if (!$user->email_verified_at) {
        //     return (new PostResource(
        //         'failed',
        //         'User is not verified',
        //         []
        //     ))->response()->setStatusCode(404);
        // }

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

    public function logout()
    {
        auth('api')->logout(true);

        return (new PostResource('Berhasil keluar', 200, []))->response();

        // return response()->json([
        //         'message' => 'Berhasil keluar',
        //         'statusCode' => 200,
        //         'data' => []
        //     ]);
    }
}

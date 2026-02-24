<?php

namespace App\Services;

use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Class RefreshTokenService
 * 
 * Service: pembuatan token baru saat login, pembaruan access token, 
 * dan penonaktifan refresh token.
 *
 * @package App\Services
 */
class RefreshTokenService
{
    protected RefreshTokenRepository $refreshTokenRepository;
    protected UserRepository $userRepository;

    public function __construct(
        RefreshTokenRepository $refreshTokenRepository,
        UserRepository $userRepository
    )
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Generate Refresh Token pada proses Login
     *
     * @param string $userId ID pengguna yang login
     * @param string|null $platform Platform ('android', 'ios', 'web')
     * @param string|null $deviceUnq Device ID
     * @param string|null $deviceName Nama perangkat
     * @return string|null String refresh token
     */
    public function generateFromLogin(
        string $userId, 
        ?string $platform = null, 
        ?string $deviceUnq = null, 
        ?string $deviceName = null
    )
    {
        if (!in_array($platform, ['android', 'ios', 'web'])) {
            return null;
        }

        $refreshToken = Str::random(64);
        $hashedToken = hash('sha256', $refreshToken);
        $expiresAt = Carbon::now()->addDays(60);

        $existingToken = $this->refreshTokenRepository->findByDevice(
            $userId, 
            $deviceUnq
        );

        if ($existingToken) {
            $this->refreshTokenRepository->update($existingToken, [
                'device_name' => $deviceName,
                'token'       => $hashedToken,
                'expires_at'  => $expiresAt,
            ]);
        } else {
            $this->refreshTokenRepository->create([
                'id_user'     => $userId,
                'device_unq'  => $deviceUnq,
                'device_name' => $deviceName,
                'token'       => $hashedToken,
                'expires_at'  => $expiresAt,
            ]);
        }

        return $refreshToken;
    }

    /**
     * Validasi refresh token dan generate token JWT baru jika valid
     *
     * @param string $refreshToken Refresh token (plaintext)
     * @return array Status dan token baru
     */
    public function generateNewToken(string $refreshToken)
    {
        $hashToken = hash('sha256', $refreshToken);
        $storedToken = $this->refreshTokenRepository->findByHashToken($hashToken);

        if (!$storedToken) {
            return [
                'success'   => false, 
                'message'   => 'Failed: Invalid refresh token', 
                'code'      => 401];
        }

        if ($storedToken->expires_at < Carbon::now()) {
            $this->refreshTokenRepository->delete($storedToken);
            return [
                'success'   => false, 
                'message'   => 'Failed: Refresh token expired', 
                'code'      => 401
            ];
        }

        /** @var \PHPOpenSourceSaver\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $newAccToken = $guard->tokenById($storedToken->id_user);

        if (!$newAccToken) {
            return [
                'success'   => false, 
                'message'   => 'Failed: User not found', 
                'code'      => 404
            ];
        }

        $user = $this->userRepository->findProfileById($storedToken->id_user);

        $newRefreshToken = Str::random(64);
        $this->refreshTokenRepository->update($storedToken, [
            'token'      => hash('sha256', $newRefreshToken),
            'expires_at' => Carbon::now()->addDays(60),
        ]);

        return [
            'success'   => true,
            'user'      => $user,
            'acc_token' => $newAccToken,
            'ref_token' => $newRefreshToken,
        ];
    }

    /**
     * Menonaktifkan (soft-delete) refresh token
     *
     * @param string $userId ID pengguna
     * @param string|null $refreshToken Refresh token yang dihapus
     * @return void
     */
    public function deactivateToken(string $userId, ?string $refreshToken = null)
    {
        if ($refreshToken) {
            $hashToken = hash('sha256', $refreshToken);
            $this->refreshTokenRepository->deleteByUserAndHash($userId, $hashToken);
        }
    }
}
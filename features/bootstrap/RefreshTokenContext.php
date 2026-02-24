<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Carbon;

class RefreshTokenContext implements Context
{
    protected $user;
    protected $password = 'password_rahasia123';
    
    protected $currentAccessToken;
    protected $currentRefreshToken;
    protected $oldRefreshToken;

    protected $response;
    protected $responseContent;

    /**
     * @AfterScenario
     */
    public function bersihkanWaktuMocking()
    {
        Carbon::setTestNow(null);
    }

    /**
     * @Given Pengguna yang sudah memiliki akun ingin login ke sistem
     */
    public function penggunaYangSudahMemilikiAkunInginLoginKeSistem()
    {
        $this->user = User::factory()->create([
            'email' => 'petani_test@sitanihut.com',
            'password' => bcrypt($this->password),
        ]);

        $userExists = User::where('email', 'petani_test@sitanihut.com')->exists();

        Assert::assertTrue($userExists, 'Belum ada User');
    }

    /**
     * @When Pengguna memasukkan kredensial akun nya
     */
    public function penggunaMemasukkanKredensialAkunNya()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create('/api/v1/login', 'POST', [
            'login'       => $this->user->email,
            'password'    => $this->password,
            'platform'    => 'web',
            'device_unq'  => 'device-test-1',
            'device_name' => 'Chrome Browser'
        ]);

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @Then Sistem memberikan access token serta refresh token
     */
    public function sistemMemberikanAccessTokenSertaRefreshToken()
    {
        Assert::assertEquals(200, $this->responseContent['statusCode'] ?? $this->response->getStatusCode(), 'Proses gagal');
        
        $data = $this->responseContent['data'] ?? [];
        
        Assert::assertArrayHasKey('token', $data, 'Key token tidak ditemukan');
        Assert::assertArrayHasKey('refresh_token', $data, 'Key refresh_token tidak ditemukan');

        if ($this->currentRefreshToken !== null) {
            $this->oldRefreshToken = $this->currentRefreshToken;
        }

        $this->currentAccessToken = $data['token'];
        $this->currentRefreshToken = $data['refresh_token'];
    }

    /**
     * @Then Refresh token disimpan di database
     * @Then Refresh token yang baru disimpan di database
     */
    public function refreshTokenDisimpanDiDatabase()
    {
        $hashedToken = hash('sha256', $this->currentRefreshToken);
        $tokenExists = RefreshToken::where('id_user', $this->user->id)
            ->where('token', $hashedToken)
            ->exists();

        Assert::assertTrue($tokenExists, 'Refresh token tidak ditemukan di database');
    }

    /**
     * @Given Pengguna telah login ke sistem
     */
    public function penggunaTelahLoginKeSistem()
    {
        $this->user = User::factory()->create([
            'email' => 'petani_test@sitanihut.com',
            'password' => bcrypt($this->password),
        ]);

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create('/api/v1/login', 'POST', [
            'login'       => $this->user->email,
            'password'    => $this->password,
            'platform'    => 'web',
            'device_unq'  => 'device-test-1',
            'device_name' => 'Chrome Browser'
        ]);

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);

        $data = $this->responseContent['data'] ?? [];

        if ($this->currentRefreshToken !== null) {
            $this->oldRefreshToken = $this->currentRefreshToken;
        }

        $this->currentAccessToken = $data['token'];
        $this->currentRefreshToken = $data['refresh_token'];
        
        $this->oldRefreshToken = $this->currentRefreshToken;
    }

    /**
     * @Given Access token telah expired
     */
    public function accessTokenTelahExpired()
    {
        Carbon::setTestNow(now()->addMinutes(65));
    }

    /**
     * @When Pengguna memperbarui access token dengan refresh token yang valid
     */
    public function penggunaMemperbaruiAccessTokenDenganRefreshTokenYangValid()
    {
        $this->hitRefreshEndpoint($this->currentRefreshToken);
    }

    /**
     * @Then Refresh token yang lama dihapus dari database
     */
    public function refreshTokenYangLamaDihapusDariDatabase()
    {
        $hashedOldToken = hash('sha256', $this->oldRefreshToken);
        $tokenExists = RefreshToken::where('token', $hashedOldToken)->exists();

        Assert::assertFalse($tokenExists, 'Refresh token lama gagal dihapus (masih ada di DB)');
    }

    /**
     * @When Pengguna ingin logout dari sistem
     */
    public function penggunaInginLogoutDariSistem()
    {
       // 
    }

    /**
     * @When Menyertakan refresh token yang valid
     */
    public function menyertakanRefreshTokenYangValid()
    {
        $this->hitLogoutEndpoint($this->currentRefreshToken);
    }

    /**
     * @Then Sistem menonaktifkan access token
     */
    public function sistemMenonaktifkanAccessToken()
    {
        Assert::assertEquals(200, $this->responseContent['statusCode'] ?? $this->response->getStatusCode(), 'Logout gagal');
        
        try {
            \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::setToken($this->currentAccessToken)->checkOrFail();
            Assert::fail('Access token seharusnya ditolak, tapi masih bisa dipakai');
        } catch (\Exception $e) {
            Assert::assertTrue(true);
        }
    }

    /**
     * @Then Refresh token dihapus dari database
     */
    public function refreshTokenDihapusDariDatabase()
    {
        $hashedToken = hash('sha256', $this->currentRefreshToken);
        $tokenExists = RefreshToken::where('token', $hashedToken)->exists();

        Assert::assertFalse($tokenExists, 'Refresh token gagal dihapus saat logout');
    }

    /**
     * @When Pengguna memasukkan kredensial akun nya dengan platform :platform
     */
    public function penggunaMemasukkanKredensialAkunNyaDenganPlatform($platform)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        
        $request = Request::create('/api/v1/login', 'POST', [
            'login'       => $this->user->email,
            'password'    => $this->password,
            'platform'    => $platform, 
            'device_unq'  => 'device-test-unknown',
            'device_name' => 'Unknown Browser'
        ]);

        $request->headers->set('Accept', 'application/json');

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @Then Sistem memberikan access token
     */
    public function sistemMemberikanAccessToken()
    {
        Assert::assertEquals(200, $this->responseContent['statusCode'] ?? $this->response->getStatusCode(), 'Login gagal');
        
        $data = $this->responseContent['data'] ?? [];
        
        Assert::assertArrayHasKey('token', $data, 'Key token tidak ditemukan');
        $this->currentAccessToken = $data['token'];
    }

    /**
     * @Then Refresh token bernilai null
     */
    public function refreshTokenBernilaiNull()
    {
        $data = $this->responseContent['data'] ?? [];

        Assert::assertArrayHasKey('refresh_token', $data);
        Assert::assertNull(
            $data['refresh_token'] ?? null, 
            'Refresh token seharusnya null jika platform tidak valid, tetapi sistem malah memberikannya.'
        );
    }

    /**
     * @When Pengguna memperbarui access token dengan refresh token yang invalid
     */
    public function penggunaMemperbaruiAccessTokenDenganRefreshTokenYangInvalid()
    {
        $this->hitRefreshEndpoint('token-ngawur-yang-tidak-ada-12345');
    }

    /**
     * @Then Sistem menolak request tersebut
     */
    public function sistemMenolakRequestTersebut()
    {
        $status = $this->responseContent['statusCode'] ?? $this->response->getStatusCode();
        Assert::assertEquals(401, $status, 'Ekspektasi HTTP 401 Unauthorized');
    }

    /**
     * @Then Sistem memberikan pesan refresh token tidak valid
     */
    public function sistemMemberikanPesanRefreshTokenTidakValid()
    {
        $message = $this->responseContent['message'] ?? '';
        Assert::assertEquals('Failed: Invalid refresh token', $message);
    }

    /**
     * @When Pengguna memperbarui access token dengan refresh token yang expired
     */
    public function penggunaMemperbaruiAccessTokenDenganRefreshTokenYangExpired()
    {
        $hashedToken = hash('sha256', $this->currentRefreshToken);
        RefreshToken::where('token', $hashedToken)->update([
            'expires_at' => Carbon::now()->subDays(61)
        ]);

        $this->hitRefreshEndpoint($this->currentRefreshToken);
    }

    /**
     * @Then Sistem memberikan pesan bahwa refresh token expired
     */
    public function sistemMemberikanPesanBahwaRefreshTokenExpired()
    {
        $message = $this->responseContent['message'] ?? '';
        Assert::assertEquals('Failed: Refresh token expired', $message);
    }

    /**
     * @When Menyertakan refresh token yang tidak valid
     */
    public function menyertakanRefreshTokenYangTidakValid()
    {
        $this->hitLogoutEndpoint('token-palsu-saat-logout');
    }

    private function hitRefreshEndpoint($refreshToken)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create('/api/v1/refresh', 'POST', [
            'refresh_token' => $refreshToken
        ]);
        
        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    private function hitLogoutEndpoint($refreshToken)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create('/api/v1/logout', 'POST', [
            'refresh_token' => $refreshToken
        ]);

        $request->headers->set('Authorization', 'Bearer ' . $this->currentAccessToken);

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }
}
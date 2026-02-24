<?php

namespace Tests\Unit;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Http\Requests\RefreshRequest;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Services\RefreshTokenService;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Mockery;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    protected $password = 'password_rahasia123';
    protected $refreshTokenRepository;
    protected $userRepository;
    protected $refreshTokenService;
    protected $authController;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->refreshTokenRepository = new RefreshTokenRepository();
        $this->userRepository = new UserRepository();
        $this->refreshTokenService = new RefreshTokenService(
            $this->refreshTokenRepository, 
            $this->userRepository
        );
        $this->authController = new AuthController($this->refreshTokenService);
    }

    private function createUser($email = 'petani_test@sitanihut.com')
    {
        return User::factory()->create([
            'email' => $email,
            'password' => bcrypt($this->password),
        ]);
    }

    private function getLoginPayload($email, $platform = 'web')
    {
        return [
            'login'       => $email,
            'password'    => $this->password,
            'platform'    => $platform,
            'device_unq'  => 'device-test-1',
            'device_name' => 'Chrome Browser'
        ];
    }

    public function test_controller_login_berhasil_membuat_token_dan_refresh_token()
    {
        $user = $this->createUser();
        $request = new LoginRequest($this->getLoginPayload($user->email));

        $response = TestResponse::fromBaseResponse($this->authController->login($request));

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'refresh_token']]);
                 
        $token = $response->json('data.token');
        $refreshToken = $response->json('data.refresh_token');

        $this->assertNotEmpty($token);
        $this->assertNotEmpty($refreshToken);

        $this->assertDatabaseHas('refresh_token', [
            'id_user' => $user->id,
            'token'   => hash('sha256', $refreshToken)
        ]);
    }

    public function test_service_generate_from_login_memperbarui_data_lama_jika_device_sudah_ada()
    {
        $user = $this->createUser();

        $dvcUnq = 'device-test-1';
        $refreshToken = Str::random(64);
        RefreshToken::create([
            'id_user'     => $user->id,
            'device_unq'  => $dvcUnq,
            'device_name' => 'Old Device Name',
            'token'       => hash('sha256', $refreshToken),
            'expires_at'  => Carbon::now()->subDays(61)
        ]);

        $newRefreshToken = $this->refreshTokenService->generateFromLogin($user->id, 'web', $dvcUnq, 'New Device Name');

        $this->assertDatabaseHas('refresh_token', [
            'id_user' => $user->id,
            'token'   => hash('sha256', $newRefreshToken),
            'device_name' => 'New Device Name'
        ]);
    }

    public function test_controller_refresh_token_berhasil_generate_token_baru()
    {
        $user = $this->createUser();
        $oldRefreshToken = Str::random(64);
        RefreshToken::create([
            'id_user'     => $user->id,
            'device_unq'  => 'device-test-1',
            'device_name' => 'Chrome Browser',
            'token'       => hash('sha256', $oldRefreshToken),
            'expires_at'  => Carbon::now()->addDays(60)
        ]);

        $request = \Mockery::mock(RefreshRequest::class);
        $request->shouldReceive('validated')->andReturn(['refresh_token' => $oldRefreshToken]);
        $request->shouldReceive('input')->with('refresh_token')->andReturn($oldRefreshToken);

        $response = TestResponse::fromBaseResponse($this->authController->refreshToken($request));

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['token', 'refresh_token']]);
                 
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.refresh_token'));
        
        $newRefreshToken = $response->json('data.refresh_token');
        $this->assertNotEquals($oldRefreshToken, $newRefreshToken);

        $this->assertDatabaseMissing('refresh_token', [
            'token' => hash('sha256', $oldRefreshToken)
        ]);

        $this->assertDatabaseHas('refresh_token', [
            'token' => hash('sha256', $newRefreshToken)
        ]);
    }

    public function test_controller_refresh_token_gagal_invalid_token()
    {
        $oldRefreshToken = Str::random(64);

        $request = \Mockery::mock(RefreshRequest::class);
        $request->shouldReceive('validated')->andReturn(['refresh_token' => $oldRefreshToken]);
        $request->shouldReceive('input')->with('refresh_token')->andReturn($oldRefreshToken);

        $response = TestResponse::fromBaseResponse($this->authController->refreshToken($request));

        $response->assertStatus(401)
                    ->assertJsonStructure(['message', 'statusCode', 'data']);
        $message = $response->json('message');

        $this->assertStringContainsString('Invalid', $message);
    }

    public function test_service_deactivate_token_berhasil_menonaktifkan_refresh_token()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();
        $refreshToken = Str::random(64);
        
        RefreshToken::create([
            'id_user'     => $user->id,
            'device_unq'  => 'device-test-1',
            'device_name' => 'Chrome Browser',
            'token'       => hash('sha256', $refreshToken),
            'expires_at'  => Carbon::now()->addDays(60)
        ]);

        $this->refreshTokenService->deactivateToken($user->id, $refreshToken);

        $refreshRecord = RefreshToken::where('token', hash('sha256', $refreshToken))->exists();
        $this->assertFalse($refreshRecord, 'Refresh token seharusnya sudah dihapus dari database.');
    }

    public function test_service_generate_from_login_platform_yang_tidak_terdaftar()
    {
        $user = $this->createUser();
        $dvcUnq = 'device-test-1';

        $newRefreshToken = $this->refreshTokenService->generateFromLogin($user->id, 'desktop', $dvcUnq, 'New Device Name');

        $this->assertEquals(null, $newRefreshToken, 'Refresh Token harus Null');
    }

    public function test_service_generate_new_token_refresh_token_yang_tidak_valid()
    {
        $refreshToken = Str::random(64);
        
        $generateToken = $this->refreshTokenService->generateNewToken($refreshToken);
        $message = $generateToken['message'];

        $this->assertStringContainsString('Invalid', $message);
    }

    public function test_service_generate_new_token_refresh_token_expired()
    {
        $user = $this->createUser();
        $dvcUnq = 'device-test-1';
        $refreshToken = Str::random(64);
        RefreshToken::create([
            'id_user'     => $user->id,
            'device_unq'  => $dvcUnq,
            'device_name' => 'Old Device Name',
            'token'       => hash('sha256', $refreshToken),
            'expires_at'  => Carbon::now()->subDays(61)
        ]);
        
        $generateToken = $this->refreshTokenService->generateNewToken($refreshToken);
        $message = $generateToken['message'];

        $this->assertStringContainsString('expired', $message);
    }

    public function test_controller_logout_dengan_refresh_token_yang_tidak_valid()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->sekretariat()->create();

        $guardMock = Mockery::mock(\PHPOpenSourceSaver\JWTAuth\JWTGuard::class);
        $guardMock->shouldReceive('id')->once()->andReturn($user->id);
        $guardMock->shouldReceive('logout')->with(true)->once()->andReturnNull();

        Auth::shouldReceive('guard')
            ->with('api')
            ->andReturn($guardMock);

        $request = new LogoutRequest();
        $request->merge(['refresh_token' => 'invalid-refresh-token-12345']);
        $request->setContainer($this->app);
        $request->setValidator(
            app('validator')->make($request->all(), $request->rules())
        );

        $response = TestResponse::fromBaseResponse($this->authController->logout($request));

        $response->assertStatus(200);

        $refreshRecord = RefreshToken::where('token', hash('sha256', 'invalid-refresh-token-12345'))->exists();
        $this->assertFalse($refreshRecord);
    }
}
<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPenyuluhContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    
    protected $endpointList = '/api/v1/user-penyuluh';
    protected $endpointDetail = '/api/v1/user-penyuluh/';
    
    protected $queryParams = [];
    protected $targetPenyuluhId;
    protected $kphId;

    /**
     * Membangun Ekosistem KPH dan Penyuluh
     */
    protected function setupEcosystem()
    {
        if ($this->targetPenyuluhId) return;

        // 1. Buat KPH Dummy
        $this->kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $this->kphId, 
            'nama_kph'   => 'KPH Pengujian Penyuluh',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 88, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999), 
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Buat Penyuluh Target (Pria, Nama spesifik untuk diuji filternya)
        $penyuluh = User::factory()->penyuluh()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user'     => 'Tirta Penyuluh Pengujian'
        ])->create([
            'id_kph' => $this->kphId
        ]);
        
        $this->targetPenyuluhId = $penyuluh->id;
    }

    /**
     * Helper untuk HTTP GET Request
     */
    protected function sendGetRequest($url)
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create($url, 'GET');
        $request->headers->set('Accept', 'application/json');
        
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    // --- AUTHENTICATION ---

    /**
     * @Given Pengguna dengan peran sekretariat login untuk melihat akun penyuluh
     */
    public function penggunaDenganPeranSekretariatLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->sekretariat()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran auditor login untuk melihat akun penyuluh
     */
    public function penggunaDenganPeranAuditorLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->auditor()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran penanggung jawab login untuk melihat akun penyuluh
     */
    public function penggunaDenganPeranPenanggungJawabLogin()
    {
        $this->setupEcosystem();
        // Set Penanggung Jawab di KPH yang sama dengan penyuluh target
        $this->user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $this->kphId
        ]);
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran selain sekretariat, auditor, dan penanggung jawab login untuk melihat akun penyuluh
     */
    public function penggunaDenganPeranSelainDiAtasLogin()
    {
        $this->setupEcosystem();
        // Petani mencoba melihat list penyuluh (Seharusnya ditolak)
        $this->user = User::factory()->petani()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    // --- SETUP FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci untuk list penyuluh
     */
    public function penggunaMemasukkanKataKunci()
    {
        $this->queryParams['search'] = 'Tirta';
    }

    /**
     * @Given Pengguna ingin melihat penyuluh yang berjenis kelamin pria
     */
    public function penggunaInginMelihatPenyuluhYangBerjenisKelaminPria()
    {
        $this->queryParams['jenis_kelamin'] = 'pria';
    }

    /**
     * @Given Pengguna ingin list penyuluh diurutkan berdasarkan nama penyuluh
     */
    public function penggunaInginListDiurutkanBerdasarkanNama()
    {
        $this->queryParams['sort_by'] = 'nama_user';
    }

    /**
     * @Given Pengguna ingin list penyuluh diurutkan secara descending
     */
    public function penggunaInginListDiurutkanSecaraDescending()
    {
        $this->queryParams['sort_direction'] = 'desc';
    }

    // --- SETUP INVALID FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci untuk list penyuluh yang sangat panjang
     */
    public function penggunaMemasukkanKataKunciSangatPanjang()
    {
        $this->queryParams['search'] = str_repeat('A', 105); // Max 100
    }

    /**
     * @Given Pengguna ingin melihat penyuluh yang berjenis kelamin selain pria dan wanita
     */
    public function penggunaInginMelihatBerjenisKelaminSelainPriaDanWanita()
    {
        $this->queryParams['jenis_kelamin'] = 'alien'; 
    }

    /**
     * @Given Pengguna ingin list penyuluh diurutkan berdasarkan nomor induk
     */
    public function penggunaInginListDiurutkanBerdasarkanNomorInduk()
    {
        $this->queryParams['sort_by'] = 'nomor_induk'; // Invalid rule
    }

    /**
     * @Given Pengguna ingin list penyuluh diurutkan selain ascending dan descending
     */
    public function penggunaInginListDiurutkanSelainAscendingDanDescending()
    {
        $this->queryParams['sort_direction'] = 'acak'; 
    }

    // --- ACTIONS ---

    /**
     * @When Pengguna ingin melihat list penyuluh
     */
    public function penggunaInginMelihatListPenyuluh()
    {
        $queryString = http_build_query($this->queryParams);
        $url = $this->endpointList . ($queryString ? '?' . $queryString : '');
        $this->sendGetRequest($url);
    }

    /**
     * @When Pengguna ingin melihat detail akun penyuluh
     */
    public function penggunaInginMelihatDetailAkunPenyuluh()
    {
        $this->sendGetRequest($this->endpointDetail . $this->targetPenyuluhId);
    }

    // --- ASSERTIONS LIST ---

    /**
     * @Then Sistem memberikan pesan sukses list penyuluh
     */
    public function sistemMemberikanPesanSuksesListPenyuluh()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil list. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data list penyuluh
     */
    public function terdapatDataListPenyuluh()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list penyuluh kosong padahal ekspektasinya ada. \nIsi Response: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Penyuluh yang berada di KPH yang sama dengan penanggung jawab
     */
    public function penyuluhYangBeradaDiKphYangSamaDenganPenanggungJawab()
    {
        $penyuluhList = $this->responseContent['data']['data'] ?? [];
        foreach ($penyuluhList as $penyuluh) {
            Assert::assertEquals(
                $this->user->id_kph, 
                $penyuluh['id_kph'], 
                "Terjadi kebocoran data! Penanggung Jawab melihat Penyuluh dari KPH lain."
            );
        }
    }

    // --- ASSERTIONS DETAIL ---

    /**
     * @Then Sistem memberikan pesan sukses detail penyuluh
     */
    public function sistemMemberikanPesanSuksesDetailPenyuluh()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil list. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data detail penyuluh
     */
    public function terdapatDataDetailPenyuluh()
    {
        $data = $this->responseContent['data'] ?? null;
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if (!$data) {
            throw new \Exception("Data detail (key 'data') tidak ditemukan di response. \nIsi Response: $responseString");
        }

        // Sudah disesuaikan dengan key id_user sesuai Resource Laravel Anda
        if (!isset($data['id_user'])) { 
            throw new \Exception("Detail penyuluh tidak memiliki key 'id_user'. \nIsi Response: $responseString");
        }
        
        Assert::assertTrue(true);
    }

    // --- ASSERTIONS ERROR ---

    /**
     * @Then Sistem memberikan pesan error terkait validasi request list penyuluh yang tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitValidasiRequest()
    {
        $status = $this->response->getStatusCode();
        $responseString = json_encode($this->responseContent);

        if ($status !== 422) {
            throw new \Exception("Ekspektasi 422 Unprocessable Entity, tapi mendapat $status. Response: $responseString");
        }

        Assert::assertStringContainsString('The search field must not be greater than 100 characters', $responseString);
        Assert::assertStringContainsString('The selected jenis kelamin is invalid', $responseString);
        Assert::assertStringContainsString('The selected sort by is invalid', $responseString);
        Assert::assertStringContainsString('The selected sort direction is invalid', $responseString);
    }

    /**
     * @Then Sistem memberikan pesan error terkait hak akses melihat list penyuluh
     * @Then Sistem memberikan pesan error terkait hak akses melihat detail penyuluh
     */
    public function sistemMemberikanPesanErrorTerkaitHakAksesMelihat()
    {
        $status = $this->response->getStatusCode();
        Assert::assertTrue(
            in_array($status, [401, 403, 500]), 
            "Sistem harusnya memblokir akses (401/403/500), tapi mendapatkan status HTTP: $status"
        );
    }
}
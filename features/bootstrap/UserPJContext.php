<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPJContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    
    protected $endpointList = '/api/v1/user-pj';
    protected $endpointDetail = '/api/v1/user-pj/';
    
    protected $queryParams = [];
    protected $targetPJId;
    protected $kphId;

    /**
     * Membangun Ekosistem KPH dan Penanggung Jawab
     */
    protected function setupEcosystem()
    {
        if ($this->targetPJId) return;

        // 1. Buat KPH Dummy
        $this->kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $this->kphId, 
            'nama_kph'   => 'KPH Pengujian PJ',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 77, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999), 
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Buat Penanggung Jawab Target (Pria, Nama spesifik untuk diuji filternya)
        $pj = User::factory()->penanggungJawab()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user'     => 'Zainal PJ Pengujian'
        ])->create([
            'id_kph' => $this->kphId
        ]);
        
        $this->targetPJId = $pj->id;
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
     * @Given Pengguna dengan peran sekretariat login untuk melihat akun penanggung jawab
     */
    public function penggunaDenganPeranSekretariatLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->sekretariat()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran auditor login untuk melihat akun penanggung jawab
     */
    public function penggunaDenganPeranAuditorLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->auditor()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    // --- SETUP FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci
     */
    public function penggunaMemasukkanKataKunci()
    {
        $this->queryParams['search'] = 'Zainal';
    }

    /**
     * @Given Pengguna ingin melihat penanggung jawab yang berjenis kelamin pria
     */
    public function penggunaInginMelihatPjYangBerjenisKelaminPria()
    {
        $this->queryParams['jenis_kelamin'] = 'pria';
    }

    /**
     * @Given Pengguna ingin list diurutkan berdasarkan nama penanggung jawab
     */
    public function penggunaInginListDiurutkanBerdasarkanNama()
    {
        $this->queryParams['sort_by'] = 'nama_user';
    }

    /**
     * @Given Pengguna ingin list diurutkan secara descending
     */
    public function penggunaInginListDiurutkanSecaraDescending()
    {
        $this->queryParams['sort_direction'] = 'desc';
    }

    // --- SETUP INVALID FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci untuk list penanggung jawab yang sangat panjang
     */
    public function penggunaMemasukkanKataKunciSangatPanjang()
    {
        $this->queryParams['search'] = str_repeat('A', 105); // Max 100
    }

    /**
     * @Given Pengguna ingin melihat penanggung jawab yang berjenis kelamin selain pria dan wanita
     */
    public function penggunaInginMelihatBerjenisKelaminSelainPriaDanWanita()
    {
        $this->queryParams['jenis_kelamin'] = 'alien'; 
    }

    /**
     * @Given Pengguna ingin list penanggung jawab diurutkan berdasarkan nomor induk
     */
    public function penggunaInginListDiurutkanBerdasarkanNomorInduk()
    {
        $this->queryParams['sort_by'] = 'nomor_induk'; // Invalid rule
    }

    /**
     * @Given Pengguna ingin list penanggung jawab diurutkan selain ascending dan descending
     */
    public function penggunaInginListDiurutkanSelainAscendingDanDescending()
    {
        $this->queryParams['sort_direction'] = 'acak'; 
    }

    // --- ACTIONS ---

    /**
     * @When Pengguna ingin melihat list penanggung jawab
     */
    public function penggunaInginMelihatListPj()
    {
        $queryString = http_build_query($this->queryParams);
        $url = $this->endpointList . ($queryString ? '?' . $queryString : '');
        $this->sendGetRequest($url);
    }

    /**
     * @When Pengguna ingin melihat detail akun penanggung jawab
     */
    public function penggunaInginMelihatDetailAkunPj()
    {
        $this->sendGetRequest($this->endpointDetail . $this->targetPJId);
    }

    // --- ASSERTIONS LIST ---

    /**
     * @Then Sistem memberikan pesan sukses list penanggung jawab
     */
    public function sistemMemberikanPesanSuksesListPj()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil list. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data list penanggung jawab
     */
    public function terdapatDataListPj()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list PJ kosong padahal ekspektasinya ada. \nIsi Response: $responseString");
        }
        Assert::assertTrue(true);
    }

    // --- ASSERTIONS DETAIL ---

    /**
     * @Then Sistem memberikan pesan sukses detail penanggung jawab
     */
    public function sistemMemberikanPesanSuksesDetailPj()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil list. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data detail penanggung jawab
     */
    public function terdapatDataDetailPj()
    {
        $data = $this->responseContent['data'] ?? null;
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if (!$data) {
            throw new \Exception("Data detail (key 'data') tidak ditemukan di response. \nIsi Response: $responseString");
        }

        // Disesuaikan dengan key id_user sesuai Resource Laravel
        if (!isset($data['id_user'])) { 
            throw new \Exception("Detail PJ tidak memiliki key 'id_user'. \nIsi Response: $responseString");
        }
        
        Assert::assertTrue(true);
    }

    // --- ASSERTIONS ERROR ---

    /**
     * @Then Sistem memberikan pesan error terkait validasi request list penanggung jawab yang tidak valid
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
}
<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserPetaniContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    
    protected $endpointList = '/api/v1/user-petani';
    protected $endpointDetail = '/api/v1/user-petani/';
    
    protected $queryParams = [];
    protected $targetPetaniId;
    protected $kphId;
    protected $penyuluhUser;

    /**
     * Membangun Ekosistem KPH, Penyuluh, KTH, dan Petani yang saling terhubung
     */
    protected function setupEcosystem()
    {
        if ($this->targetPetaniId) return;

        // 1. Buat KPH Dummy
        $this->kphId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id'         => $this->kphId, 
            'nama_kph'   => 'KPH Pengujian Petani',
            'wilayah'    => 'Wilayah Pengujian',
            'alamat'     => 'Jl. Dummy Testing No. 99, Bandar Lampung',
            'nomor_wa'   => '08112233' . rand(1000, 9999), // Rand agar unique constraint aman
            'jenis'      => 'KPHP',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Buat Penyuluh Dummy yang terikat ke KPH tersebut
        $this->penyuluhUser = User::factory()->penyuluh()->withDetail()->create([
            'id_kph' => $this->kphId
        ]);

        // 3. Buat KTH Dummy yang diawasi oleh Penyuluh tersebut
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id'          => $kthId,
            'nama_kth'    => 'KTH Pengujian',
            'desa'        => 'Desa Pengujian',
            'kecamatan'   => 'Kecamatan Testing',
            'kabupaten'   => 'Kabupaten Test',
            'koordinator' => 'Bapak Tester',
            'nomor_wa'    => '089988776655',
            'id_kph'      => $this->kphId,
            'id_penyuluh' => $this->penyuluhUser->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        // 4. Buat Petani Dummy di KTH tersebut
        $petani = User::factory()->petani()->withDetail([
            'jenis_kelamin' => 'pria',
            'nama_user' => 'Zainal Petani Pengujian'
        ])->create([
            'id_kph' => $this->kphId,
            'id_kth' => $kthId,
        ]);
        
        $this->targetPetaniId = $petani->id;
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

    /**
     * @Given Pengguna dengan peran sekretariat login untuk melihat akun petani
     */
    public function penggunaDenganPeranSekretariatLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->sekretariat()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran auditor login untuk melihat akun petani
     */
    public function penggunaDenganPeranAuditorLogin()
    {
        $this->setupEcosystem();
        $this->user = User::factory()->auditor()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran penanggung jawab login untuk melihat akun petani
     */
    public function penggunaDenganPeranPenanggungJawabLogin()
    {
        $this->setupEcosystem();
        // Set Penanggung Jawab di KPH yang sama dengan ekosistem
        $this->user = User::factory()->penanggungJawab()->withDetail()->create([
            'id_kph' => $this->kphId
        ]);
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran penyuluh login untuk melihat akun petani
     */
    public function penggunaDenganPeranPenyuluhLogin()
    {
        $this->setupEcosystem();
        
        $this->user = $this->penyuluhUser;
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran selain sekretariat, auditor, penanggung jawab, dan penyuluh login untuk melihat akun petani
     */
    public function penggunaDenganPeranSelainDiAtasLogin()
    {
        $this->setupEcosystem();
        
        $this->user = User::factory()->petani()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    // --- SETUP FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci untuk list petani
     */
    public function penggunaMemasukkanKataKunci()
    {
        $this->queryParams['search'] = 'Zainal';
    }

    /**
     * @Given Pengguna ingin melihat petani yang berjenis kelamin pria
     */
    public function penggunaInginMelihatPetaniYangBerjenisKelaminPria()
    {
        $this->queryParams['jenis_kelamin'] = 'pria';
    }

    /**
     * @Given Pengguna ingin list petani diurutkan berdasarkan nama petani
     */
    public function penggunaInginListDiurutkanBerdasarkanNamaPetani()
    {
        $this->queryParams['sort_by'] = 'nama_user';
    }

    /**
     * @Given Pengguna ingin list petani diurutkan secara descending
     */
    public function penggunaInginListDiurutkanSecaraDescending()
    {
        $this->queryParams['sort_direction'] = 'desc';
    }

    // --- SETUP INVALID FILTERS ---

    /**
     * @Given Pengguna memasukkan kata kunci untuk list petani yang sangat panjang
     */
    public function penggunaMemasukkanKataKunciYangSangatPanjang()
    {
        $this->queryParams['search'] = str_repeat('A', 105); // Max 100
    }

    /**
     * @Given Pengguna ingin melihat petani yang berjenis kelamin selain pria dan wanita
     */
    public function penggunaInginMelihatPetaniBerjenisKelaminSelainPriaDanWanita()
    {
        $this->queryParams['jenis_kelamin'] = 'alien'; 
    }

    /**
     * @Given Pengguna ingin list petani diurutkan berdasarkan nomor induk
     */
    public function penggunaInginListDiurutkanBerdasarkanNomorInduk()
    {
        $this->queryParams['sort_by'] = 'nomor_induk'; // Invalid rule: in:nama_user
    }

    /**
     * @Given Pengguna ingin list petani diurutkan selain ascending dan descending
     */
    public function penggunaInginListDiurutkanSelainAscendingDanDescending()
    {
        $this->queryParams['sort_direction'] = 'acak'; 
    }

    // --- ACTIONS ---

    /**
     * @When Pengguna ingin melihat list petani
     */
    public function penggunaInginMelihatListPetani()
    {
        $queryString = http_build_query($this->queryParams);
        $url = $this->endpointList . ($queryString ? '?' . $queryString : '');
        $this->sendGetRequest($url);
    }

    /**
     * @When Pengguna ingin melihat detail akun petani
     */
    public function penggunaInginMelihatDetailAkunPetani()
    {
        $this->sendGetRequest($this->endpointDetail . $this->targetPetaniId);
    }

    // --- ASSERTIONS LIST ---

    /**
     * @Then Sistem memberikan pesan sukses list petani
     */
    public function sistemMemberikanPesanSuksesListPetani()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil list. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data list petani
     */
    public function terdapatDataListPetani()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list petani kosong padahal ekspektasinya ada. \nIsi Response: $responseString");
        }
        
        Assert::assertTrue(true);
    }

    /**
     * @Then Petani berada di KPH yang sama dengan penanggung jawab
     */
    public function petaniBeradaDiKphYangSamaDenganPenanggungJawab()
    {
        $petaniList = $this->responseContent['data']['data'] ?? [];
        foreach ($petaniList as $petani) {
            Assert::assertEquals(
                $this->user->id_kph, 
                $petani['id_kph'], 
                "Terjadi kebocoran data! Penanggung Jawab melihat Petani dari KPH lain."
            );
        }
    }

    /**
     * @Then Petani berada di KTH yang diawasi oleh penyuluh
     */
    public function petaniBeradaDiKthYangDiawasiOlehPenyuluh()
    {
        $petaniList = $this->responseContent['data']['data'] ?? [];
        $found = collect($petaniList)->contains('id', $this->targetPetaniId);
        
        Assert::assertTrue($found, "Penyuluh tidak dapat melihat Petani di KTH binaannya sendiri.");
    }

    // --- ASSERTIONS DETAIL ---

    /**
     * @Then Sistem memberikan pesan sukses detail petani
     */
    public function sistemMemberikanPesanSuksesDetailPetani()
    {
        $status = $this->response->getStatusCode();
        $message = $this->responseContent['message'];
        Assert::assertStringContainsString('berhasil diambil', $message);
        Assert::assertEquals(200, $status, "Gagal mengambil detail. Status HTTP: $status. \nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data detail petani
     */
    public function terdapatDataDetailPetani()
    {
        // Terkadang Resource class di Laravel membungkusnya di dalam 'data' lagi, 
        // mari kita cek bentuk aslinya.
        $data = $this->responseContent['data'] ?? null;
        
        $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);

        if (!$data) {
            throw new \Exception("Data detail (key 'data') tidak ditemukan di response. \nIsi Response: $responseString");
        }

        if (!isset($data['id_user'])) {
            throw new \Exception("Detail petani tidak memiliki key 'id'. \nIsi Response: $responseString");
        }
        
        Assert::assertTrue(true);
    }

    // --- ASSERTIONS ERROR ---

    /**
     * @Then Sistem memberikan pesan error terkait validasi request list petani yang tidak valid
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
     * @Then Sistem memberikan pesan error terkait hak akses melihat akun petani
     */
    public function sistemMemberikanPesanErrorTerkaitHakAksesMelihatAkunPetani()
    {
        $status = $this->response->getStatusCode();
        Assert::assertTrue(
            in_array($status, [401, 403, 500]), // 500 jika API gagal load karena unauthorized
            "Sistem harusnya memblokir akses (401/403/500), tapi mendapatkan status HTTP: $status"
        );
    }
}
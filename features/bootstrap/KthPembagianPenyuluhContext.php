<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KthPembagianPenyuluhContext implements Context
{
    protected $user;
    protected $token;
    protected $requestData = [];
    protected $response;
    protected $responseContent;
    
    // Properti bantuan
    protected $targetKthId;
    protected $targetKphId;
    protected $targetPenyuluhId;
    protected $httpMethod = 'POST';
    protected $endpoint = '/api/v1/kth';

    /**
     * @Given Pengguna dengan peran sekretariat login untuk manajemen KTH
     */
    public function penggunaDenganPeranSekretariatLoginKeDalamSistem()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran penyuluh login untuk manajemen KTH
     */
    public function penggunaDenganPeranPenyuluhLoginKeDalamSistem()
    {
        $kph = \Illuminate\Support\Facades\DB::table('kph')->first();
        $this->user = User::factory()->penyuluh()->create(
            ['id_kph' => $kph->id]
        );
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @When Pengguna ingin membuat data KTH
     */
    public function penggunaInginMembuatDataKth()
    {
        $this->httpMethod = 'POST';
        $this->endpoint = '/api/v1/kth';
        
        $this->requestData = [
            'nama_kth' => 'KTH Suka Makmur',
            'desa' => 'Suka Maju',
            'kecamatan' => 'Way Lima',
            'kabupaten' => 'Pesawaran',
            'koordinator' => 'Bapak Budi',
            'nomor_wa' => '08123456789'
        ];
    }

    /**
     * @When Pengguna ingin memperbarui data KTH
     */
    public function penggunaInginMemperbaruiDataKth()
    {
        $this->httpMethod = 'PATCH';
        
        $kphId = DB::table('kph')->first()->id;
        $kthId = Str::uuid()->toString();

        // Buat KTH dummy dulu ke database
        DB::table('kth')->insert([
            'id' => $kthId,
            'nama_kth' => 'KTH Lama',
            'desa' => 'Desa Lama',
            'kecamatan' => 'Kecamatan Lama',
            'kabupaten' => 'Kabupaten Lama',
            'koordinator' => 'Lama',
            'nomor_wa' => '0000',
            'id_kph' => $kphId
        ]);
        
        $this->targetKthId = $kthId;
        $this->endpoint = '/api/v1/kth/' . $this->targetKthId;

        $this->requestData = [
            'nama_kth' => 'KTH Diperbarui',
        ];
    }

    /**
     * @When Pengguna ingin memperbarui data KTH yang diawasi olehnya
     */
    public function penggunaInginMemperbaruiDataKthYangDiawasiOlehnya()
    {
        $this->httpMethod = 'PATCH';
        
        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id' => $kthId,
            'nama_kth' => 'KTH Binaan Saya',
            'desa' => 'Desa Binaan',
            'kecamatan' => 'Kec Binaan',
            'kabupaten' => 'Kab Binaan',
            'koordinator' => 'Koordinator',
            'nomor_wa' => '081111',
            'id_penyuluh' => $this->user->id,
            'id_kph' => $this->user->id_kph ?? DB::table('kph')->first()->id
        ]);
        
        $this->targetKthId = $kthId;
        $this->endpoint = '/api/v1/kth/' . $this->targetKthId;

        $this->requestData = [
            'nama_kth' => 'KTH Binaan Saya (Update)',
        ];
    }

    /**
     * @When Menentukan pengawas yang berada di KPH yang ditentukan
     */
    public function menentukanPengawasYangBeradaDiKphYangDitentukan()
    {
        $kph = DB::table('kph')->first();
        $penyuluh = User::factory()->penyuluh()->create(['id_kph' => $kph->id]);

        $this->requestData['id_kph'] = $kph->id;
        $this->requestData['id_penyuluh'] = $penyuluh->id;
        
        $this->eksekusiRequest();
    }

    /**
     * @When Pengawas yang ditentukan tidak ditemukan
     */
    public function pengawasYangDitentukanTidakDitemukan()
    {
        $kph = DB::table('kph')->first();
        
        $this->requestData['id_kph'] = $kph->id;
        $this->requestData['id_penyuluh'] = Str::uuid()->toString(); // UUID Acak yang tidak ada di DB
        
        $this->eksekusiRequest();
    }

    /**
     * @When Pengawas yang ditentukan tidak berada di KPH yang telah ditentukan
     */
    public function pengawasYangDitentukanTidakBeradaDiKphYangTelahDitentukan()
    {
        $kphA = DB::table('kph')->first();
        $kphB = DB::table('kph')->skip(1)->first(); // KPH Berbeda

        $penyuluhDiKphB = User::factory()->penyuluh()->create(['id_kph' => $kphB->id]);

        $this->requestData['id_kph'] = $kphA->id; // KPH A
        $this->requestData['id_penyuluh'] = $penyuluhDiKphB->id; // Tapi penyuluh ada di KPH B
        
        $this->eksekusiRequest();
    }

    /**
     * @When Membarui penyuluh yang mengawasi dan KPH yang menaungi KTH
     */
    public function membaruiPenyuluhYangMengawasiDanKphYangMenaungiKth()
    {
        $kphBaruId = Str::uuid()->toString();
        DB::table('kph')->insert([
            'id' => $kphBaruId,
            'nama_kph' => 'KPH Dummy B',
            'wilayah'  => 'Wilayah Dummy B',
            'alamat'   => 'Alamat Wilayah',
            'nomor_wa' => '0813' . rand(10000000, 99999999),
            'jenis'    => 'KPHK'
        ]);
        
        $penyuluhBaru = User::factory()->penyuluh()->create(['id_kph' => $kphBaruId]);

        $this->requestData['id_kph'] = $kphBaruId;
        $this->requestData['id_penyuluh'] = $penyuluhBaru->id;
        
        $this->eksekusiRequest();
    }

    /**
     * @When Pengguna ingin melihat list KTH yang diawasi olehnya
     */
    public function penggunaInginMelihatListKthYangDiawasiOlehnya()
    {
        // 1. Buat KTH milik penyuluh lain (harusnya tidak masuk list)
        $kphId = DB::table('kph')->first()->id;
        $penyuluhLain = User::factory()->penyuluh()->create(['id_kph' => $kphId]);
        
        DB::table('kth')->insert([
            'id' => Str::uuid()->toString(),
            'nama_kth' => 'KTH Orang Lain 1',
            'desa' => 'Desa A',
            'kecamatan' => 'Kec A',
            'kabupaten' => 'Kab A',
            'koordinator' => 'Koordinator A',
            'nomor_wa' => '08122',
            'id_penyuluh' => $penyuluhLain->id,
            'id_kph' => $kphId
        ]);

        // 2. Buat 2 KTH milik $this->user (yang diawasi olehnya)
        DB::table('kth')->insert([
            'id' => Str::uuid()->toString(),
            'nama_kth' => 'KTH Binaan Saya 1',
            'desa' => 'Desa C',
            'kecamatan' => 'Kec C',
            'kabupaten' => 'Kab C',
            'koordinator' => 'Koordinator C',
            'nomor_wa' => '08124',
            'id_penyuluh' => $this->user->id,
            'id_kph' => $this->user->id_kph ?? $kphId
        ]);
        DB::table('kth')->insert([
            'id' => Str::uuid()->toString(),
            'nama_kth' => 'KTH Binaan Saya 2',
            'desa' => 'Desa D',
            'kecamatan' => 'Kec D',
            'kabupaten' => 'Kab D',
            'koordinator' => 'Koordinator D',
            'nomor_wa' => '08125',
            'id_penyuluh' => $this->user->id,
            'id_kph' => $this->user->id_kph ?? $kphId
        ]);

        $this->httpMethod = 'GET';
        $this->endpoint = '/api/v1/kth';
        $this->eksekusiRequest();
    }

    /**
     * @When Pengguna ingin menghapus KTH yang tidak diawasi olehnya
     */
    public function penggunaInginMenghapusKthYangTidakDiawasiOlehnya()
    {
        $this->httpMethod = 'DELETE';
        
        $kphId = DB::table('kph')->first()->id;

        $penyuluhLain = User::factory()->penyuluh()->create([
            'id_kph' => $kphId
        ]);

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id' => $kthId,
            'nama_kth' => 'KTH Orang Lain',
            'desa' => '-', 
            'kecamatan' => '-', 
            'kabupaten' => '-', 
            'koordinator' => '-', 
            'nomor_wa' => '-',
            'id_penyuluh' => $penyuluhLain->id,
            'id_kph' => $kphId 
        ]);
        
        $this->endpoint = '/api/v1/kth/' . $kthId;
        $this->eksekusiRequest();
    }

    // ==========================================
    // EXECUTE & ASSERTIONS (THEN)
    // ==========================================

    protected function eksekusiRequest()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create($this->endpoint, $this->httpMethod, $this->requestData);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Authorization', 'Bearer ' . $this->token);

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @Then Sistem memberikan pesan sukses KTH
     */
    public function sistemMemberikanPesanSukses()
    {
        if (!$this->response) {
            $this->eksekusiRequest();
        }

        $status = $this->response->getStatusCode();
        if (!in_array($status, [200, 201])) {
            throw new \Exception(
                "Gagal! Status HTTP $status bukan sukses (200/201).\n" .
                "Isi Response API: " . json_encode($this->responseContent, JSON_PRETTY_PRINT)
            );
        }

        // Jika request adalah GET ke /api/v1/kth (Penyuluh melihat list KTH yang diawasi)
        if ($this->httpMethod === 'GET' && preg_match('#/api/v1/kth$#', $this->endpoint)) {
            $data = $this->responseContent['data']['data'] ?? [];
            if (count($data) !== 2) {
                throw new \Exception("Gagal! Jumlah KTH yang diawasi tidak sesuai. Diharapkan 2, didapat: " . count($data));
            }

            foreach ($data as $kth) {
                if (!array_key_exists('id_penyuluh', $kth)) {
                    throw new \Exception("Gagal! Response KTH tidak mengandung key 'id_penyuluh'.");
                }
                if (!array_key_exists('nama_penyuluh', $kth)) {
                    throw new \Exception("Gagal! Response KTH tidak mengandung key 'nama_penyuluh'.");
                }
                if ($kth['id_penyuluh'] !== $this->user->id) {
                    throw new \Exception("Gagal! id_penyuluh ({$kth['id_penyuluh']}) tidak sesuai dengan user yang login ({$this->user->id}).");
                }
            }
        }

        // Jika request adalah POST atau PATCH ke /api/v1/kth (KTH Detail)
        if (in_array($this->httpMethod, ['POST', 'PATCH']) && preg_match('#/api/v1/kth#', $this->endpoint)) {
            $data = $this->responseContent['data'] ?? [];
            if (!array_key_exists('id_penyuluh', $data)) {
                throw new \Exception("Gagal! Response KTH tidak mengandung key 'id_penyuluh'.");
            }
            if (!array_key_exists('nama_penyuluh', $data)) {
                throw new \Exception("Gagal! Response KTH tidak mengandung key 'nama_penyuluh'.");
            }
        }

        Assert::assertTrue(true);
    }

    /**
     * @Then KTH sudah memiliki pengawas
     * @Then KTH diawasi oleh penyuluh yang membuat
     */
    public function kthSudahMemilikiPengawas()
    {
        $idPenyuluhResponse = $this->responseContent['data']['id_penyuluh'] ?? null;
        if (is_null($idPenyuluhResponse)) {
            throw new \Exception('id_penyuluh di response kosong.');
        }
    }

    /**
     * @Then Sistem memberikan pesan error terkait penyuluh tidak ditemukan
     */
    public function sistemMemberikanPesanErrorTerkaitPenyuluhTidakDitemukan()
    {
        Assert::assertEquals(422, $this->response->getStatusCode());
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertArrayHasKey('id_penyuluh', $errors);
    }

    /**
     * @Then Sistem memberikan pesan error terkait ketidaksesuaian Penyuluh dan KPH
     */
    public function sistemMemberikanPesanErrorTerkaitKetidaksesuaianPenyuluhDanKph()
    {
        $status = $this->response->getStatusCode();
        Assert::assertTrue(in_array($status, [400, 422]), "Sistem gagal menolak ketidaksesuaian (Status: $status)");
    }

    /**
     * @Then Sistem memberikan pesan error terkait data tidak boleh diubah
     * @Then Sistem memberikan pesan error terkait hak akses
     */
    public function sistemMemberikanPesanErrorTerkaitHakAkses()
    {
        $status = $this->response->getStatusCode();
        Assert::assertTrue(
            in_array($status, [403, 422]), 
            "Sistem tidak memblokir dengan 403 atau 422. Didapat: $status"
        );
    }
}
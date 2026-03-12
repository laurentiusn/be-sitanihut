<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RekapContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    protected $endpoint;
    protected $hasDummyData;
    protected $exportPayload = [];

    protected function siapkanDataProduksiNteDummy()
    {
        if ($this->hasDummyData) return;

        $kph = DB::table('kph')->first();
        $kphId = $kph->id;

        $kthId = Str::uuid()->toString();
        DB::table('kth')->insert([
            'id' => $kthId,
            'nama_kth' => 'KTH Testing Rekap',
            'desa' => 'Desa Rekap', 
            'kecamatan' => 'Kec Rekap', 
            'kabupaten' => 'Kab Rekap', 
            'koordinator' => 'Koordinator Rekap', 
            'nomor_wa' => '08123456789',
            'id_kph' => $kphId
        ]);

        $petani = User::factory()->petani()->withDetail()->create([
            'id_kph' => $kphId,
            'id_kth' => $kthId
        ]);

        \App\Models\Laporan::factory()
            ->status('disetujui')
            ->withMasaTanamPanen(2)
            ->create([
                'id_user' => $petani->id,
                'periode' => 2026
            ]);

        $this->hasDummyData = true;
    }

    //Produksi=====================================================================================================================================

    /**
     * @Given Pengguna dengan peran sekretariat login untuk melihat rekapitulasi
     * @Given Pengguna login sebagai sekretariat untuk download rekapitulasi
     */
    public function penggunaDenganPeranSekretariatLoginKeDalamSistem()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran auditor login untuk melihat rekapitulasi
     */
    public function penggunaDenganPeranAuditorLoginKeDalamSistem()
    {
        $this->user = User::factory()->auditor()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @When Pengguna ingin melihat data produksi tiap KPH pada Rekapitulasi NTE
     */
    public function penggunaInginMelihatDataProduksiTiapKphPadaRekapitulasiNte()
    {
        $this->siapkanDataProduksiNteDummy();
        $this->endpoint = '/api/v1/rekap/nte?type=kph';
        $this->eksekusiRequest();
    }

    /**
     * @When Pengguna ingin melihat data produksi tiap KTH pada Rekapitulasi NTE
     */
    public function penggunaInginMelihatDataProduksiTiapKthPadaRekapitulasiNte()
    {
        $this->siapkanDataProduksiNteDummy();
        $this->endpoint = '/api/v1/rekap/nte?type=kth';
        $this->eksekusiRequest();
    }

    /**
     * @When Pengguna ingin melihat data produksi tiap Petani pada Rekapitulasi NTE
     */
    public function penggunaInginMelihatDataProduksiTiapPetaniPadaRekapitulasiNte()
    {
        $this->siapkanDataProduksiNteDummy();
        $this->endpoint = '/api/v1/rekap/nte?type=petani';
        $this->eksekusiRequest();
    }

    /**
     * @When Pengguna ingin melihat data produksi tiap Komoditas pada Rekapitulasi NTE
     */
    public function penggunaInginMelihatDataProduksiTiapKomoditasPadaRekapitulasiNte()
    {
        $this->siapkanDataProduksiNteDummy();
        $this->endpoint = '/api/v1/rekap/nte?type=komoditas';
        $this->eksekusiRequest();
    }

    protected function eksekusiRequest()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create($this->endpoint, 'GET');
        $request->headers->set('Accept', 'application/json');
        
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @Then Sistem berhasil menampilkan Rekap
     * @Then Sistem memberikan pesan sukses export
     */
    public function sistemMemberikanPesanSukses()
    {
        $status = $this->response->getStatusCode();
        if ($status !== 200) {
            throw new \Exception(
                "Status HTTP $status bukan sukses (200).\n" .
                "Response: " . json_encode($this->responseContent, JSON_PRETTY_PRINT)
            );
        }
        Assert::assertEquals(200, $status);
    }

    /**
     * @Then Terdapat data produksi tiap KPH
     */
    public function terdapatDataProduksiTiapKph()
    {
        $data = $this->responseContent['data']['rekap']['data'] ?? [];
        Assert::assertNotEmpty($data, 'Data rekap untuk KPH tidak boleh kosong');
        Assert::assertArrayHasKey('nama_kph', $data[0], "Key 'nama_kph' tidak ditemukan di response.");
        Assert::assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan di response.");
    }

    /**
     * @Then Terdapat data produksi tiap KTH
     */
    public function terdapatDataProduksiTiapKth()
    {
        $data = $this->responseContent['data']['rekap']['data'] ?? [];
        Assert::assertNotEmpty($data, 'Data rekap untuk KTH tidak boleh kosong');
        Assert::assertArrayHasKey('nama_kth', $data[0], "Key 'nama_kth' tidak ditemukan di response.");
        Assert::assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan di response.");
    }

    /**
     * @Then Terdapat data produksi tiap Petani
     */
    public function terdapatDataProduksiTiapPetani()
    {
        $data = $this->responseContent['data']['rekap']['data'] ?? [];
        Assert::assertNotEmpty($data, 'Data rekap untuk Petani tidak boleh kosong');
        Assert::assertArrayHasKey('nama_petani', $data[0], "Key 'nama_petani' tidak ditemukan di response.");
        Assert::assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan di response.");
    }

    /**
     * @Then Terdapat data produksi tiap Komoditas
     */
    public function terdapatDataProduksiTiapKomoditas()
    {
        $data = $this->responseContent['data']['rekap']['data'] ?? [];
        Assert::assertNotEmpty($data, 'Data rekap untuk Komoditas tidak boleh kosong');
        Assert::assertArrayHasKey('komoditas', $data[0], "Key 'komoditas' tidak ditemukan di response.");
        Assert::assertArrayHasKey('produksi', $data[0], "Key 'produksi' tidak ditemukan di response.");
    }
}
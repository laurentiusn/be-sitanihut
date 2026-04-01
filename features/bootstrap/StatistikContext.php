<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;

class StatistikContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    protected $endpoint = '/api/v1/statistik';
    
    // Untuk menyimpan state sebelum data baru dimasukkan
    protected $initialRangeData = [];

    /**
     * Helper untuk Request GET
     */
    protected function fetchStatistikData()
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
     * Helper membuat Petani via DB (Menggunakan Factory)
     */
    protected function insertDummyPetaniWithNik($nik)
    {
        User::factory()
            ->petani()
            ->withDetail([
                'nomor_induk' => $nik,
                'jenis_kelamin' => 'pria'
            ])
            ->create();
    }

    /**
     * @Given Pengguna dengan peran sekretariat login untuk melihat statistik
     */
    public function penggunaDenganPeranSekretariatLoginUntukMelihatStatistik()
    {
        $this->user = User::factory()->sekretariat()->withDetail()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @When Pengguna ingin melihat data umur pada statistik
     */
    public function penggunaInginMelihatDataUmurPadaStatistik()
    {
        $this->fetchStatistikData();
    }

    /**
     * @Given Pengguna dengan peran sekretariat telah melihat rentang umur petani
     */
    public function penggunaDenganPeranSekretariatTelahMelihatRentangUmurPetani()
    {
        $this->penggunaDenganPeranSekretariatLoginUntukMelihatStatistik();
        $this->fetchStatistikData();
        
        // Simpan data umur saat ini
        $this->initialRangeData = $this->responseContent['data']['range_umur_petani'] ?? [];
    }

    /**
     * @When Pengguna membuat akun petani serta nomor induk yang valid
     */
    public function penggunaMembuatAkunPetaniSertaNomorIndukYangValid()
    {
        // Format NIK: XXXXXX + DD + MM + YY + XXXX
        // Contoh: Kelahiran 15-08-1990 (Masuk kategori 31-40)
        $validNik = '1801011508900001'; 
        $this->insertDummyPetaniWithNik($validNik);
    }

    /**
     * @When Pengguna membuat akun petani serta nomor induk yang kurang dari 12 digit
     */
    public function penggunaMembuatAkunPetaniSertaNomorIndukYangKurangDari12Digit()
    {
        $invalidNik = '12345678';
        $this->insertDummyPetaniWithNik($invalidNik);
    }

    /**
     * @When Pengguna membuat akun petani serta nomor induk dengan hari dan bulan lahir yang tidak sesuai
     */
    public function penggunaMembuatAkunPetaniSertaNomorIndukDenganHariDanBulanLahirYangTidakSesuai()
    {
        // Hari 99 dan bulan 99 tidak valid
        $invalidDateNik = '1801019999900001';
        $this->insertDummyPetaniWithNik($invalidDateNik);
    }

    /**
     * @Then Sistem memberikan pesan sukses statistik
     */
    public function sistemMemberikanPesanSuksesStatistik()
    {
        $status = $this->response->getStatusCode();
        Assert::assertEquals(200, $status, "Status gagal. Response: " . json_encode($this->responseContent));
    }

    /**
     * @Then Terdapat data rentang umur petani
     */
    public function terdapatDataRentangUmurPetani()
    {
        $data = $this->responseContent['data'] ?? [];
        Assert::assertArrayHasKey('range_umur_petani', $data, 'Key range_umur_petani tidak ditemukan.');
        
        $range = $data['range_umur_petani'];
        Assert::assertArrayHasKey('31-40', $range, 'Format rentang umur tidak sesuai.');
    }

    /**
     * @Then Data rentang umur petani berubah
     */
    public function dataRentangUmurPetaniBerubah()
    {
        // Fetch ulang agar mendapat data ter-update dari DB
        $this->fetchStatistikData();
        $currentRangeData = $this->responseContent['data']['range_umur_petani'] ?? [];

        Assert::assertNotEquals(
            $this->initialRangeData, 
            $currentRangeData, 
            "Ekspektasi rentang umur berubah setelah NIK valid dimasukkan, namun nilainya tetap."
        );
    }

    /**
     * @Then Data rentang umur petani tidak berubah
     */
    public function dataRentangUmurPetaniTidakBerubah()
    {
        // Fetch ulang untuk memastikan data error tidak masuk
        $this->fetchStatistikData();
        $data = $this->responseContent['data'];
        Assert::assertArrayHasKey('range_umur_petani', $data);
        $currentRangeData = $this->responseContent['data']['range_umur_petani'] ?? [];

        Assert::assertEquals(
            $this->initialRangeData, 
            $currentRangeData, 
            "Ekspektasi rentang umur TIDAK berubah karena NIK tidak valid, namun nilainya malah berubah."
        );
    }
}
<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformasiContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    
    protected $endpoint = '/api/v1/informasi';
    
    protected $informasiId;
    protected $tempFiles = [];

    /**
     * Helper membuat Informasi di Database untuk Test Update/Delete/Show
     */
    protected function ensureInformasiExists()
    {
        if ($this->informasiId) return;

        $this->informasiId = Str::uuid()->toString();

        DB::table('informasi')->insert([
            'id'         => $this->informasiId,
            'title'      => 'Informasi Original Test',
            'desc'       => '<p>Deskripsi original dari database.</p>',
            'date'       => '2026-05-05',
            'loc'        => 'Bandar Lampung',
            'organizer'  => 'Sekretariat Pusat',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Helper untuk membuat file UploadedFile sementara
     */
    protected function createTempUploadedFile($fileName, $content, $mimeType)
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $fileName;
        file_put_contents($tempPath, $content);
        $this->tempFiles[] = $tempPath; 

        return new UploadedFile($tempPath, $fileName, $mimeType, null, true);
    }

    /**
     * Hapus semua file temporary setelah scenario selesai
     * @AfterScenario
     */
    public function cleanUpTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file); 
            }
        }
    }

    /**
     * Helper untuk HTTP Request
     */
    protected function sendRequest($method, $url, $data = [], $files = [])
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create($url, $method, $data, [], $files);
        $request->headers->set('Accept', 'application/json');
        
        if ($this->token) {
            $request->headers->set('Authorization', 'Bearer ' . $this->token);
        }

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    // --- AUTHENTICATION ---

    /**
     * @Given Pengguna dengan peran sekretariat login untuk mengelola informasi
     */
    public function penggunaDenganPeranSekretariatLogin()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna yang tidak login untuk melihat informasi
     */
    public function penggunaYangTidakLogin()
    {
        $this->user = null;
        $this->token = null;
    }

    // --- ACTIONS ---

    /**
     * @When Pengguna ingin melihat list informasi
     */
    public function penggunaInginMelihatListInformasi()
    {
        $this->ensureInformasiExists(); 
        $this->sendRequest('GET', $this->endpoint);
    }

    /**
     * @When Pengguna ingin melihat detail informasi
     */
    public function penggunaInginMelihatDetailInformasi()
    {
        $this->ensureInformasiExists();
        $this->sendRequest('GET', $this->endpoint . '/' . $this->informasiId);
    }


    /**
     * @When Pengguna ingin membuat data informasi
     */
    public function penggunaInginMembuatDataInformasi()
    {
        $data = [
            'title'     => 'Berita Baru Test',
            'desc'      => '<p>Ini adalah isi dari berita terbaru.</p>',
            'date'      => '2026-10-10',
            'loc'       => 'Jakarta Selatan',
            'organizer' => 'Panitia Event',
        ];

        // Kode base64 untuk gambar PNG 1x1 pixel asli
        $realImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        $image = $this->createTempUploadedFile('test_informasi.png', $realImageContent, 'image/png');
        $files = ['image' => $image];

        $this->sendRequest('POST', $this->endpoint, $data, $files);
    }

    /**
     * @When Pengguna ingin memperbarui beberapa data informasi
     */
    public function penggunaInginMemperbaruiDataInformasi()
    {
        $this->ensureInformasiExists();

        $data = [
            'title'   => 'Informasi Diperbarui',
            'loc'     => 'Bandung',
            '_method' => 'PUT' // Spoofing agar bisa baca file
        ];

        $realImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        $image = $this->createTempUploadedFile('update_informasi.png', $realImageContent, 'image/png');
        $files = ['image' => $image];

        $this->sendRequest('POST', $this->endpoint . '/' . $this->informasiId, $data, $files);
    }

    /**
     * @When Pengguna ingin menghapus gambar pada informasi
     */
    public function penggunaInginMenghapusGambarPadaInformasi()
    {
        $this->ensureInformasiExists();
        $data = [
            'remove_image' => true
        ];
        $this->sendRequest('PATCH', $this->endpoint . '/' . $this->informasiId, $data);
    }

    /**
     * @When Pengguna ingin menghapus informasi yang sudah ada
     */
    public function penggunaInginMenghapusInformasiYangSudahAda()
    {
        $this->ensureInformasiExists();
        $this->sendRequest('DELETE', $this->endpoint . '/' . $this->informasiId);
    }

    /**
     * @When Pengguna ingin membuat data informasi dengan data yang tidak valid
     */
    public function penggunaInginMembuatDataInformasiTidakValid()
    {
        $image = UploadedFile::fake()->create('test_informasi.pdf', 100, 'application/pdf');
        $data = [
            'title'     => str_repeat('A', 260),
            'desc'      => ['array_bukan_string'],
            'date'      => 'sabtu, 10 Oktober 2026',
            'loc'       => str_repeat('B', 260),
            'organizer' => str_repeat('C', 260),
        ];
        $this->sendRequest('POST', $this->endpoint, $data, ['image' => $image]);
    }

    /**
     * @When Pengguna ingin memperbarui data informasi dengan data yang tidak valid
     */
    public function penggunaInginMemperbaruiDataInformasiTidakValid()
    {
        $this->ensureInformasiExists();
        $data = [
            'date'    => 'bukan-tanggal', // Format tanggal tidak valid
            '_method' => 'PUT'
        ];
        $this->sendRequest('POST', $this->endpoint . '/' . $this->informasiId, $data);
    }

    // --- ASSERTIONS SUCCESS ---

    /**
     * @Then Sistem memberikan pesan sukses informasi
     */
    public function sistemMemberikanPesanSuksesInformasi()
    {
        $status = $this->response->getStatusCode();
        if (!in_array($status, [200, 201, 204])) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Ekspektasi sukses (200/201/204), tapi mendapat $status. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Terdapat list data informasi
     */
    public function terdapatListDataInformasi()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list informasi kosong. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Terdapat detail data informasi
     */
    public function terdapatDetailDataInformasi()
    {
        $data = $this->responseContent['data'] ?? null;
        if (!$data || !isset($data['id'])) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Detail informasi (key 'id') tidak ditemukan. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Data informasi diperbarui
     */
    public function dataInformasiDiperbarui()
    {
        // Cek data terbaru
        $this->sendRequest('GET', $this->endpoint . '/' . $this->informasiId);
        
        $title = $this->responseContent['data']['title'] ?? '';
        Assert::assertEquals('Informasi Diperbarui', $title, "Data tidak ter-update di database!");
    }

    /**
     * @Then Gambar pada data informasi dihapus
     */
    public function gambarPadaDataInformasiDihapus()
    {
        $this->sendRequest('GET', $this->endpoint . '/' . $this->informasiId);
        
        $namaImage = $this->responseContent['data']['nama_image'] ?? null;
        $pathImage = $this->responseContent['data']['path_image'] ?? null;
        
        Assert::assertNull($namaImage, "Image nama_image tidak terhapus!");
        Assert::assertNull($pathImage, "Image path_image tidak terhapus!");
    }

    // --- ASSERTIONS ERROR ---

    /**
     * @Then Sistem memberikan pesan error terkait gagal pembuatan informasi
     * @Then Sistem memberikan pesan error terkait gagal pembaruan informasi
     */
    public function sistemMemberikanPesanErrorTerkaitValidasi()
    {
        $status = $this->response->getStatusCode();
        if ($status !== 422) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Ekspektasi 422 Unprocessable Entity, tapi mendapat $status. \nResponse: $responseString");
        }
        Assert::assertArrayHasKey('errors', $this->responseContent);
    }
}
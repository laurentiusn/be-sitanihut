<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KatalogContext implements Context
{
    protected $user;
    protected $token;
    protected $response;
    protected $responseContent;
    
    protected $endpoint = '/api/v1/katalog';
    
    protected $katalogId;
    protected $tempFiles = [];

    /**
     * Helper membuat Katalog di Database untuk Test Update/Delete/Show
     */
    protected function ensureKatalogExists()
    {
        if ($this->katalogId) return;

        $this->katalogId = Str::uuid()->toString();
        $userId = $this->user ? $this->user->id : null;

        DB::table('catalog')->insert([
            'id'          => $this->katalogId,
            'name'        => 'Katalog Original',
            'description' => 'Deskripsi Katalog Original',
            'location'    => 'Bandar Lampung',
            'kth'         => 'KTH Pengujian',
            'cp1'         => '081122334455',
            'price'       => 50000,
            'is_active'   => 1,
            'id_user'     => $userId, // Diikat ke user yang login (jika ada)
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    /**
     * Helper membuat Katalog milik penyuluh lain
     */
    protected function ensureOtherKatalogExists()
    {
        $this->katalogId = Str::uuid()->toString();
        $otherUser = User::factory()->penyuluh()->create();

        DB::table('catalog')->insert([
            'id'          => $this->katalogId,
            'name'        => 'Katalog Original Lain',
            'description' => 'Deskripsi Katalog Original Lain',
            'location'    => 'Bandar Lampung',
            'kth'         => 'KTH Pengujian',
            'cp1'         => '081122334455',
            'price'       => 50000,
            'is_active'   => 1,
            'id_user'     => $otherUser->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    /**
     * Helper untuk membuat file UploadedFile sementara
     */
    protected function createTempUploadedFile($fileName, $content, $mimeType)
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_' . $fileName;
        file_put_contents($tempPath, $content);
        $this->tempFiles[] = $tempPath; // Simpan path untuk dihapus nanti

        return new UploadedFile($tempPath, $fileName, $mimeType, null, true);
    }

    /**
     * Hapus semua file temporary setelah scenario selesai
     * @AfterScenario("@cleanup_temp_files")
     */
    public function cleanUpTempFiles()
    {
        foreach ($this->tempFiles as $file) {
            if (file_exists($file)) {
                unlink($file); // Hapus file dari server/komputer
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
     * @Given Pengguna dengan peran sekretariat login untuk mengelola katalog
     */
    public function penggunaDenganPeranSekretariatLogin()
    {
        $this->user = User::factory()->sekretariat()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran penyuluh login untuk mengelola katalog
     */
    public function penggunaDenganPeranPenyuluhLogin()
    {
        $this->user = User::factory()->penyuluh()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran auditor login untuk mengelola katalog
     */
    public function penggunaDenganPeranAuditorLogin()
    {
        $this->user = User::factory()->auditor()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna yang tidak login untuk melihat katalog
     */
    public function penggunaYangTidakLogin()
    {
        $this->user = null;
        $this->token = null;
    }

    // --- ACTIONS ---

    /**
     * @When Pengguna ingin melihat list katalog
     */
    public function penggunaInginMelihatListKatalog()
    {
        $this->ensureKatalogExists(); // Pastikan minimal ada 1 data untuk dilihat
        $this->sendRequest('GET', $this->endpoint);
    }

    /**
     * @When Pengguna ingin melihat detail katalog
     */
    public function penggunaInginMelihatDetailKatalog()
    {
        $this->ensureKatalogExists();
        $this->sendRequest('GET', $this->endpoint . '/' . $this->katalogId);
    }


    /**
     * @When Pengguna ingin membuat data katalog
     */
    public function penggunaInginMembuatDataKatalog()
    {
        $data = [
            'name'        => 'Katalog Baru Test',
            'description' => 'Katalog ini dibuat dari behat',
            'kth'         => 'KTH Baru',
            'cp1'         => '0812345678',
            'price'       => 100000,
            'location'    => 'Jakarta',
            'is_active'   => 1,
        ];

        $realImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        
        $image = $this->createTempUploadedFile('test_katalog.png', $realImageContent, 'image/png');
        $files = ['images' => [$image]];

        $this->sendRequest('POST', $this->endpoint, $data, $files);
    }

    /**
     * @When Pengguna ingin memperbarui beberapa data katalog
     */
    public function penggunaInginMemperbaruiDataKatalog()
    {
        $this->ensureKatalogExists();

        $data = [
            'name'    => 'Katalog Diperbarui',
            'price'   => 75000,
            '_method' => 'PUT' // <--- Trik Laravel agar bisa baca file via PUT
        ];

        // Kode base64 untuk gambar asli PNG berukuran 1x1 pixel
        $realImageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
        
        $image = $this->createTempUploadedFile('update_katalog.png', $realImageContent, 'image/png');
        $files = ['images_new' => [$image]];

        // Harus menggunakan POST karena Laravel tidak bisa mem-parsing multipart/form-data di rute PUT
        $this->sendRequest('POST', $this->endpoint . '/' . $this->katalogId, $data, $files);
    }

    /**
     * @When Pengguna ingin menghapus data katalog yang sudah ada
     */
    public function penggunaInginMenghapusDataKatalogYangSudahAda()
    {
        $this->ensureKatalogExists();
        $this->sendRequest('DELETE', $this->endpoint . '/' . $this->katalogId);
    }

    /**
     * @When Pengguna ingin membuat data katalog dengan data yang tidak diisi
     */
    public function penggunaInginMembuatDataKatalogDenganDataYangTidakDiisi()
    {
        $this->sendRequest('POST', $this->endpoint, []);
    }

    /**
     * @When Pengguna ingin membuat data katalog dengan data yang tidak sesuai aturan
     */
    public function penggunaInginMembuatDataKatalogDenganDataYangTidakSesuaiAturan()
    {
        $data = [
            'name'        => 'Katalog Invalid',
            'description' => 'Deskripsi',
            'kth'         => 'KTH',
            'cp1'         => '0812',
            'price'       => -5000, // Harus >= 0
            'location'    => 'Jakarta',
            'is_active'   => 'bukan-boolean', // Harus boolean
        ];
        $this->sendRequest('POST', $this->endpoint, $data);
    }

    /**
     * @When Pengguna ingin memperbarui data katalog dengan data yang tidak valid
     */
    public function penggunaInginMemperbaruiDataKatalogTidakValid()
    {
        $this->ensureKatalogExists();
        $data = [
            'price'   => -5000, // Invalid price
            '_method' => 'PUT'
        ];
        $this->sendRequest('POST', $this->endpoint . '/' . $this->katalogId, $data);
    }

    /**
     * @When Pengguna ingin melihat detail katalog yang bukan miliknya
     */
    public function penggunaInginMelihatDetailKatalogYangBukanMiliknya()
    {
        $this->ensureOtherKatalogExists();
        $this->sendRequest('GET', $this->endpoint . '/' . $this->katalogId);
    }

    /**
     * @When Pengguna ingin memperbarui data katalog yang bukan miliknya
     */
    public function penggunaInginMemperbaruiDataKatalogYangBukanMiliknya()
    {
        $this->ensureOtherKatalogExists();

        $data = [
            'name'    => 'Katalog Diperbarui Ilegal',
            'price'   => 75000,
            '_method' => 'PUT'
        ];

        $this->sendRequest('POST', $this->endpoint . '/' . $this->katalogId, $data);
    }

    /**
     * @When Pengguna ingin menghapus data katalog yang bukan miliknya
     */
    public function penggunaInginMenghapusDataKatalogYangBukanMiliknya()
    {
        $this->ensureOtherKatalogExists();
        $this->sendRequest('DELETE', $this->endpoint . '/' . $this->katalogId);
    }

    // --- ASSERTIONS SUCCESS ---

    /**
     * @Then Sistem memberikan pesan sukses katalog
     */
    public function sistemMemberikanPesanSuksesKatalog()
    {
        $status = $this->response->getStatusCode();
        if (!in_array($status, [200, 201, 204])) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Ekspektasi sukses (200/201/204), tapi mendapat $status. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Terdapat list data katalog
     */
    public function terdapatListDataKatalog()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list katalog kosong. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Terdapat detail data katalog
     */
    public function terdapatDetailDataKatalog()
    {
        $data = $this->responseContent['data'] ?? null;
        if (!$data || !isset($data['id'])) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Detail katalog (key 'id') tidak ditemukan. \nResponse: $responseString");
        }
        Assert::assertTrue(true);
    }

    /**
     * @Then Data Katalog diperbarui
     */
    public function dataKatalogDiperbarui()
    {
        // Panggil endpoint GET untuk melihat perubahannya
        $this->sendRequest('GET', $this->endpoint . '/' . $this->katalogId);
        
        $name = $this->responseContent['data']['name'] ?? '';
        Assert::assertEquals('Katalog Diperbarui', $name, "Data tidak ter-update di database!");
    }

    // --- ASSERTIONS ERROR ---

    /**
     * @Then Sistem memberikan pesan error terkait gagal pembuatan katalog
     * @Then Sistem memberikan pesan error terkait gagal pembaruan katalog
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


    /**
     * @Then Sistem memberikan pesan error terkait hak akses membuat katalog
     * @Then Sistem memberikan pesan error terkait hak akses katalog
     */
    public function sistemMemberikanPesanErrorTerkaitHakAkses()
    {
        $status = $this->response->getStatusCode();
        Assert::assertTrue(
            in_array($status, [401, 403]), 
            "Ekspektasi 401 atau 403, tapi mendapat HTTP: $status."
        );
    }

    /**
     * @Then Terdapat list data katalog milik penyuluh
     */
    public function terdapatListDataKatalogMilikPenyuluh()
    {
        $data = $this->responseContent['data']['data'] ?? [];
        if (empty($data)) {
            $responseString = json_encode($this->responseContent, JSON_PRETTY_PRINT);
            throw new \Exception("Data list katalog kosong. \nResponse: $responseString");
        }
        $returnedIds = array_column($data, 'id');
        $katalogCount = DB::table('catalog')
            ->whereIn('id', $returnedIds)
            ->where('id_user', $this->user->id)
            ->count();
            
        Assert::assertEquals(count($returnedIds), $katalogCount);
    }
}
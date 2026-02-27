<?php

use Behat\Behat\Context\Context;
use Illuminate\Http\Request;
use PHPUnit\Framework\Assert;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class BulkLaporanContext implements Context
{
    protected $user;
    protected $token;
    protected $requestData = [];
    protected $requestFiles = [];
    protected $response;
    protected $responseContent;

    protected $idLaporanLain;

    //BULK-STORE=============================================================================================

    /**
     * @Given Pengguna dengan peran petani login ke dalam sistem
     */
    public function penggunaDenganPeranPetaniLoginKeDalamSistem()
    {
        $this->user = User::factory()->petani()->create();
        
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna dengan peran bukan petani login ke dalam sistem
     */
    public function penggunaDenganPeranBukanPetaniLoginKeDalamSistem()
    {
        $this->user = User::factory()->auditor()->create();
        $this->token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($this->user);
    }

    /**
     * @Given Pengguna telah menyiapkan data-data laporan
     */
    public function penggunaTelahMenyiapkanDataDataLaporan()
    {
        $komoditas = DB::table('komoditas')->first();
        $idKomoditas = $komoditas ? $komoditas->id : Str::uuid()->toString();

        $this->requestData = [
            'laporan' => [
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'oktober',
                    'modal' => 5000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa tanam pertama',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2024-10-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 150,
                            'usia_tanam' => 14
                        ]
                    ],
                    'lampiran' => [
                        UploadedFile::fake()->create('dokumen_lampiran_1.pdf', 100),
                        UploadedFile::fake()->create('dokumen_lampiran_2.pdf', 200),
                        UploadedFile::fake()->image('gambar_1.jpg')->size(500),
                        UploadedFile::fake()->create('dokumen_lampiran_3.pdf', 200),
                    ]
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'november',
                    'modal' => 2000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa panen',
                    'masa_panen' => [
                        [
                            'tanggal' => '2024-11-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 500,
                            'harga_satuan' => 12000
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @Given Pengguna tidak memiliki data-data laporan
     */
    public function penggunaTidakMemilikiDataDataLaporan()
    {
        $this->requestData = [
            'laporan' => []
        ];
    }

    /**
     * @Given Pengguna memiliki data-data laporan namun ada yang tidak valid
     */
    public function penggunaMemilikiDataDataLaporanNamunAdaYangTidakValid()
    {
        $komoditas = DB::table('komoditas')->first();
        $idKomoditas = $komoditas ? $komoditas->id : Str::uuid()->toString();

        $this->requestData = [
            'laporan' => [
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'bulan',
                    'modal' => 5000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa tanam pertama',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2024-10-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 150,
                            'usia_tanam' => 14
                        ]
                    ]
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'november',
                    'modal' => -500,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa panen',
                    'masa_panen' => [
                        [
                            'tanggal' => '2024-11-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 500,
                            'harga_satuan' => 12000
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @Given Pengguna memiliki data-data laporan dan menyertakan lebih dari :jumlah lampiran
     */
    public function penggunaMemilikiDataDataLaporanDanMenyertakanLebihDariLampiran($jumlah)
    {
        $komoditas = DB::table('komoditas')->first();
        $idKomoditas = $komoditas ? $komoditas->id : Str::uuid()->toString();

        $fakeFiles = [];

        for ($i = 0; $i <= (int)$jumlah; $i++) {
            $fakeFiles[] = UploadedFile::fake()->create("dokumen_lampiran_{$i}.pdf", 100);
        }

        $this->requestData = [
            'laporan' => [
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'oktober',
                    'modal' => 5000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa tanam pertama',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2024-10-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 150,
                            'usia_tanam' => 14
                        ]
                    ]
                ],
                [
                    'id' => Str::uuid()->toString(),
                    'periode' => '2024',
                    'bulan' => 'november',
                    'modal' => 2000000,
                    'status' => 'belum diajukan',
                    'catatan_petani' => 'Laporan masa panen',
                    'masa_panen' => [
                        [
                            'tanggal' => '2024-11-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 500,
                            'harga_satuan' => 12000
                        ]
                    ]
                ]
            ]
        ];

        $this->requestFiles = [
            'laporan' => [
                0 => [
                    'lampiran' => $fakeFiles
                ]
            ]
        ];
    }

    /**
     * @When Pengguna ingin membuat banyak laporan secara bersamaan
     */
    public function penggunaInginMembuatBanyakLaporanSecaraBersamaan()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        
        $request = Request::create(
            '/api/v1/laporan/bulk-create', 
            'POST', 
            $this->requestData, 
            [],
            $this->requestFiles
        );

        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Authorization', 'Bearer ' . $this->token);

        $this->response = $kernel->handle($request);
        $this->responseContent = json_decode($this->response->getContent(), true);
    }

    /**
     * @Then Sistem memberikan pesan sukses bahwa beberapa laporan berhasil ditambahkan
     */
    public function sistemMemberikanPesanSuksesBahwaBeberapaLaporanBerhasilDitambahkan()
    {
        Assert::assertEquals(201, $this->responseContent['statusCode'] ?? $this->response->getStatusCode());
        Assert::assertEquals('Laporan massal berhasil dibuat', $this->responseContent['message'] ?? '');
        
        // Memastikan ada UUID di dalam array 'sukses'
        $suksesArray = $this->responseContent['data']['sukses'] ?? [];
        Assert::assertGreaterThanOrEqual(1, count($suksesArray), 'Daftar ID sukses kosong');
    }

    /**
     * @Then Sistem menolak permintaan tersebut
     */
    public function sistemMenolakPermintaanTersebut($status = 422)
    {
        Assert::assertEquals((int)$status, $this->response->getStatusCode(), 'Ekspektasi sistem menolak request.');
    }

    /**
     * @Then Sistem memberikan pesan error terkait minimal laporan untuk dibuat
     */
    public function sistemMemberikanPesanErrorTerkaitMinimalLaporanUntukDibuat()
    {
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertArrayHasKey('laporan', $errors, 'Validasi min:1 untuk laporan gagal dipicu.');
    }

    /**
     * @Then Sistem memberikan pesan error terkait data yang tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitDataYangTidakValid()
    {
        $errors = $this->responseContent['errors'] ?? [];
        $errors = $this->responseContent['errors'] ?? [];

        $hasBulanError = false;
        $hasModalError = false;

        foreach ($errors as $key => $messages) {
            if (str_contains($key, '.bulan')) {
                $hasBulanError = true;
            }
            if (str_contains($key, '.modal')) {
                $hasModalError = true;
            }
        }

        Assert::assertTrue(
            $hasBulanError || $hasModalError, 
            "Sistem menolak request (422), tetapi tidak mengembalikan pesan error untuk kolom 'bulan' atau 'modal'.\n" .
            "Response aslinya: " . json_encode($this->responseContent)
        );
    }

    /**
     * @Then Sistem memberikan pesan error terkait batas maksimal lampiran
     */
    public function sistemMemberikanPesanErrorTerkaitBatasMaksimalLampiran()
    {
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertArrayHasKey('laporan.0.lampiran', $errors, 'Validasi max:10 untuk lampiran tidak terpanggil.');
    }

    /**
     * @Then Sistem memberikan pesan error terkait hak akses pembuatan laporan
     */
    public function sistemMemberikanPesanErrorTerkaitHakAksesPembuatanLaporan()
    {
        Assert::assertEquals(500, $this->response->getStatusCode(), 'Bukan 500.');
    }
}
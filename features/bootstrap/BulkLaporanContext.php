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

    //BULK-UPDATE==============================================================================================

    /**
     * @Given Pengguna telah mengisi data-data pada laporan-laporan yang ingin diperbarui
     */
    public function penggunaTelahMengisiDataDataPadaLaporanLaporanYangInginDiperbarui()
    {
        $komoditas = DB::table('komoditas')->first();
        $idKomoditas = $komoditas ? $komoditas->id : Str::uuid()->toString();

        $laporans = \App\Models\Laporan::factory()->count(2)->create([
            'id_user' => $this->user->id,
            'status' => 'belum diajukan'
        ]);

        $this->requestData = [
            'laporan' => [
                [
                    'id' => $laporans[0]->id,
                    'modal' => 5000000, 
                    'catatan_petani' => 'Revisi modal laporan pertama',
                    'periode' => '2025',
                    'bulan' => 'juni',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2025-06-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 120,
                            'usia_tanam' => 2
                        ]
                    ],
                    'masa_panen' => [
                        [
                            'tanggal' => '2025-06-15',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 300,
                            'harga_satuan' => 15000
                        ]
                    ],
                    'lampiran_existing' => []
                ],
                [
                    'id' => $laporans[1]->id,
                    'modal' => 8500000, 
                    'catatan_petani' => 'Ada tambahan pupuk di laporan kedua',
                    'periode' => '2025',
                    'bulan' => 'agustus',
                    'masa_tanam' => [
                        [
                            'tanggal' => '2025-08-01',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 200,
                            'usia_tanam' => 3
                        ]
                    ],
                    'masa_panen' => [
                        [
                            'tanggal' => '2025-08-20',
                            'id_komoditas' => $idKomoditas,
                            'jumlah' => 400,
                            'harga_satuan' => 18000
                        ]
                    ],
                    'lampiran_existing' => []
                ]
            ]
        ];

        $this->requestFiles = [
            'laporan' => [
                0 => [
                    'lampiran_new' => [UploadedFile::fake()->create('laporan1_baru.pdf', 200)]
                ],
                1 => [
                    'lampiran_new' => [UploadedFile::fake()->create('laporan2_baru.pdf', 250)]
                ]
            ]
        ];
    }

    /**
     * @When Pengguna ingin memperbarui banyak laporan secara bersamaan
     */
    public function penggunaInginMemperbaruiBanyakLaporanSecaraBersamaan()
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $request = Request::create(
            '/api/v1/laporan/bulk-update', 
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
     * @Then Sistem memberikan pesan sukses bahwa semua laporan berhasil diperbarui
     */
    public function sistemMemberikanPesanSuksesBahwaSemuaLaporanBerhasilDiperbarui()
    {
        Assert::assertEquals(200, $this->response->getStatusCode(), 'Status harus 200 OK');
        $sukses = $this->responseContent['data']['sukses'] ?? [];
        $ditolak = $this->responseContent['data']['ditolak'] ?? [];
        
        Assert::assertEquals(2, count($sukses), "Harusnya ada laporan yang berhasil diperbarui");
        Assert::assertEmpty($ditolak, "Harusnya tidak ada laporan yang ditolak");
    }

    /**
     * @Given Pengguna tidak menentukan laporan-laporan yang ingin diperbarui
     */
    public function penggunaTidakMenentukanLaporanLaporanYangInginDiperbarui()
    {
        $this->requestData = ['laporan' => []];
    }

    /**
     * @Then Sistem memberikan pesan error terkait minimal laporan untuk melakukan pembaruan
     */
    public function sistemMemberikanPesanErrorTerkaitMinimalLaporanUntukMelakukanPembaruan()
    {
        $errors = $this->responseContent['errors'] ?? [];
        Assert::assertArrayHasKey('laporan', $errors, 'Pesan error terkait minimal 1 laporan harus muncul');
    }

    /**
     * @Given Pengguna menyertakan data yang tidak valid pada laporan-laporan yang ingin diperbarui
     */
    public function penggunaMenyertakanDataYangTidakValidPadaLaporanLaporanYangInginDiperbarui()
    {
        $laporan = \App\Models\Laporan::factory()->create([
            'id_user' => $this->user->id,
            'status' => 'belum diajukan'
        ]);

        $this->requestData = [
            'laporan' => [
                [
                    'id' => $laporan->id,
                    'bulan' => 'bulan_rahasia',
                    'modal' => -500
                ]
            ]
        ];
    }

    /**
     * @Then Sistem memberikan pesan error terkait data yang dimasukan tidak valid
     */
    public function sistemMemberikanPesanErrorTerkaitDataYangDimasukanTidakValid()
    {
        $errors = $this->responseContent['errors'] ?? [];
        
        $hasInvalidDataError = false;
        foreach ($errors as $key => $messages) {
            if (str_contains($key, '.bulan') || str_contains($key, '.modal')) {
                $hasInvalidDataError = true;
            }
        }
        
        Assert::assertTrue($hasInvalidDataError, "Harusnya ada error validasi untuk 'bulan' atau 'modal'.\nResponse: " . json_encode($this->responseContent));
    }

    /**
     * @Given Laporan yang ingin diperbarui milik petani lain
     */
    public function laporanYangInginDiperbaruiMilikPetaniLain()
    {
        $petaniLain = \App\Models\User::factory()->petani()->create();
        $laporanOrangLain = \App\Models\Laporan::factory()->create([
            'id_user' => $petaniLain->id,
            'status' => 'belum diajukan'
        ]);

        $this->idLaporanLain = $laporanOrangLain->id;

        $this->requestData = [
            'laporan' => [
                [
                    'id' => $laporanOrangLain->id,
                    'modal' => 2000000
                ]
            ]
        ];
    }

    /**
     * @Then Sistem memberitau bahwa laporan tersebut bukan milik si petani
     */
    public function sistemMemberitahuLaporanBukanMilikSiPetani()
    {
        Assert::assertEquals(200, $this->response->getStatusCode(), 'Status harus tetap 200 (Partial Success)');
        $idDitolak = $this->responseContent['data']['ditolak'][0]['id'] ?? [];
        $pesanDitolak = $this->responseContent['data']['ditolak'][0]['alasan'];
        Assert::assertNotNull($this->responseContent['data']['ditolak']);
        Assert::assertEquals($this->idLaporanLain, $idDitolak, 'Id Laporan tidak sama');
        Assert::assertEquals("Data tidak ditemukan atau bukan milik Anda.", $pesanDitolak, 'Pesan tidak ada');
    }

    /**
     * @Then Sistem memberikan pesan terkait laporan tidak ada
     * @Then Sistem memberitau bahwa laporan tersebut sedang diproses
     */
    public function sistemMeresponDenganPenolakanLaporanPadaArrayDitolak()
    {
        Assert::assertEquals(200, $this->response->getStatusCode(), 'Status harus tetap 200 (Partial Success)');
        $ditolak = $this->responseContent['data']['ditolak'] ?? [];
        Assert::assertGreaterThanOrEqual(1, count($ditolak), "Harusnya ada laporan yang masuk ke dalam array ditolak");
    }

    /**
     * @Given Pengguna menyertakan laporan yang tidak ada di sistem
     */
    public function penggunaMenyertakanLaporanYangTidakAdaDiSistem()
    {
        $this->requestData = [
            'laporan' => [
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'modal' => 2000000
                ],
                [
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'modal' => 120000
                ]
            ]
        ];
    }

    /**
     * @Given Laporan yang ingin diperbarui berstatus selain 'belum diajukan' atau 'ditolak'
     */
    public function laporanYangInginDiperbaruiBerstatusSelainBelumDiajukanAtauDitolak()
    {
        $laporanDisetujui = \App\Models\Laporan::factory()->create([
            'id_user' => $this->user->id,
            'status' => 'disetujui'
        ]);

        $laporanMenunggu = \App\Models\Laporan::factory()->create([
            'id_user' => $this->user->id,
            'status' => 'menunggu'
        ]);

        $this->requestData = [
            'laporan' => [
                [
                    'id' => $laporanDisetujui->id,
                    'modal' => 2000000
                ],
                [
                    'id' => $laporanMenunggu->id,
                    'modal' => 5000
                ]
            ]
        ];
    }

    /**
     * @Given Pengguna menentukan laporan-laporan yang ingin diperbarui namun hanya beberapa laporan yang valid
     */
    public function penggunaMenentukanLaporanLaporanYangInginDiperbaruiNamunHanyaBeberapaLaporanYangValid()
    {
        $laporanValid = \App\Models\Laporan::factory()->create([
            'id_user' => $this->user->id,
            'status' => 'belum diajukan'
        ]);

        $laporanInvalidStatus = \App\Models\Laporan::factory()->create([
            'id_user' => $this->user->id,
            'status' => 'disetujui'
        ]);

        $this->requestData = [
            'laporan' => [
                [
                    'id' => $laporanValid->id,
                    'modal' => 1500000
                ],
                [
                    'id' => $laporanInvalidStatus->id,
                    'modal' => 2000000
                ]
            ]
        ];
    }

    /**
     * @Then Sistem memberitau bahwa terdapat laporan yang berhasil diperbarui dan laporan yang tidak berhasil diperbarui
     */
    public function sistemMemberitauBahwaTerdapatLaporanBerhasilDanTidak()
    {
        Assert::assertEquals(200, $this->response->getStatusCode());
        
        $sukses = $this->responseContent['data']['sukses'] ?? [];
        $ditolak = $this->responseContent['data']['ditolak'] ?? [];
        
        Assert::assertGreaterThanOrEqual(1, count($sukses), "Harusnya ada yang masuk ke array sukses");
        Assert::assertGreaterThanOrEqual(1, count($ditolak), "Harusnya ada yang masuk ke array ditolak");
    }
}
<?php

namespace App\Services;

use App\Repository\LaporanRepository;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Arr;

/**
 * Class LaporanBulkService
 * * Service: laporan massal (bulk) pembuatan (create), pembaruan (update), penghapusan (delete)
 *
 * @package App\Services
 */
class LaporanBulkService
{
    protected LaporanRepository $repository;
    protected LaporanBulkParser $parser;
    protected LaporanBulkValidator $validator;

    public function __construct(LaporanRepository $repository)
    {
        $this->repository = $repository;
        $this->parser = new LaporanBulkParser($repository);
        $this->validator = new LaporanBulkValidator();
    }

    /**
     * Buat Laporan secara Massal
     *
     * @param array $laporanBaruRaw
     * @param User $user
     * @param array $kumpulanLampiran
     * @return array Status sukses/gagal
     */
    public function createBulk(array $laporanBaruRaw, User $user, array $kumpulanLampiran): array
    {
        Gate::authorize('create', Laporan::class);

        $hasil = ['sukses' => [], 'gagal' => []];
        $insertData = ['laporan' => [], 'masa_tanam' => [], 'masa_panen' => [], 'lampiran' => [], 'log' => []];
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($laporanBaruRaw as $index => $dataLaporan) {
                $laporanId = $dataLaporan['id'];
                $clientTime = $this->parser->resolveTimestamp($dataLaporan, 'created_at', $now);

                $nteTotal = $this->parser->parseMasaPanen($dataLaporan['masa_panen'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);
                $this->parser->parseMasaTanam($dataLaporan['masa_tanam'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);
                $this->parser->parseLampiran($kumpulanLampiran[$index]['lampiran'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);

                $insertData['laporan'][] = [
                    'id'                => $laporanId, 
                    'periode'           => $dataLaporan['periode'], 
                    'bulan'             => $dataLaporan['bulan'],
                    'tanggal'           => $clientTime->toDateString(), 
                    'modal'             => $dataLaporan['modal'] ?? 0,
                    'nte'               => $nteTotal, 
                    'status'            => $dataLaporan['status'],
                    'tanggal_pengajuan' => ($dataLaporan['status'] === 'menunggu') ? $clientTime->toDateString() : null,
                    'catatan_petani'    => $dataLaporan['catatan_petani'] ?? null, 
                    'id_user'           => $user->id,
                    'created_at'        => $clientTime, 
                    'updated_at'        => $this->parser->resolveTimestamp($dataLaporan, 'updated_at', $now),
                ];

                $hasil['sukses'][] = $laporanId;
            }

            $this->repository->executeInserts($insertData);
            DB::commit();
            return $hasil;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->parser->cleanupFailedUploads($insertData['lampiran']);
            throw $e;
        }
    }

    /**
     * Perbarui Laporan secara Massal
     *
     * @param array $laporanUbahRaw
     * @param User $user
     * @param array $lampiranFiles
     * @return array Status sukses/ditolak
     */
    public function updateBulk(array $laporanUbahRaw, User $user, array $lampiranFiles): array
    {
        Gate::authorize('canBulkUpdate', Laporan::class);

        $hasil = ['sukses' => [], 'ditolak' => []];
        $insertData = ['laporan' => [], 'masa_tanam' => [], 'masa_panen' => [], 'lampiran' => [], 'log' => []];
        $now = now();

        $idLaporanUbah = array_column($laporanUbahRaw, 'id');
        $laporansPusat = $this->repository->getByIds($idLaporanUbah, $user->id);

        DB::beginTransaction();
        try {
            foreach ($laporanUbahRaw as $index => $dataLaporan) {
                $laporanId = $dataLaporan['id'];
                
                // Ekstraksi Logika Validasi Akses
                $rejectionReason = $this->validator->validateUpdateAccess($laporanId, $laporansPusat, $user);
                if ($rejectionReason) {
                    $hasil['ditolak'][] = $rejectionReason;
                    continue;
                }

                $clientUpdated = $this->parser->resolveTimestamp($dataLaporan, 'updated_at', $now);
                $dataToUpdate = Arr::only($dataLaporan, ['periode', 'bulan', 'tanggal', 'modal', 'catatan_petani', 'status']);
                
                $this->parser->handleUpdateMasaPanen($laporanId, $dataLaporan['masa_panen'] ?? null, $clientUpdated, $insertData, $dataToUpdate);
                $this->parser->handleUpdateMasaTanam($laporanId, $dataLaporan['masa_tanam'] ?? null, $clientUpdated, $insertData);
                $this->parser->handleUpdateLampiran($laporanId, $dataLaporan['lampiran_existing'] ?? null, $lampiranFiles[$index]['lampiran_new'] ?? [], $clientUpdated, $insertData);

                if (($dataToUpdate['status'] ?? null) === 'menunggu') {
                    $dataToUpdate['tanggal_pengajuan'] = $clientUpdated->toDateString();
                }

                if (!empty($dataToUpdate)) {
                    $dataToUpdate['updated_at'] = $clientUpdated;
                    $this->repository->updateLaporan($laporanId, $dataToUpdate);
                }

                $hasil['sukses'][] = $laporanId;
            }

            $this->repository->executeInserts($insertData);
            DB::commit();
            return $hasil;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->parser->cleanupFailedUploads($insertData['lampiran']);
            throw $e;
        }
    }

    /**
     * Hapus Laporan secara Massal
     *
     * @param array $laporanIds
     * @param User $user
     * @return array
     */
    public function deleteBulk(array $laporanIds, User $user): array
    {
        $hasil = [
            'sukses'                   => [], 
            'sudah_dihapus_sebelumnya' => [], 
            'tidak_ditemukan'          => [], 
            'ditolak'                  => []
        ];

        $laporanDiServer = $this->repository->getByIdsWithTrashed($laporanIds);

        DB::beginTransaction();
        try {
            foreach ($laporanIds as $id) {
                $rejection = $this->validator->validateDeleteAccess($id, $laporanDiServer, $user);

                if ($rejection) {
                    $hasil[$rejection['tipe']][] = $rejection['data'];
                    continue;
                }

                $laporanDiServer[$id]->delete();
                $hasil['sukses'][] = $id;
            }
            DB::commit();
            return $hasil;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
<?php

namespace App\Services;

use App\Repository\LaporanRepository;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;

/**
 * Class LaporanBulkService
 * * Service: Laporan massal (bulk) pembuatan (create), pembaruan (update), penghapusan (delete)
 *
 * @package App\Services
 */
class LaporanBulkService
{
    /**
     * @var LaporanRepository Instansiasi LaporanRepository
     */
    protected LaporanRepository $repository;

    /**
     * LaporanBulkService constructor.
     *
     * @param LaporanRepository $repository Dependency injection untuk LaporanRepository
     */
    public function __construct(LaporanRepository $repository)
    {
        $this->repository = $repository;
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
                $clientTime = $this->resolveTimestamp($dataLaporan, 'created_at', $now);

                $nteTotal = $this->parsingMasaPanen($dataLaporan['masa_panen'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);
                $this->parsingMasaTanam($dataLaporan['masa_tanam'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);
                $this->parsingLampiran($kumpulanLampiran[$index]['lampiran'] ?? [], $laporanId, $clientTime, $clientTime, $insertData);

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
                    'updated_at'        => $this->resolveTimestamp($dataLaporan, 'updated_at', $now),
                ];

                $hasil['sukses'][] = $laporanId;
            }

            $this->repository->executeInserts($insertData);
            DB::commit();
            return $hasil;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->cleanupFailedUploads($insertData['lampiran']);
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
                $rejectionReason = $this->validateUpdateAccess($laporanId, $laporansPusat, $user);
                if ($rejectionReason) {
                    $hasil['ditolak'][] = $rejectionReason;
                    continue;
                }

                $clientUpdated = $this->resolveTimestamp($dataLaporan, 'updated_at', $now);
                $dataToUpdate = Arr::only($dataLaporan, ['periode', 'bulan', 'tanggal', 'modal', 'catatan_petani', 'status']);

                if (isset($dataLaporan['masa_panen'])) {
                    $this->repository->deleteMasaPanenByLaporanId($laporanId);
                    $dataToUpdate['nte'] = $this->parsingMasaPanen($dataLaporan['masa_panen'], $laporanId, $clientUpdated, $clientUpdated, $insertData);
                }

                if (isset($dataLaporan['masa_tanam'])) {
                    $this->repository->deleteMasaTanamByLaporanId($laporanId);
                    $this->parsingMasaTanam($dataLaporan['masa_tanam'], $laporanId, $clientUpdated, $clientUpdated, $insertData);
                }

                if (isset($dataLaporan['lampiran_existing'])) {
                    $this->processLampiranDeletion($laporanId, $dataLaporan['lampiran_existing']);
                }

                $this->parsingLampiran($lampiranFiles[$index]['lampiran_new'] ?? [], $laporanId, $clientUpdated, $clientUpdated, $insertData);

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
            $this->cleanupFailedUploads($insertData['lampiran']);
            throw $e;
        }
    }

    /**
     * Simpan File Lampiran ke Storage
     *
     * @param mixed $file
     * @return string Path file tersimpan
     */
    private function storeFile($file): string
    {
        $originalFilename = $file->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^A-Za-z0-9\-_]/', '', pathinfo($originalFilename, PATHINFO_FILENAME));
        $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('lampiran', $newFilename, 'public');
    }

    /**
     * Hapus File Lampiran jika Proses Gagal
     *
     * @param array $lampiranData
     * @return void
     */
    private function cleanupFailedUploads(array $lampiranData): void
    {
        foreach ($lampiranData as $lampiran) {
            Storage::disk('public')->delete($lampiran['path']);
        }
    }

    /**
     * Mengambil instance Carbon dari array data atau mengembalikan waktu saat ini.
     */
    private function resolveTimestamp(array $data, string $key, Carbon $default): Carbon
    {
        return isset($data[$key]) ? Carbon::parse($data[$key]) : clone $default;
    }

    /**
     * Validasi Hak Akses untuk Update Laporan
     */
    private function validateUpdateAccess(string $laporanId, $laporansPusat, User $user): ?array
    {
        if (!$laporansPusat->has($laporanId)) {
            return [
                'id'            => $laporanId,
                'status_pusat'  => 'tidak diketahui',
                'alasan'        => 'Data tidak ditemukan atau bukan milik Anda.'
            ];
        }

        $lapPusat = $laporansPusat[$laporanId];

        if ($user->cannot('updateBulk', $lapPusat)) {
            return [
                'id'            => $laporanId,
                'status_pusat'  => $lapPusat->status,
                'alasan'        => 'Akses ditolak: Laporan sedang diproses atau sudah diverifikasi.'
            ];
        }

        return null;
    }

    /**
     * Penghapusan Data Lampiran pada DB dan Storage
     */
    private function processLampiranDeletion(string $laporanId, array $idsToKeep): void
    {
        $pathsAkanDihapus = $this->repository->getLampiranPaths($laporanId, $idsToKeep);
        foreach ($pathsAkanDihapus as $path) {
            Storage::disk('public')->delete($path);
        }
        $this->repository->deleteLampiran($laporanId, $idsToKeep);
    }

    /**
     * Ekstraksi Data Masa Panen
     *
     * @param array $panenRaw
     * @param string $laporanId
     * @param Carbon $created
     * @param Carbon $updated
     * @param array &$insertData Reference array insert
     * @return float Total NTE
     */
    private function parsingMasaPanen(array $panenRaw, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): float
    {
        $nteTotal = 0;
        foreach ($panenRaw as $panen) {
            $hargaTotal = (float)($panen['jumlah'] ?? 0) * (float)($panen['harga_satuan'] ?? 0);
            $nteTotal += $hargaTotal;
            $insertData['masa_panen'][] = [
                'id'           => Str::uuid()->toString(),
                'id_laporan'   => $laporanId,
                'tanggal'      => $panen['tanggal'] ?? $created->toDateString(),
                'id_komoditas' => $panen['id_komoditas'],
                'jumlah'       => (float)($panen['jumlah'] ?? 0),
                'harga_satuan' => (float)($panen['harga_satuan'] ?? 0),
                'harga_total'  => $hargaTotal,
                'created_at'   => $created,
                'updated_at'   => $updated,
            ];
        }
        return $nteTotal;
    }

    /**
     * Ekstraksi Data Masa Tanam
     *
     * @param array $tanamRaw
     * @param string $laporanId
     * @param Carbon $created
     * @param Carbon $updated
     * @param array &$insertData Reference array insert
     * @return void
     */
    private function parsingMasaTanam(array $tanamRaw, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): void
    {
        foreach ($tanamRaw as $tanam) {
            $insertData['masa_tanam'][] = [
                'id'           => Str::uuid()->toString(),
                'id_laporan'   => $laporanId,
                'tanggal'      => $tanam['tanggal'] ?? $created->toDateString(),
                'id_komoditas' => $tanam['id_komoditas'],
                'jumlah'       => (float)($tanam['jumlah'] ?? 0),
                'usia_tanam'   => (float)($tanam['usia_tanam'] ?? 0),
                'created_at'   => $created,
                'updated_at'   => $updated,
            ];
        }
    }

    /**
     * Ekstraksi Data Lampiran
     *
     * @param array $files
     * @param string $laporanId
     * @param Carbon $created
     * @param Carbon $updated
     * @param array &$insertData Reference array insert
     * @return void
     */
    private function parsingLampiran(array $files, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): void
    {
        foreach ($files as $file) {
            $insertData['lampiran'][] = [
                'id'         => Str::uuid()->toString(),
                'id_laporan' => $laporanId,
                'path'       => $this->storeFile($file),
                'nama_file'  => $file->getClientOriginalName(),
                'created_at' => $created,
                'updated_at' => $updated,
            ];
        }
    }
}
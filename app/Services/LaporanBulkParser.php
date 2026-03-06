<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Repository\LaporanRepository;

/** Class LaporanBulkParser
 * @package App\Services
 */
class LaporanBulkParser
{
    private LaporanRepository $repository;

    /** LaporanBulkParser constructor
     * @param LaporanRepository $repository Injeksi repositori
     */
    public function __construct(LaporanRepository $repository)
    {
        $this->repository = $repository;
    }

    /** Ekstraksi Data Masa Panen
     * @param array $panenRaw Array raw input
     * @param string $laporanId UUID laporan
     * @param Carbon $created Waktu dibuat
     * @param Carbon $updated Waktu diubah
     * @param array $insertData Array referensi koleksi insert bulk
     * @return float Total uang dari Masa Panen (NTE)
     */
    public function parseMasaPanen(array $panenRaw, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): float
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

    /** Ekstraksi Data Masa Tanam
     * @param array $tanamRaw Array raw input
     * @param string $laporanId UUID laporan
     * @param Carbon $created Waktu dibuat
     * @param Carbon $updated Waktu diubah
     * @param array $insertData Array referensi koleksi insert bulk
     * @return void
     */
    public function parseMasaTanam(array $tanamRaw, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): void
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

    /** Ekstraksi Data Lampiran Fisik
     * @param array $files Koleksi file fisik yang diunggah
     * @param string $laporanId UUID laporan
     * @param Carbon $created Waktu dibuat
     * @param Carbon $updated Waktu diubah
     * @param array $insertData Array referensi koleksi insert bulk
     * @return void
     */
    public function parseLampiran(array $files, string $laporanId, Carbon $created, Carbon $updated, array &$insertData): void
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

    /** Menangani proses pembaruan masa panen
     * @param string $laporanId ID dari Laporan terkait
     * @param array|null $masaPanen Data payload masa panen
     * @param Carbon $clientUpdated Waktu pembaruan
     * @param array $insertData Referensi array pengumpulan data insert
     * @param array $dataToUpdate Referensi array untuk menyimpan total NTE kalkulasi ulang
     * @return void
     */
    public function handleUpdateMasaPanen(string $laporanId, ?array $masaPanen, Carbon $clientUpdated, array &$insertData, array &$dataToUpdate): void
    {
        if (isset($masaPanen)) {
            $this->repository->deleteMasaPanenByLaporanId($laporanId);
            $dataToUpdate['nte'] = $this->parseMasaPanen($masaPanen, $laporanId, $clientUpdated, $clientUpdated, $insertData);
        }
    }

    /** Menangani proses pembaruan masa tanam
     * @param string $laporanId ID Laporan terkait
     * @param array|null $masaTanam Data payload masa tanam
     * @param Carbon $clientUpdated Waktu pembaruan
     * @param array $insertData Referensi array pengumpulan data insert
     * @return void
     */
    public function handleUpdateMasaTanam(string $laporanId, ?array $masaTanam, Carbon $clientUpdated, array &$insertData): void
    {
        if (isset($masaTanam)) {
            $this->repository->deleteMasaTanamByLaporanId($laporanId);
            $this->parseMasaTanam($masaTanam, $laporanId, $clientUpdated, $clientUpdated, $insertData);
        }
    }

    /** Menangani sinkronisasi file lampiran
     * @param string $laporanId ID Laporan terkait
     * @param array|null $lampiranExisting Array ID lampiran lama yang dipertahankan
     * @param array $lampiranNew Koleksi file lampiran baru yang diunggah
     * @param Carbon $clientUpdated Waktu pembaruan
     * @param array $insertData Referensi array pengumpulan data insert
     * @return void
     */
    public function handleUpdateLampiran(string $laporanId, ?array $lampiranExisting, array $lampiranNew, Carbon $clientUpdated, array &$insertData): void
    {
        if (isset($lampiranExisting)) {
            $this->processLampiranDeletion($laporanId, $lampiranExisting);
        }
        $this->parseLampiran($lampiranNew, $laporanId, $clientUpdated, $clientUpdated, $insertData);
    }

    /** Penghapusan data lampiran
     * @param string $laporanId UUID laporan
     * @param array $idsToKeep ID lampiran mana saja yang dipertahankan
     * @return void
     */
    private function processLampiranDeletion(string $laporanId, array $idsToKeep): void
    {
        $pathsAkanDihapus = $this->repository->getLampiranPaths($laporanId, $idsToKeep);
        foreach ($pathsAkanDihapus as $path) {
            Storage::disk('public')->delete($path);
        }
        $this->repository->deleteLampiran($laporanId, $idsToKeep);
    }

    /** Menyimpan file ke local storage
     * @param mixed $file
     * @return string Path relasional file
     */
    private function storeFile($file): string
    {
        $originalFilename = $file->getClientOriginalName();
        $sanitizedFilename = preg_replace('/[^A-Za-z0-9\._-]/', '', pathinfo($originalFilename, PATHINFO_FILENAME));
        $newFilename = Str::random(8) . '-' . $sanitizedFilename . '.' . $file->getClientOriginalExtension();
        
        return $file->storeAs('lampiran', $newFilename, 'public');
    }

    /** Membersihkan file terunggah bila terjadi gagal
     * @param array $lampiranData List objek lampiran dengan property 'path'
     * @return void
     */
    public function cleanupFailedUploads(array $lampiranData): void
    {
        foreach ($lampiranData as $lampiran) {
            Storage::disk('public')->delete($lampiran['path']);
        }
    }

    /** Memvalidasi tipe timestamp
     * @param array $data Array inputan
     * @param string $key Nama field
     * @param Carbon $default Nilai kembalian default jika data tidak ada
     * @return Carbon Instance carbon
     */
    public function resolveTimestamp(array $data, string $key, Carbon $default): Carbon
    {
        return isset($data[$key]) ? Carbon::parse($data[$key]) : clone $default;
    }
}

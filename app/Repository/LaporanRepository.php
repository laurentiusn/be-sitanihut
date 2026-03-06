<?php

namespace App\Repository;

use App\Models\Laporan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/** Class LaporanRepository
 * @package App\Repository
 */
class LaporanRepository
{
    /** Mengeksekusi query insert massal
     * @param array $data Array asosiatif yang berisi data-data insert. Key yang didukung: 
     *                    'laporan', 'masa_tanam', 'masa_panen', 'lampiran', 'log'
     * @return void
     */
    public function executeInserts(array $data): void
    {
        if (!empty($data['laporan']))    Laporan::insert($data['laporan']);
        if (!empty($data['masa_tanam'])) DB::table('masa_tanam')->insert($data['masa_tanam']);
        if (!empty($data['masa_panen'])) DB::table('masa_panen')->insert($data['masa_panen']);
        if (!empty($data['lampiran']))   DB::table('lampiran_laporan')->insert($data['lampiran']);
        if (!empty($data['log']))        DB::table('log_laporans')->insert($data['log']);
    }

    /** Mengambil kumpulan model Laporan berdasarkan ID
     * @param array $ids Array berisi daftar ID laporan
     * @param string|null $userId ID user (opsional) untuk memastikan laporan milik user tersebut
     * @return Collection Koleksi objek Laporan dengan ID sebagai key
     */
    public function getByIds(array $ids, ?string $userId = null): Collection
    {
        $query = Laporan::whereIn('id', $ids);
        if ($userId) {
            $query->where('id_user', $userId);
        }
        return $query->get()->keyBy('id');
    }

    /** Menghapus data masa panen
     * @param string $laporanId ID laporan
     * @return void
     */
    public function deleteMasaPanenByLaporanId(string $laporanId): void
    {
        DB::table('masa_panen')->where('id_laporan', $laporanId)->delete();
    }

    /** Menghapus data masa tanam
     * @param string $laporanId ID laporan
     * @return void
     */
    public function deleteMasaTanamByLaporanId(string $laporanId): void
    {
        DB::table('masa_tanam')->where('id_laporan', $laporanId)->delete();
    }

    /** Mengambil path lampiran laporan
     * @param string $laporanId ID laporan
     * @param array $idsToKeep Daftar ID lampiran yang akan dipertahankan (tidak dihapus)
     * @return array Daftar path lampiran
     */
    public function getLampiranPaths(string $laporanId, array $idsToKeep): array
    {
        return DB::table('lampiran_laporan')
            ->where('id_laporan', $laporanId)
            ->whereNotIn('id', $idsToKeep)
            ->pluck('path')
            ->toArray();
    }

    /** Menghapus data lampiran laporan
     * @param string $laporanId ID laporan
     * @param array $idsToKeep Daftar ID lampiran yang akan dipertahankan
     * @return void
     */
    public function deleteLampiran(string $laporanId, array $idsToKeep): void
    {
        DB::table('lampiran_laporan')
            ->where('id_laporan', $laporanId)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    /** Memperbarui tabel laporan
     * @param string $laporanId ID laporan
     * @param array $dataToUpdate Data yang akan di-update
     * @return void
     */
    public function updateLaporan(string $laporanId, array $dataToUpdate): void
    {
        DB::table('laporan')->where('id', $laporanId)->update($dataToUpdate);
    }

    /** Mengambil laporan termasuk soft-deleted
     * @param array $ids Array berisi daftar ID laporan
     * @return Collection Koleksi objek Laporan yang key-nya adalah ID laporan
     */
    public function getByIdsWithTrashed(array $ids): Collection
    {
        return Laporan::withTrashed()->whereIn('id', $ids)->get()->keyBy('id');
    }
}
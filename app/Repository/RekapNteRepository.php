<?php

namespace App\Repository;

use App\Models\Laporan;
use Illuminate\Support\Facades\DB;

/**
 * Class RekapNteRepository
 * 
 * Repository untuk menangani logika rekapitulasi Nilai Tambah Ekonomi (NTE)
 * berdasarkan berbagai level (KPH, KTH, Petani, dan Komoditas).
 *
 * @package App\Repository
 */
class RekapNteRepository
{
    /**
     * Membuat dan mengembalikan query dasar untuk rekapitulasi NTE berdasarkan tipe dan periode.
     *
     * @param string $type Tipe rekapitulasi ('kph', 'kth', 'petani', 'komoditas')
     * @param int $periode Tahun/Periode laporan
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws \InvalidArgumentException Jika tipe rekap tidak dikenali
     */
    public function getBaseQuery(string $type, int $periode)
    {
        if ($type === 'kph') {
            return Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('kph.nama_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('kph.nama_kph');
        } elseif ($type === 'kth') {
            return Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('kth.nama_kth', 'kph.nama_kph as asal_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('kth.nama_kth', 'kph.nama_kph');
        } elseif ($type === 'petani') {
            return Laporan::query()
                ->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('user_details', 'users.id', '=', 'user_details.id_user')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select('user_details.nama_user as nama_petani', 'kth.nama_kth as asal_kth', 'kph.nama_kph as asal_kph', DB::raw('SUM(laporan.nte) as total_nte'))
                ->groupBy('user_details.nama_user', 'kth.nama_kth', 'kph.nama_kph');
        } elseif ($type === 'komoditas') {
            return Laporan::query()
                ->join('masa_panen', 'laporan.id', '=', 'masa_panen.id_laporan')
                ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
                ->where('laporan.periode', $periode)
                ->where('laporan.status', 'disetujui')
                ->select(
                    'komoditas.nama_komoditas as nama_komoditas',
                    DB::raw('SUM(masa_panen.harga_total) as total_nte'),
                    DB::raw('SUM(masa_panen.jumlah) as jumlah_produksi')
                )
                ->groupBy('komoditas.nama_komoditas');
        }

        throw new \InvalidArgumentException("Tipe rekap tidak valid");
    }

    /**
     * Menerapkan pencarian teks (search) pada query rekapitulasi berdasarkan tipe rekap.
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param string|null $search Teks pencarian
     * @param string $type Tipe rekapitulasi ('kph', 'kth', 'petani', 'komoditas')
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function applySearch($query, ?string $search, string $type)
    {
        if (!$search) return $query;

        return $query->where(function ($subQuery) use ($search, $type) {
            if ($type === 'kph') {
                $subQuery->where('kph.nama_kph', 'like', "%{$search}%");
            } elseif ($type === 'kth') {
                $subQuery->where('kth.nama_kth', 'like', "%{$search}%")
                         ->orWhere('kph.nama_kph', 'like', "%{$search}%");
            } elseif ($type === 'petani') {
                $subQuery->where('user_details.nama_user', 'like', "%{$search}%")
                         ->orWhere('kth.nama_kth', 'like', "%{$search}%")
                         ->orWhere('kph.nama_kph', 'like', "%{$search}%");
            } elseif ($type === 'komoditas') {
                $subQuery->where('komoditas.nama_komoditas', 'like', "%{$search}%");
            }
        });
    }
    /**
     * Mendapatkan total keseluruhan NTE (Grand Total) dari laporan yang disetujui
     * pada periode tertentu.
     *
     * @param int $periode Tahun/Periode laporan
     * @return float|int Total NTE
     */
    public function getGrandTotal(int $periode)
    {
        return Laporan::query()
            ->where('periode', $periode)
            ->where('status', 'disetujui')
            ->sum('nte');
    }
    /**
     * Mendapatkan rincian produksi (komoditas dan jumlah panen) berdasarkan tipe rekap dan item spesifik.
     *
     * @param string $type Tipe rekapitulasi ('kph', 'kth', 'petani')
     * @param int $periode Tahun/Periode laporan
     * @param object $item Objek data iterasi saat ini (hasil dari query utama)
     * @return \Illuminate\Support\Collection Koleksi rincian produksi
     */
    public function getProduksiDetail(string $type, int $periode, object $item)
    {
        $query = DB::table('masa_panen')
            ->join('laporan', 'masa_panen.id_laporan', '=', 'laporan.id')
            ->join('komoditas', 'masa_panen.id_komoditas', '=', 'komoditas.id')
            ->where('laporan.periode', $periode)
            ->where('laporan.status', 'disetujui');

        if ($type === 'kph') {
            $query->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('kph.nama_kph', $item->nama_kph);
        } elseif ($type === 'kth') {
            $query->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('kth.nama_kth', $item->nama_kth)
                ->where('kph.nama_kph', $item->asal_kph);
        } elseif ($type === 'petani') {
            $query->join('users', 'laporan.id_user', '=', 'users.id')
                ->join('user_details', 'users.id', '=', 'user_details.id_user')
                ->join('kth', 'users.id_kth', '=', 'kth.id')
                ->join('kph', 'kth.id_kph', '=', 'kph.id')
                ->where('user_details.nama_user', $item->nama_petani)
                ->where('kth.nama_kth', $item->asal_kth)
                ->where('kph.nama_kph', $item->asal_kph);
        }

        return $query->select(['komoditas.nama_komoditas as komoditas', DB::raw('SUM(masa_panen.jumlah) as jumlah')])
            ->groupBy('komoditas.nama_komoditas')
            ->get();
    }
}
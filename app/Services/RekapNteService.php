<?php

namespace App\Services;

use App\Repository\RekapNteRepository;

/**
 * Class RekapNteService
 * * Service: Rekapitulasi Nilai Tambah Ekonomi (NTE), pemrosesan data tampilan tabel, 
 * perhitungan grand total, hingga ekspor data PDF maupun CSV.
 *
 * @package App\Services
 */
class RekapNteService
{
    protected RekapNteRepository $rekapNteRepository;

    /**
     * Konstruktor RekapNteService.
     * Menginjeksi *Repository Pattern* untuk menjauhkan Service dari Query Builder DB langsung.
     *
     * @param RekapNteRepository $rekapNteRepository Dependensi Repository
     */
    public function __construct(RekapNteRepository $rekapNteRepository)
    {
        $this->rekapNteRepository = $rekapNteRepository;
    }

    /**
     * Fungsi orkestrasi sentral untuk mengambil, menyaring, dan mengubah
     * wujud (transform) data Rekapitulasi secara dinamis menurut Tipe Pemfilteran (KPH/KTH/Petani).
     *
     * @param array $filters Argument array (periode, type, search, is_export)
     * @return array Mengembalikan data Paginator (untuk UI) atau Collection murni (untuk Export)
     */
    public function getRekapitulasi(array $filters): array
    {
        $periode = $filters['periode'] ?? now()->year;
        $type = $filters['type'];
        $search = $filters['search'] ?? null;
        $isExport = $filters['is_export'] ?? false;

        $query = $this->rekapNteRepository->getBaseQuery($type, $periode);
        $query = $this->rekapNteRepository->applySearch($query, $search, $type)
                      ->having('total_nte', '>', 0)
                      ->orderBy('total_nte', 'desc');

        $grandTotalNte = $this->rekapNteRepository->getGrandTotal($periode);

        $result = $isExport ? $query->get() : $query->paginate(20)->withQueryString();
        $collectionToTransform = $isExport ? $result : $result->getCollection();

        $collectionToTransform->transform(function ($item) use ($periode, $type) {
            return $this->transformRekapItem($item, $type, $periode);
        });

        if ($isExport) {
            return ['data' => $result, 'grand_total' => $grandTotalNte];
        }

        return ['paginator' => $result, 'grand_total' => $grandTotalNte];
    }

    /**
     * Menangani logika transformasi atau penyesuaian (format angka) detail produksi untuk tiap baris data rekap.
     *
     * @param object $item Objek baris iterasi data
     * @param string $type Tipe filter (kph, kth, petani, komoditas)
     * @param string|int $periode Nilai periode filter
     * @return object Objek hasil transformasi
     */
    private function transformRekapItem(object $item, string $type, $periode): object
    {
        if ($type === 'komoditas') {
            $item->produksi = $this->formatJumlah($item->jumlah_produksi);
        } else {
            $item->produksi = $this->rekapNteRepository
                ->getProduksiDetail($type, $periode, $item)
                ->map(function ($prod) {
                    $prod->jumlah = $this->formatJumlah($prod->jumlah);
                    return $prod;
                });
        }
        return $item;
    }

    /**
     * Berperan sebagai jembatan (Dispatcher) untuk mengeksekusi format eksport data.
     * Meneruskan kumpulan data rekapitulasi utuh (*unpaginated*) ke penghasil PDF atau CSV.
     *
     * @param array $filters Array kriteria ekspor dari Controller
     * @return mixed Response stream CSV atau Unduhan File PDF
     * @throws \InvalidArgumentException Jika format ekspor tidak terdaftar
     */
    public function handleExport(array $filters)
    {
        $filters['is_export'] = true;
        $result = $this->getRekapitulasi($filters);

        $data = $result['data'];
        $type = $filters['type'];
        $grandTotal = $result['grand_total'];
        $periode = $filters['periode'];
        $format = $filters['export'];

        $exporter = new RekapNteExportService();

        return match ($format) {
            'csv' => $exporter->generateCsv($data, $type, $grandTotal, $periode),
            'pdf' => $exporter->generatePdf($data, $type, $grandTotal, $periode),
            default => throw new \InvalidArgumentException("Tipe ekspor tidak valid"),
        };
    }

    /**
     * Memformat representasi angka agar lebih elegan.
     * Menghapus desimal (.00) yang tidak berarti, mengubah float "12.0" menjadi int "12",
     * tetapi tetap mempertahankan desimal sejati seperti "12.5".
     *
     * @param mixed $angka Variabel angka (float/string num)
     * @return int|float
     */
    private function formatJumlah($angka)
    {
        $floatVal = (float) $angka;
        return ($floatVal == (int)$floatVal) ? (int)$floatVal : $floatVal;
    }
}
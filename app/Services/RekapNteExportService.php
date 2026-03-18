<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

/**
 * Class RekapNteExportService
 * 
 * Layanan khusus (*Dedicated Service*) yang menangani pembuatan file PDF (menggunakan DomPDF) dan *streaming* CSV.
 * Dipisahkan dari RekapNteService agar menghemat penggunaan RAM pada aplikasi (*Lazy Loading*).
 *
 * @package App\Services
 */
class RekapNteExportService
{
    /**
     * Membangun antarmuka representasi file PDF (*Portable Document Format*).
     * Mengemas data mentah menjadi bentuk presentasi dan mengeksekusi package Barryvdh/DomPDF.
     *
     * @param iterable $data Koleksi baris rekapitulasi
     * @param string $type Tipe filter
     * @param float $grandTotal Total NTE keseluruhan
     * @param string|int $periode Nilai periode tahun
     * @return \Illuminate\Http\Response File PDF download
     */
    public function generatePdf($data, $type, $grandTotal, $periode)
    {
        $headers = $this->getExportHeaders($type, false);
        $formattedRows = [];
        $no = 1;

        foreach ($data as $item) {
            $formattedRows[] = [
                'no'        => $no++,
                'columns'   => $this->getDynamicColumns($item, $type),
                'produksi'  => $this->formatProduksiHtml($item, $type),
                'total_nte' => number_format($item->total_nte, 0, ',', '.')
            ];
        }

        $viewData = [
            'type'        => $type,
            'periode'     => $periode,
            'headers'     => $headers,
            'rows'        => $formattedRows,
            'colspan'     => count($headers) - 1,
            'grand_total' => number_format($grandTotal, 0, ',', '.')
        ];

        return Pdf::loadView('exports.rekap_nte_pdf', $viewData)
                  ->download("rekap_nte_{$type}_{$periode}.pdf");
    }

    /**
     * Mengalirkan (*streaming*) data rekap menjadi representasi Comma Separated Values (CSV).
     * Memiliki efisiensi memori yang jauh lebih tinggi daripada pembentukan string raksasa
     * di dalam memori saat merekap puluhan ribu entri.
     *
     * @param iterable $data Koleksi baris rekapitulasi
     * @param string $type Tipe filter
     * @param float $grandTotal Total NTE keseluruhan
     * @param string|int $periode Nilai periode
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateCsv($data, $type, $grandTotal, $periode)
    {
        $fileName = "rekap_nte_{$type}_{$periode}.csv";
        $headers = $this->getExportHeaders($type, true);

        $callback = function () use ($data, $headers, $type, $grandTotal, $periode) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['REKAP NILAI TRANSAKSI EKONOMI - TIPE: ' . strtoupper($type)], ';');
            fputcsv($file, ['Periode: ' . $periode], ';');
            fputcsv($file, [], ';');
            fputcsv($file, $headers, ';');

            $no = 1;
            foreach ($data as $item) {
                $row = array_merge(
                    [$no++],
                    $this->getDynamicColumns($item, $type),
                    [$this->formatProduksiCsv($item, $type), $item->total_nte]
                );
                fputcsv($file, $row, ';');
            }

            $footerRow = array_fill(0, count($headers), '');
            $footerRow[count($headers) - 2] = 'GRAND TOTAL KESELURUHAN';
            $footerRow[count($headers) - 1] = $grandTotal;
            fputcsv($file, $footerRow, ';');
            
            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    /**
     * Menentukan susunan array header kolom berdasarkan tipe rekap.
     * 
     * @param string $type
     * @param bool $isCsv
     * @return array
     */
    private function getExportHeaders(string $type, bool $isCsv): array
    {
        $komoditasLabel = $isCsv ? 'Komoditas' : 'Nama Komoditas';

        return match ($type) {
            'kph'       => ['No', 'Nama KPH', 'Produksi', 'Total NTE (Rp)'],
            'kth'       => ['No', 'Nama KPH', 'Nama KTH', 'Produksi', 'Total NTE (Rp)'],
            'petani'    => ['No', 'Nama KPH', 'Nama KTH', 'Nama Petani', 'Produksi', 'Total NTE (Rp)'],
            'komoditas' => ['No', $komoditasLabel, 'Produksi', 'Total NTE (Rp)'],
            default     => ['No', 'Produksi', 'Total NTE (Rp)']
        };
    }

    /**
     * Mengambil nilai array kolom string yang dinamis dari objek baris tabel.
     * 
     * @param object $item
     * @param string $type
     * @return array
     */
    private function getDynamicColumns(object $item, string $type): array
    {
        return match ($type) {
            'kph'       => [$item->nama_kph ?? '-'],
            'kth'       => [$item->asal_kph ?? '-', $item->nama_kth ?? '-'],
            'petani'    => [$item->asal_kph ?? '-', $item->asal_kth ?? '-', $item->nama_petani ?? '-'],
            'komoditas' => [$item->nama_komoditas ?? '-'],
            default     => []
        };
    }

    /**
     * Memformat daftar rincian produksi menjadi HTML list (<ul><li>) siap cetak untuk dokumen PDF.
     *
     * @param object $item Objek hasil rekapan baris
     * @param string $type Tipe rekapan
     * @return string Format list tag HTML
     */
    private function formatProduksiHtml(object $item, string $type): string
    {
        if ($type === 'komoditas') {
            return (string) $item->produksi;
        }

        $html = '<ul class="list-produksi">';
        foreach ($item->produksi as $prod) {
            $html .= "<li>{$prod->komoditas}: {$prod->jumlah}</li>";
        }
        return $html . '</ul>';
    }

    /**
     * Memformat daftar rincian produksi menjadi plain text list terpisahkan tanda koma untuk keluaran file CSV.
     *
     * @param object $item Objek hasil rekapan baris
     * @param string $type Tipe rekapan
     * @return string Format comma-separated text
     */
    private function formatProduksiCsv(object $item, string $type): string
    {
        if ($type === 'komoditas') {
            return (string) $item->produksi;
        }

        $arr = [];
        foreach ($item->produksi as $prod) {
            $arr[] = $prod->komoditas . ': ' . $prod->jumlah;
        }
        return implode(', ', $arr);
    }
}
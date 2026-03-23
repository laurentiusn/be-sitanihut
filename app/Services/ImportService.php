<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\ValidationException;

/**
 * Class ImportService
 * 
 * Service umum (reusable) untuk menangani proses impor data dari file Excel.
 * Memvalidasi format kolom (duplikat dan ketersediaan), serta menangani
 * transaksi database dan kesalahan (exceptions) selama proses impor.
 *
 * @package App\Services
 */
class ImportService
{
    /**
     * Menangani logika validasi *header* dan eksekusi impor data Excel.
     * Menggunakan Maatwebsite/Laravel-Excel.
     *
     * @param \Illuminate\Http\UploadedFile|string|mixed $file File Excel yang diunggah
     * @param object $importInstance Instansiasi class Importer (misal: UserImport)
     * @param array $requiredColumns Daftar nama kolom wajib yang harus ada di baris pertama Excel
     * @return JsonResponse Balikan berupa response JSON berisi status dan pesan/error
     */
    public function handleImport($file, object $importInstance, array $requiredColumns): JsonResponse
    {
        $headings = (new HeadingRowImport)->toArray($file);
        $headerRow = $headings[0][0] ?? [];

        // Periksa Header
        $kalkulasiKolom = array_count_values($headerRow);
        $kolomDuplikat = [];
        foreach ($kalkulasiKolom as $namaKolom => $jumlahMuncul) {
            if ($jumlahMuncul > 1 && !empty($namaKolom)) {
                $kolomDuplikat[] = $namaKolom;
            }
        }
        
        // Cek Header Duplikat
        if (count($kolomDuplikat) > 0) {
            $namaKolomError = implode(', ', $kolomDuplikat);
            return response()->json([
                'statusCode' => 422,
                'message'    => 'Gagal mengimpor data. Format file Excel tidak valid',
                'errors'     => ["Terdapat judul kolom duplikat: {$namaKolomError}, Harap hapus salah satunya"]
            ], 422);
        }

        // Cek Header Hilang
        $kolomHilang = array_diff($requiredColumns, $headerRow);
        if (count($kolomHilang) > 0) {
            $namaKolomHilang = implode(', ', $kolomHilang);
            return response()->json([
                'statusCode' => 422,
                'message'    => 'Gagal mengimpor data. Format file Excel tidak sesuai',
                'errors'     => ["Kolom berikut wajib ada pada baris pertama atau penulisannya salah (typo): {$namaKolomHilang}"]
            ], 422);
        }

        DB::beginTransaction();
        try {
            Excel::import($importInstance, $file);
            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message'    => 'Data berhasil diimport',
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errorMessages = [];

            // Data yang salah
            foreach ($failures as $failure) {
                $baris = $failure->row();
                $kolom = $failure->attribute();
                foreach ($failure->errors() as $error) {
                    $errorMessages[] = "Baris {$baris} (kolom {$kolom}): {$error}";
                }
            }

            return response()->json([
                'statusCode' => 422,
                'message'    => 'Gagal mengimpor data. Terdapat kesalahan pada isi baris Excel Anda',
                'errors'     => $errorMessages
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $statusCode = str_contains($e->getMessage(), 'Baris') ? 422 : 500;
            
            return response()->json([
                'statusCode' => $statusCode,
                'message'    => 'Terjadi kesalahan sistem saat mengimpor data',
                'error'      => $e->getMessage()
            ], $statusCode);
        }
    }
}
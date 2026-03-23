<?php

namespace App\Imports;

use App\Models\Komoditas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;

/** Class KomoditasImport Excel Importer
 * @package App\Imports
 */
class KomoditasImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /** Pemetaan Data Komoditas dari baris Excel
     * @param array $row Data satu baris dari file Excel (berdasarkan header)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Komoditas([
            'id'                    => Str::uuid()->toString(),
            'kode'                  => $row['kode'],
            'nama_komoditas'        => $row['nama_komoditas'],
            'jenis_komoditas'       => $row['jenis_komoditas'],
            'nte'                   => 0,
            'komoditas_kehutanan'   => $row['komoditas_kehutanan'] ?? false,
        ]);
    }

    /** Aturan Validasi
     * @return array Aturan validasi Laravel
     */
    public function rules(): array
    {
        return [
            'kode'                => 'required|string|max:10|unique:komoditas,kode|distinct',
            'nama_komoditas'      => 'required|string|max:50',
            'jenis_komoditas'     => 'required|string|max:30',
            'komoditas_kehutanan' => 'required|boolean',
        ];
    }

    /** Pesan Gagal Validasi
     * @return array Pesan error validasi
     */
    public function customValidationMessages(): array
    {
        return [
            'kode.required'                 => 'Kode komoditas wajib diisi',
            'nama_komoditas.required'       => 'Nama komoditas wajib diisi',
            'jenis_komoditas.required'      => 'Jenis komoditas wajib diisi',
            'komoditas_kehutanan.required'  => 'Kolom komoditas_kehutanan wajib diisi',
            'kode.unique'                   => 'Kode komoditas :input sudah ada di database',
            'kode.distinct'                 => 'Kode ":input" terdeteksi ganda/duplikat di dalam file Excel',
            'komoditas_kehutanan.boolean'   => 'Kolom komoditas_kehutanan harus bernilai true atau false',

            'kode.max'                      => 'Kode komoditas tidak boleh lebih dari 10 karakter',
            'nama_komoditas.max'            => 'Nama komoditas tidak boleh lebih dari 50 karakter',
            'jenis_komoditas.max'           => 'Jenis komoditas tidak boleh lebih dari 30 karakter',
        ];
    }

    /** Ukuran Batch
     * @return int Jumlah record per batch insert
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /** Ukuran Chunk
     * @return int Jumlah baris per chunk
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
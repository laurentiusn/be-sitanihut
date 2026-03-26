<?php

namespace App\Imports;

use App\Models\Kth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use App\Models\User;
use App\Helpers\RoleHelper;

/** Class KthImport Excel Importer
 * @package App\Imports
 */
class KthImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    /** Pemetaan Data KTH dari baris Excel
     * @param array $row Data satu baris dari file Excel (berdasarkan header)
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $idKph = $row['id_kph'] ?? null;
        $idPenyuluh = $row['id_penyuluh'] ?? null;

        return new Kth([
            'id'          => Str::uuid()->toString(),
            'nama_kth'    => $row['nama_kth'],
            'desa'        => $row['desa'],
            'kecamatan'   => $row['kecamatan'],
            'kabupaten'   => $row['kabupaten'],
            'koordinator' => $row['koordinator'],
            'nomor_wa'    => $row['nomor_wa'],
            'id_penyuluh' => $idPenyuluh,
            'id_kph'      => $idKph,
        ]);
    }

    /** Manipulasi Data Nomor Wa menjadi string
     * @param array $data Data baris sebelum validasi
     * @param int $index Indeks baris
     * @return array
     */
    public function prepareForValidation($data, $index)
    {
        if (isset($data['nomor_wa'])) {
            $data['nomor_wa'] = (string) $data['nomor_wa'];
        }

        return $data;
    }

    /** Aturan Validasi
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_kth'    => 'required|string|max:35',
            'desa'        => 'required|string|max:35',
            'kecamatan'   => 'required|string|max:35',
            'kabupaten'   => 'required|string|max:35',
            'koordinator' => 'required|string|max:35',
            'nomor_wa'    => 'required|string|max:15',
            'id_penyuluh' => ['required', 'uuid', 'exists:users,id'],
            'id_kph'      => ['required', 'uuid', 'exists:kph,id']
        ];
    }

    /** Pesan Gagal Validasi
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama_kth.required'     => 'Nama KTH wajib diisi',
            'desa.required'         => 'Desa wajib diisi',
            'kecamatan.required'    => 'Kecamatan wajib diisi',
            'kabupaten.required'    => 'Kabupaten wajib diisi',
            'koordinator.required'  => 'Koordinator wajib diisi',
            'nomor_wa.required'     => 'Nomor WA wajib diisi',
            'id_penyuluh.required'  => 'ID Penyuluh wajib diisi',
            'id_kph.required'       => 'ID KPH wajib diisi',

            'nama_kth.max'          => 'Nama KTH tidak boleh lebih dari 35 karakter',
            'desa.max'              => 'Desa tidak boleh lebih dari 35 karakter',
            'kecamatan.max'         => 'Kecamatan tidak boleh lebih dari 35 karakter',
            'kabupaten.max'         => 'Kabupaten tidak boleh lebih dari 35 karakter',
            'koordinator.max'       => 'Koordinator tidak boleh lebih dari 35 karakter',
            'nomor_wa.max'          => 'Nomor WA tidak boleh lebih dari 15 karakter',

            'id_kph.exists'         => 'KPH :input yang dipilih tidak ditemukan',
            'id_penyuluh.exists'    => 'Penyuluh :input yang dipilih tidak ditemukan',
        ];
    }

    /** Validasi Penyuluh
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $rolePenyuluh = RoleHelper::id('penyuluh');

            $idPenyuluhArray = collect($data)->pluck('id_penyuluh')->filter()->unique()->toArray();

            $penyuluhs = User::whereIn('id', $idPenyuluhArray)
                             ->where('id_role', $rolePenyuluh)
                             ->get()
                             ->keyBy('id');

            foreach ($data as $index => $row) {
                if (!is_array($row)) continue;

                $idKph = $row['id_kph'] ?? null;
                $idPenyuluh = $row['id_penyuluh'] ?? null;

                if ($idKph && $idPenyuluh) {
                    $penyuluh = $penyuluhs->get($idPenyuluh);

                    if ($penyuluh && $penyuluh->id_kph !== $idKph) {
                        $validator->errors()->add(
                            "{$index}.id_penyuluh", 
                            'Penyuluh tidak terdaftar di KPH yang dipilih'
                        );
                    }
                }
            }
        });
    }

    /** Ukuran Batch
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /** Ukuran Chunk
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
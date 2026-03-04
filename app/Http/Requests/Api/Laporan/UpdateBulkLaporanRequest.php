<?php

namespace App\Http\Requests\Api\Laporan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBulkLaporanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'laporan'                   => ['required', 'array', 'min:1'],
            
            'laporan.*.id'              => ['required', 'uuid'],

            // Validasi Laporan
            'laporan.*.periode'         => ['sometimes', 'required', 'digits:4'],
            'laporan.*.bulan'           => ['sometimes', 'required', 'string', Rule::in([
                                        'januari', 'februari', 'maret', 'april', 'mei', 'juni',
                                        'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],
                                            
            'laporan.*.modal'           => ['sometimes', 'required', 'numeric', 'min:0'],
            'laporan.*.status'          => ['sometimes', 'required', Rule::in(['belum diajukan', 'menunggu'])],
            'laporan.*.catatan_petani'  => ['nullable', 'string'],

            // Validasi Masa Tanam
            'laporan.*.masa_tanam'                => ['sometimes', 'nullable', 'array'],
            'laporan.*.masa_tanam.*.tanggal'      => ['nullable', 'date'],
            'laporan.*.masa_tanam.*.id_komoditas' => ['required', 'uuid', 'exists:komoditas,id'],
            'laporan.*.masa_tanam.*.jumlah'       => ['required', 'numeric', 'min:0'],
            'laporan.*.masa_tanam.*.usia_tanam'   => ['nullable', 'numeric', 'min:0'],

            // Validasi Masa Panen
            'laporan.*.masa_panen'                => ['sometimes', 'nullable', 'array'],
            'laporan.*.masa_panen.*.tanggal'      => ['required', 'date'],
            'laporan.*.masa_panen.*.id_komoditas' => ['required', 'uuid', 'exists:komoditas,id'],
            'laporan.*.masa_panen.*.jumlah'       => ['required', 'numeric', 'min:0'],
            'laporan.*.masa_panen.*.harga_satuan' => ['required', 'numeric', 'min:0'],

            // Lampiran
            'laporan.*.lampiran_existing'         => ['nullable', 'array'],
            'laporan.*.lampiran_existing.*'       => ['nullable', 'uuid', 'exists:lampiran_laporan,id'],
            'laporan.*.lampiran_new.*'            => ['required', 'file', 'max:10240'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dataLaporan = $this->all()['laporan'] ?? [];

            if (!is_array($dataLaporan)) {
                return;
            }

            foreach ($dataLaporan as $index => $item) {
                $existingFiles = $item['lampiran_existing'] ?? [];
                $jumlahExisting = is_array($existingFiles) ? count($existingFiles) : 0;

                $newFiles = $item['lampiran_new'] ?? [];
                $jumlahNew = is_array($newFiles) ? count($newFiles) : 0;

                $totalLampiran = $jumlahExisting + $jumlahNew;

                if ($totalLampiran > 10) {
                    $validator->errors()->add(
                        "laporan.{$index}.lampiran", 
                        "Total lampiran tidak boleh lebih dari 10. 
                        (Ditemukan: {$jumlahExisting} file lama dipertahankan + {$jumlahNew} file baru)."
                    );
                }
            }
        });
    }
}
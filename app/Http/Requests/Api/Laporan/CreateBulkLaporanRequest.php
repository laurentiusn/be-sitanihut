<?php

namespace App\Http\Requests\Api\Laporan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBulkLaporanRequest extends FormRequest
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
            'laporan.*.id'              => ['required', 'uuid', 'distinct', 'unique:laporan,id'],
            'laporan.*.periode'         => ['required', 'numeric', 'digits:4'],
            'laporan.*.bulan'           => ['required', 'string', Rule::in(
                ['januari', 'februari', 'maret', 'april', 'mei', 'juni',
                'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],

            'laporan.*.modal'           => ['required', 'numeric', 'min:0'],
            'laporan.*.status'          => ['required', Rule::in(['belum diajukan', 'menunggu'])],
            'laporan.*.catatan_petani'  => ['nullable', 'string'],

            'laporan.*.lampiran'        => ['nullable', 'array', 'max:10'],
            'laporan.*.lampiran.*'      => ['file', 'max:10240'],

            'laporan.*.masa_tanam'                 => ['nullable', 'array'],
            'laporan.*.masa_tanam.*.tanggal'       => ['nullable', 'date'],
            'laporan.*.masa_tanam.*.id_komoditas'  => ['required', 'uuid', 'exists:komoditas,id'],
            'laporan.*.masa_tanam.*.jumlah'        => ['required', 'numeric', 'min:0'],
            'laporan.*.masa_tanam.*.usia_tanam'    => ['nullable', 'numeric', 'min:0'],

            'laporan.*.masa_panen'                 => ['nullable', 'array'],
            'laporan.*.masa_panen.*.tanggal'       => ['required', 'date'],
            'laporan.*.masa_panen.*.id_komoditas'  => ['required', 'uuid', 'exists:komoditas,id'],
            'laporan.*.masa_panen.*.jumlah'        => ['required', 'numeric', 'min:0'],
            'laporan.*.masa_panen.*.harga_satuan'  => ['required', 'numeric', 'min:0'],
        ];
    }
}
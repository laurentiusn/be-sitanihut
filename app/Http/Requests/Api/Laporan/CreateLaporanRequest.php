<?php

namespace App\Http\Requests\Api\Laporan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateLaporanRequest extends FormRequest
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
            'periode'           => ['required', 'numeric', 'digits:4'],
            'bulan'             => ['required', 'string', Rule::in(['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],

            'modal'             => ['required', 'numeric', 'min:0'],
            'status'            => ['required', Rule::in(['belum diajukan', 'menunggu'])],
            'catatan_petani'    => ['nullable', 'string'],


            'lampiran'          => ['nullable', 'array', 'max:10'],
            'lampiran.*'        => ['file', 'max:10240'],

            'masa_tanam'                 => ['nullable', 'array'],
            'masa_tanam.*.tanggal'       => ['nullable', 'date'],
            'masa_tanam.*.id_komoditas'  => ['required', 'uuid', 'exists:komoditas,id'],
            'masa_tanam.*.jumlah'        => ['required', 'numeric', 'min:0'],
            'masa_tanam.*.usia_tanam'    => ['nullable', 'numeric', 'min:0'],

            'masa_panen'                       => ['nullable', 'array'],
            'masa_panen.*.tanggal'             => ['required', 'date'],
            'masa_panen.*.id_komoditas'        => ['required', 'uuid', 'exists:komoditas,id'],
            'masa_panen.*.jumlah'              => ['required', 'numeric', 'min:0'],
            'masa_panen.*.harga_satuan'        => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->role->slug === 'petani') {
            $this->request->remove('catatan_penyuluh');
        }
    }
}

<?php

namespace App\Http\Requests\Api\Laporan;

use App\Rules\TotalAttachmentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanRequest extends FormRequest
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
            'periode'           => ['sometimes', 'required', 'digits:4'],
            'bulan'             => ['sometimes', 'required', 'string', Rule::in(['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'])],

            'modal'             => ['sometimes', 'required', 'numeric', 'min:0'],

            'status'            => ['sometimes', 'required', Rule::in(['belum diajukan', 'menunggu', 'diverifikasi', 'disetujui', 'ditolak'])],
            'catatan_petani'    => ['nullable', 'string'],
            'catatan_penyuluh'  => ['nullable', 'string'],

            'masa_tanam'                => ['sometimes', 'array'],
            'masa_tanam.*.tanggal'      => ['nullable', 'date'],
            'masa_tanam.*.id_komoditas' => ['required', 'uuid', 'exists:komoditas,id'],
            'masa_tanam.*.jumlah'       => ['required', 'numeric', 'min:0'],
            'masa_tanam.*.usia_tanam'   => ['nullable', 'numeric', 'min:0'],

            'masa_panen'                => ['sometimes', 'array'],
            'masa_panen.*.tanggal'      => ['required', 'date'],
            'masa_panen.*.id_komoditas' => ['required', 'uuid', 'exists:komoditas,id'],
            'masa_panen.*.jumlah'       => ['required', 'numeric', 'min:0'],
            'masa_panen.*.harga_satuan' => ['required', 'numeric', 'min:0'],

            'lampiran_existing'     => ['nullable', 'array'],
            'lampiran_existing.*'   => ['nullable', 'uuid', 'exists:lampiran_laporan,id'],
            'lampiran_new'          => [
                'nullable',
                'array',
                new TotalAttachmentRule($this->input('lampiran_existing') ?? [])
            ],
            'lampiran_new.*'        => ['required', 'file', 'max:10240'],
        ];
    }
}

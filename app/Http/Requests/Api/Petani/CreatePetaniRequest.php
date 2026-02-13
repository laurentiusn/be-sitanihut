<?php

namespace App\Http\Requests\Api\Petani;

use Illuminate\Foundation\Http\FormRequest;

class CreatePetaniRequest extends FormRequest
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
            'nama_petani'           => 'required|string|max:35',
            'nik'                   => 'required|string|max:20|unique:petani,nik',
            'jenis_kelamin'         => 'required|in:pria,wanita',
            'alamat'                => 'required|string',
            'nomor_wa'              => 'required|string|max:20',
            'pendidikan_terakhir'   => 'required|string|max:10',
            'pekerjaan_sampingan'   => 'required|string|max:20',
            'luas_lahan'            => 'required|numeric',
            'id_kth'                => 'required|string|exists:kth,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id_kth.exists' => 'ID KTH tidak ditemukan',
            'id_kph.exists' => 'ID KPH tidak ditemukan',
            'nik.unique' => 'NIK sudah terdaftar',
        ];
    }
}

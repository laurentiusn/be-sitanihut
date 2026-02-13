<?php

namespace App\Http\Requests\Api\Petani;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetaniRequest extends FormRequest
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
            'nama_petani'           => 'sometimes|required|string|max:35',
            'nik'                   => 'sometimes|required|string|max:20|unique:petani,nik',
            'jenis_kelamin'         => 'sometimes|required|in:pria,wanita',
            'alamat'                => 'sometimes|required|string',
            'nomor_wa'              => 'nullable|string|max:20',
            'pendidikan_terakhir'   => 'nullable|string|max:10',
            'pekerjaan_sampingan'   => 'nullable|string|max:20',
            'luas_lahan'            => 'sometimes|required|numeric',
            'id_kth'                => 'sometimes|required|string|exists:kth,id',
            'id_kph'                => 'sometimes|required|string|exists:kph,id',
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

<?php

namespace App\Http\Requests\Api\Penyuluh;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenyuluhRequest extends FormRequest
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
            'nama_penyuluh' => 'sometimes|required|string|max:35',
            'nip'           => 'sometimes|required|string|max:20',
            'nomor_wa'      => 'sometimes|required|string|max:20',
            'jenis_kelamin' => 'sometimes|required|in:pria,wanita',
            'jabatan'       => 'sometimes|required|string|max:100',
            'id_kph'        => 'sometimes|required|string|exists:kph,id',
        ];
    }

    public function messages(): array
    {
        return [
            'id_kph.exists'    => 'ID KPH tidak ditemukan'
        ];
    }
}

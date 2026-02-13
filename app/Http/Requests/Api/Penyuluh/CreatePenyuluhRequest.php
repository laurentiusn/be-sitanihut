<?php

namespace App\Http\Requests\Api\Penyuluh;

use Illuminate\Foundation\Http\FormRequest;

class CreatePenyuluhRequest extends FormRequest
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
            'nama_penyuluh'     => 'required|string|max:35',
            'nip'               => 'required|string|max:20',
            'nomor_wa'          => 'required|string|max:20',
            'jenis_kelamin'     => 'required|in:pria,wanita',
            'jabatan'           => 'required|string|max:100',
            'id_kph'            => 'required|string|exists:kph,id',
        ];
    }
    public function messages(): array
    {
        return [
            'id_kph.exists'    => 'ID KPH tidak ditemukan'
        ];
    }
}

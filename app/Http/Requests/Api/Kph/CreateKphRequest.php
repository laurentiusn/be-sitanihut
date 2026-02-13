<?php

namespace App\Http\Requests\Api\Kph;

use Illuminate\Foundation\Http\FormRequest;

class CreateKphRequest extends FormRequest
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
            'nama_kph'  => 'required|string|max:35',
            'wilayah'   => 'required|string|max:35',
            'alamat'    => 'required|string',
            'nomor_wa'  => 'required|string|max:15|unique:kph,nomor_wa',
            'jenis'     => 'required|string|max:25',
        ];
    }
}

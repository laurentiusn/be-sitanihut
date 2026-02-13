<?php

namespace App\Http\Requests\Api\Kph;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKphRequest extends FormRequest
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
        $kph = $this->route('kph');
        return [
            'nama_kph'  => 'sometimes|required|string|max:35',
            'wilayah'   => 'sometimes|required|string|max:35',
            'alamat'    => 'sometimes|required|string',
            'nomor_wa'  => ['sometimes','nullable','string', 'max:15', Rule::unique('kph', 'nomor_wa')->ignore($kph->id)],
            'jenis'     => 'sometimes|required|string|max:25',
        ];
    }
}

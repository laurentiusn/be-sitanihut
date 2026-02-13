<?php

namespace App\Http\Requests\Api\Kth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateKthRequest extends FormRequest
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
            'nama_kth'    => 'required|string|max:35',
            'desa'        => 'required|string|max:35',
            'kecamatan'   => 'required|string|max:35',
            'kabupaten'   => 'required|string|max:35',
            'koordinator' => 'required|string|max:35',
            'nomor_wa'    => 'required|string|max:15',
            'id_kph'      => [Rule::requiredIf($this->user()->hasRole('sekretariat')), 'string', 'exists:kph,id']
        ];
    }

    public function messages(): array
    {
        return [
            'id_kph.exists'    => 'ID KPH tidak ditemukan'
        ];
    }
}

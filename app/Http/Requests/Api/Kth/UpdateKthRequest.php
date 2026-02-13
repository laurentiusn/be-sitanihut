<?php

namespace App\Http\Requests\Api\Kth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKthRequest extends FormRequest
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
            'nama_kth'    => 'sometimes|required|string|max:35',
            'desa'        => 'sometimes|required|string|max:35',
            'kecamatan'   => 'sometimes|required|string|max:35',
            'kabupaten'   => 'sometimes|required|string|max:35',
            'koordinator' => 'sometimes|required|string|max:35',
            'nomor_wa'    => 'sometimes|required|string|max:15',
            'id_kph'      => [
                Rule::requiredIf(fn() => $this->user()->hasRole('sekretariat')),
                'string',
                'exists:kph,id',
                // Rule::when(!$this->user()->hasRole('sekretariat'), 'prohibited')
            ]
        ];
    }
}

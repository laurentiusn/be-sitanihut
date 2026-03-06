<?php

namespace App\Http\Requests\Api\Laporan;

use Illuminate\Foundation\Http\FormRequest;

class DestroyBulkLaporanRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'laporan_ids'   => ['required', 'array', 'min:1'],
            'laporan_ids.*' => ['required', 'uuid'],
        ];
    }
}
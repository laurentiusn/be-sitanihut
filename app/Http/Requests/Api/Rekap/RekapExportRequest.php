<?php

namespace App\Http\Requests\Api\Rekap;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RekapExportRequest extends FormRequest
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
            'periode' => 'nullable|integer|digits:4',
            'type'    => ['required', 'string', Rule::in(['kph', 'kth', 'petani', 'komoditas'])],
            'export'  => ['nullable', Rule::in(['pdf', 'csv'])]
        ];
    }
}

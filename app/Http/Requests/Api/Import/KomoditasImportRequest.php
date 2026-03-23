<?php

namespace App\Http\Requests\Api\Import;

use Illuminate\Foundation\Http\FormRequest;

class KomoditasImportRequest extends FormRequest
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
            'file' => 'required|file|extensions:xlsx,xls,csv|max:5120'
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'     => 'File Excel wajib diunggah',
            'file.file'         => 'Unggahan harus berupa file',
            'file.extensions'   => 'Format file harus .xlsx, .xls, atau .csv',
            'file.max'          => 'Ukuran file maksimal 5MB',
        ];
    }
}

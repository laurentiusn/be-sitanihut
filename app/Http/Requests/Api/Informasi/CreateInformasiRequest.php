<?php

namespace App\Http\Requests\Api\Informasi;

use Illuminate\Foundation\Http\FormRequest;

class CreateInformasiRequest extends FormRequest
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
            'title'     => 'required|string|max:255',
            'desc'      => 'required|string',
            'date'      => 'required|date',
            'loc'       => 'required|string|max:255',
            'organizer' => 'required|string|max:255',
            'image'     => 'nullable|image|extensions:jpeg,png,jpg,gif,svg'
        ];
    }
}

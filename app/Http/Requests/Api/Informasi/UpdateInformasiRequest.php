<?php

namespace App\Http\Requests\Api\Informasi;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInformasiRequest extends FormRequest
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
            'title'        => 'sometimes|required|string|max:255',
            'desc'         => 'sometimes|required|string',
            'date'         => 'sometimes|required|date',
            'loc'          => 'sometimes|required|string|max:255',
            'organizer'    => 'sometimes|required|string|max:255',
            'image'        => 'sometimes|nullable|image|extensions:jpeg,png,jpg,gif,svg',
            'remove_image' => 'sometimes|boolean'
        ];
    }
}

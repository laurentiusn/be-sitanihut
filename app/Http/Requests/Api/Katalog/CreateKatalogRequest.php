<?php

namespace App\Http\Requests\Api\Katalog;

use Illuminate\Foundation\Http\FormRequest;

class CreateKatalogRequest extends FormRequest
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
            'name'          => 'required|string|max:100',
            'description'   => 'required|string',
            'kth'           => 'required|string|max:50',
            'cp1'           => 'required|string|max:50',
            'cp2'           => 'nullable|string|max:50',
            'price'         => 'required|numeric|min:0',
            'location'      => 'required|string',
            'is_active'     => 'required|boolean',
            'id_user'       => 'nullable|uuid|exists:users,id',
            'images'        => 'required|array|min:1|max:5',
            'images.*'      => 'file|image|extensions:jpeg,png,jpg|max:5120',
        ];
    }
}

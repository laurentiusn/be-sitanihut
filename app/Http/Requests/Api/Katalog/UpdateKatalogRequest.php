<?php

namespace App\Http\Requests\Api\Katalog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKatalogRequest extends FormRequest
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
            'name'          => ['sometimes', 'required', 'string', 'max:100'],
            'description'   => ['sometimes', 'required', 'string', 'max:255'],
            'location'      => ['sometimes', 'required', 'string', 'max:255'],
            'kth'           => ['sometimes', 'required', 'string', 'max:50'],
            'cp1'           => ['sometimes', 'required', 'string', 'max:50'],
            'cp2'           => ['sometimes', 'nullable', 'string', 'max:50'],
            'price'         => ['sometimes', 'required', 'numeric', 'min:0'],
            'is_active'     => ['sometimes', 'required', 'boolean'],
            'id_user'       => ['sometimes', 'nullable', 'uuid', 'exists:users,id'],

            'images_existing'   => ['nullable', 'array'],
            'images_existing.*' => ['uuid', 'exists:catalog_picts,id'],
            'images_new'        => ['nullable', 'array'],
            'images_new.*'      => ['file', 'image', 'extensions:jpeg,png,jpg', 'max:5120'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('images_existing') || $this->hasFile('images_new')) {
                
                // Gunakan ternary operator yang aman
                $existingCount = is_array($this->input('images_existing')) ? count($this->input('images_existing')) : 0;
                $newCount = is_array($this->file('images_new')) ? count($this->file('images_new')) : 0;
                $totalImages = $existingCount + $newCount;

                if ($totalImages < 1) {
                    $validator->errors()->add('images', 'Katalog harus memiliki setidaknya 1 gambar.');
                }

                if ($totalImages > 5) {
                    $validator->errors()->add('images', 'Katalog tidak boleh memiliki lebih dari 5 gambar.');
                }
            }
        });
    }
}

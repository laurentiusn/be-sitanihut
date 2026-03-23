<?php
namespace App\Http\Requests\Api\Komoditas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKomoditasRequest extends FormRequest
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
        $komoditas = $this->route('komoditas') ?? $this->route('komodita');

        // get id whether it's model instance or raw id
        if (!$komoditas) {
            return [];
        }
        return [
            'kode'                => ['sometimes','required','string','max:10', Rule::unique('komoditas', 'kode')->ignore($komoditas->id)],
            'nama_komoditas'      => ['sometimes','required','string','max:50'],
            'jenis_komoditas'     => ['sometimes','required','string','max:30'],
            'komoditas_kehutanan' => ['sometimes','required','boolean'],
        ];
    }
}
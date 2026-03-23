<?php
namespace App\Http\Requests\Api\Komoditas;
use Illuminate\Foundation\Http\FormRequest;

class CreateKomoditasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode'                => 'required|string|max:10|unique:komoditas,kode',
            'nama_komoditas'      => 'required|string|max:50',
            'jenis_komoditas'     => 'required|string|max:30',
            'komoditas_kehutanan' => 'required|boolean',
        ];
    }
}
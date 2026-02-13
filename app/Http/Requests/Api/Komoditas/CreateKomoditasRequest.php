<?php
namespace App\Http\Requests\Api\Komoditas;
use Illuminate\Foundation\Http\FormRequest;

class CreateKomoditasRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'kode'              => 'required|string|max:10|unique:komoditas,kode',
            'nama_komoditas'    => 'required|string|max:50',
            'jenis_komoditas'   => 'required|string|max:30',
        ];
    }
}
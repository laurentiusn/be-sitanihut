<?php
namespace App\Http\Requests\Api\Komoditas;

use App\Models\Komoditas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKomoditasRequest extends FormRequest
{
    public function authorize(): bool 
    { 
        return true; 
    }
    public function rules(): array
    {
        $komoditas = $this->route('komoditas') ?? $this->route('komodita');

        // get id whether it's model instance or raw id
        if (!$komoditas) {
            return [];
        }
        return [
            'kode'              => ['sometimes','required','string','max:10', Rule::unique('komoditas', 'kode')->ignore($komoditas->id)],
            'nama_komoditas'    => ['sometimes','required','string','max:50'],
            'jenis_komoditas'   => ['sometimes','required','string','max:30']
        ];
    }
}
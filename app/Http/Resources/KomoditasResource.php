<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KomoditasResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'kode'                => $this->kode,
            'nama_komoditas'      => $this->nama_komoditas,
            'jenis_komoditas'     => $this->jenis_komoditas,
            'komoditas_kehutanan' => $this->komoditas_kehutanan,
            'created_at'          => $this->created_at,
        ];
    }
}
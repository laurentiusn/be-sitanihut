<?php

namespace App\Http\Resources\Penyuluh;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenyuluhDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'nama_penyuluh' => $this->nama_penyuluh,
            'nip'           => $this->nip,
            'nomor_wa'      => $this->nomor_wa,
            'jabatan'       => $this->jabatan,
            'jenis_kelamin' => $this->jenis_kelamin,
            'id_kph'        => $this->kph->id,
            'asal_kph'      => $this->whenLoaded('kph', function () {
                return $this->kph->nama_kph;
            }),
        ];
    }
}

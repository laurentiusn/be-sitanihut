<?php

namespace App\Http\Resources\Petani;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetaniResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'nama_petani'          => $this->nama_petani,
            'nik'                  => $this->nik,
            'id_kth'               => $this->kth->id,
            'asal_kth'             => $this->kth->nama_kth,
            'id_kph'               => $this->kth->kph->id ?? null,
            'nama_kph'             => $this->kth->kph->nama_kph ?? null,
        ];
    }
}

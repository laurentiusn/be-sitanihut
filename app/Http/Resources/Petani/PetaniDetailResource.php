<?php

namespace App\Http\Resources\Petani;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetaniDetailResource extends JsonResource
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
            'jenis_kelamin'        => $this->jenis_kelamin,
            'alamat'               => $this->alamat,
            'nomor_wa'             => $this->nomor_wa,
            'pendidikan_terakhir'  => $this->pendidikan_terakhir,
            'pekerjaan_sampingan'  => $this->pekerjaan_sampingan,
            'luas_lahan'           => $this->luas_lahan,
            'id_kth'               => $this->kth->id,
            'asal_kth'             => $this->kth->nama_kth,
            'id_kph'               => $this->kth->kph->id,
            'nama_kph'             => $this->kth->kph->nama_kph,
        ];
    }
}

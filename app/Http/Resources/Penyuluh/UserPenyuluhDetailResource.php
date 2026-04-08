<?php

namespace App\Http\Resources\Penyuluh;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPenyuluhDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_user'           => $this->id,
            'email'             => $this->email,
            'id_role'           => $this->role->id,
            'id_kph'            => $this->kph->id ?? null,
            'nama_kph'          => $this->kph->nama_kph ?? null,
            'id_kth'            => $this->kth->id ?? null,
            'nama_kth'          => $this->kth->nama_kth ?? null,
            'id_detail'         => $this->userDetail->id,
            'nama_user'         => $this->userDetail->nama_user,
            'nomor_induk'       => $this->userDetail->nomor_induk,
            'jenis_kelamin'     => $this->userDetail->jenis_kelamin,
            'alamat'            => $this->userDetail->alamat ?? null,
            'nomor_wa'          => $this->userDetail->nomor_wa ?? null,
            'jabatan'           => $this->userDetail->jabatan ?? null,
        ];
    }
}
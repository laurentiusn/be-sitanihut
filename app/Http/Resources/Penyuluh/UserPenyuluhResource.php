<?php

namespace App\Http\Resources\Penyuluh;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPenyuluhResource extends JsonResource
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
            'nama_user'     => $this->userDetail->nama_user,
            'jenis_kelamin' => $this->userDetail->jenis_kelamin,
            'nomor_induk'   => $this->userDetail->nomor_induk,
            'jabatan'       => $this->userDetail->jabatan,
            'slug'          => $this->role->slug,
            'role'          => $this->role->nama_role,
            'id_kph'        => $this->kph->id ?? null,
            'nama_kph'      => $this->kph->nama_kph ?? null
        ];
    }
}
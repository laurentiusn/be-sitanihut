<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'nomor_induk'   => $this->userDetail->nomor_induk ?? null,
            'slug'          => $this->role->slug,
            'role'          => $this->role->nama_role,
            'id_kph'        => $this->kph->id ?? null,
            'nama_kph'      => $this->kph->nama_kph ?? null,
            'id_kth'        => $this->kth->id ?? null,
            'nama_kth'      => $this->kth->nama_kth ?? null,
        ];
    }
}
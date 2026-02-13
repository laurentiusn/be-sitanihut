<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // This combines the User and UserDetail fields into one flat object
        return [
            'id_user' => $this->id,
            'email'   => $this->email,
            'id_role' => $this->role->id,
            'id_kph'  => $this->kph->id ?? null,
            'nama_kph'=> $this->kph->nama_kph ?? null,
            'id_kth'  => $this->kth->id ?? null,
            'nama_kth'=> $this->kth->nama_kth ?? null,

            // Merge all fields from the UserDetail model
            'id_detail'           => $this->userDetail->id,
            'nama_user'           => $this->userDetail->nama_user,
            'nomor_induk'         => $this->userDetail->nomor_induk,
            'jenis_kelamin'       => $this->userDetail->jenis_kelamin,
            // 'profile_picture'     => $this->userDetail->profile_picture ?? null,
            'profile_picture_url' => $this->userDetail->profile_picture_url ?? null,
            'alamat'              => $this->userDetail->alamat ?? null,
            'nomor_wa'            => $this->userDetail->nomor_wa ?? null,
            'pendidikan_terakhir' => $this->userDetail->pendidikan_terakhir ?? null,
            'pekerjaan_sampingan' => $this->userDetail->pekerjaan_sampingan ?? null,
            'luas_lahan'          => $this->userDetail->luas_lahan ?? null,
            'jabatan'             => $this->userDetail->jabatan ?? null,

           
            // 'role' => $this->whenLoaded('role', function () {
            //     return [
            //         'id' => $this->role->id,
            //         'nama_role' => $this->role->nama_role,
            //         'slug' => $this->role->slug,
            //     ];
            // }),

            // 'kph' => $this->whenLoaded('kph', function () {
            
            //     return [
            //         'id' => $this->kph->id,
            //         'nama_kph' => $this->kph->nama_kph,
            //     ];
            // }),

            // 'kth' => $this->whenLoaded('kth', function () {
            //     return $this->kth ? [
            //         'id' => $this->kth->id,
            //         'nama_kth' => $this->kth->nama_kth,
            //     ] : null;
            // }),
        ];
    }
}
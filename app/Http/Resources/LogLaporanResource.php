<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogLaporanResource extends JsonResource
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
            'laporan_id'    => $this->id_laporan,
            'id_pengaju'    => $this->pengaju->id,
            'nama_pengaju'  => $this->pengaju->userDetail->nama_user,
            'id_pemeriksa'  => $this->pemeriksa->id,
            'nama_pemeriksa'=> $this->pemeriksa->userDetail->nama_user,
            'status'        => $this->status,
            'periode'       => $this->periode,
            'bulan'         => $this->bulan,
            'tanggal'       => $this->created_at,
        ];
    }
}

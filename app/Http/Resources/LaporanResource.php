<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $formatNumber = function ($value) {
            $floatVal = (float)$value;
            if ($floatVal === (int)$floatVal) {
                return (int)$floatVal;
            }
            return $floatVal;
        };

        return [
            'id'        => $this->id,
            'tanggal'   => $this->tanggal,
            'periode'   => (int)$this->periode,
            'bulan'     => $this->bulan,
            'nte'       => $formatNumber($this->nte),
            'status'    => $this->status,
            'id_user'   => $this->user->id,
            'nama_user' => $this->user->userDetail?->nama_user,
            'id_kph'    => $this->user->kph->id,
            'asal_kph'  => $this->user->kph->nama_kph ?? null,
            'id_kth'    => $this->user->kth->id,
            'nama_kth'  => $this->user->kth->nama_kth,

        ];
    }
}

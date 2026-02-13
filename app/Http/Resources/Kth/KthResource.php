<?php

namespace App\Http\Resources\Kth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'nama_kth'    => $this->nama_kth,
            'desa'        => $this->desa,
            'kabupaten'   => $this->kabupaten,
            'koordinator' => $this->koordinator,
            'id_kph'      => $this->id_kph,
            'asal_kph'    => $this->whenLoaded('kph', function () {
                return $this->kph->nama_kph;
            }),
        ];
    }
}

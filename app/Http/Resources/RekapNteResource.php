<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RekapNteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalNte = (float) $this->total_nte;

        return [
            'nama_petani' => $this->when(isset($this->nama_petani), $this->nama_petani),
            'nama_kth'    => $this->when(isset($this->nama_kth), $this->nama_kth),
            'nama_kph'    => $this->when(isset($this->nama_kph) && !isset($this->asal_kph), $this->nama_kph),
            'asal_kth'    => $this->when(isset($this->asal_kth), $this->asal_kth),
            'asal_kph'    => $this->when(isset($this->asal_kph), $this->asal_kph),
            'komoditas'   => $this->when(isset($this->nama_komoditas), $this->nama_komoditas),
            'total_nte'   => ($totalNte == (int)$totalNte) ? (int)$totalNte : $totalNte,
            'produksi'    => $this->when(isset($this->produksi), $this->produksi),
        ];
    }
}
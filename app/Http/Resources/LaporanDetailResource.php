<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanDetailResource extends JsonResource
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
            'id' => $this->id,
            'periode'           => (int)$this->periode,
            'bulan'             => $this->bulan,
            'tanggal'           => $this->tanggal,
            'modal'             => $formatNumber($this->modal),
            'nte'               => $formatNumber($this->nte),
            'status'            => $this->status,
            'tanggal_keputusan'  => $this->tanggal_keputusan,
            'tanggal_tindakan'  => $this->tanggal_tindakan,
            'tanggal_pengajuan' => $this->tanggal_pengajuan,
            'lampiran'          => LampiranLaporanResource::collection($this->whenLoaded('lampiran')),
            'catatan_petani'    => $this->catatan_petani,
            'catatan_penyuluh'  => $this->catatan_penyuluh,
            'id_user'           => $this->user->id,
            'nama_user'         => $this->user->userDetail?->nama_user,
            'nomor_induk'       => $this->user->userDetail?->nomor_induk,
            'jenis_kelamin'     => $this->user->userDetail?->jenis_kelamin,
            'alamat'            => $this->user->userDetail?->alamat,
            'luas_lahan'        => $this->user->userDetail?->luas_lahan,
            'id_kth'            => $this->user->kth->id ?? null,
            'nama_kth'          => $this->user->kth->nama_kth ?? null,
            'id_kph'            => $this->user->kth->kph->id,
            'nama_kph'          => $this->user->kth->kph->nama_kph,

            'masa_tanam' => $this->whenLoaded('masaTanam', function () use ($formatNumber) {
                return $this->masaTanam->map(function ($tanam) use ($formatNumber) {
                    return [
                        'id' => $tanam->id,
                        'tanggal'           => $tanam->tanggal,
                        'id_komoditas'      => $tanam->id_komoditas,
                        'nama_komoditas'    => $tanam->komoditas?->nama_komoditas,
                        'kode_komoditas'    => $tanam->komoditas?->kode,
                        'jenis_komoditas'   => $tanam->komoditas?->jenis_komoditas,
                        'jumlah'            => $formatNumber($tanam->jumlah),
                        'usia_tanam'        => $formatNumber($tanam->usia_tanam),
                    ];
                });
            }),

            'masa_panen' => $this->whenLoaded('masaPanen', function () use ($formatNumber) {
                return $this->masaPanen->map(function ($panen) use ($formatNumber) {
                    return [
                        'id'                => $panen->id,
                        'tanggal'           => $panen->tanggal,
                        'id_komoditas'      => $panen->id_komoditas,
                        'nama_komoditas'    => $panen->komoditas?->nama_komoditas,
                        'kode_komoditas'    => $panen->komoditas?->kode,
                        'jenis_komoditas'   => $panen->komoditas?->jenis_komoditas,
                        'jumlah'            => $formatNumber($panen->jumlah),
                        'harga_satuan'      => $formatNumber($panen->harga_satuan),
                        'harga_total'       => $formatNumber($panen->harga_total),
                    ];
                });
            }),
        ];
    }
}

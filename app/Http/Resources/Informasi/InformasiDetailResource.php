<?php

namespace App\Http\Resources\Informasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InformasiDetailResource extends JsonResource
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
            'title'         => $this->title,
            'desc'          => $this->desc,
            'date'          => $this->date,
            'loc'           => $this->loc,
            'organizer'     => $this->organizer,
            'nama_image'    => $this->nama_image ?? null,
            'path_image'    => $this->path_image ? asset('storage/' . $this->path_image) : null,
        ];
    }
}

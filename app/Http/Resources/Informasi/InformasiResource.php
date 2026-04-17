<?php

namespace App\Http\Resources\Informasi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class InformasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cleanDesc = html_entity_decode(strip_tags($this->desc));
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'short_desc'    => Str::words($cleanDesc, 20, '...'),
            'date'          => $this->date,
            'nama_image'    => $this->nama_image ?? null,
            'path_image'    => $this->path_image ? asset('storage/' . $this->path_image) : null,
        ];
    }
}

<?php

namespace App\Http\Resources\Katalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KatalogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'location'          => $this->location,
            'kth'               => $this->kth,
            'price'             => $this->price,
            'is_active'         => $this->is_active,

            'pictures'          => $this->whenLoaded('pictures', function () {
                return $this->pictures->map(function ($picture) {
                    return [
                        'id'        => $picture->id,
                        'nama_file' => $picture->nama_file,
                        'path'      => $picture->path,
                        'url'       => asset('storage/' . $picture->path),
                    ];
                });
            }),
        ];
    }
}
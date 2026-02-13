<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    private $token;

    public function __construct($token, $resource)
    {
        parent::__construct($resource);
        $this->token = $token;
    }


    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'id'    => $this->id,
            'email' => $this->email,
            'role'  => $this->whenLoaded('role', fn() => $this->role->nama_role),
            'name'  => $this->whenLoaded('userDetail', fn() => $this->userDetail->nama_user),
        ];
    }
}

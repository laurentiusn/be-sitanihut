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
    private $refreshToken;

    public function __construct($token, $refreshToken, $resource)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->refreshToken = $refreshToken;
    }


    public function toArray(Request $request): array
    {
        return [
            'token'         => $this->token,
            'refresh_token' => $this->refreshToken,
            'id'            => $this->id,
            'email'         => $this->email,
            'role'          => $this->whenLoaded('role', fn() => $this->role->nama_role),
            'name'          => $this->whenLoaded('userDetail', fn() => $this->userDetail->nama_user),
        ];
    }
}

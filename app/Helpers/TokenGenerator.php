<?php

namespace App\Helpers;

use App\Models\User;

class TokenGenerator{
    public static function generateAccessToken(User $user){
        $payload =[
            'iat' => now()->timestamp,
            'exp' => now()->addSeconds(config('jwt.refresh_expires'))->timestamp,
            'sub' => $user->id
        ];
    }
}
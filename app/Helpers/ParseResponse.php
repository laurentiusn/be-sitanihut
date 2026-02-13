<?php

namespace App\Helpers;

use App\Http\Resources\PostResource;

class ParseResponse{
    public static function parseResponse($data, $message='Data berhasil diambil', $code=200){
        return (new PostResource(
            $code,
            $message,
            $data
        ))->response()->setStatusCode($code);
    }
}
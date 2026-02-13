<?php

namespace App\Exception;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostException extends Exception
{
    // protected $statusCode;
    // protected $message;

    // public function __construct($message, $statusCode)
    // {
    //     if ($statusCode === 0) $statusCode = 500;
    //     parent::__construct($message, $statusCode);
    // }

    public function render(Request $request): JsonResponse
    {
        $statusCode = $this->getCode() ?: 500;
        return response()->json([
            'message' => $this->getMessage(),
            'statusCode' => $statusCode,
            'data' => []
        ], $statusCode);
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public $code;
    public $message;
    public $resource;

    public function __construct($message, $code, $resource)
    {
        parent::__construct($resource);
        $this->code = $code;
        $this->message = $message;
    }

    public function toArray(Request $request): array
    {
        return [
            'message' => $this->message,
            'statusCode' => $this->code,
            'data' => $this->resource
        ];
    }
}

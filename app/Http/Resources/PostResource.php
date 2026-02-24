<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Class PostResource JSON Wrapper
 * @package App\Http\Resources
 */
class PostResource extends JsonResource
{
    public $code;
    public $message;
    public $resource;

    /** Constructor JSON API
     * @param string $message
     * @param int $code
     * @param mixed $resource
     */
    public function __construct($message, $code, $resource)
    {
        parent::__construct($resource);
        $this->code = $code;
        $this->message = $message;
    }

    /** Transform resource to array
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'statusCode' => $this->code,
            'message' => $this->message,
            'data' => $this->resource
        ];
    }
}
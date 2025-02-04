<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{
    public int $statusCode;
    public static $wrap = null;

    public function __construct($resource, $statusCode = 400)
    {
        parent::__construct($resource);
        $this->statusCode = $statusCode;
    }

    public function toArray($request)
    {
        return is_array($this->resource) ? $this->resource : ['message' => $this->resource];
    }

    public function toResponse($request)
    {
        $data = is_array($this->resource) ? $this->resource : ['message' => $this->resource];
        return parent::toResponse($request->merge($data))->setStatusCode($this->statusCode);
    }

    public function withWrappData()
    {
        self::$wrap = 'data';
        return $this;
    }
}

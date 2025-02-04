<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class SuccessResource extends JsonResource
{
    public int $statusCode;
    public static $wrap = null;

    public function __construct($resource, $statusCode = 200)
    {
        parent::__construct($resource);
        $this->statusCode = $statusCode;
    }

    public function toArray($request)
    {
        return is_array($this->resource) ? $this->resource : ['message' => $this->resource];
    }

    public function toResponse($request): \Illuminate\Http\JsonResponse
    {
        $data = is_array($this->resource) ? $this->resource : ['message' => $this->resource];
        return response()->json(self::$wrap ? [self::$wrap => $data] : $data, $this->statusCode);
//        return parent::toResponse($request->merge($data))->setStatusCode($this->statusCode);
//        return parent::toResponse($data)->setStatusCode($this->statusCode);
    }

    public function withWrappData(): static
    {
        self::$wrap = 'data';
        return $this;
    }
}

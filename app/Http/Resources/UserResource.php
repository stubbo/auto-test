<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->resource->id,
            'name'              => $this->resource->name,
            'email'             => $this->resource->email,
            'email_verified_at' => $this->resource->email_verified_at,
            'updated_at'        => $this->resource->updated_at,
            'created_at'        => $this->resource->created_at,
        ];
    }
}

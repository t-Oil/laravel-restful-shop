<?php

namespace App\Http\Resources\Backoffice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = $this->whenLoaded('products');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => $this->is_active,
            'products' => ProductResource::collection($products)
        ];
    }
}

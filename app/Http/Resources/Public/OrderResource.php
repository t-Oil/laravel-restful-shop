<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $products = $this->whenLoaded('products');
        $addresses = $this->whenLoaded('addresses');

        return [
            'order_number' => $this->order_number,
            'customer_name' => $this->getFullname(),
            'email' => $this->email,
            'phone' => $this->phone,
            'summary_price' => $this->summary_price,
            'products' => ProductResource::collection($products),
            'addresses' => AddressResource::collection($addresses),
        ];
    }
}

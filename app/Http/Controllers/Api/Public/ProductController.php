<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Cache::remember('products', 60, function () {
                return Product::with('category')->where('is_active', 1)->get();
            });

            return $this->successResponse(ProductResource::collection($products));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    public function show($id)
    {
        try {

            $product = Product::with(['category'])->where('is_active', 1)->find($id);

            if (!$product) {
                return $this->errorResponse(404);
            }

            return $this->successResponse(new ProductResource($product));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Backoffice\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $parameters = $request->all();

            $perPage = Arr::get($parameters, 'per_page', 2);

            $products = Product::with(['category'])->paginate($perPage);

            return $this->successResponse(ProductResource::collection($products));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {

            $product = Product::with(['category'])->find($id);

            return $this->successResponse(new ProductResource($product));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $parameter = $request->all();
            $validator = $this->updateValidator($parameter);

            if ($validator->fails()) {
                $errors = ['errors' => $validator->errors()->toArray()];
                $errorMessage = 'Validation Error';

                return $this->errorResponse(400, $errors, $errorMessage);
            }

            $product = Product::find($id);

            if (empty($product)) {
                return $this->errorResponse(404);
            }


            $product->update([
                'name' => Arr::get($parameter, 'name'),
                'description' => Arr::get($parameter, 'description'),
                'price' => Arr::get($parameter, 'price'),
                'category_id' => Arr::get($parameter, 'category_id')
            ]);

            return $this->successResponse(new ProductResource($product));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    protected function updateValidator(array $parameter)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
        ];

        $customMessages = [
            'name.required' => 'กรุณากรอกชื่อ',
            'description.required' => 'กรุณากรอกรายละเอียด',
            'category_id.required' => 'กรุณากรอกหมวดหมู่สินค้า',
            'category_id.integer' => 'หมวดหมู่สินค้าต้องเป็นตัวเลขเท่านั้น',
            'price.required' => 'กรุณากรอกราคา',
            'price.numeric' => 'ราคาต้องเป็นตัวเลขเท่านั้น',
        ];

        return Validator::make($parameter, $rules, $customMessages);
    }

}

<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\Backoffice\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function index(Request $request)
    {
        try {
            $parameters = $request->all();

            $perPage = Arr::get($parameters, 'per_page', 2);
            $categories = Category::with(['products'])->paginate($perPage);

            return $this->successResponse(CategoryResource::collection($categories));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {
            $category = Category::with(['products'])->find($id);

            if (!$category) {
                return $this->errorResponse(404);
            }

            return $this->successResponse(new CategoryResource($category));
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

            $category = Category::find($id);

            if (empty($category)) {
                return $this->errorResponse(404);
            }


            $category->update([
                'name' => Arr::get($parameter, 'name')
            ]);

            return $this->successResponse(new CategoryResource($category));
        } catch (\Throwable $e) {
            return $this->errorResponse(422, $e->getMessage());
        }
    }

    protected function updateValidator(array $parameter)
    {

        $rules = [
            'name' => 'required',
        ];

        $customMessages = [
            'name.required' => 'กรุณากรอกชื่อ',
        ];

        return Validator::make($parameter, $rules, $customMessages);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data)
    {
        $response = [
            'status' => 200,
            'message' => 'OK',
        ];

        if (!Arr::has($data->resource->toArray(), 'data')) {
            $response['data'] = $data;
        } else {
            $response['data'] = Arr::get($data->resource->toArray(), 'data') ?? $data->resource;


            if (Arr::has($data->resource->toArray(), 'data')) {
                $response['meta'] = Arr::except($data->resource->toArray(), 'data');
            }
        }

        return response()->json($response, 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($status, $errors = null, $message = null)
    {
        if ($status === 404) {
            return response()->json([
                'status' => 404,
                'message' => 'Not Found',
                'data' => [],
                'error' => $errors ?? 'Not Found',
            ], 404);
        }

        if ($status === 422) {
            return response()->json([
                'status' => 422,
                'message' => 'Unprocessable Entity',
                'error' => $errors,
            ], 422);
        }

        return response()->json([
            'status' => 400,
            'message' => $message,
            'error' => $errors,
        ], 400);
    }
}

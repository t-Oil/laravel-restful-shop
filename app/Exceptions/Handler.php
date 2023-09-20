<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * return when user does not have permission to route
     */
    public function render($request, Throwable $e): JsonResponse
    {
        $statusCode = $e->getCode();
        $errorErrors = [$e->getMessage()];
        $errorMessage = 'SOMETHING_WENT_WRONG';

        if ($e instanceof UnauthorizedException) {
            $statusCode = 403;
            $errorMessage = 'INVALID_TOKEN_SCOPE';
            $errorErrors = [];
        }

        if ($e instanceof HttpResponseException) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = json_decode($e->getResponse()->getContent())->message;
            $errorErrors = [];
        }

        if ($e instanceof ValidationException) {
            $statusCode = 400;
            $statusMessage = Arr::get(Response::$statusTexts, $statusCode);
            $errorMessage = 'BAD_REQUEST';
            $errorErrors = [$e->validator->errors()->toArray()];
        }

        $statusMessage = Arr::get(Response::$statusTexts, $statusCode);

        if (empty($statusMessage)) {
            $statusCode = 422;
            $statusMessage = Arr::get(Response::$statusTexts, $statusCode);
        }

        return response()->json(
            [
                'status' => $statusCode,
                'message' => $statusMessage,
                'error' => [
                    'message' => $errorMessage,
                    'errors' => $errorErrors,
                ],
            ]
        );
    }
}

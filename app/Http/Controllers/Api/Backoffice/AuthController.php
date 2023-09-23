<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Enums\ProviderType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Backoffice\UserResource;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'provider' => ProviderType::LOCAL])) {
            $user = Auth::user();

            return $this->successResponse([
                'token' => $user->createToken('ApiToken')->plainTextToken,
            ]);
        }

        return $this->errorResponse(401, null, 'Invalid credentials');
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return $this->successResponse([
            'message' => 'Successfully logged out',
        ]);
    }

    public function me()
    {
        return $this->successResponse(new UserResource(Auth::user()));
    }
}

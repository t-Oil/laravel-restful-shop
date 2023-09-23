<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\ProviderType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Socialite;

class AuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);

        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $user = Socialite::driver($provider)->stateless()->user();

            $userCreated = User::firstOrCreate(
                [
                    'email' => $user->getEmail(),
                    'provider' => $provider,
                    'provider_id' => $user->getId(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $user->getName(),
                    'avatar' => $user->getAvatar()
                ]
            );

            return $this->successResponse([
                'user' => $userCreated->toArray(),
                'token' => $userCreated->createToken('povider-token')->plainTextToken,
            ]);
        } catch (\Throwable $e) {
            Log::error(get_class($this), [
                'Line' => $e->getLine(),
                'Message' => $e->getMessage(),
            ]);

            return $this->errorResponse(401, null, 'Invalid credentials');
        }
    }

    protected function validateProvider($provider)
    {
        if (!in_array($provider, [ProviderType::FACEBOOK, ProviderType::GOOGLE])) {
            return $this->errorResponse(422, ['error' => 'Please login using facebook, github or google']);
        }
    }
}

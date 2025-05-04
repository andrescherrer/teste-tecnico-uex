<?php

namespace App\Http\Services\Auth;

use App\Models\User;

class AuthService
{
    protected function createToken(User $user): array
    {
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }
}
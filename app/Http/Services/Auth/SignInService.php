<?php

namespace App\Http\Services\Auth;

use App\Http\Requests\Auth\SignInRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SignInService extends AuthService
{
    public function execute(SignInRequest $request): array
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        return $this->createToken($user);
    }
}
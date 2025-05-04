<?php

namespace App\Http\Services\Auth;

use App\Http\Requests\Auth\SignUpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SignUpService extends AuthService
{
    public function execute(SignUpRequest $request): array
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $this->createToken($user);
    }
}
<?php

namespace App\Http\Services\Auth;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Str;

class ForgotPasswordService
{
    public function execute(ForgotPasswordRequest $request): void
    {
        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);
        
        PasswordResetToken::updateOrCreate(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        $user->notify(new ResetPasswordNotification($token));
    }
}
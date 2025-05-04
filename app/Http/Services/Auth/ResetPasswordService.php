<?php

namespace App\Http\Services\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    public function execute(ResetPasswordRequest $request): void
    {
        $expirationMinutes = env('PASSWORD_RESET_TIMEOUT', 60);
        $passwordReset = PasswordResetToken::where([
            'token' => $request->token,
            'email' => $request->email,
        ])->first();

        if (!$passwordReset) {
            throw new \Exception('Token inválido ou expirado');
        }

        $createdAt = $this->safeDateConversion($passwordReset->created_at);

        if ($this->isTokenExpired($createdAt)) {
            throw new \Exception('Token expirado. Por favor, solicite um novo link.');
        }        

        $user = User::where('email', $request->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();
    }
    
    protected function safeDateConversion($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_string($date)) {
            $cleanDate = preg_replace('/[^\d\-: ]/', '', $date);
            return Carbon::createFromFormat('Y-m-d H:i:s', $cleanDate);
        }

        throw new \InvalidArgumentException('Formato de data inválido');
    }

    protected function isTokenExpired(Carbon $createdAt): bool
    {
        $now = Carbon::now();
        $expirationTime = $createdAt->copy()->addHour();
        
        return $now->gt($expirationTime);
    }
}
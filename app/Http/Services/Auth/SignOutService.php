<?php

namespace App\Http\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class SignOutService
{
    public function execute($request): void
    {
        try {
            if (!Auth::check()) {
                throw new \RuntimeException('Nenhum usuário autenticado');
            }

            $request->user()->currentAccessToken()->delete();

            // Alternativa: Revoga todos os tokens
            // $request->user()->tokens()->delete();

        } catch (\Exception $e) {
            Log::error('Erro durante logout: ' . $e->getMessage());
            throw $e;
        }
    }
}
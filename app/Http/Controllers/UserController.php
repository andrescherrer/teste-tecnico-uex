<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['A senha fornecida está incorreta.'],
            ]);
        }

        $request->user()->contatos()->delete();
        $request->user()->delete();
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Conta e todos os contatos associados foram removidos com sucesso.'
        ], JsonResponse::HTTP_OK);
    }
}
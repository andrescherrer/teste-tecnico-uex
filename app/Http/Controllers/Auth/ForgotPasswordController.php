<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Services\Auth\ForgotPasswordService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private ForgotPasswordService $forgotPasswordService
    ) {}

    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->forgotPasswordService->execute($request);

        return response()->json([
            'message' => 'Link de redefinição de senha enviado para seu email',
        ]);
    }
}

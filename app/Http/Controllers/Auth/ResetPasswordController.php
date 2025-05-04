<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Services\Auth\ResetPasswordService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function __construct(
        private ResetPasswordService $resetPasswordService
    ) {}

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->resetPasswordService->execute($request);

            return response()->json([
                'message' => 'Senha redefinida com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

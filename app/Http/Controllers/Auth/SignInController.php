<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignInRequest;
use App\Http\Services\Auth\SignInService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SignInController extends Controller
{
    public function __construct(
        private SignInService $signInService
    ) {}

    public function __invoke(SignInRequest $request): JsonResponse
    {
        try {
            $response = $this->signInService->execute($request);

            return response()->json([
                'message' => 'Login realizado com sucesso',
                'data' => $response,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}

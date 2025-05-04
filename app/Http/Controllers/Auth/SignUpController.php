<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Services\Auth\SignUpService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SignUpController extends Controller
{
    public function __construct(
        private SignUpService $signUpService
    ){}

    public function __invoke(SignUpRequest $request): JsonResponse
    {
        $response = $this->signUpService->execute($request);

        return response()->json([
            'message' => 'Usuário criado com sucesso',
            'data' => $response,
        ], Response::HTTP_CREATED);
    }
}

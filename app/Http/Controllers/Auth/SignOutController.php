<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignOutRequest;
use App\Http\Services\Auth\SignOutService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class SignOutController extends Controller
{
    private SignOutService $signOutService;
    
    public function __construct(
        SignOutService $signOutService
    ) {
        $this->middleware('auth:sanctum');
        $this->signOutService = $signOutService;
    }

    public function __invoke(SignOutRequest $request): JsonResponse
    {
        try {
            $this->signOutService->execute($request);

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);

        } catch (\RuntimeException $e) {
            Log::error($e->getMessage() . ' ' . Response::HTTP_UNAUTHORIZED);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
            
        } catch (\Exception $e) {
            Log::error('Erro durante o logout '  . ' ' .  $e->getMessage()  . ' ' .  Response::HTTP_INTERNAL_SERVER_ERROR);
            return response()->json([
                'success' => false,
                'message' => 'Erro durante o logout',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }    
}

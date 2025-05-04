<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignOutRequest;
use App\Http\Services\Auth\SignOutService;
use Illuminate\Http\JsonResponse;

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
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro durante o logout',
            ], 500);
        }
    }    
}

<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\{JsonResponse};
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Recurso não encontrado'
            ], JsonResponse::HTTP_NOT_FOUND);
        });
    }
}
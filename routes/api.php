<?php

use App\Http\Controllers\Auth\{ForgotPasswordController, ResetPasswordController, SignInController, SignOutController, SignUpController};
use App\Http\Controllers\{ContatoController, UserController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    
    Route::post('/signup', SignUpController::class);
    Route::post('/signin', SignInController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);

    Route::delete('/user', [UserController::class, 'destroy'])->middleware('auth:sanctum');
    Route::post('/signout', SignOutController::class)->middleware('auth:sanctum')->name('auth.signout');    
    Route::apiResource('contatos', ContatoController::class)->parameters(['contatos' => 'contato'])->middleware('auth:sanctum');
});
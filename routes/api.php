<?php

use App\Http\Controllers\Auth\{ForgotPasswordController, ResetPasswordController, SignInController, SignOutController, SignUpController};
use App\Http\Controllers\ContatoController;
use App\Http\Controllers\Location\{LocationController, ViaCepController};
use App\Http\Controllers\Document\{CpfController};
use Illuminate\Support\Facades\Route;

Route::get('/address-google', LocationController::class);
Route::get('/cep-viacep', ViaCepController::class);
Route::get('/cpf-validator', CpfController::class);

Route::prefix('v1')->group(function() {
    
    Route::post('/signup', SignUpController::class);
    Route::post('/signin', SignInController::class);
    Route::post('/forgot-password', ForgotPasswordController::class);
    Route::post('/reset-password', ResetPasswordController::class);
    
    Route::post('/signout', SignOutController::class)->middleware('auth:sanctum')->name('auth.signout');    
    Route::apiResource('contatos', ContatoController::class)->parameters(['contatos' => 'contato'])->middleware('auth:sanctum');
});
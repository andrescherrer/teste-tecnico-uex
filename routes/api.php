<?php

use App\Http\Controllers\Auth\{ForgotPasswordController, ResetPasswordController, SignInController, SignOutController, SignUpController};
use Illuminate\Support\Facades\Route;

Route::post('/signup', SignUpController::class);
Route::post('/signin', SignInController::class);
Route::post('/forgot-password', ForgotPasswordController::class);
Route::post('/reset-password', ResetPasswordController::class);
Route::post('/signout', SignOutController::class)->middleware('auth:sanctum')->name('auth.signout');
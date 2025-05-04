<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password', function (Request $request) {
    if (!$request->has('token') || !$request->has('email')) {
        return redirect('/login')->with('error', 'Token ou email inválidos');
    }
    return view('auth.reset-password');
})->name('password.reset');


<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Login page (web only - untuk menampilkan form login)
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

// Catch-all route untuk Vue Router - semua route selain login di-handle oleh Vue
Route::get('/{any}', function () {
    return view('dashboard');
})->where('any', '^(?!login|api).*');

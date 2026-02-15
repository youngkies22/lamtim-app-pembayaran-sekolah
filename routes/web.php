<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Root route - Load the Vue app (dashboard view contains the mount point)
Route::get('/', function () {
    return view('dashboard');
});

// Login page
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

// Catch-all route untuk Vue Router
Route::get('/{any}', function () {
    return view('dashboard');
})->where('any', '^(?!login|api).*');

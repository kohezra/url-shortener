<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

// Homepage routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/shorten', [HomeController::class, 'shorten'])->name('url.shorten');
Route::post('/bulk-shorten', [HomeController::class, 'bulkShorten'])->name('url.bulk-shorten');

// URL info and preview routes
Route::get('/info/{shortCode}', [HomeController::class, 'show'])->name('url.show');
Route::get('/preview/{shortCode}', [RedirectController::class, 'preview'])->name('url.preview');

// Main redirect route (must be last to avoid conflicts)
// Support both GET and POST methods for password-protected URLs
Route::match(['get', 'post'], '/{shortCode}', [RedirectController::class, 'redirect'])
    ->where('shortCode', '[a-zA-Z0-9]{3,10}')
    ->name('url.redirect');

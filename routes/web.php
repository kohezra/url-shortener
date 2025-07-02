<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

// Homepage route - update to use our HomeController
Route::get('/', [HomeController::class, 'index'])->name('home');

// Dashboard route (from Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile routes (from Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// URL Shortener routes
Route::post('/shorten', [HomeController::class, 'shorten'])->name('url.shorten');
Route::get('/info/{shortCode}', [HomeController::class, 'info'])->name('url.info');
Route::get('/preview/{shortCode}', [RedirectController::class, 'preview'])->name('url.preview');

// Bulk URL shortening (requires authentication)
Route::middleware('auth')->group(function () {
    Route::post('/bulk-shorten', [HomeController::class, 'bulkShorten'])->name('url.bulk-shorten');
});

// Password-protected URL routes
Route::get('/password/{shortCode}', [RedirectController::class, 'showPasswordForm'])->name('url.password');
Route::post('/password/{shortCode}', [RedirectController::class, 'handlePassword'])->name('url.password.submit');

// Main redirect route (must be last to avoid conflicts)
Route::get('/{shortCode}', [RedirectController::class, 'redirect'])
    ->where('shortCode', '[a-zA-Z0-9]+')
    ->name('url.redirect');

// Authentication routes
require __DIR__ . '/auth.php';

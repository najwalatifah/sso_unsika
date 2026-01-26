<?php

use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/* =========================
   OIDC ROUTES (SUDAH DIBENERIN)
   ========================= */

Route::middleware('guest')->group(function () {
    Route::get('/login/oidc', [OidcController::class, 'redirect'])
        ->withoutMiddleware([RedirectIfAuthenticated::class])
        ->name('oidc.redirect');

    Route::get('/login/oidc/callback', [OidcController::class, 'callback'])
        ->withoutMiddleware([RedirectIfAuthenticated::class])
        ->name('oidc.callback');
});

Route::post('/logout', [OidcController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/mahasiswa/dashboard', fn () => 'Dashboard Mahasiswa');
    });

    Route::middleware('role:dosen')->group(function () {
        Route::get('/dosen/dashboard', fn () => 'Dashboard Dosen');
    });

    Route::middleware('role:admin,dosen')->group(function () {
        Route::get('/admin', fn () => 'Admin Area');
    });

});

require __DIR__.'/auth.php';

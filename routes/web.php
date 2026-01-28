<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\OidcController;
use App\Http\Controllers\DosenDashboardController;
use App\Http\Controllers\MahasiswaDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    return match (auth()->user()->role) {
        'admin' => redirect('/admin/dashboard'),
        'dosen' => redirect('/dosen/dashboard'),
        'mahasiswa' => redirect('/mahasiswa/dashboard'),
        default => abort(403),
    };
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('/login/oidc', [OidcController::class, 'redirect'])
        ->name('oidc.redirect');

    Route::get('/login/oidc/callback', [OidcController::class, 'callback'])
        ->name('oidc.callback');
});

Route::get('/login', [OidcController::class, 'redirect'])
    ->middleware('guest')
    ->name('login');

Route::post('/logout', [OidcController::class, 'logout'])
    ->name('logout');


Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin');

    Route::get('/dosen/dashboard', [DosenDashboardController::class, 'index'])
        ->middleware('role:dosen');

    Route::get('/mahasiswa/dashboard', [MahasiswaDashboardController::class, 'index'])
        ->middleware('role:mahasiswa');
});

require __DIR__.'/auth.php';

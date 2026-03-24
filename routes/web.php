<?php

use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ProfilePhotoController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => 'Home page')->name('home');

// ---------------------------------------------------------------------------
// Authenticated routes
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', 'workspace'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// ---------------------------------------------------------------------------
// Profile routes
// ---------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('settings/profile-photo', [ProfilePhotoController::class, 'destroy'])->name('profile.photo.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});

// ---------------------------------------------------------------------------
// Onboarding routes
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified'])
    ->prefix('onboarding')
    ->name('onboarding.')
    ->group(function () {
        Route::get('workspace', [OnboardingController::class, 'createWorkspace'])->name('workspace.create');
        Route::post('workspace', [OnboardingController::class, 'storeWorkspace'])->name('workspace.store');

        Route::middleware(['workspace'])->group(function () {
            Route::get('invite', [OnboardingController::class, 'invite'])->name('invite');
            Route::post('invite', [OnboardingController::class, 'storeInvite'])->name('invite.store');
            Route::get('currency', [OnboardingController::class, 'currency'])->name('currency');
            Route::post('currency', [OnboardingController::class, 'storeCurrency'])->name('currency.store');
        });
    });

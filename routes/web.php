<?php

use App\Livewire\Auth\ConfirmPassword;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Guest routes
// ---------------------------------------------------------------------------
// Route::middleware('guest')->group(function () {
//    Route::get('/login', Login::class)->name('login');
//    Route::get('/register', Register::class)->name('register');
//    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
//    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
// });

// ---------------------------------------------------------------------------
// Authenticated routes
// ---------------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    //    // Email verification
    //    Route::get('/email/verify', VerifyEmail::class)->name('verification.notice');
    //
    //    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    //        $request->fulfill();
    //
    //        return redirect()->route('onboarding.workspace.create');
    //    })->middleware('signed')->name('verification.verify');
    //
    //    // Password confirmation
    //    Route::get('/user/confirm-password', ConfirmPassword::class)->name('password.confirm');
    //
    //    // Logout
    //    Route::post('/logout', function (Request $request) {
    //        Auth::logout();
    //        $request->session()->invalidate();
    //        $request->session()->regenerateToken();
    //
    //        return redirect('/');
    //    })->name('logout');

    // Onboarding stub
    Route::get('/onboarding/workspace', fn () => 'Onboarding coming soon')
        ->name('onboarding.workspace.create');
});

// ---------------------------------------------------------------------------
// Root redirect
// ---------------------------------------------------------------------------
Route::redirect('/', '/dashboard');

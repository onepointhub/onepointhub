<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Settings\NotificationPreferenceController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ProfilePhotoController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\WorkspaceSettings\ActivityLogController;
use App\Http\Controllers\WorkspaceSettings\InvitationController;
use App\Http\Controllers\WorkspaceSettings\MemberController;
use App\Http\Controllers\WorkspaceSwitchController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => 'Home page')->name('home');

// ---------------------------------------------------------------------------
// Authenticated routes
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', 'workspace', 'internal'])->group(function () {
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

    Route::get('settings/notifications', [NotificationPreferenceController::class, 'edit'])->name('notifications.preferences.edit');
    Route::patch('settings/notifications/{type}', [NotificationPreferenceController::class, 'update'])->name('notifications.preferences.update');

    Route::get('activity', [ActivityLogController::class, 'index'])->name('workspace.activity-log.index');

    Route::post('workspace/switch', WorkspaceSwitchController::class)->name('workspace.switch');
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

// ---------------------------------------------------------------------------
// Workspace Settings routes
// ---------------------------------------------------------------------------
Route::middleware(['auth', 'verified', 'workspace', 'internal'])->group(function () {
    Route::prefix('workspace/settings')
        ->name('workspace.')
        ->group(function () {
            Route::get('members', [MemberController::class, 'index'])->name('members.index');
            Route::patch('members/{user}', [MemberController::class, 'update'])->name('members.update');
            Route::delete('members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
        });

    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware('auth')->get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');

<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Http\Requests\Settings\ProfileDeleteRequest;
use App\Modules\Core\Http\Requests\Settings\ProfileUpdateRequest;
use App\Modules\Core\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $tz = timezone_identifiers_list();

        return Inertia::render('Core::settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'timezones' => $tz,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($request->hasFile('photo')) {
            /** @var UploadedFile $photo */
            $photo = $request->validated('photo');

            $user->updateProfilePhoto($photo);
        }

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        Auth::logout();

        $user->deleteProfilePhoto();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

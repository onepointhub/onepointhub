<?php

namespace App\Modules\Core\Http\Controllers\Settings;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilePhotoController extends Controller
{
    /**
     * Delete the current user's profile photo.
     */
    public function destroy(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->deleteProfilePhoto();

        return to_route('profile.edit');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the User's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the User's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $request->user()->password = Hash::make($validated['password']);
        }

        $request->user()->save();

        return Redirect::back()->with('status', 'profile-updated');
    }

    /**
     * Delete the User's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('UserDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $User = $request->user();

        Auth::logout();

        $User->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}








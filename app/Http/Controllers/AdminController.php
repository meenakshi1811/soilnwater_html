<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('backend.dashboard');
    }

    public function editProfile(Request $request): View
    {
        return view('backend.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $user->fill($validated);
        $user->save();

        return redirect()
            ->route('admin.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $id = max(1, (int) $user->id);

        // Placeholder metrics until dedicated ad / offer / product tables exist (per-account seed).
        $totalAds = (int) (($id * 3) % 24);
        $totalOffers = (int) (($id * 2) % 18);
        $totalProducts = (int) (($id * 5) % 32);

        return view('backend.user-dashboard', compact(
            'totalAds',
            'totalOffers',
            'totalProducts'
        ));
    }

    public function editProfile(Request $request): View
    {
        return view('backend.user-profile', [
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
            ->route('user.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\VendorRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    public function convertToVendor(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        if (! $user->isGeneralUser()) {
            $message = 'Only user accounts can be converted to vendor accounts.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('home')->with('status', $message);
        }

        DB::transaction(function () use ($user): void {
            $user->forceFill(['role' => 'vendor'])->save();

            $vendor = VendorRegistrationService::createProfileForUser($user->fresh());
            $vendor->forceFill([
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ])->save();
        });

        $message = 'Your profile has been converted to a vendor account and sent to admin for approval.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('vendor.pending'),
            ]);
        }

        return redirect()->route('vendor.pending')->with('status', $message);
    }

    public function updateProfile(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
            ]);
        }

        return redirect()
            ->route('user.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\VendorRegistrationService;
use App\Support\UserFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            $vendor = VendorRegistrationService::createProfileForUser($user->fresh(), [
                'profile_image_path' => $user->profile_image,
            ]);
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
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
            'whatsapp_number.regex' => 'WhatsApp number must contain only digits and be between 10 and 15 characters.',
            'pincode.regex' => 'Pincode must contain only digits and be between 4 and 10 characters.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
        ]);

        $phoneChanged = $user->phone_number !== $validated['phone_number'];

        $user->name = $validated['name'];
        $user->full_name = $validated['name'];
        $user->phone_number = $validated['phone_number'];
        $user->whatsapp_number = $validated['whatsapp_number'];
        $user->address = $validated['address'];
        $user->city = $validated['city'];
        $user->pincode = $validated['pincode'];
        $user->date_of_birth = $validated['date_of_birth'];

        if ($phoneChanged) {
            $user->phone_verified_at = null;
        }

        if ($request->hasFile('profile_image')) {
            UserFileUploader::deleteIfExists($user->profile_image);
            $user->profile_image = UserFileUploader::storeImage($request->file('profile_image'), 'profiles');
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($phoneChanged) {
            $message = 'You need to verify the number before continuing. Please login again to verify your phone number.';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => route('login'),
                ]);
            }

            return redirect()->route('login')->with('status', $message);
        }

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

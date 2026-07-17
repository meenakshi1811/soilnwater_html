<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\Consultant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConsultantProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('consultant');

        return view('backend.consultant.profile', [
            'user' => $user,
            'consultant' => $user->consultant,
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user()->load('consultant');
        $consultant = $user->consultant;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'pan_number' => ['required', 'string', 'max:20'],
            'has_gst' => ['required', 'in:0,1'],
            'gst_number' => ['nullable', 'required_if:has_gst,1', 'string', 'max:20'],
            'government_certificate_number' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
            'whatsapp_number.regex' => 'WhatsApp number must contain only digits and be between 10 and 15 characters.',
            'pincode.regex' => 'Pincode must contain only digits and be between 4 and 10 characters.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old.',
            'gst_number.required_if' => 'GST number is required when you select yes for GST.',
        ]);

        $phoneChanged = $user->phone_number !== $validated['phone_number'];
        $gstNumber = $validated['has_gst'] === '1' ? ($validated['gst_number'] ?? null) : null;

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

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($consultant) {
            $consultant->forceFill([
                'company_name' => $validated['name'],
                'contact_person' => $validated['name'],
                'display_name' => $validated['name'],
                'phone' => $validated['phone_number'],
                'whatsapp' => $validated['whatsapp_number'],
                'email' => $user->email,
                'address' => $validated['address'],
                'city' => $validated['city'],
                'pincode' => $validated['pincode'],
                'pan_number' => $validated['pan_number'],
                'gst_number' => $gstNumber,
                'government_certificate_number' => $validated['government_certificate_number'] ?? null,
            ])->save();

            $this->syncDisplayNameInPageSnapshots($consultant, $validated['name']);
        }

        if ($phoneChanged) {
            return $this->logoutForPhoneVerification($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
            ]);
        }

        return redirect()
            ->route('consultant.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    private function syncDisplayNameInPageSnapshots(Consultant $consultant, string $displayName): void
    {
        $updates = [];

        foreach (['published_page_data', 'pending_page_data'] as $snapshotKey) {
            $snapshot = $consultant->{$snapshotKey};

            if (! is_array($snapshot) || ! isset($snapshot['profile']) || ! is_array($snapshot['profile'])) {
                continue;
            }

            $snapshot['profile']['display_name'] = $displayName;
            $updates[$snapshotKey] = $snapshot;
        }

        if ($updates !== []) {
            $consultant->forceFill($updates)->save();
        }
    }

    private function logoutForPhoneVerification(Request $request): RedirectResponse|JsonResponse
    {
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
}

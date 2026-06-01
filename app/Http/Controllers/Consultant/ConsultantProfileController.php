<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultantProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('backend.consultant.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'];

        if ($user->email !== $validated['email']) {
            $user->email = $validated['email'];
            $user->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Profile updated successfully.',
            ]);
        }

        return redirect()
            ->route('consultant.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

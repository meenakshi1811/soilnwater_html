<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeRegisterController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.employee-register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('employees', 'email')],
            'phone_number' => ['required', 'digits_between:10,15', Rule::unique('employees', 'phone_number')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'An employee account with this email already exists.',
            'phone_number.unique' => 'An employee account with this phone number already exists.',
        ]);

        Employee::query()->create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'is_active' => false,
            'email_verified_at' => now(),
        ]);

        Auth::guard('employee')->logout();

        return redirect()
            ->route('employee.login')
            ->with('status', 'Your employee account was created. An admin must assign a role and activate it before you can sign in. You can keep using the same email on a regular user account.');
    }
}

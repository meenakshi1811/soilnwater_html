<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeLoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.employee-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = strtolower(trim($credentials['email']));
        $employee = Employee::query()->where('email', $email)->first();

        if (! $employee || ! Auth::guard('employee')->validate([
            'email' => $email,
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an employee account.',
            ]);
        }

        if (! $employee->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your employee account is waiting for admin activation and a role assignment.',
            ]);
        }

        Auth::guard('web')->logout();
        Auth::guard('employee')->login($employee, $request->boolean('remember'));
        $request->session()->regenerate();
        Auth::shouldUse('employee');

        return redirect()->intended($this->redirectPath($employee));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login');
    }

    private function redirectPath(Employee $employee): string
    {
        $slug = $employee->firstReadableModuleSlug();

        if ($slug) {
            return route('modules.show', ['module' => $slug]);
        }

        return route('employee.dashboard');
    }
}

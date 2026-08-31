<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user();
        $slug = $employee->firstReadableModuleSlug();

        return view('backend.employees.dashboard', [
            'employee' => $employee,
            'roleName' => $employee->assignedRoleName(),
            'firstModuleSlug' => $slug,
        ]);
    }

    public function editProfile(Request $request): View
    {
        return view('backend.employees.profile', [
            'employee' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $employee = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required',
                'digits_between:10,15',
                Rule::unique('employees', 'phone_number')->ignore($employee->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $employee->name = $validated['name'];
        $employee->phone_number = $validated['phone_number'];

        if (! empty($validated['password'])) {
            $employee->password = $validated['password'];
        }

        $employee->save();

        return redirect()
            ->route('employee.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

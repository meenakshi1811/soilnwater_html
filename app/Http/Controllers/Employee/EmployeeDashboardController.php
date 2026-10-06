<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\ProfilePhoneNumbers;
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

        $rules = array_merge([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], ProfilePhoneNumbers::validationRules());
        $rules['phone_numbers.0'][] = Rule::unique('employees', 'phone_number')->ignore($employee->id);

        $validated = $request->validate($rules, array_merge(
            ProfilePhoneNumbers::validationMessages(),
            ['phone_numbers.0.unique' => 'An employee account with this phone number already exists.']
        ));

        $phoneNumbers = ProfilePhoneNumbers::normalize($validated['phone_numbers']);
        $primary = ProfilePhoneNumbers::primary($phoneNumbers);

        $employee->name = $validated['name'];
        $employee->phone_numbers = $phoneNumbers;
        $employee->phone_number = $primary;

        if (! empty($validated['password'])) {
            $employee->password = $validated['password'];
        }

        $employee->save();

        return redirect()
            ->route('employee.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

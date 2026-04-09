<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\EmployeeRegistrationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('backend.employees.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $employees = User::query()
            ->where('role', 'employee')
            ->with(['roles'])
            ->select(['id', 'name', 'email', 'phone_number', 'is_active', 'created_at']);

        return DataTables::of($employees)
            ->addColumn('role_name', function (User $employee): string {
                $role = $employee->roles->first();

                return $role ? e($role->name) : '<span class="text-muted">—</span>';
            })
            ->editColumn('created_at', function (User $employee) {
                return $employee->created_at ? $employee->created_at->format('Y-m-d') : '';
            })
            ->addColumn('status_badge', function (User $employee): string {
                return $employee->is_active
                    ? '<span class="badge text-bg-success">Active</span>'
                    : '<span class="badge text-bg-secondary">Inactive</span>';
            })
            ->addColumn('actions', function (User $employee): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-employee" data-id="'.$employee->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-employee" data-id="'.$employee->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->filterColumn('role_name', function ($query, $keyword): void {
                $query->whereHas('roles', function ($q) use ($keyword): void {
                    $q->where('name', 'like', '%'.$keyword.'%');
                });
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if ($k === '' || $k === '^') {
                    return;
                }
                if (str_contains($k, 'inactive')) {
                    $query->where('is_active', false);

                    return;
                }
                if (str_contains($k, 'active')) {
                    $query->where('is_active', true);
                }
            })
            ->rawColumns(['status_badge', 'actions', 'role_name'])
            ->make(true);
    }

    public function show(User $employee): JsonResponse
    {
        abort_if($employee->role !== 'employee', 404);

        $role = $employee->roles->first();

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'is_active' => (bool) $employee->is_active,
                'role_id' => $role?->id,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateEmployee($request);
        $role = Role::query()->where('guard_name', 'web')->findOrFail($validated['role_id']);

        $employee = User::create([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone_number' => $validated['phone_number'],
            'role' => 'employee',
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'created_by' => $request->user()?->id,
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
        ]);

        $employee->syncRoles([$role]);

        Mail::to($employee->email)->send(new EmployeeRegistrationMail(
            employee: $employee,
            temporaryPassword: $validated['password'],
            roleName: $role->name,
        ));

        return response()->json([
            'message' => 'Employee created successfully. Login credentials were emailed.',
        ]);
    }

    public function update(Request $request, User $employee): JsonResponse
    {
        abort_if($employee->role !== 'employee', 404);

        $validated = $this->validateEmployee($request, $employee);
        $role = Role::query()->where('guard_name', 'web')->findOrFail($validated['role_id']);

        $employee->fill([
            'name' => $validated['name'],
            'full_name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone_number' => $validated['phone_number'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        if (! empty($validated['password'])) {
            $employee->password = Hash::make($validated['password']);
        }

        $employee->save();
        $employee->syncRoles([$role]);

        return response()->json([
            'message' => 'Employee updated successfully.',
        ]);
    }

    public function destroy(User $employee): JsonResponse
    {
        abort_if($employee->role !== 'employee', 404);
        $employee->syncRoles([]);
        $employee->delete();

        return response()->json([
            'message' => 'Employee deleted successfully.',
        ]);
    }

    private function validateEmployee(Request $request, ?User $employee = null): array
    {
        $passwordRules = $employee
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->id)],
            'phone_number' => ['required', 'digits_between:10,15'],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'web')],
            'is_active' => ['nullable', 'boolean'],
            'password' => $passwordRules,
        ]);
    }
}

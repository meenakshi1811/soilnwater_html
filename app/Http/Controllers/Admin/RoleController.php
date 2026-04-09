<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ModulePermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index()
    {
        return view('backend.roles.index', [
            'modules' => ModulePermissions::modules(),
            'actions' => ModulePermissions::ACTIONS,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->select(['id', 'name', 'created_at']);

        return DataTables::of($roles)
            ->addColumn('permissions_count', fn (Role $role) => (int) $role->permissions()->count())
            ->editColumn('created_at', function (Role $role) {
                return $role->created_at ? $role->created_at->format('Y-m-d') : '';
            })
            ->addColumn('actions', function (Role $role): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    . '<button type="button" class="btn btn-sm btn-outline-primary js-edit-role" data-id="'.$role->id.'"><i class="fa-solid fa-pen"></i></button>'
                    . '<button type="button" class="btn btn-sm btn-outline-danger js-delete-role" data-id="'.$role->id.'"><i class="fa-solid fa-trash"></i></button>'
                    . '</div>';
            })
            ->filterColumn('permissions_count', function ($query, $keyword): void {
                if ($keyword === '' || $keyword === '^') {
                    return;
                }
                if (is_numeric($keyword)) {
                    $query->whereRaw('(select count(*) from role_has_permissions where role_has_permissions.role_id = roles.id) = ?', [(int) $keyword]);
                }
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permission_names' => $role->permissions()->pluck('name')->values()->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateRole($request);
        $permissionNames = $this->normalizePermissionNames($validated['permissions'] ?? []);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->resolvePermissions($permissionNames));

        return response()->json(['message' => 'Role created successfully.']);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $this->validateRole($request, $role);
        $permissionNames = $this->normalizePermissionNames($validated['permissions'] ?? []);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($this->resolvePermissions($permissionNames));

        return response()->json(['message' => 'Role updated successfully.']);
    }

    public function destroy(Role $role): JsonResponse
    {
        $usersWithRole = User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return response()->json([
                'message' => 'Cannot delete role while employees are assigned to it.',
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    public function listForSelect(): JsonResponse
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['roles' => $roles]);
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'array'],
            'permissions.*.*' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * @param  array<string, array<string, bool>>  $permissions
     * @return list<string>
     */
    private function normalizePermissionNames(array $permissions): array
    {
        $allowed = [];
        foreach (ModulePermissions::modules() as $slug => $_label) {
            foreach (ModulePermissions::ACTIONS as $action) {
                $checked = (bool) ($permissions[$slug][$action] ?? false);
                if ($checked) {
                    $allowed[] = ModulePermissions::permissionName($slug, $action);
                }
            }
        }

        return $allowed;
    }

    /**
     * @param  list<string>  $names
     * @return list<string|\Spatie\Permission\Contracts\Permission>
     */
    private function resolvePermissions(array $names): array
    {
        $existing = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        return array_values(array_intersect($names, $existing));
    }
}

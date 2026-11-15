<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * A "Study Materials" module on the Roles & Permissions screen, so an employee's role can be given
 * the study materials review list on its own (it used to need "Teachers & Tutors → Approve").
 * Roles that already reached it keep it: "Teachers & Tutors → Approve" gets every Study Materials
 * permission, and any other Teachers & Tutors permission gets the matching one.
 */
return new class extends Migration
{
    private const ACTIONS = ['add', 'read', 'write', 'delete', 'approve'];

    public function up(): void
    {
        foreach (self::ACTIONS as $action) {
            Permission::query()->firstOrCreate(['name' => 'study_materials.'.$action, 'guard_name' => 'web']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Role::query()->where('guard_name', 'web')->with('permissions')->get() as $role) {
            $had = $role->permissions->pluck('name')->all();
            $give = in_array('educators.approve', $had, true)
                ? self::ACTIONS
                : array_values(array_filter(self::ACTIONS, fn (string $a): bool => in_array('educators.'.$a, $had, true)));

            if ($give !== []) {
                $role->givePermissionTo(array_map(fn (string $a): string => 'study_materials.'.$a, $give));
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_map(fn (string $a): string => 'study_materials.'.$a, self::ACTIONS))
            ->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

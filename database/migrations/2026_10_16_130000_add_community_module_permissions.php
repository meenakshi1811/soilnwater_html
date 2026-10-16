<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Permissions for the "Community" RBAC module (Roles & Permissions screen). Roles can only be
 * given permissions that exist, so they are created here rather than by a seeder alone.
 */
return new class extends Migration
{
    private const ACTIONS = ['add', 'read', 'write', 'delete', 'approve'];

    public function up(): void
    {
        foreach (self::ACTIONS as $action) {
            Permission::query()->firstOrCreate(['name' => 'community.'.$action, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_map(fn (string $action): string => 'community.'.$action, self::ACTIONS))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

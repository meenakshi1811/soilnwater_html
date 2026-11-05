<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * The college account role (like school and institute: the same profile, public page and portal),
 * and the permissions for its "Colleges" RBAC module (Roles & Permissions screen).
 */
return new class extends Migration
{
    private const ACTIONS = ['add', 'read', 'write', 'delete', 'approve'];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $roles = $this->roleValues();
            if (! in_array('college', $roles, true)) {
                // (right after school, keeping every role the column has now)
                $at = array_search('school', $roles, true);
                array_splice($roles, $at === false ? count($roles) : $at + 1, 0, ['college']);
                $this->setRoleValues($roles);
            }
        }

        foreach (self::ACTIONS as $action) {
            Permission::query()->firstOrCreate(['name' => 'colleges.'.$action, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', array_map(fn (string $action): string => 'colleges.'.$action, self::ACTIONS))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (DB::getDriverName() === 'mysql') {
            // (any college accounts become institutes, which the column still allows)
            DB::table('users')->where('role', 'college')->update(['role' => 'institute']);
            $this->setRoleValues(array_values(array_diff($this->roleValues(), ['college'])));
        }
    }

    /** @return list<string> the users.role ENUM's values */
    private function roleValues(): array
    {
        $column = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'role'");
        preg_match_all("/'([^']+)'/", (string) $column->Type, $matches);

        return $matches[1];
    }

    /** @param list<string> $roles */
    private function setRoleValues(array $roles): void
    {
        $list = implode(',', array_map(fn (string $role): string => "'".$role."'", $roles));
        DB::statement("ALTER TABLE users MODIFY role ENUM(".$list.") NOT NULL DEFAULT 'user'");
    }
};

<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasTable('users')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        $legacyEmployees = DB::table('users')->where('role', 'employee')->orderBy('id')->get();

        foreach ($legacyEmployees as $legacy) {
            $email = strtolower((string) $legacy->email);

            $existingId = DB::table('employees')->where('email', $email)->value('id');
            if ($existingId) {
                $this->moveSpatieAssignments((int) $legacy->id, (int) $existingId);
                $this->deleteLegacyUser((int) $legacy->id);
                continue;
            }

            $createdBy = $legacy->created_by ?? null;
            if ($createdBy && DB::table('users')->where('id', $createdBy)->where('role', 'employee')->exists()) {
                $createdBy = null;
            }

            $employeeId = DB::table('employees')->insertGetId([
                'name' => $legacy->name,
                'email' => $email,
                'phone_number' => $legacy->phone_number,
                'email_verified_at' => $legacy->email_verified_at,
                'password' => $legacy->password,
                'is_active' => (bool) ($legacy->is_active ?? true),
                'created_by' => $createdBy,
                'remember_token' => $legacy->remember_token,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            $this->moveSpatieAssignments((int) $legacy->id, (int) $employeeId);
            $this->deleteLegacyUser((int) $legacy->id);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasTable('users')) {
            return;
        }

        $employees = DB::table('employees')->orderBy('id')->get();

        foreach ($employees as $employee) {
            if (DB::table('users')->where('email', $employee->email)->exists()) {
                continue;
            }

            $userId = DB::table('users')->insertGetId([
                'name' => $employee->name,
                'full_name' => $employee->name,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'email_verified_at' => $employee->email_verified_at,
                'password' => $employee->password,
                'role' => 'employee',
                'is_active' => (bool) $employee->is_active,
                'created_by' => $employee->created_by,
                'remember_token' => $employee->remember_token,
                'created_at' => $employee->created_at,
                'updated_at' => $employee->updated_at,
            ]);

            $this->moveSpatieAssignments((int) $employee->id, (int) $userId, Employee::class, User::class);
        }
    }

    private function moveSpatieAssignments(int $fromId, int $toId, string $fromType = User::class, string $toType = Employee::class): void
    {
        foreach (['model_has_roles', 'model_has_permissions'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)
                ->where('model_type', $fromType)
                ->where('model_id', $fromId)
                ->update([
                    'model_type' => $toType,
                    'model_id' => $toId,
                ]);
        }
    }

    private function deleteLegacyUser(int $userId): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }

        DB::table('users')->where('id', $userId)->delete();
    }
};

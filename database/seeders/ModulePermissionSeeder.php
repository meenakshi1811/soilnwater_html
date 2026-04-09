<?php

namespace Database\Seeders;

use App\Support\ModulePermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ModulePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (ModulePermissions::allPermissionNames() as $name) {
            Permission::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }
    }
}

<?php

namespace Tests\Unit;

use App\Models\Employee;
use App\Support\ModulePermissions;
use App\Support\ModuleSidebar;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_sees_only_sidebar_items_matching_role_permissions(): void
    {
        $this->seed(ModulePermissionSeeder::class);

        $role = Role::query()->create([
            'name' => 'Offers Desk',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['offers.read', 'ads.write']);

        $employee = Employee::factory()->create(['is_active' => true]);
        $employee->syncRoles([$role]);

        $sections = ModuleSidebar::visibleSections($employee, false);
        $labels = collect($sections)->flatMap(fn (array $section) => collect($section['items'])->pluck('label'))->all();

        $this->assertContains('All Offers', $labels);
        $this->assertContains('Report Offers', $labels);
        $this->assertNotContains('Offer Prices', $labels);
        $this->assertContains('Ad Sizes', $labels);
        $this->assertNotContains('All Ads', $labels);
    }

    public function test_action_for_route_maps_sidebar_items_to_permission_actions(): void
    {
        $this->assertSame('write', ModulePermissions::actionForRoute('admin.offer-prices.index'));
        $this->assertSame('approve', ModulePermissions::actionForRoute('admin.ads.submissions.index'));
        $this->assertSame('read', ModulePermissions::actionForRoute('admin.vendors.index'));
        $this->assertSame('add', ModulePermissions::actionForRoute('admin.vendor-products.create'));
    }
}

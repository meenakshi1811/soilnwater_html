<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use App\Support\ModulePermissions;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_signup_allows_an_email_already_used_by_a_user(): void
    {
        User::factory()->create([
            'email' => 'shared@example.com',
            'role' => 'user',
        ]);

        $this->post(route('employee.register'), [
            'name' => 'Staff Member',
            'email' => 'shared@example.com',
            'phone_number' => '9876543210',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('employee.login'));

        $this->assertDatabaseHas('employees', [
            'email' => 'shared@example.com',
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'shared@example.com',
            'role' => 'user',
        ]);
    }

    public function test_inactive_employee_cannot_sign_in(): void
    {
        Employee::factory()->pending()->create([
            'email' => 'staff@example.com',
            'password' => 'password123',
        ]);

        $this->from(route('employee.login'))
            ->post(route('employee.login'), [
                'email' => 'staff@example.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('employee.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest('employee');
    }

    public function test_employee_can_only_open_modules_granted_by_their_role(): void
    {
        $this->seed(ModulePermissionSeeder::class);

        $role = Role::query()->create([
            'name' => 'Vendor Desk',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['vendors.read', 'vendors.write']);

        $employee = Employee::factory()->create([
            'email' => 'desk@example.com',
            'is_active' => true,
        ]);
        $employee->syncRoles([$role]);

        $this->actingAs($employee, 'employee')
            ->get(route('modules.show', 'vendors'))
            ->assertOk()
            ->assertSee('Vendors')
            ->assertSee('READ')
            ->assertSee('WRITE');

        $this->actingAs($employee, 'employee')
            ->get(route('modules.show', 'ads'))
            ->assertForbidden();
    }

    public function test_user_login_does_not_authenticate_an_employee_with_the_same_email(): void
    {
        User::factory()->create([
            'email' => 'shared@example.com',
            'password' => 'user-pass-123',
            'role' => 'user',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
        ]);

        Employee::factory()->create([
            'email' => 'shared@example.com',
            'password' => 'staff-pass-123',
            'is_active' => true,
        ]);

        $this->post(route('login'), [
            'login' => 'shared@example.com',
            'password' => 'staff-pass-123',
        ])->assertSessionHasErrors();

        $this->assertGuest('web');
        $this->assertGuest('employee');

        $this->post('/employee/login', [
            'email' => 'shared@example.com',
            'password' => 'staff-pass-123',
        ])->assertRedirect();

        $this->assertAuthenticated('employee');
        $this->assertGuest('web');
    }

    public function test_admin_can_create_employee_with_an_email_that_already_exists_on_users(): void
    {
        Mail::fake();
        $this->seed(ModulePermissionSeeder::class);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'email' => 'dual@example.com',
            'role' => 'user',
        ]);

        $role = Role::query()->create([
            'name' => 'Content',
            'guard_name' => 'web',
        ]);
        $role->givePermissionTo(ModulePermissions::permissionName('enquiry', 'read'));

        $this->actingAs($admin)
            ->postJson(route('admin.employees.store'), [
                'name' => 'Dual Account',
                'email' => 'dual@example.com',
                'phone_number' => '9123456789',
                'role_id' => $role->id,
                'is_active' => 1,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Employee created successfully. Login credentials were emailed. They sign in at the employee portal, even if the same email is already a user.']);

        $this->assertDatabaseHas('employees', [
            'email' => 'dual@example.com',
            'is_active' => 1,
        ]);
    }
}

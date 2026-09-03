<?php

namespace Tests\Feature;

use App\Mail\ConsultantPublicPageApprovedMail;
use App\Mail\VendorPublicPageApprovedMail;
use App\Models\Consultant;
use App\Models\Employee;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VendorConsultantPublicPageApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_draft_submission_preview_and_approval_workflow(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['role' => 'vendor', 'email' => 'vendor@example.com']);
        $vendor = Vendor::query()->create([
            'user_id' => $owner->id,
            'company_name' => 'Draft Vendor',
            'slug' => 'draft-vendor',
            'status' => 'approved',
            'public_page_status' => 'draft',
        ]);

        $this->get(route('store.show', $vendor->slug))->assertNotFound();
        $this->actingAs($owner)->putJson(route('vendor.public-page.update'), [
            'slug' => $vendor->slug,
            'hero_main_heading' => 'Vendor submission',
            'submission_action' => 'submit',
        ])->assertOk()->assertJsonPath('public_page_status', 'pending');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.vendors.public-page.review', $vendor))
            ->assertOk()
            ->assertSee('Approve &amp; Publish', false);
        $this->actingAs($admin)
            ->get(route('admin.vendors.public-page.preview', $vendor))
            ->assertOk()
            ->assertSee('Vendor submission');
        $this->actingAs($admin)
            ->postJson(route('admin.vendors.approve-public-page', $vendor))
            ->assertOk();

        Mail::assertSent(VendorPublicPageApprovedMail::class, fn ($mail) => $mail->hasTo('vendor@example.com'));
        $this->get(route('store.show', $vendor->slug))->assertOk()->assertSee('Vendor submission');
    }

    public function test_admin_can_preview_approved_vendor_store_before_public_page_is_published(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::query()->create([
            'user_id' => User::factory()->create(['role' => 'vendor'])->id,
            'company_name' => 'Rawat Cycle Station',
            'slug' => 'rawat-cycle-station',
            'status' => 'approved',
            'public_page_status' => 'draft',
            'hero_main_heading' => 'Welcome to Rawat Cycle Station',
        ]);

        $this->get(route('store.show', $vendor->slug))->assertNotFound();
        $this->actingAs($admin)
            ->get(route('store.show', $vendor->slug))
            ->assertOk()
            ->assertSee('Welcome to Rawat Cycle Station');
        $this->actingAs($admin)
            ->get(route('admin.vendors.store-preview', $vendor))
            ->assertOk()
            ->assertSee('Welcome to Rawat Cycle Station');
    }

    public function test_admin_store_preview_works_for_pending_vendor_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $vendor = Vendor::query()->create([
            'user_id' => User::factory()->create(['role' => 'vendor'])->id,
            'company_name' => 'Pending Vendor',
            'slug' => 'pending-vendor-store',
            'status' => 'pending',
            'hero_main_heading' => 'Pending vendor preview',
        ]);

        $this->actingAs($admin)
            ->get(route('store.show', $vendor->slug))
            ->assertOk()
            ->assertSee('Pending vendor preview');
        $this->actingAs($admin)
            ->get(route('admin.vendors.store-preview', $vendor))
            ->assertOk()
            ->assertSee('Pending vendor preview');
    }

    public function test_employee_can_publish_vendor_public_page_without_user_foreign_key_error(): void
    {
        $this->seed(ModulePermissionSeeder::class);

        $role = Role::query()->create([
            'name' => 'Vendor Publisher',
            'guard_name' => 'web',
        ]);
        $role->syncPermissions(['vendors.read', 'vendors.write']);

        $employee = Employee::factory()->create(['is_active' => true]);
        $employee->syncRoles([$role]);

        $vendor = Vendor::query()->create([
            'user_id' => User::factory()->create(['role' => 'vendor'])->id,
            'company_name' => 'Manchanda Electronics',
            'slug' => 'manchanda-electronics',
            'status' => 'approved',
            'public_page_status' => 'draft',
            'hero_main_heading' => 'MANCHANDA ELECTRONICS',
        ]);

        $this->actingAs($employee, 'employee')
            ->putJson(route('admin.vendors.public-page.update', $vendor), [
                'slug' => $vendor->slug,
                'hero_main_heading' => 'MANCHANDA ELECTRONICS',
                'submission_action' => 'publish',
            ])
            ->assertOk()
            ->assertJsonPath('public_page_status', 'approved');

        $vendor->refresh();
        $this->assertSame('approved', $vendor->public_page_status);
        $this->assertNull($vendor->public_page_approved_by);
        $this->assertNotNull($vendor->published_page_data);
    }

    public function test_consultant_draft_submission_preview_and_decline_workflow(): void
    {
        $owner = User::factory()->create(['role' => 'consultant']);
        $consultant = Consultant::query()->create([
            'user_id' => $owner->id,
            'company_name' => 'Draft Consultant',
            'slug' => 'draft-consultant',
            'status' => 'approved',
            'public_page_status' => 'approved',
            'published_page_data' => $this->snapshot('draft-consultant', 'Published consultant page'),
        ]);

        $this->actingAs($owner)->putJson(route('consultant.public-page.update'), [
            'slug' => $consultant->slug,
            'hero_main_heading' => 'Consultant submission',
            'submission_action' => 'submit',
        ])->assertOk()->assertJsonPath('public_page_status', 'pending');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.consultants.public-page.preview', $consultant))
            ->assertOk()
            ->assertSee('Consultant submission');
        $this->actingAs($admin)
            ->postJson(route('admin.consultants.decline-public-page', $consultant))
            ->assertOk();

        $consultant->refresh();
        $this->assertSame('declined', $consultant->public_page_status);
        $this->assertNull($consultant->pending_page_data);
        $this->get(route('consultant.show', $consultant->slug))
            ->assertOk()
            ->assertSee('Published consultant page')
            ->assertDontSee('Consultant submission');
    }

    public function test_consultant_approval_sends_email(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['role' => 'consultant', 'email' => 'consultant@example.com']);
        $consultant = Consultant::query()->create([
            'user_id' => $owner->id,
            'company_name' => 'Approval Consultant',
            'slug' => 'approval-consultant',
            'status' => 'approved',
            'public_page_status' => 'pending',
            'pending_page_data' => $this->snapshot('approval-consultant', 'Approved consultant page'),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson(route('admin.consultants.approve-public-page', $consultant))
            ->assertOk();

        Mail::assertSent(ConsultantPublicPageApprovedMail::class, fn ($mail) => $mail->hasTo('consultant@example.com'));
    }

    private function snapshot(string $slug, string $heading): array
    {
        return [
            'profile' => ['slug' => $slug, 'hero_main_heading' => $heading],
            'banner_slides' => [],
            'page_sections' => [],
        ];
    }
}

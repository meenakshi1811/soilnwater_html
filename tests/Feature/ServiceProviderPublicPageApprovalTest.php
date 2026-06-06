<?php

namespace Tests\Feature;

use App\Mail\ServiceProviderPublicPageApprovedMail;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServiceProviderPublicPageApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_is_preview_only_until_admin_approves_the_submission(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'role' => 'service_provider',
            'email' => 'provider@example.com',
        ]);
        $serviceProvider = ServiceProvider::query()->create([
            'user_id' => $owner->id,
            'company_name' => 'Approval Services',
            'slug' => 'approval-services',
            'hero_main_heading' => 'Original draft',
            'status' => 'approved',
            'approved_at' => now(),
            'public_page_status' => 'draft',
        ]);

        $this->get(route('service_provider.show', $serviceProvider->slug))->assertNotFound();

        $this->actingAs($owner)
            ->putJson(route('service_provider.public-page.update'), [
                'slug' => 'approval-services',
                'hero_main_heading' => 'Draft heading',
                'submission_action' => 'draft',
            ])
            ->assertOk()
            ->assertJsonPath('public_page_status', 'draft');

        $this->actingAs($owner)
            ->get(route('service_provider.public-page.preview'))
            ->assertOk()
            ->assertSee('Draft heading');

        $this->get(route('service_provider.show', $serviceProvider->slug))->assertNotFound();

        $this->actingAs($owner)
            ->putJson(route('service_provider.public-page.update'), [
                'slug' => 'approval-services',
                'hero_main_heading' => 'Submitted heading',
                'submission_action' => 'submit',
            ])
            ->assertOk()
            ->assertJsonPath('public_page_status', 'pending');

        $serviceProvider->refresh();
        $this->assertSame('Submitted heading', $serviceProvider->pending_page_data['profile']['hero_main_heading']);
        $this->get(route('service_provider.show', $serviceProvider->slug))->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->postJson(route('admin.service_providers.approve-public-page', $serviceProvider))
            ->assertOk();

        Mail::assertSent(ServiceProviderPublicPageApprovedMail::class, fn ($mail) => $mail->hasTo('provider@example.com'));

        $this->get(route('service_provider.show', $serviceProvider->slug))
            ->assertOk()
            ->assertSee('Submitted heading');

        $this->actingAs($owner)
            ->putJson(route('service_provider.public-page.update'), [
                'slug' => 'approval-services',
                'hero_main_heading' => 'New unapproved draft',
                'submission_action' => 'draft',
            ])
            ->assertOk();

        $this->get(route('service_provider.show', $serviceProvider->slug))
            ->assertOk()
            ->assertSee('Submitted heading')
            ->assertDontSee('New unapproved draft');
    }
}

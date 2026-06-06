<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderOffersAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_routes_use_service_urls_while_internal_names_remain_compatible(): void
    {
        $this->assertSame(url('/services'), route('frontend.service_providers.index'));
        $this->assertSame(url('/service-enquiry'), route('frontend.service-provider-enquiry'));
        $this->assertSame(url('/service/example-service'), route('service_provider.show', 'example-service'));
        $this->assertSame(url('/service/dashboard'), route('service_provider.dashboard'));
        $this->assertSame(url('/admin/services'), route('admin.service_providers.index'));
        $this->assertSame(url('/admin/service-approvals'), route('admin.service-provider-services.index'));
    }

    public function test_approved_service_provider_can_view_offer_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'service_provider',
        ]);

        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Test Service',
            'slug' => 'test-service-provider',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard/offers')
            ->assertOk();
    }

    public function test_approved_service_provider_dashboard_shows_posting_actions(): void
    {
        $user = User::factory()->create([
            'role' => 'service_provider',
        ]);

        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Action Service',
            'slug' => 'action-service-provider',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/service/dashboard')
            ->assertOk()
            ->assertSee('Service Dashboard')
            ->assertSee('Manage every active service location')
            ->assertSee('Slides shown on your public service page')
            ->assertSee('Custom content blocks on your service profile')
            ->assertDontSee('active service_provider location')
            ->assertDontSee('public service_provider page')
            ->assertDontSee('service_provider profile')
            ->assertSee('Post Offer')
            ->assertSee('Post Ad')
            ->assertSee(route('post-offer'), false)
            ->assertSee(route('ads.create.size'), false)
            ->assertSee(route('service_provider.profile.edit'), false);
    }

    public function test_pending_service_provider_cannot_post_marketplace_content(): void
    {
        $user = User::factory()->create([
            'role' => 'service_provider',
        ]);

        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Pending Service',
            'slug' => 'pending-service-provider',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/post-offer')
            ->assertRedirect(route('service_provider.pending'));
    }
}

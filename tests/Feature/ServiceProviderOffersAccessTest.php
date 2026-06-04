<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderOffersAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_service_provider_can_view_offer_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'service_provider',
        ]);

        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Test Service Provider',
            'slug' => 'test-service-provider',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard/offers')
            ->assertOk();
    }

    public function test_pending_service_provider_cannot_post_marketplace_content(): void
    {
        $user = User::factory()->create([
            'role' => 'service_provider',
        ]);

        ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Pending Service Provider',
            'slug' => 'pending-service-provider',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/post-offer')
            ->assertRedirect(route('service_provider.pending'));
    }
}

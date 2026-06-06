<?php

namespace Tests\Feature;

use App\Models\Consultant;
use App\Models\ProfileReport;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_report_an_approved_consultant_with_ajax(): void
    {
        $reporter = User::factory()->create(['email_verified_at' => now()]);
        $owner = User::factory()->create();
        $consultant = Consultant::create([
            'user_id' => $owner->id,
            'company_name' => 'Helpful Consultant',
            'slug' => 'helpful-consultant',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($reporter)->postJson(route('consultant.report', $consultant->slug), [
            'reason' => 'The public profile contains misleading information.',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Consultant reported successfully.',
        ]);
        $this->assertDatabaseHas('profile_reports', [
            'reportable_type' => Consultant::class,
            'reportable_id' => $consultant->id,
            'reported_by' => $reporter->id,
            'reason' => 'The public profile contains misleading information.',
        ]);
    }

    public function test_verified_user_can_report_an_approved_service_provider_with_ajax(): void
    {
        $reporter = User::factory()->create(['email_verified_at' => now()]);
        $owner = User::factory()->create();
        $serviceProvider = ServiceProvider::create([
            'user_id' => $owner->id,
            'company_name' => 'Reliable Services',
            'slug' => 'reliable-services',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($reporter)->postJson(route('service_provider.report', $serviceProvider->slug), [
            'reason' => 'The service details appear fraudulent.',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Service provider reported successfully.',
        ]);
        $this->assertDatabaseHas('profile_reports', [
            'reportable_type' => ServiceProvider::class,
            'reportable_id' => $serviceProvider->id,
            'reported_by' => $reporter->id,
        ]);
    }

    public function test_profile_report_requires_a_reason(): void
    {
        $reporter = User::factory()->create(['email_verified_at' => now()]);
        $owner = User::factory()->create();
        $consultant = Consultant::create([
            'user_id' => $owner->id,
            'company_name' => 'Helpful Consultant',
            'slug' => 'helpful-consultant',
            'status' => 'approved',
        ]);

        $this->actingAs($reporter)
            ->postJson(route('consultant.report', $consultant->slug), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reason');

        $this->assertSame(0, ProfileReport::count());
    }

    public function test_consultant_cannot_report_their_own_profile(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $consultant = Consultant::create([
            'user_id' => $owner->id,
            'company_name' => 'Owner Consultant',
            'slug' => 'owner-consultant',
            'status' => 'approved',
        ]);

        $this->actingAs($owner)
            ->postJson(route('consultant.report', $consultant->slug), [
                'reason' => 'Self report attempt.',
            ])
            ->assertForbidden();

        $this->assertSame(0, ProfileReport::count());
    }

    public function test_service_provider_cannot_report_their_own_profile(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $serviceProvider = ServiceProvider::create([
            'user_id' => $owner->id,
            'company_name' => 'Owner Services',
            'slug' => 'owner-services',
            'status' => 'approved',
        ]);

        $this->actingAs($owner)
            ->postJson(route('service_provider.report', $serviceProvider->slug), [
                'reason' => 'Self report attempt.',
            ])
            ->assertForbidden();

        $this->assertSame(0, ProfileReport::count());
    }

    public function test_consultant_report_action_is_only_visible_to_logged_in_non_owners(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $consultant = Consultant::create([
            'user_id' => $owner->id,
            'company_name' => 'Visible Consultant',
            'slug' => 'visible-consultant',
            'status' => 'approved',
        ]);
        $viewData = ['consultant' => $consultant, 'activeNav' => 'home'];

        $this->view('frontend.consultant.partials.store-header', $viewData)
            ->assertDontSee('data-bs-target="#consultantReportModal"', false);

        $this->actingAs($owner)
            ->view('frontend.consultant.partials.store-header', $viewData)
            ->assertDontSee('data-bs-target="#consultantReportModal"', false);

        $this->actingAs($otherUser)
            ->view('frontend.consultant.partials.store-header', $viewData)
            ->assertSee('data-bs-target="#consultantReportModal"', false);
    }

    public function test_service_report_action_is_only_visible_to_logged_in_non_owners(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $serviceProvider = ServiceProvider::create([
            'user_id' => $owner->id,
            'company_name' => 'Visible Services',
            'slug' => 'visible-services',
            'status' => 'approved',
        ]);
        $viewData = ['service_provider' => $serviceProvider, 'activeNav' => 'home'];

        $this->view('frontend.service_provider.partials.store-header', $viewData)
            ->assertDontSee('data-bs-target="#serviceProviderReportModal"', false);

        $this->actingAs($owner)
            ->view('frontend.service_provider.partials.store-header', $viewData)
            ->assertDontSee('data-bs-target="#serviceProviderReportModal"', false);

        $this->actingAs($otherUser)
            ->view('frontend.service_provider.partials.store-header', $viewData)
            ->assertSee('data-bs-target="#serviceProviderReportModal"', false);
    }
}

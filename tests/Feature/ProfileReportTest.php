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
}

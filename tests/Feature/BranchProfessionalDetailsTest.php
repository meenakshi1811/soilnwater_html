<?php

namespace Tests\Feature;

use App\Models\Consultant;
use App\Models\HomepageSetting;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchProfessionalDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultant_branch_professional_details_are_saved_and_displayed(): void
    {
        $user = User::factory()->create(['role' => 'consultant']);
        $consultant = Consultant::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Expert Consultants',
            'slug' => 'expert-consultants',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $branch = $consultant->branches()->create($this->branchData());

        $this->actingAs($user)
            ->get(route('consultant.branches.edit', $branch))
            ->assertOk()
            ->assertSee('Professional Experience')
            ->assertSee('Services Offered');

        $this->actingAs($user)
            ->putJson(route('consultant.branches.update', $branch), $this->branchData([
                'professional_experience' => 'Twelve years advising agricultural businesses.',
                'services_offered' => "Soil planning\nWater management",
            ]))
            ->assertOk();

        $this->assertDatabaseHas('consultant_branches', [
            'id' => $branch->id,
            'professional_experience' => 'Twelve years advising agricultural businesses.',
            'services_offered' => "Soil planning\nWater management",
        ]);

        $this->get(route('consultant.about', $consultant->slug))
            ->assertOk()
            ->assertSee('Twelve years advising agricultural businesses.')
            ->assertSee('Soil planning')
            ->assertSee('Water management');

        $this->get(route('consultant.show', $consultant->slug))
            ->assertOk()
            ->assertSee('Professional Experience &amp; Services Offered', false)
            ->assertSee('Twelve years advising agricultural businesses.')
            ->assertSee('Soil planning')
            ->assertSee('Water management');

        $branch->update(['is_primary' => false]);
        $consultant->branches()->create($this->branchData([
            'branch_name' => 'Primary Branch Without Professional Details',
            'is_primary' => true,
        ]));

        HomepageSetting::query()->create([
            'section_toggles' => ['consultants_enquiry' => true],
        ]);

        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Experience:')
            ->assertSee('Twelve years advising agricultural businesses.')
            ->assertSee('Services:')
            ->assertSee('Soil planning');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.consultants.show', $consultant))
            ->assertOk()
            ->assertSee('Professional experience')
            ->assertSee('Twelve years advising agricultural businesses.')
            ->assertSee('Services offered')
            ->assertSee('Soil planning');
    }

    public function test_service_provider_branch_professional_details_are_saved_and_displayed(): void
    {
        $user = User::factory()->create(['role' => 'service_provider']);
        $serviceProvider = ServiceProvider::query()->create([
            'user_id' => $user->id,
            'company_name' => 'Expert Services',
            'slug' => 'expert-services',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        $branch = $serviceProvider->branches()->create($this->branchData());

        $this->actingAs($user)
            ->get(route('service_provider.branches.edit', $branch))
            ->assertOk()
            ->assertSee('Professional Experience')
            ->assertSee('Services Offered');

        $this->actingAs($user)
            ->putJson(route('service_provider.branches.update', $branch), $this->branchData([
                'professional_experience' => 'Eight years delivering field services.',
                'services_offered' => "Irrigation setup\nEquipment maintenance",
            ]))
            ->assertOk();

        $this->assertDatabaseHas('service_provider_branches', [
            'id' => $branch->id,
            'professional_experience' => 'Eight years delivering field services.',
            'services_offered' => "Irrigation setup\nEquipment maintenance",
        ]);

        $this->get(route('service_provider.about', $serviceProvider->slug))
            ->assertOk()
            ->assertSee('Eight years delivering field services.')
            ->assertSee('Irrigation setup')
            ->assertSee('Equipment maintenance');

        $this->get(route('service_provider.show', $serviceProvider->slug))
            ->assertOk()
            ->assertSee('Professional Experience &amp; Services Offered', false)
            ->assertSee('Eight years delivering field services.')
            ->assertSee('Irrigation setup')
            ->assertSee('Equipment maintenance');

        $branch->update(['is_primary' => false]);
        $serviceProvider->branches()->create($this->branchData([
            'branch_name' => 'Primary Branch Without Professional Details',
            'is_primary' => true,
        ]));

        HomepageSetting::query()->create([
            'section_toggles' => ['popular_services' => true],
        ]);

        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertSee('Experience:')
            ->assertSee('Eight years delivering field services.')
            ->assertSee('Services:')
            ->assertSee('Irrigation setup');

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.service_providers.show', $serviceProvider))
            ->assertOk()
            ->assertSee('Professional experience')
            ->assertSee('Eight years delivering field services.')
            ->assertSee('Services offered')
            ->assertSee('Irrigation setup');
    }

    private function branchData(array $overrides = []): array
    {
        return array_merge([
            'branch_name' => 'Main Branch',
            'contact_person' => 'Branch Manager',
            'occupation' => 'Advisor',
            'phone' => '1234567890',
            'address' => '123 Farm Road',
            'city' => 'Springfield',
            'state' => 'Illinois',
            'pincode' => '62701',
            'pan_number' => 'ABCDE1234F',
            'has_gst' => false,
            'is_primary' => true,
        ], $overrides);
    }
}

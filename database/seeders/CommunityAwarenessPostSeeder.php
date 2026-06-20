<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityAwarenessPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Awareness Campaign Author',
            'email' => 'awareness-campaign@example.com',
        ]);

        foreach ($this->awarenessPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge([
            'author_bio' => 'Community advocate sharing practical awareness campaigns across Rajasthan.',
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'location_locality' => 'Malviya Nagar',
        ], $post['meta'] ?? []);

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'awareness',
                'category' => $post['category'],
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                'location' => self::LOCATION,
                'location_lat' => self::LOCATION_LAT,
                'location_lng' => self::LOCATION_LNG,
                'video' => $post['video'] ?? null,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => $post['allow_comments'] ?? true,
                'allow_questions' => $post['allow_questions'] ?? true,
                'allow_suggestions' => $post['allow_suggestions'] ?? false,
                'allow_sharing' => $post['allow_sharing'] ?? true,
                'allow_poll' => $post['allow_poll'] ?? false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function awarenessPosts(): array
    {
        return [
            $this->waterConservationCampaign(),
            $this->roadSafetyInitiative(),
            $this->healthCheckupDrive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function waterConservationCampaign(): array
    {
        return [
            'slug' => 'awareness-water-conservation-summer-drive',
            'category' => 'Water Conservation',
            'title' => 'Save Every Drop This Summer: Jaipur Community Water Drive',
            'excerpt' => 'A district-wide awareness campaign with volunteer sign-ups, pledges, and practical tips for households to cut water waste during peak summer.',
            'featured_image' => 'https://picsum.photos/seed/awareness-water/960/540',
            'tags' => ['Awareness', 'Water Conservation', 'Campaign', 'Jaipur'],
            'days_ago' => 3,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h3>Problem</h3>
<p>Jaipur faces rising summer demand and falling groundwater levels. Many households still lose water through leaks, over-watering, and inefficient daily habits.</p>
<h3>Why It Matters</h3>
<p>Every litre saved at home reduces pressure on municipal supply and protects neighbourhood borewells for farmers and schools nearby.</p>
<h3>Facts &amp; Statistics</h3>
<ul>
    <li>A dripping tap can waste more than 75 litres per day.</li>
    <li>Fixing one leaking flush can save hundreds of litres each week.</li>
    <li>Rainwater harvesting can recharge local aquifers when done collectively.</li>
</ul>
<h3>Solutions</h3>
<p>Repair leaks, reuse RO reject water for plants, run washers only on full loads, and share conservation tips with neighbours.</p>
<h3>Call To Action</h3>
<p>Join the summer drive, take the water pledge, and invite your housing society to audit outdoor usage this month.</p>
HTML,
            'meta' => [
                'awareness_category' => 'Water Conservation',
                'awareness_type' => 'Campaign',
                'awareness_level' => 'District',
                'awareness_target_audience' => ['General Public', 'Parents', 'Students'],
                'awareness_posted_by' => 'NGO',
                'awareness_organization_name' => 'Jaipur Jal Rakshak Samiti',
                'awareness_campaign_start_date' => now()->subDays(10)->toDateString(),
                'awareness_campaign_end_date' => now()->addMonths(2)->toDateString(),
                'awareness_call_to_action' => 'Take the household water audit this week and share one change your family will keep all summer.',
                'awareness_action_items' => ['Save Water Daily', 'Plant One Tree'],
                'awareness_allow_campaign_join' => true,
                'awareness_has_event' => true,
                'awareness_event_type' => 'Workshop',
                'awareness_event_date' => now()->addWeeks(2)->toDateString(),
                'awareness_event_venue' => 'Community Hall, Malviya Nagar, Jaipur',
                'awareness_event_time' => '10:00 AM',
                'awareness_event_organizer' => 'Jaipur Jal Rakshak Samiti',
                'awareness_social_impact_categories' => ['Environment', 'Water Conservation', 'Community Development'],
                'awareness_allow_cause_support' => true,
                'awareness_allow_pledges' => true,
                'awareness_pledge_options' => CommunityContentTaxonomy::awarenessPledgeExamples(),
                'awareness_poll_question' => 'Do you reuse kitchen water for plants or cleaning?',
                'awareness_impact_trees_planted' => 120,
                'awareness_impact_volunteers_joined' => 85,
                'awareness_impact_people_reached' => 4200,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roadSafetyInitiative(): array
    {
        return [
            'slug' => 'awareness-helmet-road-safety-jaipur',
            'category' => 'Road Safety',
            'title' => 'Helmet Saves Lives: Two-Wheeler Safety Week in Jaipur',
            'excerpt' => 'An NGO-led road safety advisory with pledge options, volunteer outreach, and a free helmet-check camp for young riders.',
            'featured_image' => 'https://picsum.photos/seed/awareness-road/960/540',
            'tags' => ['Awareness', 'Road Safety', 'NGO', 'Youth'],
            'days_ago' => 7,
            'allow_suggestions' => true,
            'body' => <<<'HTML'
<h3>Problem</h3>
<p>Many two-wheeler riders in urban Jaipur still skip helmets for short trips, especially teenagers riding to tuition and coaching centres.</p>
<h3>Why It Matters</h3>
<p>Head injuries from low-speed falls are preventable. A properly strapped helmet dramatically reduces serious injury risk.</p>
<h3>Solutions</h3>
<p>Always strap the chin buckle, replace damaged helmets, and remind family members before every ride — even for nearby errands.</p>
<h3>Call To Action</h3>
<p>Pledge to wear a helmet on every ride and volunteer at the neighbourhood safety checkpoint this weekend.</p>
HTML,
            'meta' => [
                'awareness_category' => 'Road Safety',
                'awareness_type' => 'NGO Initiative',
                'awareness_level' => 'City',
                'awareness_target_audience' => ['Youth', 'General Public', 'Parents'],
                'awareness_posted_by' => 'NGO',
                'awareness_organization_name' => 'Safe Roads Rajasthan',
                'awareness_campaign_start_date' => now()->subDays(5)->toDateString(),
                'awareness_campaign_end_date' => now()->addWeeks(3)->toDateString(),
                'awareness_call_to_action' => 'Pledge to wear a helmet on every ride and ask one friend to do the same today.',
                'awareness_action_items' => ['Use Helmets'],
                'awareness_allow_campaign_join' => true,
                'awareness_has_event' => true,
                'awareness_event_type' => 'Campaign Drive',
                'awareness_event_date' => now()->addDays(5)->toDateString(),
                'awareness_event_venue' => 'Central Park Gate, Jaipur',
                'awareness_event_time' => '7:30 AM',
                'awareness_event_organizer' => 'Safe Roads Rajasthan',
                'awareness_social_impact_categories' => ['Health', 'Community Development'],
                'awareness_allow_cause_support' => true,
                'awareness_allow_pledges' => true,
                'awareness_pledge_options' => ['I Pledge to Follow Road Safety Rules', 'I Pledge to Always Wear a Helmet'],
                'awareness_impact_volunteers_joined' => 45,
                'awareness_impact_people_reached' => 3100,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function healthCheckupDrive(): array
    {
        return [
            'slug' => 'awareness-free-health-checkup-camp',
            'category' => 'Health Awareness',
            'title' => 'Free Preventive Health Checkups for Senior Citizens',
            'excerpt' => 'A public health advisory encouraging early screening, with an optional poll and community support for neighbourhood wellness camps.',
            'featured_image' => 'https://picsum.photos/seed/awareness-health/960/540',
            'tags' => ['Awareness', 'Health', 'Senior Citizens', 'Public Health'],
            'days_ago' => 12,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h3>Problem</h3>
<p>Many senior citizens delay routine checkups until symptoms become severe, especially for blood pressure, diabetes, and vision screening.</p>
<h3>Why It Matters</h3>
<p>Early detection lowers treatment costs, reduces emergency visits, and helps families plan care with confidence.</p>
<h3>Call To Action</h3>
<p>Book a screening this month and help a neighbour or parent register for the free camp listed below.</p>
HTML,
            'meta' => [
                'awareness_category' => 'Health Awareness',
                'awareness_type' => 'Public Advisory',
                'awareness_level' => 'Local',
                'awareness_target_audience' => ['Senior Citizens', 'General Public', 'Parents'],
                'awareness_posted_by' => 'Community Group',
                'awareness_organization_name' => 'Malviya Nagar Wellness Circle',
                'awareness_call_to_action' => 'Register yourself or a senior family member for a free preventive checkup this month.',
                'awareness_action_items' => ['Get Health Checkups'],
                'awareness_allow_campaign_join' => false,
                'awareness_has_event' => true,
                'awareness_event_type' => 'Blood Donation Camp',
                'awareness_event_date' => now()->addWeeks(1)->toDateString(),
                'awareness_event_venue' => 'Primary Health Centre, Malviya Nagar',
                'awareness_event_time' => '9:00 AM – 1:00 PM',
                'awareness_event_organizer' => 'Malviya Nagar Wellness Circle',
                'awareness_social_impact_categories' => ['Health', 'Community Development'],
                'awareness_allow_cause_support' => true,
                'awareness_allow_pledges' => false,
                'awareness_poll_question' => 'Have you had a preventive health checkup in the last 12 months?',
                'awareness_impact_people_reached' => 1800,
            ],
        ];
    }
}

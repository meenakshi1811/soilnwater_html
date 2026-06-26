<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\CommunityReportAgreement;
use App\Models\CommunityReportFollow;
use App\Models\CommunityReportSupport;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityMyAreaPostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'My Area Community Author',
            'email' => 'my-area@example.com',
        ]);

        $neighbour = User::query()->whereKeyNot($author->id)->first()
            ?? User::factory()->create([
                'name' => 'Neighbour Supporter',
                'email' => 'my-area-neighbour@example.com',
            ]);

        $civicVolunteer = User::query()
            ->whereNotIn('id', [$author->id, $neighbour->id])
            ->first()
            ?? User::factory()->create([
                'name' => 'Civic Volunteer',
                'email' => 'my-area-volunteer@example.com',
            ]);

        foreach ($this->myAreaPosts() as $post) {
            $created = $this->upsertPost($author, $post);
            $this->seedEngagement($created, $post['engagement'] ?? [], [$neighbour, $civicVolunteer]);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): CommunityPost
    {
        $location = $post['location'];
        $activity = $post['activity'];
        $topic = $post['topic'];

        $meta = array_merge([
            'author_bio' => 'Local resident using My Area to connect neighbours with civic issues, improvements, and community wins.',
            'location_country' => $location['country'],
            'location_state' => $location['state'],
            'location_district' => $location['district'],
            'location_city' => $location['city'],
            'location_locality' => $location['locality'],
            'my_area_activity_type' => $activity,
            'my_area_topic_category' => $topic,
            'my_area_visibility' => $post['visibility'] ?? 'public',
        ], $post['meta'] ?? []);

        if (($meta['my_area_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'my_area_private_link_token'))) {
            $meta['my_area_private_link_token'] = Str::random(48);
        }

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'my-area',
                'category' => $topic,
                'writing_purpose' => $activity,
                'title' => $post['title'],
                'excerpt' => $post['excerpt'],
                'body' => $post['body'],
                'featured_image_path' => $post['featured_image'],
                'featured_images' => $post['featured_images'] ?? [$post['featured_image']],
                'tags' => $post['tags'],
                'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                'location' => $location['label'],
                'location_lat' => $location['lat'],
                'location_lng' => $location['lng'],
                'video' => $post['video'] ?? null,
                'publish_as' => $post['publish_as'] ?? CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'pen_name' => $post['pen_name'] ?? null,
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => $post['allow_comments'] ?? true,
                'allow_questions' => $post['allow_questions'] ?? true,
                'allow_suggestions' => $post['allow_suggestions'] ?? true,
                'allow_feedback' => $post['allow_feedback'] ?? true,
                'allow_sharing' => $post['allow_sharing'] ?? true,
                'allow_poll' => $post['allow_poll'] ?? false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @param  array{supports?: int, agreements?: int, follows?: int}  $engagement
     * @param  list<User>  $supporters
     */
    private function seedEngagement(CommunityPost $post, array $engagement, array $supporters): void
    {
        $supportCount = (int) ($engagement['supports'] ?? 0);
        $agreementCount = (int) ($engagement['agreements'] ?? 0);
        $followCount = (int) ($engagement['follows'] ?? 0);

        if ($supportCount + $agreementCount + $followCount === 0) {
            return;
        }

        $users = collect($supporters)->filter()->values();

        if ($users->isEmpty()) {
            return;
        }

        for ($index = 0; $index < $supportCount; $index++) {
            CommunityReportSupport::query()->updateOrCreate(
                [
                    'community_post_id' => $post->id,
                    'user_id' => $users[$index % $users->count()]->id,
                ],
                []
            );
        }

        for ($index = 0; $index < $agreementCount; $index++) {
            CommunityReportAgreement::query()->updateOrCreate(
                [
                    'community_post_id' => $post->id,
                    'user_id' => $users[($index + 1) % $users->count()]->id,
                ],
                []
            );
        }

        for ($index = 0; $index < $followCount; $index++) {
            CommunityReportFollow::query()->updateOrCreate(
                [
                    'community_post_id' => $post->id,
                    'user_id' => $users[$index % $users->count()]->id,
                ],
                []
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function myAreaPosts(): array
    {
        return [
            $this->reportPotholeIssue(),
            $this->suggestParkImprovement(),
            $this->recognizeSanitationHero(),
            $this->shareSchoolAchievement(),
            $this->raiseWaterAwareness(),
            $this->trackGarbageResolution(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reportPotholeIssue(): array
    {
        return [
            'slug' => 'my-area-malviya-nagar-pothole-report',
            'activity' => 'Report Issues',
            'topic' => 'Roads & Transport',
            'title' => 'Dangerous Potholes on Malviya Nagar Main Road Need Immediate Repair',
            'excerpt' => 'Multiple deep potholes near the community market are damaging vehicles and risking two-wheeler accidents every evening.',
            'featured_image' => 'https://picsum.photos/seed/my-area-pothole/960/540',
            'tags' => ['My Area', 'Roads', 'Potholes', 'Malviya Nagar', 'Jaipur'],
            'days_ago' => 2,
            'allow_poll' => true,
            'location' => $this->jaipurMalviyaNagar(),
            'engagement' => ['supports' => 2, 'agreements' => 2, 'follows' => 2],
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>The stretch between the community market and the bus stop has at least six large potholes that fill with rainwater and become invisible at night.</p>
<h2>Background</h2>
<p>Residents raised this during the last ward meeting in March, but only one small patch was filled temporarily.</p>
<h2>Current Situation</h2>
<p>Traffic slows to a crawl during school hours. Auto drivers are refusing evening trips on this route.</p>
<h2>Impact on Community</h2>
<p>Students, shopkeepers, and elderly residents are most affected. Two scooters skidded here last week.</p>
<h2>Suggested Solution</h2>
<p>Complete resurfacing of the 200-metre stretch and installation of reflective markers until permanent repair is done.</p>
<h2>Call for Action</h2>
<p>Support this report, share photos if you have newer damage, and tag neighbours who use this road daily.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'Critical',
                'my_area_affected_communities' => ['Residents', 'Students', 'Businesses', 'General Public'],
                'my_area_status_tracker' => 'Reported',
                'my_area_authorities' => ['Municipality', 'PWD'],
                'my_area_suggested_solution' => 'Emergency patch work within 72 hours, followed by full resurfacing before monsoon.',
                'my_area_poll_question' => 'Do you support urgent repair of Malviya Nagar main road?',
                'my_area_poll_options' => CommunityContentTaxonomy::myAreaDefaultPollOptions(),
                'my_area_photo_evidence' => [
                    $this->externalImage('my-area-pothole-1', 'pothole-near-market.jpg'),
                    $this->externalImage('my-area-pothole-2', 'waterlogged-crater.jpg'),
                ],
                'my_area_documents' => [
                    $this->externalDocument('municipal-complaint-reference.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function suggestParkImprovement(): array
    {
        return [
            'slug' => 'my-area-central-park-bench-lighting-suggestion',
            'activity' => 'Suggest Improvements',
            'topic' => 'Infrastructure',
            'title' => 'Add Benches and Solar Lighting to Central Park Walking Track',
            'excerpt' => 'A practical neighbourhood proposal to make the evening walking track safer for families and senior citizens.',
            'featured_image' => 'https://picsum.photos/seed/my-area-park/960/540',
            'tags' => ['My Area', 'Park', 'Infrastructure', 'Suggestion', 'Dehradun'],
            'days_ago' => 5,
            'location' => $this->dehradunRajpur(),
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>The popular walking track lacks seating and becomes poorly lit after sunset.</p>
<h2>Background</h2>
<p>Morning walkers have requested improvements for two seasons. The park sees heavy use on weekends.</p>
<h2>Current Situation</h2>
<p>Elderly residents stop halfway because there is nowhere to rest. Families avoid the track after 7 PM.</p>
<h2>Impact on Community</h2>
<p>Senior citizens, women walkers, and parents with children would benefit most from low-cost upgrades.</p>
<h2>Suggested Solution</h2>
<p>Install six benches, solar pathway lights every 25 metres, and a small community notice board.</p>
<h2>Call for Action</h2>
<p>Comment with your ideas, volunteer for a weekend cleanliness drive, and vote on priority locations.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'Medium',
                'my_area_affected_communities' => ['Residents', 'Senior Citizens', 'Women', 'General Public'],
                'my_area_status_tracker' => 'Under Discussion',
                'my_area_authorities' => ['Municipality', 'Panchayat'],
                'my_area_suggested_solution' => 'Phase 1: solar lights and two benches near the main gate. Phase 2: full loop seating.',
                'my_area_photo_evidence' => [
                    $this->externalImage('my-area-park-track', 'unlit-walking-track.jpg'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function recognizeSanitationHero(): array
    {
        return [
            'slug' => 'my-area-recognize-sanitation-hero-lakshmi',
            'activity' => 'Recognize Heroes',
            'topic' => 'Community Development',
            'title' => 'Recognizing Lakshmi Devi for 15 Years of Street-Level Cleanliness Leadership',
            'excerpt' => 'A neighbourhood tribute to a resident who coordinates weekly lane cleanups and mentors student volunteers.',
            'featured_image' => 'https://picsum.photos/seed/my-area-hero/960/540',
            'tags' => ['My Area', 'Local Hero', 'Cleanliness', 'Community', 'Jaipur'],
            'days_ago' => 8,
            'location' => $this->jaipurMalviyaNagar(),
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>Our lane stays noticeably cleaner because one resident has led voluntary cleanups for over a decade.</p>
<h2>Background</h2>
<p>Lakshmi Devi began organizing Sunday cleanups when waste collection timings were irregular.</p>
<h2>Current Situation</h2>
<p>She now coordinates 20+ volunteers, separates recyclables, and teaches students about composting.</p>
<h2>Impact on Community</h2>
<p>Fewer open dumps, better hygiene near the school lane, and stronger neighbour participation.</p>
<h2>Suggested Solution</h2>
<p>Nominate her for the ward civic award and support a small tool bank for volunteer teams.</p>
<h2>Call for Action</h2>
<p>Share your own stories of working with Lakshmi ji and suggest ways the community can honour her work.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'High',
                'my_area_affected_communities' => ['Residents', 'Students', 'Women', 'General Public'],
                'my_area_status_tracker' => 'Action Taken',
                'my_area_authorities' => ['Municipality', 'School Authority'],
                'my_area_hero_name' => 'Lakshmi Devi',
                'my_area_hero_location' => 'Malviya Nagar, Jaipur',
                'my_area_hero_contribution' => 'Leads weekly lane cleanups, mentors student volunteers, and maintains a shared compost corner used by twelve households.',
                'my_area_hero_images' => [
                    $this->externalImage('my-area-hero-1', 'cleanup-drive.jpg'),
                    $this->externalImage('my-area-hero-2', 'volunteer-team.jpg'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function shareSchoolAchievement(): array
    {
        return [
            'slug' => 'my-area-government-school-science-fair-win',
            'activity' => 'Share Local Achievements',
            'topic' => 'Education',
            'title' => 'Rajpur Government School Wins District Science Innovation Fair',
            'excerpt' => 'Students built a low-cost water filtration model using local materials — a proud moment for our area.',
            'featured_image' => 'https://picsum.photos/seed/my-area-achievement/960/540',
            'tags' => ['My Area', 'Education', 'Achievement', 'Students', 'Dehradun'],
            'days_ago' => 11,
            'location' => $this->dehradunRajpur(),
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>Our government school students won first place at the district science fair with a practical water project.</p>
<h2>Background</h2>
<p>The team worked after school for six weeks with guidance from a science teacher and a local engineer volunteer.</p>
<h2>Current Situation</h2>
<p>The model is now displayed in the school hall and neighbouring schools have requested demonstrations.</p>
<h2>Impact on Community</h2>
<p>Shows that local students can solve real water quality problems with affordable designs.</p>
<h2>Suggested Solution</h2>
<p>Support a community demo day and help the team apply for the state-level competition.</p>
<h2>Call for Action</h2>
<p>Congratulate the students in comments and share this story with families in nearby lanes.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'Medium',
                'my_area_affected_communities' => ['Students', 'Residents', 'General Public'],
                'my_area_status_tracker' => 'Resolved',
                'my_area_authorities' => ['School Authority', 'District Administration'],
                'my_area_achievement_title' => 'District Science Innovation Fair — First Place',
                'my_area_achievement_description' => 'Team AquaRoots demonstrated a filter using sand, charcoal, and copper mesh that reduced turbidity in canal samples by 78% in field tests.',
                'my_area_photo_evidence' => [
                    $this->externalImage('my-area-science-fair', 'science-fair-display.jpg'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function raiseWaterAwareness(): array
    {
        return [
            'slug' => 'my-area-borewell-recharge-awareness',
            'activity' => 'Raise Awareness',
            'topic' => 'Water Issues',
            'title' => 'Our Block Is Losing Groundwater — Here Is What Every Household Can Do',
            'excerpt' => 'A neighbour-led awareness post on falling borewell levels and simple recharge steps before summer peaks.',
            'featured_image' => 'https://picsum.photos/seed/my-area-water/960/540',
            'tags' => ['My Area', 'Water', 'Awareness', 'Groundwater', 'Jaipur'],
            'days_ago' => 14,
            'allow_poll' => true,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'video_id' => '9bZkp7q19f0',
            ],
            'location' => $this->jaipurMalviyaNagar(),
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>Three borewells in our block dropped more than 40 feet compared to last summer.</p>
<h2>Background</h2>
<p>Rooftop runoff is mostly lost to drains even though the colony has space for small recharge pits.</p>
<h2>Current Situation</h2>
<p>Water tanker dependence is increasing. Several families run pumps only at night.</p>
<h2>Impact on Community</h2>
<p>Every household shares the same aquifer. Conservation and recharge are collective responsibilities.</p>
<h2>Suggested Solution</h2>
<p>Install rooftop rainwater pipes, create recharge pits where safe, and audit outdoor usage weekly.</p>
<h2>Call for Action</h2>
<p>Watch the short video, take the poll, and comment if you want a lane-wise recharge workshop.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'High',
                'my_area_affected_communities' => ['Residents', 'Farmers', 'General Public'],
                'my_area_status_tracker' => 'Under Discussion',
                'my_area_authorities' => ['Water Department', 'Municipality', 'Panchayat'],
                'my_area_suggested_solution' => 'Organize a society-level water audit and fund two shared recharge pits near the park boundary.',
                'my_area_poll_question' => 'Should our block host a rainwater harvesting workshop this month?',
                'my_area_poll_options' => ['Yes', 'No', 'Need more information first'],
                'my_area_photo_evidence' => [
                    $this->externalImage('my-area-borewell-chart', 'borewell-depth-chart.jpg'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trackGarbageResolution(): array
    {
        return [
            'slug' => 'my-area-ward-12-garbage-dump-resolution-tracker',
            'activity' => 'Track Resolutions',
            'topic' => 'Cleanliness & Waste',
            'title' => 'Resolution Update: Ward 12 Open Garbage Dump Cleared After 45 Days',
            'excerpt' => 'Tracking how residents, the municipality, and a local NGO worked together to close an illegal dump site.',
            'featured_image' => 'https://picsum.photos/seed/my-area-garbage-resolved/960/540',
            'tags' => ['My Area', 'Cleanliness', 'Resolution', 'Waste', 'Dehradun'],
            'days_ago' => 18,
            'publish_as' => CommunityPost::PUBLISH_AS_FIRST_NAME_ONLY,
            'visibility' => 'registered_users',
            'location' => $this->dehradunRajpur(),
            'engagement' => ['supports' => 2, 'follows' => 1],
            'body' => <<<'HTML'
<h2>Issue / Topic</h2>
<p>An open garbage dump behind the market was causing smell, pests, and drainage blockages.</p>
<h2>Background</h2>
<p>Residents filed complaints, tagged authorities on My Area, and documented the site for six weeks.</p>
<h2>Current Situation</h2>
<p>The dump has been cleared, the area disinfected, and a monitoring signboard installed.</p>
<h2>Impact on Community</h2>
<p>Shopkeepers and nearby homes report improved hygiene. Drainage flow has improved after debris removal.</p>
<h2>Suggested Solution</h2>
<p>Maintain a rotating volunteer watch and ensure scheduled municipal collection does not slip again.</p>
<h2>Call for Action</h2>
<p>Follow this issue for monthly updates and share lessons for other wards facing similar dumps.</p>
HTML,
            'meta' => [
                'my_area_impact_level' => 'High',
                'my_area_affected_communities' => ['Residents', 'Businesses', 'General Public'],
                'my_area_status_tracker' => 'Resolved',
                'my_area_authorities' => ['Municipality', 'District Administration', 'Others'],
                'my_area_suggested_solution' => 'Weekly photo check-ins for 90 days and a shared WhatsApp alert if waste reappears.',
                'my_area_photo_evidence' => [
                    $this->externalImage('my-area-dump-before', 'dump-site-before.jpg'),
                    $this->externalImage('my-area-dump-after', 'cleared-site-after.jpg'),
                ],
                'my_area_documents' => [
                    $this->externalDocument('municipal-closure-notice.pdf'),
                    $this->externalDocument('ngo-cleanup-report.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, locality: string, lat: float, lng: float}
     */
    private function jaipurMalviyaNagar(): array
    {
        return [
            'label' => 'Malviya Nagar, Jaipur, Rajasthan, India',
            'country' => 'India',
            'state' => 'Rajasthan',
            'district' => 'Jaipur',
            'city' => 'Jaipur',
            'locality' => 'Malviya Nagar',
            'lat' => 26.8545000,
            'lng' => 75.8143000,
        ];
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, locality: string, lat: float, lng: float}
     */
    private function dehradunRajpur(): array
    {
        return [
            'label' => 'Rajpur Road, Dehradun, Uttarakhand, India',
            'country' => 'India',
            'state' => 'Uttarakhand',
            'district' => 'Dehradun',
            'city' => 'Dehradun',
            'locality' => 'Rajpur',
            'lat' => 30.3456000,
            'lng' => 78.0541000,
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalImage(string $seed, string $name): array
    {
        return [
            'path' => 'seeders/my-area/'.$seed.'.jpg',
            'url' => 'https://picsum.photos/seed/'.$seed.'/960/540',
            'name' => $name,
            'type' => 'image/jpeg',
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalDocument(string $name): array
    {
        return [
            'path' => 'seeders/my-area/'.$name,
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'name' => $name,
            'type' => 'application/pdf',
        ];
    }
}

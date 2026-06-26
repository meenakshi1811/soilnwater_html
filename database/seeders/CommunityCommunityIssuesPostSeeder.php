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

class CommunityCommunityIssuesPostSeeder extends Seeder
{
    public function run(): void
    {
        $issueReporter = $this->user('Issue Reporter Priya', 'community-issues-reporter@example.com');
        $waterWarrior = $this->user('Water Warrior Ravi', 'water-warrior@example.com');
        $greenChampion = $this->user('Green Champion Ananya', 'green-champion@example.com');
        $problemSolver = $this->user('Problem Solver Mohan', 'problem-solver@example.com');
        $civicVolunteer = $this->user('Civic Volunteer Neha', 'civic-volunteer@example.com');

        $supporterPool = collect([
            $civicVolunteer,
            $waterWarrior,
            $greenChampion,
            $problemSolver,
            $this->user('Resident Supporter A', 'ci-supporter-a@example.com'),
            $this->user('Resident Supporter B', 'ci-supporter-b@example.com'),
            $this->user('Resident Supporter C', 'ci-supporter-c@example.com'),
            $this->user('Resident Supporter D', 'ci-supporter-d@example.com'),
            $this->user('Resident Supporter E', 'ci-supporter-e@example.com'),
            $this->user('Resident Supporter F', 'ci-supporter-f@example.com'),
            $this->user('Resident Supporter G', 'ci-supporter-g@example.com'),
            $this->user('Resident Supporter H', 'ci-supporter-h@example.com'),
            $this->user('Resident Supporter I', 'ci-supporter-i@example.com'),
            $this->user('Resident Supporter J', 'ci-supporter-j@example.com'),
            $this->user('Resident Supporter K', 'ci-supporter-k@example.com'),
        ])->unique('id')->values();

        foreach ($this->communityIssuePosts() as $post) {
            $author = match ($post['author'] ?? 'reporter') {
                'water' => $waterWarrior,
                'green' => $greenChampion,
                'solver' => $problemSolver,
                default => $issueReporter,
            };

            $created = $this->upsertPost($author, $post);
            $this->seedEngagement($created, $post['engagement'] ?? [], $supporterPool, (int) ($post['author_id'] ?? $author->id));
        }
    }

    private function user(string $name, string $email): User
    {
        return User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt('password')]
        );
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): CommunityPost
    {
        $location = $post['location'];
        $category = $post['category'];

        $meta = array_merge([
            'author_bio' => 'Community member reporting civic issues through SoilnWater to drive local accountability and resolution.',
            'community_issue_category' => $category,
            'community_issue_type' => $post['issue_type'],
            'community_issue_severity' => $post['severity'],
            'location_country' => $location['country'],
            'location_state' => $location['state'],
            'location_district' => $location['district'],
            'location_city' => $location['city'],
            'location_locality' => $location['locality'],
            'location_landmark' => $location['landmark'] ?? null,
            'community_issue_visibility' => $post['visibility'] ?? 'public',
            'community_issue_allow_campaign' => $post['allow_campaign'] ?? true,
            'community_issue_allow_support' => $post['allow_support'] ?? true,
            'community_issue_allow_follow' => $post['allow_follow'] ?? true,
            'community_issue_allow_verification' => $post['allow_verification'] ?? true,
            'community_issue_escalation_threshold' => $post['escalation_threshold'] ?? CommunityContentTaxonomy::communityIssueDefaultEscalationThreshold(),
        ], $post['meta'] ?? []);

        if (($meta['community_issue_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'community_issue_private_link_token'))) {
            $meta['community_issue_private_link_token'] = Str::random(48);
        }

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'community-issues',
                'category' => $category,
                'writing_purpose' => $post['issue_type'],
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
     * @param  \Illuminate\Support\Collection<int, User>  $supporters
     */
    private function seedEngagement(CommunityPost $post, array $engagement, $supporters, int $authorId): void
    {
        CommunityReportSupport::query()->where('community_post_id', $post->id)->delete();
        CommunityReportAgreement::query()->where('community_post_id', $post->id)->delete();
        CommunityReportFollow::query()->where('community_post_id', $post->id)->delete();

        $eligibleSupporters = $supporters->where('id', '!=', $authorId)->values();

        if ($eligibleSupporters->isEmpty()) {
            return;
        }

        $supportCount = (int) ($engagement['supports'] ?? 0);
        $agreementCount = (int) ($engagement['agreements'] ?? 0);
        $followCount = (int) ($engagement['follows'] ?? 0);

        for ($index = 0; $index < $supportCount; $index++) {
            CommunityReportSupport::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $eligibleSupporters[$index % $eligibleSupporters->count()]->id,
            ]);
        }

        for ($index = 0; $index < $agreementCount; $index++) {
            CommunityReportAgreement::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $eligibleSupporters[($index + 1) % $eligibleSupporters->count()]->id,
            ]);
        }

        for ($index = 0; $index < $followCount; $index++) {
            CommunityReportFollow::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $eligibleSupporters[($index + 2) % $eligibleSupporters->count()]->id,
            ]);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function communityIssuePosts(): array
    {
        return [
            $this->premNagarWaterLeak(),
            $this->connaughtPlacePotholes(),
            $this->bandraStreetLight(),
            $this->dehradunDrainageOverflow(),
            $this->jaipurBorewellContamination(),
            $this->rajpurGarbageDump(),
            $this->ghaziabadFactorySmoke(),
            $this->noidaResolvedDrain(),
            $this->hyderabadEscalatedSafetyHazard(),
            $this->bengaluruLakePollution(),
            $this->chennaiPublicTransportIssue(),
            $this->kolkataAnonymousElectricityComplaint(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function premNagarWaterLeak(): array
    {
        return [
            'slug' => 'community-issue-prem-nagar-water-leakage',
            'author' => 'reporter',
            'category' => 'Water Issues',
            'issue_type' => 'Urgent Problem',
            'severity' => 'High',
            'title' => 'Major Water Pipeline Leakage Near SBI Bank, Prem Nagar',
            'excerpt' => 'A burst pipeline has been wasting thousands of litres daily and flooding the lane outside Prem Nagar market.',
            'featured_image' => 'https://picsum.photos/seed/ci-water-leak/960/540',
            'tags' => ['Water Leakage', 'Prem Nagar', 'Infrastructure', 'Municipality'],
            'days_ago' => 1,
            'allow_poll' => true,
            'location' => $this->location('Prem Nagar, Jaipur, Rajasthan, India', 'India', 'Rajasthan', 'Jaipur', 'Jaipur', 'Prem Nagar', 26.8562000, 75.8118000, 'Near SBI Bank'),
            'engagement' => ['supports' => 8, 'agreements' => 6, 'follows' => 5],
            'body' => $this->structuredBody(
                'A main distribution line is leaking at three points beside the Prem Nagar community market.',
                'Residents first noticed damp patches on 12 June. By 14 June water was flowing continuously into the storm drain.',
                'Households, shopkeepers, students using the bus stop, and elderly walkers.',
                'Slippery footpaths, wasted drinking water, and reduced pressure for uphill homes.',
                'Complaint filed with Water Department on 14 June. Local photos shared in WhatsApp groups.',
                'Replace damaged pipe section and install a pressure valve before the monsoon peak.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '100-500 People',
                'community_issue_affected_groups' => ['Residents', 'Businesses', 'Senior Citizens', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(14)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Continuous',
                'community_issue_authority' => 'Water Department',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'WD-JPR-2026-4412',
                'community_issue_complaint_date' => now()->subDays(12)->toDateString(),
                'community_issue_department_contacted' => 'Jaipur Water Department — Zone 4',
                'community_issue_suggested_solution' => 'Emergency clamp repair within 48 hours, followed by full pipe replacement before monsoon.',
                'community_issue_support_requests' => ['Authority Attention', 'Community Feedback', 'Awareness'],
                'community_issue_status_tracker' => 'Reported',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(14)->format('d M').' - Issue first noticed',
                    now()->subDays(12)->format('d M').' - Reported to Water Department',
                    now()->subDays(1)->format('d M').' - Posted on SoilnWater Community Issues',
                ]),
                'community_issue_poll_question' => 'Do you think this issue requires urgent action?',
                'community_issue_poll_options' => CommunityContentTaxonomy::communityIssueDefaultPollOptions(),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-water-leak-1', 'water-leakage-road.jpg'),
                    $this->externalImage('ci-water-leak-2', 'flooded-lane.jpg'),
                ],
                'community_issue_documents' => [
                    $this->externalDocument('water-department-complaint-letter.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function connaughtPlacePotholes(): array
    {
        return [
            'slug' => 'community-issue-connaught-place-potholes',
            'author' => 'reporter',
            'category' => 'Roads & Transport',
            'issue_type' => 'Complaint',
            'severity' => 'Critical',
            'title' => 'Deep Potholes on Connaught Place Inner Circle Disrupt Traffic Daily',
            'excerpt' => 'Multiple crater-sized potholes are damaging vehicles and slowing emergency access in central Delhi.',
            'featured_image' => 'https://picsum.photos/seed/ci-road-pothole/960/540',
            'tags' => ['Road Damage', 'Connaught Place', 'PWD', 'Delhi'],
            'days_ago' => 3,
            'location' => $this->location('Connaught Place, New Delhi, Delhi, India', 'India', 'Delhi', 'New Delhi', 'New Delhi', 'Connaught Place', 28.6315000, 77.2167000, 'Near Inner Circle Metro Exit'),
            'engagement' => ['supports' => 5, 'agreements' => 4, 'follows' => 3],
            'body' => $this->structuredBody(
                'At least eight large potholes on the inner circle stretch between blocks C and F.',
                'Problem worsened after the last spell of rain three weeks ago.',
                'Commuters, taxi drivers, tourists, and businesses along the circle.',
                'Traffic jams, vehicle damage, and risk of two-wheeler accidents at night.',
                'Online complaint logged with PWD. Councillor office contacted by shop association.',
                'Full resurfacing of the affected 300-metre stretch with proper drainage gradient.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '500+',
                'community_issue_affected_groups' => ['Residents', 'Businesses', 'Tourists', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(21)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Seasonal',
                'community_issue_authority' => 'PWD',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'PWD-DL-77821',
                'community_issue_complaint_date' => now()->subDays(18)->toDateString(),
                'community_issue_department_contacted' => 'PWD Delhi — Central Division',
                'community_issue_suggested_solution' => 'Night-time milling and resurfacing within two weeks; reflective markers until permanent repair.',
                'community_issue_support_requests' => ['Authority Attention', 'Volunteers', 'Awareness'],
                'community_issue_status_tracker' => 'Forwarded to Authority',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(21)->format('d M').' - Reported',
                    now()->subDays(16)->format('d M').' - Community Verified',
                    now()->subDays(10)->format('d M').' - Forwarded to PWD',
                ]),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-pothole-1', 'road-damage.jpg'),
                    $this->externalImage('ci-pothole-2', 'traffic-slowdown.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bandraStreetLight(): array
    {
        return [
            'slug' => 'community-issue-bandra-broken-street-lights',
            'author' => 'reporter',
            'category' => 'Electricity',
            'issue_type' => 'Safety Hazard',
            'severity' => 'Medium',
            'title' => 'Broken Street Lights on Bandra Linking Road Raise Safety Concerns',
            'excerpt' => 'A 400-metre stretch has non-functional lights, making evening walks unsafe for women and seniors.',
            'featured_image' => 'https://picsum.photos/seed/ci-street-light/960/540',
            'tags' => ['Broken Street Light', 'Bandra', 'Safety', 'Mumbai'],
            'days_ago' => 6,
            'location' => $this->location('Linking Road, Bandra West, Mumbai, Maharashtra, India', 'India', 'Maharashtra', 'Mumbai', 'Mumbai', 'Bandra West', 19.0596000, 72.8295000, 'Opposite National College'),
            'engagement' => ['supports' => 3, 'agreements' => 5, 'follows' => 2],
            'body' => $this->structuredBody(
                'Seven street lights between 16th and 22nd Road crossings are not working.',
                'First reported after a power fluctuation event four weeks ago.',
                'Women, senior citizens, students, and shop staff leaving after closing hours.',
                'Poor visibility, increased theft risk, and reluctance to use the footpath after sunset.',
                'BMC helpline called twice. Local corporator tagged on social media.',
                'Replace fused units, audit the feeder line, and add temporary solar lights within 10 days.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '10-100 People',
                'community_issue_affected_groups' => ['Women', 'Senior Citizens', 'Students', 'Businesses'],
                'community_issue_first_noticed_on' => now()->subDays(28)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Electricity Department',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'MSEB-BW-9921',
                'community_issue_complaint_date' => now()->subDays(20)->toDateString(),
                'community_issue_department_contacted' => 'Adani Electricity Mumbai — Bandra zone',
                'community_issue_suggested_solution' => 'Immediate repair of seven poles and monthly maintenance checklist for the lane.',
                'community_issue_support_requests' => ['Authority Attention', 'Community Feedback'],
                'community_issue_status_tracker' => 'Pending Verification',
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-streetlight-1', 'broken-street-light.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dehradunDrainageOverflow(): array
    {
        return [
            'slug' => 'community-issue-dehradun-drainage-overflow',
            'author' => 'water',
            'category' => 'Drainage & Sewage',
            'issue_type' => 'Public Concern',
            'severity' => 'High',
            'title' => 'Rajpur Drain Overflow Floods Lanes After Every Rainfall',
            'excerpt' => 'Blocked culverts cause sewage-mixed runoff to enter ground floors during moderate showers.',
            'featured_image' => 'https://picsum.photos/seed/ci-drainage/960/540',
            'tags' => ['Drainage', 'Rajpur', 'Monsoon', 'Dehradun'],
            'days_ago' => 4,
            'allow_poll' => true,
            'location' => $this->location('Rajpur Road, Dehradun, Uttarakhand, India', 'India', 'Uttarakhand', 'Dehradun', 'Dehradun', 'Rajpur', 30.3471000, 78.0523000, 'Near Ballupur Chowk'),
            'engagement' => ['supports' => 4, 'agreements' => 3, 'follows' => 4],
            'body' => $this->structuredBody(
                'Main storm drain overflows at two bends and backs up into residential lanes.',
                'Recurring every monsoon for three years; worse after recent construction debris dumping.',
                'Residents, children walking to tuition centres, and ground-floor shop owners.',
                'Health risk, property damage, and school route disruption.',
                'Municipal complaint in 2025. Fresh inspection requested this season.',
                'Desilt drains, remove construction debris, and install a new drainage channel before peak monsoon.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '100-500 People',
                'community_issue_affected_groups' => ['Residents', 'Children', 'Businesses'],
                'community_issue_first_noticed_on' => now()->subDays(90)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Seasonal',
                'community_issue_authority' => 'Municipal Corporation',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'DNN-MC-2025-118',
                'community_issue_department_contacted' => 'Dehradun Municipal Corporation',
                'community_issue_suggested_solution' => 'Install a new drainage channel and repair existing culverts before monsoon season.',
                'community_issue_support_requests' => ['Authority Attention', 'Technical Guidance', 'Volunteers'],
                'community_issue_status_tracker' => 'Work Started',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(90)->format('d M').' - Reported',
                    now()->subDays(60)->format('d M').' - Community Verified',
                    now()->subDays(30)->format('d M').' - Forwarded to Municipality',
                    now()->subDays(7)->format('d M').' - Repair Work Started',
                ]),
                'community_issue_poll_question' => 'Should monsoon drain desilting be prioritised this week?',
                'community_issue_poll_options' => CommunityContentTaxonomy::communityIssueDefaultPollOptions(),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-drain-1', 'overflowing-drain.jpg'),
                    $this->externalImage('ci-drain-2', 'flooded-lane-rajpur.jpg'),
                ],
                'community_issue_documents' => [
                    $this->externalDocument('municipal-inspection-notice.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jaipurBorewellContamination(): array
    {
        return [
            'slug' => 'community-issue-jaipur-borewell-contamination',
            'author' => 'water',
            'category' => 'Water Issues',
            'issue_type' => 'Environmental Concern',
            'severity' => 'Critical',
            'title' => 'Murky Borewell Water in Vaishali Nagar After Pipeline Work',
            'excerpt' => 'Several households report brown, odorous water since adjacent road digging damaged a supply line.',
            'featured_image' => 'https://picsum.photos/seed/ci-borewell/960/540',
            'tags' => ['Water Issues', 'Vaishali Nagar', 'Health', 'Jaipur'],
            'days_ago' => 7,
            'location' => $this->location('Vaishali Nagar, Jaipur, Rajasthan, India', 'India', 'Rajasthan', 'Jaipur', 'Jaipur', 'Vaishali Nagar', 26.9124000, 75.7435000, 'Block C Community Tap'),
            'engagement' => ['supports' => 6, 'agreements' => 4, 'follows' => 3],
            'body' => $this->structuredBody(
                'Tap water turned cloudy with sediment after PWD road excavation near the supply main.',
                'Started five days after digging began on 1 June.',
                'Families, elderly residents, and a nearby anganwadi centre.',
                'Health concerns, reliance on costly tankers, and cooking disruption.',
                'Water quality sample collected by residents. Health department informed.',
                'Flush lines, test water quality publicly, and restore sealed pipe joints immediately.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '10-100 People',
                'community_issue_affected_groups' => ['Residents', 'Children', 'Senior Citizens'],
                'community_issue_first_noticed_on' => now()->subDays(10)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Water Department',
                'community_issue_suggested_solution' => 'Emergency water tanker service until lines are flushed and certified safe.',
                'community_issue_support_requests' => ['Authority Attention', 'Awareness', 'Community Feedback'],
                'community_issue_status_tracker' => 'Verified',
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-murky-water', 'murky-tap-water.jpg'),
                ],
                'community_issue_documents' => [
                    $this->externalDocument('water-quality-complaint.pdf'),
                    $this->externalDocument('rti-response-water-test.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rajpurGarbageDump(): array
    {
        return [
            'slug' => 'community-issue-rajpur-illegal-garbage-dump',
            'author' => 'green',
            'category' => 'Waste Management',
            'issue_type' => 'Complaint',
            'severity' => 'High',
            'title' => 'Illegal Garbage Dump Growing Behind Rajpur Community Market',
            'excerpt' => 'Uncollected waste is piling up near the storm drain, attracting pests and blocking water flow.',
            'featured_image' => 'https://picsum.photos/seed/ci-garbage/960/540',
            'tags' => ['Garbage Dump', 'Rajpur', 'Cleanliness', 'Dehradun'],
            'days_ago' => 5,
            'location' => $this->location('Rajpur Market Lane, Dehradun, Uttarakhand, India', 'India', 'Uttarakhand', 'Dehradun', 'Dehradun', 'Rajpur', 30.3440000, 78.0565000, 'Behind community market'),
            'engagement' => ['supports' => 7, 'agreements' => 5, 'follows' => 4],
            'body' => $this->structuredBody(
                'A vacant plot is being used as an open dump with no fencing or collection schedule.',
                'Dump appeared six weeks ago and has doubled in size.',
                'Shopkeepers, nearby homes, and walkers using the market lane.',
                'Foul smell, stray animals, and risk of drain blockage during rain.',
                'Municipal helpline called. Photos submitted to ward councillor.',
                'Clear site within 72 hours, penalise repeat offenders, and place surveillance signage.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '100-500 People',
                'community_issue_affected_groups' => ['Residents', 'Businesses', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(42)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Weekly',
                'community_issue_authority' => 'Municipal Corporation',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'DNN-SWM-3301',
                'community_issue_complaint_date' => now()->subDays(35)->toDateString(),
                'community_issue_department_contacted' => 'Solid Waste Management — Dehradun',
                'community_issue_suggested_solution' => 'Daily collection enforcement and CCTV at the vacant plot entrance.',
                'community_issue_support_requests' => ['Authority Attention', 'Volunteers', 'Awareness'],
                'community_issue_status_tracker' => 'Acknowledged',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(42)->format('d M').' - Reported',
                    now()->subDays(28)->format('d M').' - Community Verified',
                    now()->subDays(20)->format('d M').' - Forwarded to Municipality',
                    now()->subDays(8)->format('d M').' - Municipality Acknowledged',
                ]),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-garbage-1', 'garbage-dump.jpg'),
                    $this->externalImage('ci-garbage-2', 'blocked-drain-near-dump.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ghaziabadFactorySmoke(): array
    {
        return [
            'slug' => 'community-issue-ghaziabad-factory-smoke-pollution',
            'author' => 'green',
            'category' => 'Pollution',
            'issue_type' => 'Environmental Concern',
            'severity' => 'Critical',
            'title' => 'Black Smoke from Industrial Unit Affects Indirapuram Housing Societies',
            'excerpt' => 'Night-time emissions from a nearby unit are causing breathing difficulties and coated balconies.',
            'featured_image' => 'https://picsum.photos/seed/ci-pollution-smoke/960/540',
            'tags' => ['Pollution', 'Indirapuram', 'Air Quality', 'Ghaziabad'],
            'days_ago' => 9,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'video_id' => '9bZkp7q19f0',
            ],
            'location' => $this->location('Indirapuram, Ghaziabad, Uttar Pradesh, India', 'India', 'Uttar Pradesh', 'Ghaziabad', 'Ghaziabad', 'Indirapuram', 28.6692000, 77.4538000, 'Near Shipra Sun City gate'),
            'engagement' => ['supports' => 4, 'agreements' => 6, 'follows' => 3],
            'body' => $this->structuredBody(
                'Thick smoke billows from a small industrial chimney after midnight on most weekdays.',
                'Residents documented emissions for three weeks with photos and video.',
                'Families with asthma patients, children, and elderly residents in four societies.',
                'Breathing irritation, black residue on windows, and inability to keep windows open.',
                'Pollution control board complaint filed. Local MLA office contacted.',
                'Inspect emissions, enforce filtration standards, and publish compliance results publicly.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '500+',
                'community_issue_affected_groups' => ['Residents', 'Children', 'Senior Citizens', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(25)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Daily',
                'community_issue_authority' => 'District Administration',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'UPPCB-GZB-882',
                'community_issue_department_contacted' => 'Uttar Pradesh Pollution Control Board',
                'community_issue_suggested_solution' => 'Night inspection, emission testing, and temporary shutdown until filters are installed.',
                'community_issue_support_requests' => ['Authority Attention', 'Awareness', 'Technical Guidance'],
                'community_issue_status_tracker' => 'Pending Verification',
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-smoke-1', 'factory-smoke-night.jpg'),
                ],
                'community_issue_documents' => [
                    $this->externalDocument('pollution-complaint-letter.pdf'),
                    $this->externalDocument('survey-report-air-quality.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function noidaResolvedDrain(): array
    {
        return [
            'slug' => 'community-issue-noida-open-drain-resolved',
            'author' => 'solver',
            'category' => 'Drainage & Sewage',
            'issue_type' => 'Request for Action',
            'severity' => 'Medium',
            'title' => 'Open Drain Cover Repaired in Sector 62 — Resolution Update',
            'excerpt' => 'Community reporting and follow-up led to a permanent cover replacement after 38 days.',
            'featured_image' => 'https://picsum.photos/seed/ci-drain-resolved/960/540',
            'tags' => ['Drainage', 'Sector 62', 'Resolved', 'Noida'],
            'days_ago' => 12,
            'location' => $this->location('Sector 62, Noida, Uttar Pradesh, India', 'India', 'Uttar Pradesh', 'Gautam Buddha Nagar', 'Noida', 'Sector 62', 28.6270000, 77.3730000, 'Near City Centre metro'),
            'engagement' => ['supports' => 9, 'agreements' => 7, 'follows' => 6],
            'body' => $this->structuredBody(
                'A missing drain cover created a safety hazard on a busy pedestrian path.',
                'Issue open since early May after cover theft during road work.',
                'Office commuters, school children, and cyclists using the footpath.',
                'Risk of falls, especially at night; one minor injury reported.',
                'Three municipal complaints and community verification on SoilnWater.',
                'Maintain quarterly inspection of all covers on this stretch.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '10-100 People',
                'community_issue_affected_groups' => ['Residents', 'Students', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(50)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Municipal Corporation',
                'community_issue_already_reported' => 'yes',
                'community_issue_complaint_number' => 'Noida-SEC62-441',
                'community_issue_complaint_date' => now()->subDays(45)->toDateString(),
                'community_issue_department_contacted' => 'Noida Authority — Maintenance',
                'community_issue_suggested_solution' => 'Locking covers and a resident watch list for the next 90 days.',
                'community_issue_support_requests' => ['Community Feedback', 'Awareness'],
                'community_issue_status_tracker' => 'Resolved',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(50)->format('d M').' - Reported',
                    now()->subDays(42)->format('d M').' - Community Verified',
                    now()->subDays(30)->format('d M').' - Forwarded to Noida Authority',
                    now()->subDays(18)->format('d M').' - Repair Work Started',
                    now()->subDays(12)->format('d M').' - Resolved',
                ]),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-drain-open', 'open-drain-before.jpg'),
                    $this->externalImage('ci-drain-fixed', 'repaired-drain-after.jpg'),
                ],
                'community_issue_documents' => [
                    $this->externalDocument('municipal-closure-notice-sector62.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function hyderabadEscalatedSafetyHazard(): array
    {
        return [
            'slug' => 'community-issue-hyderabad-unsafe-footbridge',
            'author' => 'reporter',
            'category' => 'Public Safety',
            'issue_type' => 'Community Alert',
            'severity' => 'Emergency',
            'title' => 'Cracked Footbridge Near HITEC City Puts Commuters at Immediate Risk',
            'excerpt' => 'Visible cracks and missing railing sections force hundreds of IT workers to use an unsafe crossing daily.',
            'featured_image' => 'https://picsum.photos/seed/ci-footbridge/960/540',
            'tags' => ['Public Safety', 'HITEC City', 'Infrastructure', 'Hyderabad'],
            'days_ago' => 2,
            'escalation_threshold' => 10,
            'location' => $this->location('HITEC City, Hyderabad, Telangana, India', 'India', 'Telangana', 'Rangareddy', 'Hyderabad', 'Madhapur', 17.4435000, 78.3772000, 'Near Cyber Towers junction'),
            'engagement' => ['supports' => 14, 'agreements' => 10, 'follows' => 8],
            'body' => $this->structuredBody(
                'Pedestrian bridge shows structural cracks with broken railing on the east side.',
                'Cracks widened after heavy rains last week.',
                'IT employees, delivery workers, and bus commuters crossing daily.',
                'Immediate fall risk and crowd bottlenecks during rush hour.',
                'GHMC emergency line called. Area cordoned with tape by residents.',
                'Close bridge for inspection, deploy temporary shuttle crossing, and rebuild within 30 days.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '500+',
                'community_issue_affected_groups' => ['Residents', 'Businesses', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(8)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Municipal Corporation',
                'community_issue_suggested_solution' => 'Immediate structural audit and temporary barrier until rebuild is complete.',
                'community_issue_support_requests' => ['Authority Attention', 'Awareness', 'Volunteers'],
                'community_issue_status_tracker' => 'Forwarded to Authority',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(8)->format('d M').' - Reported',
                    now()->subDays(5)->format('d M').' - Community Verified',
                    now()->subDays(2)->format('d M').' - Forwarded to GHMC',
                ]),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-bridge-crack', 'cracked-footbridge.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bengaluruLakePollution(): array
    {
        return [
            'slug' => 'community-issue-bengaluru-lake-foam-pollution',
            'author' => 'green',
            'category' => 'Environmental Issues',
            'issue_type' => 'Public Concern',
            'severity' => 'High',
            'title' => 'Toxic Foam and Plastic Waste Choking Bellandur Lake Edge',
            'excerpt' => 'Sewage inflow and dumping are degrading the lake edge used by morning walkers and bird watchers.',
            'featured_image' => 'https://picsum.photos/seed/ci-lake-pollution/960/540',
            'tags' => ['Environmental Issues', 'Bellandur', 'Pollution', 'Bengaluru'],
            'days_ago' => 10,
            'location' => $this->location('Bellandur, Bengaluru, Karnataka, India', 'India', 'Karnataka', 'Bengaluru Urban', 'Bengaluru', 'Bellandur', 12.9352000, 77.6784000, 'Lake viewing deck'),
            'engagement' => ['supports' => 5, 'agreements' => 4, 'follows' => 5],
            'body' => $this->structuredBody(
                'Foam layers and plastic waste accumulate along the northern edge of the lake.',
                'Visible worsening over the last two monsoon cycles.',
                'Walkers, cyclists, apartment residents, and local birding groups.',
                'Foul odour, skin irritation reports, and declining bird habitat.',
                'Lake development authority emailed with geo-tagged photos.',
                'Stop sewage inflow, weekly cleanup drives, and fencing vulnerable entry points.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => 'Entire Community',
                'community_issue_affected_groups' => ['Residents', 'General Public', 'Tourists'],
                'community_issue_first_noticed_on' => now()->subDays(120)->toDateString(),
                'community_issue_is_recurring' => 'yes',
                'community_issue_frequency' => 'Monthly',
                'community_issue_authority' => 'District Administration',
                'community_issue_suggested_solution' => 'Interceptor drains and community cleanup roster with ward office support.',
                'community_issue_support_requests' => ['Volunteers', 'Awareness', 'Funding Support'],
                'community_issue_status_tracker' => 'Partially Resolved',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(120)->format('d M').' - Reported',
                    now()->subDays(90)->format('d M').' - Community Verified',
                    now()->subDays(60)->format('d M').' - Forwarded to Authority',
                    now()->subDays(20)->format('d M').' - Cleanup Work Started',
                    now()->subDays(10)->format('d M').' - Partially Resolved',
                ]),
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-lake-foam', 'lake-foam-pollution.jpg'),
                    $this->externalImage('ci-lake-plastic', 'plastic-waste-shore.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function chennaiPublicTransportIssue(): array
    {
        return [
            'slug' => 'community-issue-chennai-bus-shelter-damage',
            'author' => 'reporter',
            'category' => 'Public Transport',
            'issue_type' => 'Suggestion for Improvement',
            'severity' => 'Low',
            'title' => 'Damaged Bus Shelter on OMR Leaves Commuters in the Rain',
            'excerpt' => 'A collapsed roof panel at the Thoraipakkam stop offers no protection during sudden showers.',
            'featured_image' => 'https://picsum.photos/seed/ci-bus-shelter/960/540',
            'tags' => ['Public Transport', 'OMR', 'Chennai', 'Infrastructure'],
            'days_ago' => 15,
            'location' => $this->location('OMR Thoraipakkam, Chennai, Tamil Nadu, India', 'India', 'Tamil Nadu', 'Chennai', 'Chennai', 'Thoraipakkam', 12.9451000, 80.2394000, 'Opposite TIDEL Park gate'),
            'engagement' => ['supports' => 2, 'agreements' => 2, 'follows' => 1],
            'body' => $this->structuredBody(
                'Bus shelter roof panel is broken and seating is rusted through.',
                'Damage noticed after cyclone-season winds last month.',
                'Office commuters and students waiting for MTC buses.',
                'Exposure to sun and rain; elderly passengers cannot stand for long waits.',
                'MTC online grievance submitted.',
                'Replace shelter structure and add route information board.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '10-100 People',
                'community_issue_affected_groups' => ['Residents', 'Students', 'General Public'],
                'community_issue_first_noticed_on' => now()->subDays(32)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Municipal Corporation',
                'community_issue_suggested_solution' => 'Modular shelter replacement within 15 days.',
                'community_issue_support_requests' => ['Community Feedback', 'Authority Attention'],
                'community_issue_status_tracker' => 'Closed',
                'community_issue_resolution_timeline' => implode("\n", [
                    now()->subDays(32)->format('d M').' - Reported',
                    now()->subDays(20)->format('d M').' - Forwarded to MTC',
                    now()->subDays(15)->format('d M').' - Closed after temporary canopy installed',
                ]),
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kolkataAnonymousElectricityComplaint(): array
    {
        return [
            'slug' => 'community-issue-kolkata-transformer-hazard',
            'author' => 'reporter',
            'category' => 'Electricity',
            'issue_type' => 'Safety Hazard',
            'severity' => 'High',
            'title' => 'Exposed Transformer Wiring Poses Fire Risk in Salt Lake Block',
            'excerpt' => 'Live wiring hangs at head height near a school lane — reported anonymously to protect the whistleblower.',
            'featured_image' => 'https://picsum.photos/seed/ci-transformer/960/540',
            'tags' => ['Electricity', 'Salt Lake', 'Safety', 'Kolkata'],
            'days_ago' => 8,
            'publish_as' => CommunityPost::PUBLISH_AS_ANONYMOUS,
            'visibility' => 'public',
            'location' => $this->location('Salt Lake Sector V, Kolkata, West Bengal, India', 'India', 'West Bengal', 'Kolkata', 'Kolkata', 'Salt Lake', 22.5726000, 88.4339000, 'Near Techno India lane'),
            'engagement' => ['supports' => 3, 'agreements' => 4, 'follows' => 2],
            'body' => $this->structuredBody(
                'Damaged insulation leaves low-hanging cables beside a transformer box.',
                'Observed for at least ten days without repair.',
                'School children, tuition students, and evening market shoppers.',
                'Fire and electrocution risk during wet weather.',
                'Anonymous call to electricity helpline. Awaiting reference number.',
                'Immediate power shutdown for repair and relocation of box away from footpath.'
            ),
            'meta' => $this->commonMeta([
                'community_issue_affected_population' => '100-500 People',
                'community_issue_affected_groups' => ['Students', 'Children', 'Residents'],
                'community_issue_first_noticed_on' => now()->subDays(10)->toDateString(),
                'community_issue_is_recurring' => 'no',
                'community_issue_authority' => 'Electricity Department',
                'community_issue_already_reported' => 'yes',
                'community_issue_department_contacted' => 'WBSEDCL — Bidhannagar division',
                'community_issue_suggested_solution' => 'Barricade area and complete rewiring within 24 hours.',
                'community_issue_support_requests' => ['Authority Attention', 'Awareness'],
                'community_issue_status_tracker' => 'Reported',
                'community_issue_photo_evidence' => [
                    $this->externalImage('ci-transformer-wire', 'exposed-wiring.jpg'),
                ],
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function commonMeta(array $extra): array
    {
        return $extra;
    }

    private function structuredBody(
        string $issue,
        string $when,
        string $who,
        string $impact,
        string $actions,
        string $solution,
    ): string {
        return <<<HTML
<h2>What is the Issue?</h2>
<p>{$issue}</p>
<h2>When Did It Start?</h2>
<p>{$when}</p>
<h2>Who Is Affected?</h2>
<p>{$who}</p>
<h2>What Is the Impact?</h2>
<p>{$impact}</p>
<h2>What Action Has Been Taken So Far?</h2>
<p>{$actions}</p>
<h2>Suggested Solution</h2>
<p>{$solution}</p>
HTML;
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, locality: string, lat: float, lng: float, landmark?: string}
     */
    private function location(
        string $label,
        string $country,
        string $state,
        string $district,
        string $city,
        string $locality,
        float $lat,
        float $lng,
        ?string $landmark = null,
    ): array {
        return [
            'label' => $label,
            'country' => $country,
            'state' => $state,
            'district' => $district,
            'city' => $city,
            'locality' => $locality,
            'lat' => $lat,
            'lng' => $lng,
            'landmark' => $landmark,
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalImage(string $seed, string $name): array
    {
        return [
            'path' => 'seeders/community-issues/'.$seed.'.jpg',
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
            'path' => 'seeders/community-issues/'.$name,
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'name' => $name,
            'type' => 'application/pdf',
        ];
    }
}

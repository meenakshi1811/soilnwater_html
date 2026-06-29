<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityEnvironmentPostSeeder extends Seeder
{
    public function run(): void
    {
        $ecoVolunteer = $this->user('Eco Volunteer Priya', 'eco-volunteer@example.com');
        $waterWarrior = $this->user('Water Warrior Ravi', 'env-water-warrior@example.com');
        $greenChampion = $this->user('Green Champion Ananya', 'env-green-champion@example.com');
        $riverProtector = $this->user('River Protector Mohan', 'river-protector@example.com');
        $climateAdvocate = $this->user('Climate Advocate Neha', 'climate-advocate@example.com');

        foreach ($this->environmentPosts() as $post) {
            $author = match ($post['author'] ?? 'priya') {
                'ravi' => $waterWarrior,
                'ananya' => $greenChampion,
                'mohan' => $riverProtector,
                'neha' => $climateAdvocate,
                default => $ecoVolunteer,
            };

            $this->upsertPost($author, $post);
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
        $postType = $post['post_type'];

        $meta = array_merge([
            'author_bio' => $post['author_bio'] ?? 'Environmental champion sharing geo-tagged conservation stories on SoilnWater.',
            'environment_post_type' => $postType,
            'environment_category' => $category,
            'location_country' => $location['country'],
            'location_state' => $location['state'],
            'location_district' => $location['district'],
            'location_city' => $location['city'],
            'location_locality' => $location['locality'] ?? null,
            'environment_natural_feature_name' => $location['natural_feature'] ?? null,
            'environment_map_pin_type' => $location['map_pin_type'] ?? null,
            'environment_show_on_green_map' => $post['show_on_green_map'] ?? true,
            'environment_enable_green_leader' => $post['enable_green_leader'] ?? false,
            'environment_enable_impact_calculator' => $post['enable_impact_calculator'] ?? false,
            'environment_allow_join_campaign' => $post['allow_join_campaign'] ?? true,
            'environment_allow_volunteer' => $post['allow_volunteer'] ?? true,
            'environment_allow_donate' => $post['allow_donate'] ?? false,
            'environment_allow_support_initiative' => $post['allow_support_initiative'] ?? true,
            'environment_allow_follow_campaign' => $post['allow_follow_campaign'] ?? true,
            'environment_allow_volunteer_registration' => $post['allow_volunteer_registration'] ?? true,
            'environment_participation_requests' => $post['participation_requests'] ?? ['Join Campaign', 'Become Volunteer'],
        ], $post['meta'] ?? []);

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'environment',
                'category' => $category,
                'writing_purpose' => $postType,
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
     * @return list<array<string, mixed>>
     */
    private function environmentPosts(): array
    {
        return [
            $this->rainwaterHarvestingJaipur(),
            $this->yamunaPollutionIssue(),
            $this->dehradunTreePlantationDrive(),
            $this->plasticCleanupInitiative(),
            $this->jalJeevanMissionScheme(),
            $this->soilConservationTerracing(),
            $this->keoladeoBiodiversitySurvey(),
            $this->communityCompostingWaste(),
            $this->climateFloodAwareness(),
            $this->chambalRiverResearchReport(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rainwaterHarvestingJaipur(): array
    {
        return [
            'slug' => 'environment-rainwater-harvesting-jaipur-schools',
            'author' => 'ravi',
            'post_type' => 'Water Conservation Activity',
            'category' => 'Water Conservation',
            'title' => 'Rainwater Harvesting in 12 Jaipur Schools Saves 1.2 Million Litres Annually',
            'excerpt' => 'Recharge pits and rooftop collection across Malviya Nagar schools cut tanker dependency and revived two dry borewells.',
            'featured_image' => 'https://picsum.photos/seed/env-rainwater-jaipur/960/540',
            'tags' => ['Environment', 'Water', 'Rainwater Harvesting', 'Conservation', 'Jaipur'],
            'days_ago' => 2,
            'enable_impact_calculator' => true,
            'enable_green_leader' => true,
            'allow_poll' => true,
            'participation_requests' => ['Join Campaign', 'Donate Equipment', 'Become Volunteer'],
            'location' => $this->location(
                'Malviya Nagar, Jaipur, Rajasthan, India',
                'India', 'Rajasthan', 'Jaipur', 'Malviya Nagar',
                'Malviya Nagar Ward 42',
                26.8546000, 75.8243000,
                naturalFeature: null,
                mapPinType: 'Pond',
            ),
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'body' => $this->environmentBody(
                'Jaipur receives erratic monsoon rainfall while groundwater tables in Malviya Nagar have declined sharply over the past decade.',
                'Schools relied on daily tankers; playground borewells ran dry by March each year.',
                'Rooftop channels feed recharge pits; overflow routes to percolation tanks. Students monitor weekly water levels.',
                'Estimated 12 lakh litres captured annually; two borewells revived; tanker orders reduced by 60%.',
                'Sediment clogging in first monsoon required filter mesh upgrades.',
                'Replicate modular pit design in colonies; involve students in weekly monitoring logs.'
            ),
            'meta' => $this->baseMeta([
                'environment_water_source' => 'Rainwater',
                'environment_conservation_method' => 'Rainwater Harvesting',
                'environment_water_saved' => '12,00,000 litres per year',
                'environment_data_trees_planted' => '0',
                'environment_data_water_saved' => '12,00,000 litres',
                'environment_data_people_participated' => '340 students + 28 teachers',
                'environment_data_area_covered' => '1.8 hectares (school campuses)',
                'environment_video_type' => 'Awareness Campaign',
                'environment_ask_community' => 'Has anyone implemented rainwater harvesting successfully in a residential colony near Jaipur?',
                'environment_poll_question' => 'Would you participate in a rainwater harvesting workshop?',
                'environment_poll_options' => CommunityContentTaxonomy::environmentDefaultPollOptions(),
                'environment_climate_impacts' => ['Drought'],
                'environment_gallery' => $this->fullGallery('env-rainwater'),
                'environment_documents' => [
                    $this->externalDocument('water-audit-report-jaipur-schools.pdf'),
                ],
                'environment_event_campaign_name' => 'Malviya Nagar School Water Champions',
                'environment_event_organizer' => 'Jaipur Municipal Corporation & SoilnWater Volunteers',
                'environment_event_venue' => 'Government Senior Secondary School, Malviya Nagar',
                'environment_event_date' => now()->subMonths(2)->toDateString(),
                'environment_event_time' => '9:00 AM',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function yamunaPollutionIssue(): array
    {
        return [
            'slug' => 'environment-yamuna-plastic-pollution-delhi',
            'author' => 'mohan',
            'post_type' => 'Environmental Issue',
            'category' => 'Water Pollution',
            'title' => 'Plastic Waste Choking Yamuna Banks Near ITO Bridge — Urgent Action Needed',
            'excerpt' => 'Industrial wrappers and single-use plastic accumulate after every monsoon surge; fish kills reported downstream.',
            'featured_image' => 'https://picsum.photos/seed/env-yamuna-pollution/960/540',
            'tags' => ['Environment', 'River', 'Plastic', 'Water Pollution', 'Delhi'],
            'days_ago' => 1,
            'participation_requests' => ['Volunteers Required', 'Join Campaign'],
            'location' => $this->location(
                'ITO Bridge, Yamuna River, Delhi, India',
                'India', 'Delhi', 'Central Delhi', 'New Delhi',
                'Near ITO Bridge, Ring Road',
                28.6289000, 77.2585000,
                naturalFeature: 'Yamuna River',
                mapPinType: 'River',
            ),
            'body' => $this->environmentBody(
                'The Yamuna stretch below ITO Bridge is a critical urban water body used by communities and migratory birds.',
                'After the last monsoon, plastic sheets and thermocol blocks formed a visible mat along 400 metres of bank.',
                'Local fishermen report reduced catch; water samples show elevated coliform levels near the waste belt.',
                'MCD cleanup drives removed 2 tonnes in one week but waste returns within days from upstream drains.',
                'No sustained enforcement on plastic dumping; night-time industrial discharge continues.',
                'Install CCTV at drain mouths; schedule weekly community clean-ups; ban single-use plastic vendors on ghats.'
            ),
            'meta' => $this->baseMeta([
                'environment_issue_type' => 'Plastic Pollution',
                'environment_waste_types' => ['Plastic Waste', 'Industrial Pollution'],
                'environment_climate_impacts' => ['Flood', 'Heavy Rainfall'],
                'environment_ask_community' => 'How can we reduce plastic waste in our locality before it reaches the river?',
                'environment_gallery' => [
                    'pollution_evidence' => [
                        $this->externalImage('env-yamuna-plastic-1', 'plastic-mat-riverbank.jpg'),
                        $this->externalImage('env-yamuna-plastic-2', 'drain-outfall-waste.jpg'),
                    ],
                    'river' => [
                        $this->externalImage('env-yamuna-river-1', 'yamuna-bank-wide.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dehradunTreePlantationDrive(): array
    {
        return [
            'slug' => 'environment-tree-plantation-drive-dehradun',
            'author' => 'ananya',
            'post_type' => 'Tree Plantation Drive',
            'category' => 'Tree Plantation',
            'title' => '1,500 Native Trees Planted Along Prem Nagar Ridge in Dehradun',
            'excerpt' => 'NGO-school partnership restored green cover on a degraded slope; 78% sapling survival after first monsoon.',
            'featured_image' => 'https://picsum.photos/seed/env-tree-plantation-ddn/960/540',
            'tags' => ['Environment', 'Trees', 'Tree Plantation', 'Dehradun', 'Conservation'],
            'days_ago' => 5,
            'enable_impact_calculator' => true,
            'enable_green_leader' => true,
            'allow_poll' => true,
            'participation_requests' => ['Join Campaign', 'Donate Plants', 'Become Volunteer'],
            'location' => $this->location(
                'Prem Nagar, Dehradun, Uttarakhand, India',
                'India', 'Uttarakhand', 'Dehradun', 'Prem Nagar',
                'Ridge slope behind DAV School',
                30.3255000, 78.0437000,
                naturalFeature: 'Prem Nagar Ridge',
                mapPinType: 'Plantation Area',
            ),
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'video_id' => '9bZkp7q19f0',
            ],
            'body' => $this->environmentBody(
                'Prem Nagar ridge lost tree cover to illegal cutting and landslides between 2018 and 2023.',
                'Bare slopes increased soil erosion and heat island effect in adjoining colonies.',
                'Volunteers planted 1,500 saplings of neem, peepal, and buransh with drip-supported watering for first summer.',
                '78% survival after first monsoon; bird sightings increased; local temperature dropped measurably in shade pockets.',
                'Summer watering labour cost remains high without community roster.',
                'Adopt-a-tree programme for residents; schedule quarterly survival audits.'
            ),
            'meta' => $this->baseMeta([
                'environment_initiative_type' => 'Tree Plantation',
                'environment_tree_count' => 1500,
                'environment_tree_species' => 'Neem, Peepal, Buransh (Rhododendron)',
                'environment_tree_plantation_date' => now()->subMonths(4)->toDateString(),
                'environment_tree_organization' => 'Green Doon Foundation & DAV Prem Nagar',
                'environment_tree_survival_status' => 'Good',
                'environment_tree_maintenance_plan' => 'Weekly watering April–June; mulching in October; quarterly survival census.',
                'environment_data_trees_planted' => '1,500',
                'environment_data_people_participated' => '220 volunteers',
                'environment_data_area_covered' => '3.2 hectares',
                'environment_data_carbon_reduction' => 'Estimated 18 tonnes CO₂ over 10 years',
                'environment_video_type' => 'Tree Plantation',
                'environment_poll_question' => 'Would you participate in a tree plantation drive?',
                'environment_poll_options' => CommunityContentTaxonomy::environmentDefaultPollOptions(),
                'environment_event_campaign_name' => 'Green Prem Nagar Ridge Revival',
                'environment_event_organizer' => 'Green Doon Foundation',
                'environment_event_venue' => 'Prem Nagar Ridge, behind DAV School',
                'environment_event_date' => now()->subMonths(4)->toDateString(),
                'environment_event_time' => '6:30 AM',
                'environment_event_registration_link' => 'https://example.com/green-doon-register',
                'environment_gallery' => [
                    'plantation' => [
                        $this->externalImage('env-plant-1', 'volunteers-planting-saplings.jpg'),
                        $this->externalImage('env-plant-2', 'ridge-after-planting.jpg'),
                    ],
                    'before_after' => [
                        $this->externalImage('env-plant-before', 'bare-ridge-before.jpg'),
                        $this->externalImage('env-plant-after', 'green-ridge-after.jpg'),
                    ],
                    'community_activities' => [
                        $this->externalImage('env-plant-community', 'school-students-planting.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function plasticCleanupInitiative(): array
    {
        return [
            'slug' => 'environment-plastic-cleanup-river-initiative',
            'author' => 'priya',
            'post_type' => 'Community Initiative',
            'category' => 'Plastic-Free Campaign',
            'title' => 'Weekend Plastic Collection Drive Removes 800 kg from Chambal Ghats',
            'excerpt' => 'Fishermen, students, and ward councillors joined a coordinated clean-up with segregation at source.',
            'featured_image' => 'https://picsum.photos/seed/env-plastic-cleanup/960/540',
            'tags' => ['Environment', 'Plastic', 'River', 'Community', 'Conservation'],
            'days_ago' => 3,
            'enable_impact_calculator' => true,
            'participation_requests' => ['Volunteers Required', 'Join Campaign', 'Become Volunteer'],
            'location' => $this->location(
                'Chambal River Ghat, Kota, Rajasthan, India',
                'India', 'Rajasthan', 'Kota', 'Kota',
                'Chambal River Ghat, Dadabari',
                25.1200000, 75.8500000,
                naturalFeature: 'Chambal River',
                mapPinType: 'River',
            ),
            'body' => $this->environmentBody(
                'Chambal ghats attract pilgrims and tourists but lack consistent waste management infrastructure.',
                'Plastic bottles and food wrappers accumulated along 1.2 km of ghat steps after festival season.',
                '120 volunteers collected, segregated, and sent waste to recycling partners; awareness stalls distributed cloth bags.',
                '800 kg plastic diverted from river; local municipality committed to weekly bin placement.',
                'Recycling partner capacity limited; some mixed waste still rejected.',
                'Install permanent segregation bins; run monthly drives; ban single-use plastic vendors during festivals.'
            ),
            'meta' => $this->baseMeta([
                'environment_initiative_type' => 'Plastic Collection',
                'environment_waste_types' => ['Plastic Waste', 'Recycling'],
                'environment_data_waste_collected' => '800 kg',
                'environment_data_people_participated' => '120',
                'environment_ask_community' => 'How can we reduce plastic waste in our locality?',
                'environment_gallery' => [
                    'community_activities' => [
                        $this->externalImage('env-cleanup-1', 'volunteers-collecting-plastic.jpg'),
                    ],
                    'river' => [
                        $this->externalImage('env-cleanup-river', 'chambal-ghat-cleanup.jpg'),
                    ],
                    'before_after' => [
                        $this->externalImage('env-cleanup-before', 'ghat-before-cleanup.jpg'),
                        $this->externalImage('env-cleanup-after', 'ghat-after-cleanup.jpg'),
                    ],
                ],
                'environment_event_campaign_name' => 'Clean Chambal Weekend',
                'environment_event_organizer' => 'Kota Eco Volunteers',
                'environment_event_venue' => 'Chambal Ghat, Dadabari',
                'environment_event_date' => now()->subDays(10)->toDateString(),
                'environment_event_time' => '7:00 AM',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function jalJeevanMissionScheme(): array
    {
        return [
            'slug' => 'environment-jal-jeevan-mission-rural-water',
            'author' => 'ravi',
            'post_type' => 'Government Scheme',
            'category' => 'Water Conservation',
            'title' => 'Jal Jeevan Mission: Tap Water to Every Rural Household — Eligibility Guide',
            'excerpt' => 'How village water and sanitation committees can verify FHTC connections and report pipeline defects.',
            'featured_image' => 'https://picsum.photos/seed/env-jal-jeevan/960/540',
            'tags' => ['Environment', 'Water', 'Government Scheme', 'Conservation'],
            'days_ago' => 7,
            'location' => $this->location(
                'Bundi, Rajasthan, India',
                'India', 'Rajasthan', 'Bundi', 'Bundi',
                'Village Talera',
                25.4380000, 75.6400000,
            ),
            'body' => $this->environmentBody(
                'Jal Jeevan Mission aims to provide functional household tap connections to every rural home.',
                'Many villages in Bundi district still report intermittent supply and unmetered standposts.',
                'Compiled VWSC registration steps, grievance portal links, and field verification checklist.',
                'Talera village achieved 94% FHTC coverage after VWSC activation and leakage repair.',
                'Delay in contractor response for pipeline breaks remains a bottleneck.',
                'Form active VWSC; photograph every new connection; use JJM dashboard for defect logging.'
            ),
            'meta' => $this->baseMeta([
                'environment_scheme_name' => 'Jal Jeevan Mission',
                'environment_scheme_department' => 'Ministry of Jal Shakti',
                'environment_scheme_eligibility' => 'All rural households in eligible villages; VWSC registration required for community oversight.',
                'environment_scheme_benefits' => 'Functional household tap connection with 55 lpcd norm; reduced drudgery for women; improved health outcomes.',
                'environment_scheme_official_link' => 'https://jaljeevanmission.gov.in/',
                'environment_water_source' => 'Borewell',
                'environment_conservation_method' => 'Recharge Pit',
                'environment_documents' => [
                    $this->externalDocument('government-notification-jal-jeevan.pdf'),
                    $this->externalDocument('water-report-bundi-district.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function soilConservationTerracing(): array
    {
        return [
            'slug' => 'environment-soil-conservation-terracing-tehri',
            'author' => 'ananya',
            'post_type' => 'Success Story',
            'category' => 'Soil Conservation',
            'title' => 'Contour Terracing Stopped Hillside Erosion in Tehri Garhwal',
            'excerpt' => 'Farmers and forest department jointly built stone bunds and green cover on a 4-hectare slope.',
            'featured_image' => 'https://picsum.photos/seed/env-soil-terrace/960/540',
            'tags' => ['Environment', 'Soil', 'Conservation', 'Uttarakhand', 'Climate'],
            'days_ago' => 9,
            'enable_impact_calculator' => true,
            'location' => $this->location(
                'Chamba, Tehri Garhwal, Uttarakhand, India',
                'India', 'Uttarakhand', 'Tehri Garhwal', 'Chamba',
                'Upper Kempty slope',
                30.4133000, 78.3203000,
                mapPinType: 'Forest',
            ),
            'body' => $this->environmentBody(
                'Steep terraced farms above Chamba lost topsoil every monsoon, silting village paths below.',
                'Landslide scars widened each year; maize yields fell as fertile layer washed away.',
                'Contour stone bunds, vetiver grass strips, and mulched fallow between crop rows.',
                'Soil loss reduced an estimated 70%; maize yields recovered within two seasons.',
                'Labour cost for stone bund maintenance peaks before monsoon.',
                'Combine terracing with agroforestry on upper slopes; share equipment across hamlets.'
            ),
            'meta' => $this->baseMeta([
                'environment_soil_conservation_methods' => ['Terracing', 'Contour Farming', 'Mulching', 'Green Cover', 'Erosion Control'],
                'environment_climate_impacts' => ['Landslide', 'Heavy Rainfall'],
                'environment_data_area_covered' => '4 hectares',
                'environment_data_people_participated' => '45 farming families',
                'environment_gallery' => [
                    'before_after' => [
                        $this->externalImage('env-terrace-before', 'eroded-slope-before.jpg'),
                        $this->externalImage('env-terrace-after', 'terraced-slope-after.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function keoladeoBiodiversitySurvey(): array
    {
        return [
            'slug' => 'environment-biodiversity-survey-keoladeo',
            'author' => 'neha',
            'post_type' => 'Biodiversity Documentation',
            'category' => 'Biodiversity',
            'title' => 'Winter Bird Census at Keoladeo Records 42 Species Including Rare Siberian Visitors',
            'excerpt' => 'Citizen scientists and BNHS volunteers documented migratory patterns and wetland health indicators.',
            'featured_image' => 'https://picsum.photos/seed/env-keoladeo-birds/960/540',
            'tags' => ['Environment', 'Biodiversity', 'Wildlife', 'Wetlands', 'Conservation'],
            'days_ago' => 11,
            'enable_green_leader' => true,
            'location' => $this->location(
                'Keoladeo National Park, Bharatpur, Rajasthan, India',
                'India', 'Rajasthan', 'Bharatpur', 'Bharatpur',
                'Keoladeo Ghana entrance',
                27.1592000, 77.5195000,
                naturalFeature: 'Keoladeo Wetland',
                mapPinType: 'Lake',
            ),
            'body' => $this->environmentBody(
                'Keoladeo Ghana is a Ramsar wetland critical for migratory waterbirds and local livelihoods.',
                'Water levels fluctuated due to upstream canal allocation disputes affecting habitat quality.',
                'Three-day citizen science census with BNHS protocols; habitat zones mapped by GPS.',
                '42 species recorded including bar-headed goose and painted stork; two rare sightings logged.',
                'Incomplete night survey data; some zones inaccessible due to low water.',
                'Continue monthly monitoring; advocate equitable water release for wetland maintenance.'
            ),
            'meta' => $this->baseMeta([
                'environment_biodiversity_types' => ['Birds', 'Wetland Species', 'Rare Species', 'Animals'],
                'environment_data_species_recorded' => '42',
                'environment_ask_community' => 'Which native tree species should we plant to support wetland bird habitat?',
                'environment_gallery' => [
                    'wildlife' => [
                        $this->externalImage('env-bird-1', 'bar-headed-goose.jpg'),
                        $this->externalImage('env-bird-2', 'painted-stork-flock.jpg'),
                    ],
                ],
                'environment_documents' => [
                    $this->externalDocument('environmental-survey-keoladeo-2026.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function communityCompostingWaste(): array
    {
        return [
            'slug' => 'environment-community-composting-bangalore',
            'author' => 'priya',
            'post_type' => 'Waste Management Initiative',
            'category' => 'Waste Management',
            'title' => 'Neighbourhood Composting Unit Diverts 2 Tonnes of Organic Waste Monthly',
            'excerpt' => 'Koramangala RWAs set up aerobic composting with weekly collection and soil amendment distribution.',
            'featured_image' => 'https://picsum.photos/seed/env-compost-blr/960/540',
            'tags' => ['Environment', 'Waste Management', 'Composting', 'Urban Environment'],
            'days_ago' => 4,
            'enable_impact_calculator' => true,
            'location' => $this->location(
                'Koramangala, Bengaluru, Karnataka, India',
                'India', 'Karnataka', 'Bengaluru Urban', 'Bengaluru',
                'Koramangala 4th Block',
                12.9352000, 77.6245000,
                mapPinType: 'Dump Site',
            ),
            'body' => $this->environmentBody(
                'Koramangala generates high organic waste volumes with limited landfill capacity nearby.',
                'Mixed waste collection led to methane odours and pest problems near community parks.',
                'Installed three-bin aerobic composters; trained households on segregation; weekly pick-up roster.',
                '2 tonnes organic waste composted monthly; 180 households participating; garden soil distributed free.',
                'Apartment towers still send mixed waste; need stricter BBMP enforcement.',
                'Start with one street pilot; publish monthly impact dashboard; partner with local nurseries for compost use.'
            ),
            'meta' => $this->baseMeta([
                'environment_waste_types' => ['Organic Waste', 'Composting', 'Recycling'],
                'environment_data_waste_collected' => '2,000 kg per month',
                'environment_data_people_participated' => '180 households',
                'environment_gallery' => [
                    'community_activities' => [
                        $this->externalImage('env-compost-1', 'composting-unit-volunteers.jpg'),
                    ],
                    'before_after' => [
                        $this->externalImage('env-compost-before', 'mixed-waste-before.jpg'),
                        $this->externalImage('env-compost-after', 'compost-ready-after.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function climateFloodAwareness(): array
    {
        return [
            'slug' => 'environment-climate-flood-awareness-assam',
            'author' => 'neha',
            'post_type' => 'Climate Awareness',
            'category' => 'Climate Change',
            'title' => 'Preparing Riverine Villages for Early Flood Season — Community Action Plan',
            'excerpt' => 'Early warning drills, elevated grain storage, and mangrove buffer restoration along the Brahmaputra belt.',
            'featured_image' => 'https://picsum.photos/seed/env-flood-assam/960/540',
            'tags' => ['Environment', 'Climate', 'Flood', 'Conservation', 'Community'],
            'days_ago' => 6,
            'allow_donate' => true,
            'location' => $this->location(
                'Majuli River Island, Assam, India',
                'India', 'Assam', 'Majuli', 'Majuli',
                'Kamalabari Satra vicinity',
                26.9595000, 94.2185000,
                naturalFeature: 'Brahmaputra River',
                mapPinType: 'River',
            ),
            'body' => $this->environmentBody(
                'Majuli island communities face earlier flood peaks as Himalayan melt and rainfall intensity increase.',
                '2024 floods damaged 120 homes and submerged paddy fields weeks before historical peak dates.',
                'Village disaster committees ran evacuation drills; grain stored on raised platforms; youth planted mangrove saplings on eroding banks.',
                'Zero casualties in January flash flood; 400 saplings survived first season.',
                'Early warning SMS network still incomplete for remote hamlets.',
                'Expand community flood shelters; document traditional flood markers; link with IMD district alerts.'
            ),
            'meta' => $this->baseMeta([
                'environment_climate_impacts' => ['Flood', 'Heavy Rainfall', 'Cyclone'],
                'environment_initiative_type' => 'School Awareness Program',
                'environment_participation_requests' => ['Volunteers Required', 'Donate Equipment', 'Join Campaign'],
                'environment_event_campaign_name' => 'Majuli Flood Ready 2026',
                'environment_event_organizer' => 'Majuli District Disaster Management & NGOs',
                'environment_event_venue' => 'Kamalabari Community Hall',
                'environment_event_date' => now()->addWeeks(2)->toDateString(),
                'environment_event_time' => '10:00 AM',
                'environment_gallery' => [
                    'community_activities' => [
                        $this->externalImage('env-flood-drill', 'flood-evacuation-drill.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function chambalRiverResearchReport(): array
    {
        return [
            'slug' => 'environment-chambal-water-quality-research',
            'author' => 'mohan',
            'post_type' => 'Research Findings',
            'category' => 'River Conservation',
            'title' => 'Chambal Tributary Water Quality Study — Nitrate Trends After Mining Closures',
            'excerpt' => 'University researchers tracked 18 sampling points for two years post illegal mining crackdown.',
            'featured_image' => 'https://picsum.photos/seed/env-chambal-research/960/540',
            'tags' => ['Environment', 'River', 'Water', 'Research', 'Conservation'],
            'days_ago' => 14,
            'location' => $this->location(
                'National Chambal Sanctuary, Madhya Pradesh, India',
                'India', 'Madhya Pradesh', 'Morena', 'Morena',
                'Parvati River confluence',
                26.5020000, 77.9900000,
                naturalFeature: 'Chambal River',
                mapPinType: 'River',
            ),
            'body' => $this->environmentBody(
                'The Chambal sanctuary supports gharial, gangetic dolphin, and migratory birds dependent on clean water.',
                'Illegal sand mining raised turbidity and altered channel morphology through the early 2020s.',
                'Bi-monthly sampling at 18 GPS-tagged points; lab analysis for nitrates, phosphates, and coliform.',
                'Nitrate levels dropped 34% at upstream points two years after enforcement intensified.',
                'Agricultural runoff still elevates phosphates in monsoon; two downstream points remain impaired.',
                'Continue community watchdog reporting; restore riparian buffer strips on private farmland edges.'
            ),
            'meta' => $this->baseMeta([
                'environment_water_source' => 'River',
                'environment_issue_type' => 'Illegal Mining',
                'environment_climate_impacts' => ['Drought', 'Flood'],
                'environment_biodiversity_types' => ['Animals', 'Wetland Species', 'Rare Species'],
                'environment_data_species_recorded' => '12 aquatic indicator species',
                'environment_documents' => [
                    $this->externalDocument('research-paper-chambal-water-quality.pdf'),
                    $this->externalDocument('water-report-chambal-sanctuary.pdf'),
                    $this->externalDocument('environmental-survey-chambal-2025.pdf'),
                ],
                'environment_gallery' => [
                    'river' => [
                        $this->externalImage('env-chambal-1', 'chambal-river-sampling.jpg'),
                    ],
                    'wildlife' => [
                        $this->externalImage('env-gharial', 'gharial-sanctuary.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function baseMeta(array $extra): array
    {
        return $extra;
    }

    private function environmentBody(
        string $background,
        string $currentSituation,
        string $impact,
        string $actionsTaken,
        string $results,
        string $recommendations,
    ): string {
        return <<<HTML
<h2>Background</h2>
<p>{$background}</p>
<h2>Current Situation</h2>
<p>{$currentSituation}</p>
<h2>Environmental Impact</h2>
<p>{$impact}</p>
<h2>Actions Taken</h2>
<p>{$actionsTaken}</p>
<h2>Results</h2>
<p>{$results}</p>
<h2>Future Recommendations</h2>
<p>{$recommendations}</p>
HTML;
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, locality?: string, lat: float, lng: float, natural_feature?: string, map_pin_type?: string}
     */
    private function location(
        string $label,
        string $country,
        string $state,
        string $district,
        string $city,
        ?string $locality,
        float $lat,
        float $lng,
        ?string $naturalFeature = null,
        ?string $mapPinType = null,
    ): array {
        return array_filter([
            'label' => $label,
            'country' => $country,
            'state' => $state,
            'district' => $district,
            'city' => $city,
            'locality' => $locality,
            'lat' => $lat,
            'lng' => $lng,
            'natural_feature' => $naturalFeature,
            'map_pin_type' => $mapPinType,
        ], fn ($value) => $value !== null);
    }

    /**
     * @return array<string, list<array{path: string, url: string, name: string, type: string}>>
     */
    private function fullGallery(string $seed): array
    {
        return [
            'before_after' => [
                $this->externalImage($seed.'-before', 'site-before.jpg'),
                $this->externalImage($seed.'-after', 'site-after.jpg'),
            ],
            'plantation' => [
                $this->externalImage($seed.'-plant', 'plantation-activity.jpg'),
            ],
            'river' => [
                $this->externalImage($seed.'-river', 'river-context.jpg'),
            ],
            'community_activities' => [
                $this->externalImage($seed.'-community', 'community-volunteers.jpg'),
            ],
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalImage(string $seed, string $name): array
    {
        return [
            'path' => 'seeders/environment/'.$seed.'.jpg',
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
            'path' => 'seeders/environment/'.$name,
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'name' => $name,
            'type' => 'application/pdf',
        ];
    }
}

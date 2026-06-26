<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityAgriculturePostSeeder extends Seeder
{
    public function run(): void
    {
        $farmerRavi = $this->user('Farmer Ravi Singh', 'farmer-ravi@example.com');
        $agriExpert = $this->user('Dr. Meena Agarwal', 'agri-expert@example.com');
        $youngFarmer = $this->user('Young Farmer Aman', 'young-farmer@example.com');
        $agriEntrepreneur = $this->user('Agri Entrepreneur Priya', 'agri-entrepreneur@example.com');

        foreach ($this->agriculturePosts() as $post) {
            $author = match ($post['author'] ?? 'ravi') {
                'expert' => $agriExpert,
                'aman' => $youngFarmer,
                'priya' => $agriEntrepreneur,
                default => $farmerRavi,
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
        $shareType = $post['share_type'];

        $meta = array_merge([
            'author_bio' => $post['author_bio'] ?? 'Farmer and SoilnWater agriculture contributor sharing practical field experience.',
            'agriculture_share_type' => $shareType,
            'agriculture_category' => $category,
            'location_country' => $location['country'],
            'location_state' => $location['state'],
            'location_district' => $location['district'],
            'location_city' => $location['city'],
            'agriculture_climate_zone' => $location['climate_zone'] ?? null,
            'agriculture_soil_type' => $location['soil_type'] ?? null,
            'agriculture_enable_knowledge_exchange' => $post['enable_knowledge_exchange'] ?? true,
            'agriculture_enable_crop_doctor' => $post['enable_crop_doctor'] ?? false,
            'agriculture_target_audiences' => $post['target_audiences'] ?? ['Farmers', 'Agriculture Students', 'General Public'],
        ], $post['meta'] ?? []);

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'agriculture',
                'category' => $category,
                'writing_purpose' => $shareType,
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
    private function agriculturePosts(): array
    {
        return [
            $this->dripWheatUttarakhand(),
            $this->cropDoctorPestAttack(),
            $this->pmKisanScheme(),
            $this->wheatMandiPrices(),
            $this->mahindraTractorReview(),
            $this->dairyFarmHimachal(),
            $this->solarDryerInnovation(),
            $this->organicSeedBusiness(),
            $this->dripAdvisoryRajasthan(),
            $this->soilHealthResearch(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dripWheatUttarakhand(): array
    {
        return [
            'slug' => 'agriculture-drip-wheat-uttarakhand-success',
            'author' => 'ravi',
            'share_type' => 'Farming Experience',
            'category' => 'Crop Farming',
            'title' => 'How Drip Irrigation Doubled My Wheat Yield in Uttarakhand Hills',
            'excerpt' => 'A 3-acre terraced farm in Tehri moved from flood irrigation to drip lines — water use fell 40% and grain quality improved.',
            'featured_image' => 'https://picsum.photos/seed/agri-drip-wheat/960/540',
            'tags' => ['Wheat', 'Drip Irrigation', 'Water Conservation', 'Uttarakhand', 'Agriculture'],
            'days_ago' => 2,
            'allow_poll' => true,
            'location' => $this->location(
                'Tehri Garhwal, Uttarakhand, India',
                'India', 'Uttarakhand', 'Tehri Garhwal', 'Chamba',
                30.4133000, 78.3203000,
                'Sub-tropical hill', 'Loamy'
            ),
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'body' => $this->agricultureBody(
                'I farm 3 acres of terraced wheat on a mid-hill block near Chamba, Tehri Garhwal.',
                'Erratic canal supply and seepage losses were cutting yields every Rabi season.',
                'Installed pressure-compensating drip laterals, mulched furrows, and scheduled irrigation using a simple soil moisture check.',
                'Yield rose from 18 to 36 quintals per acre; water use dropped by roughly 40%.',
                'Initial filter clogging and slope pressure balancing took two seasons to perfect.',
                'Start with a pilot plot, invest in a good disc filter, and train family labour on line flushing.'
            ),
            'meta' => $this->baseCropMeta([
                'agriculture_crop_name' => 'Wheat',
                'agriculture_crop_variety' => 'HD-2967',
                'agriculture_sowing_date' => now()->subMonths(5)->toDateString(),
                'agriculture_harvest_date' => now()->subMonths(1)->toDateString(),
                'agriculture_growing_season' => 'Rabi',
                'agriculture_farm_size' => '1-5 Acres',
                'agriculture_farming_type' => 'Conventional',
                'agriculture_irrigation_method' => 'Drip Irrigation',
                'agriculture_water_source' => 'Canal',
                'agriculture_water_conservation_practices' => ['Drip Irrigation', 'Mulching', 'Contour Bunding'],
                'agriculture_soil_test_conducted' => 'yes',
                'agriculture_soil_ph' => '6.8',
                'agriculture_soil_organic_carbon' => '0.62%',
                'agriculture_soil_nitrogen' => '280 kg/ha',
                'agriculture_soil_phosphorus' => '18 kg/ha',
                'agriculture_soil_potassium' => '145 kg/ha',
                'agriculture_soil_recommendations' => 'Continue farmyard manure at 5 t/ha; split nitrogen into three doses after drip conversion.',
                'agriculture_weather_impact' => 'Normal Conditions',
                'agriculture_video_type' => 'Irrigation Setup',
                'agriculture_ask_community' => 'Has anyone compared HD-2967 with DBW-187 in similar hill terraces?',
                'agriculture_poll_question' => 'Which irrigation method do you use?',
                'agriculture_poll_options' => CommunityContentTaxonomy::agricultureDefaultPollOptions(),
                'agriculture_gallery' => $this->fullGallery('drip-wheat'),
                'agriculture_documents' => [
                    $this->externalDocument('soil-test-report-tehri.pdf'),
                    $this->externalDocument('crop-calendar-wheat-rabi.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cropDoctorPestAttack(): array
    {
        return [
            'slug' => 'agriculture-crop-doctor-aphid-wheat',
            'author' => 'aman',
            'share_type' => 'Problem & Solution',
            'category' => 'Crop Farming',
            'title' => 'Yellow Aphid Outbreak on Wheat — Seeking Expert Guidance',
            'excerpt' => 'Sticky leaves and stunted tillers appeared across 2 acres near Prem Nagar, Dehradun. Crop Doctor photos uploaded for community review.',
            'featured_image' => 'https://picsum.photos/seed/agri-aphid-wheat/960/540',
            'tags' => ['Wheat', 'Pest Attack', 'Crop Doctor', 'Dehradun', 'Organic Farming'],
            'days_ago' => 1,
            'enable_crop_doctor' => true,
            'location' => $this->location(
                'Prem Nagar, Dehradun, Uttarakhand, India',
                'India', 'Uttarakhand', 'Dehradun', 'Prem Nagar',
                30.3255000, 78.0437000,
                'Temperate', 'Alluvial Soil'
            ),
            'body' => $this->agricultureBody(
                'Two acres of irrigated wheat showed curling flag leaves and honeydew coating last week.',
                'Aphid colonies spread from field edge after a warm spell; neighbour plot already affected.',
                'Removed heavily infested tillers, sprayed neem oil 5% at evening, and released ladybird beetles on border rows.',
                'Spread slowed on 60% of plot; edge rows still need monitoring.',
                'Cannot decide whether to add a low-dose systemic or wait for natural predators.',
                'Requesting guidance on economic threshold and safe intervals before harvest.'
            ),
            'meta' => $this->baseCropMeta([
                'agriculture_crop_name' => 'Wheat',
                'agriculture_crop_variety' => 'DBW-187',
                'agriculture_sowing_date' => now()->subMonths(4)->toDateString(),
                'agriculture_growing_season' => 'Rabi',
                'agriculture_farm_size' => '1-5 Acres',
                'agriculture_farming_type' => 'Organic',
                'agriculture_problem_type' => 'Pest Attack',
                'agriculture_expert_assistance' => 'yes',
                'agriculture_problem_photos' => [
                    $this->externalImage('agri-aphid-1', 'aphid-damaged-leaves.jpg'),
                    $this->externalImage('agri-aphid-2', 'sticky-wheat-tillers.jpg'),
                    $this->externalImage('agri-aphid-3', 'field-edge-infestation.jpg'),
                ],
                'agriculture_ask_community' => 'Has anyone successfully controlled yellow aphids on wheat without harming beneficial insects?',
                'agriculture_weather_impact' => 'Heat Wave',
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pmKisanScheme(): array
    {
        return [
            'slug' => 'agriculture-pm-kisan-installment-guide',
            'author' => 'expert',
            'share_type' => 'Government Scheme',
            'category' => 'Government Schemes',
            'title' => 'PM-KISAN 18th Installment: Eligibility Checklist for Small Farmers',
            'excerpt' => 'Step-by-step guide for verifying land records, Aadhaar linking, and eKYC before the next disbursement window.',
            'featured_image' => 'https://picsum.photos/seed/agri-pm-kisan/960/540',
            'tags' => ['Government Scheme', 'PM-KISAN', 'Farmer Support', 'Agriculture'],
            'days_ago' => 5,
            'target_audiences' => ['Farmers', 'Government Officials', 'NGOs'],
            'location' => $this->location(
                'Jaipur, Rajasthan, India',
                'India', 'Rajasthan', 'Jaipur', 'Vaishali Nagar',
                26.9124000, 75.7435000,
                'Semi-arid', 'Sandy'
            ),
            'body' => $this->agricultureBody(
                'PM-KISAN provides ₹6,000 per year in three equal installments to eligible landholding farmer families.',
                'Many smallholders miss payments due to outdated land records or pending eKYC.',
                'Compiled district-wise checklist from agriculture department circulars and farmer helpline FAQs.',
                'Farmers who completed eKYC in our ward received the 17th installment without delay.',
                'Name mismatches between Aadhaar and land records remain the top rejection reason.',
                'Visit the agriculture office with RTC copy, Aadhaar, and bank passbook before the deadline.'
            ),
            'meta' => [
                'agriculture_scheme_name' => 'PM-KISAN',
                'agriculture_scheme_department' => 'Ministry of Agriculture & Farmers Welfare',
                'agriculture_scheme_eligibility' => 'Landholding farmer families with cultivable land as per state records; institutional landholders excluded.',
                'agriculture_scheme_subsidy' => '₹6,000 per year (3 installments of ₹2,000)',
                'agriculture_scheme_application_link' => 'https://pmkisan.gov.in/',
                'agriculture_scheme_last_date' => now()->addMonths(2)->toDateString(),
                'agriculture_documents' => [
                    $this->externalDocument('government-notification-pm-kisan.pdf'),
                ],
                'agriculture_ask_community' => 'Did your village receive the latest installment on time? Share your experience.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function wheatMandiPrices(): array
    {
        return [
            'slug' => 'agriculture-wheat-mandi-prices-jaipur',
            'author' => 'priya',
            'share_type' => 'Market Information',
            'category' => 'Agri-Business',
            'title' => 'Jaipur Grain Mandi Wheat Prices Trending Up This Week',
            'excerpt' => 'FAQ quality wheat touched ₹2,450/quintal on Tuesday; moisture discounts still apply above 12%.',
            'featured_image' => 'https://picsum.photos/seed/agri-mandi-wheat/960/540',
            'tags' => ['Wheat', 'Market Information', 'Jaipur', 'Mandi Prices', 'Agriculture'],
            'days_ago' => 3,
            'target_audiences' => ['Farmers', 'Agri-Entrepreneurs', 'General Public'],
            'location' => $this->location(
                'Jaipur Grain Mandi, Rajasthan, India',
                'India', 'Rajasthan', 'Jaipur', 'Jaipur',
                26.9150000, 75.8200000,
                'Semi-arid', 'Sandy'
            ),
            'body' => $this->agricultureBody(
                'Visited Jaipur grain mandi on Tuesday to compare FAQ wheat quotes across licensed commission agents.',
                'Prices moved up after delayed arrivals from neighbouring districts and strong mill demand.',
                'Surveyed 12 agents, noted moisture deductions, and compared with last week\'s closing rates.',
                'FAQ wheat quoted ₹2,380–₹2,450/quintal; average up ₹120 from last week.',
                'Transport costs and cash liquidity still affect small farmers\' net returns.',
                'Dry grain to 12% moisture before dispatch; monitor rates early morning for best bids.'
            ),
            'meta' => [
                'agriculture_market_commodity' => 'Wheat (FAQ)',
                'agriculture_market_name' => 'Jaipur Grain Mandi',
                'agriculture_market_price' => '₹2,450/quintal',
                'agriculture_market_date' => now()->subDays(3)->toDateString(),
                'agriculture_market_price_trend' => 'Increasing',
                'agriculture_agri_business_type' => 'Food Processing',
                'agriculture_ask_community' => 'What price are you getting for wheat at your local mandi this week?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mahindraTractorReview(): array
    {
        return [
            'slug' => 'agriculture-mahindra-575-di-tractor-review',
            'author' => 'ravi',
            'share_type' => 'Equipment Review',
            'category' => 'Farm Machinery',
            'title' => 'Mahindra 575 DI XP Plus — Two-Season Field Review from a Rajasthan Farm',
            'excerpt' => 'Fuel economy, lift capacity, and service network experience after 900 engine hours on mixed cropping.',
            'featured_image' => 'https://picsum.photos/seed/agri-tractor-review/960/540',
            'tags' => ['Farm Machinery', 'Tractor', 'Equipment Review', 'Rajasthan'],
            'days_ago' => 8,
            'location' => $this->location(
                'Sikar, Rajasthan, India',
                'India', 'Rajasthan', 'Sikar', 'Piprali',
                27.6094000, 75.1398000,
                'Arid', 'Sandy'
            ),
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'video_id' => '9bZkp7q19f0',
            ],
            'body' => $this->agricultureBody(
                'Purchased Mahindra 575 DI XP Plus for wheat, mustard, and fodder operations on 8 acres.',
                'Needed reliable power for disc harrow and trailer work on sandy loam soils.',
                'Logged fuel use, maintenance costs, and downtime across two Rabi seasons.',
                'Average 1.8 L/hour on medium load; dealer service within 24 hours on hydraulic leak.',
                'Cabin heat in April and PTO vibration at high RPM are minor annoyances.',
                'Good value for 45 HP class if dealer is within 20 km; negotiate extended warranty.'
            ),
            'meta' => [
                'agriculture_equipment_name' => 'Mahindra 575 DI XP Plus',
                'agriculture_equipment_manufacturer' => 'Mahindra & Mahindra',
                'agriculture_equipment_cost' => '₹7,85,000 (on-road Sikar)',
                'agriculture_equipment_experience' => '900 engine hours over two seasons. Strong pull on disc harrow; comfortable for 6-hour sessions. Service camp organised by dealer in village.',
                'agriculture_equipment_benefits' => 'Lower fuel consumption than previous 42 HP model; improved lift for baler work; good resale demand locally.',
                'agriculture_farm_size' => '5-10 Acres',
                'agriculture_farming_type' => 'Conventional',
                'agriculture_video_type' => 'Field Demonstration',
                'agriculture_gallery' => [
                    'equipment' => [
                        $this->externalImage('agri-tractor-1', 'tractor-field-work.jpg'),
                        $this->externalImage('agri-tractor-2', 'tractor-cabin.jpg'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dairyFarmHimachal(): array
    {
        return [
            'slug' => 'agriculture-dairy-farm-himachal-practices',
            'author' => 'aman',
            'share_type' => 'Livestock Management',
            'category' => 'Dairy Farming',
            'title' => 'Small Dairy Unit in Himachal: Fodder Planning and Milk Quality Tips',
            'excerpt' => 'A 12-animal crossbred unit improved fat percentage through green fodder rotation and clean milking protocol.',
            'featured_image' => 'https://picsum.photos/seed/agri-dairy-hp/960/540',
            'tags' => ['Dairy Farming', 'Livestock', 'Himachal Pradesh', 'Fodder', 'Agriculture'],
            'days_ago' => 6,
            'location' => $this->location(
                'Solan, Himachal Pradesh, India',
                'India', 'Himachal Pradesh', 'Solan', 'Kandaghat',
                30.5675000, 77.0475000,
                'Sub-temperate', 'Loamy'
            ),
            'body' => $this->agricultureBody(
                'Family-run dairy with 12 crossbred animals on 2 acres including maize–berseem fodder rotation.',
                'Low fat content and mastitis risk during humid monsoon months.',
                'Introduced strip grazing, mineral mixture, and stainless milking utensils with hot water rinse.',
                'Average fat rose from 3.8% to 4.3%; somatic cell count improved on cooperative tests.',
                'Fodder shortfall in January still requires purchased hay.',
                'Plan winter fodder silage in October; keep vaccination calendar on the shed wall.'
            ),
            'meta' => [
                'agriculture_livestock_types' => ['Dairy'],
                'agriculture_farm_size' => '1-5 Acres',
                'agriculture_farming_type' => 'Integrated Farming',
                'agriculture_water_source' => 'Borewell',
                'agriculture_irrigation_method' => 'Manual',
                'agriculture_gallery' => [
                    'farm_photos' => [
                        $this->externalImage('agri-dairy-shed', 'dairy-shed.jpg'),
                    ],
                ],
                'agriculture_ask_community' => 'Which green fodder combination works best for crossbred cattle in hill districts?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function solarDryerInnovation(): array
    {
        return [
            'slug' => 'agriculture-solar-dryer-tomato-innovation',
            'author' => 'expert',
            'share_type' => 'Agricultural Innovation',
            'category' => 'Agricultural Technology',
            'title' => 'Low-Cost Solar Dryer Cuts Tomato Post-Harvest Loss by 35%',
            'excerpt' => 'Polyhouse-based solar dryer designed by KVK scientists helps small growers store surplus produce as dried slices.',
            'featured_image' => 'https://picsum.photos/seed/agri-solar-dryer/960/540',
            'tags' => ['Agricultural Innovation', 'Solar Dryer', 'Horticulture', 'Post-Harvest'],
            'days_ago' => 10,
            'target_audiences' => ['Farmers', 'Researchers', 'Agri-Entrepreneurs'],
            'location' => $this->location(
                'Karnal, Haryana, India',
                'India', 'Haryana', 'Karnal', 'Nilokheri',
                29.6857000, 76.9905000,
                'Sub-tropical', 'Alluvial Soil'
            ),
            'body' => $this->agricultureBody(
                'KVK Nilokheri piloted a 12-tray solar dryer for tomato and chilli surplus during glut season.',
                'Fresh tomato prices crash to ₹3/kg while 25% of harvest rotted in peak weeks.',
                'Designed forced-air solar cabinet with UV-stabilised poly sheet and adjustable vents.',
                'Drying time 18–22 hours; dried tomato sold at ₹280/kg to urban buyers.',
                'Initial cost still high for landless labourers without group ownership.',
                'Form FPO clusters to share one dryer unit per 20 farmers.'
            ),
            'meta' => [
                'agriculture_innovation_name' => 'KVK Solar Forced-Air Dryer',
                'agriculture_innovation_description' => 'Portable 12-tray cabinet using solar collector and DC fan for uniform drying of horticulture slices.',
                'agriculture_innovation_benefits' => '35% reduction in post-harvest loss; adds value-added product line; low operating cost.',
                'agriculture_innovation_results' => '22 pilot farmers processed 4.2 tonnes surplus tomato; average income increase ₹18,000 per season.',
                'agriculture_gallery' => [
                    'equipment' => [
                        $this->externalImage('agri-solar-dryer-1', 'solar-dryer-unit.jpg'),
                    ],
                    'harvest' => [
                        $this->externalImage('agri-dried-tomato', 'dried-tomato-slices.jpg'),
                    ],
                ],
                'agriculture_documents' => [
                    $this->externalDocument('research-report-solar-dryer-kvk.pdf'),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function organicSeedBusiness(): array
    {
        return [
            'slug' => 'agriculture-organic-seed-supply-business',
            'author' => 'priya',
            'share_type' => 'Agri-Business Opportunity',
            'category' => 'Agri-Business',
            'title' => 'Starting an Organic Seed Supply Shop in Tier-2 Towns — Lessons Learned',
            'excerpt' => 'Licensing, supplier ties, and farmer trust-building from our first year operating in Alwar district.',
            'featured_image' => 'https://picsum.photos/seed/agri-seed-business/960/540',
            'tags' => ['Agri-Business', 'Organic Products', 'Seed Supplier', 'Rajasthan'],
            'days_ago' => 12,
            'target_audiences' => ['Agri-Entrepreneurs', 'Farmers', 'Consultants'],
            'location' => $this->location(
                'Alwar, Rajasthan, India',
                'India', 'Rajasthan', 'Alwar', 'Alwar',
                27.5530000, 76.6346000,
                'Semi-arid', 'Sandy'
            ),
            'body' => $this->agricultureBody(
                'Opened a certified organic seed outlet serving 40 villages around Alwar after three years in agri-input sales.',
                'Farmers wanted reliable organic seed but distrusted unknown brands and fake labels.',
                'Partnered with two NPOP-certified suppliers, ran field demos, and offered germination guarantees.',
                'Year-one turnover ₹14 lakh; repeat customers at 68%.',
                'Working capital spikes before Kharif; cold storage for vegetable seed still missing.',
                'Start with legume and fodder seed lines; invest in transparent labelling and demo plots.'
            ),
            'meta' => [
                'agriculture_agri_business_type' => 'Seed Supplier',
                'agriculture_farming_type' => 'Organic',
                'agriculture_water_conservation_practices' => ['Rainwater Harvesting', 'Mulching'],
                'agriculture_ask_community' => 'Which organic seed varieties have performed best in your district?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function dripAdvisoryRajasthan(): array
    {
        return [
            'slug' => 'agriculture-drip-advisory-mustard-rajasthan',
            'author' => 'expert',
            'share_type' => 'Crop Advisory',
            'category' => 'Irrigation & Water Management',
            'title' => 'Drip Scheduling Advisory for Mustard Under Water-Scarce Blocks',
            'excerpt' => 'KVK advisory for flowering-stage irrigation on sandy soils when canal rotation is delayed.',
            'featured_image' => 'https://picsum.photos/seed/agri-mustard-drip/960/540',
            'tags' => ['Crop Advisory', 'Mustard', 'Drip Irrigation', 'Water Management'],
            'days_ago' => 4,
            'allow_poll' => true,
            'location' => $this->location(
                'Bikaner, Rajasthan, India',
                'India', 'Rajasthan', 'Bikaner', 'Nokha',
                27.5609000, 73.4712000,
                'Arid', 'Sandy'
            ),
            'body' => $this->agricultureBody(
                'Mustard on sandy loam in Nokha block faces canal gaps during critical flowering.',
                'Delayed irrigation causing flower drop and pod setting failure across 200+ ha.',
                'Recommend 2-hour drip cycles every third day at 4 L/hour emitters; avoid midday runs.',
                'Pilot farmers retained 85% pod set vs 60% on flood-check plots.',
                'Power cuts disrupt evening schedules; farmers need shared genset roster.',
                'Mulch immediately after last weed control; group buy diesel for backup pumping.'
            ),
            'meta' => $this->baseCropMeta([
                'agriculture_crop_name' => 'Mustard',
                'agriculture_crop_variety' => 'RH-749',
                'agriculture_growing_season' => 'Rabi',
                'agriculture_irrigation_method' => 'Drip Irrigation',
                'agriculture_water_source' => 'Canal',
                'agriculture_water_conservation_practices' => ['Drip Irrigation', 'Mulching', 'Farm Pond'],
                'agriculture_weather_impact' => 'Drought',
                'agriculture_poll_question' => 'Which irrigation method do you use for mustard?',
                'agriculture_poll_options' => ['Drip', 'Sprinkler', 'Flood', 'Rainfed'],
                'agriculture_gallery' => [
                    'irrigation_systems' => [
                        $this->externalImage('agri-mustard-drip-line', 'mustard-drip-lines.jpg'),
                    ],
                    'crop_growth_stages' => [
                        $this->externalImage('agri-mustard-flower', 'mustard-flowering.jpg'),
                    ],
                ],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function soilHealthResearch(): array
    {
        return [
            'slug' => 'agriculture-soil-health-microbial-research',
            'author' => 'expert',
            'share_type' => 'Research Findings',
            'category' => 'Agricultural Research',
            'title' => 'Soil Microbial Activity After Green Manure — Field Trial Summary',
            'excerpt' => 'One-season trial on black cotton soils shows higher organic carbon and lower bulk density after dhaincha incorporation.',
            'featured_image' => 'https://picsum.photos/seed/agri-soil-research/960/540',
            'tags' => ['Soil Health', 'Research Findings', 'Organic Farming', 'Agriculture'],
            'days_ago' => 14,
            'target_audiences' => ['Researchers', 'Agriculture Students', 'Farmers'],
            'location' => $this->location(
                'Nagpur, Maharashtra, India',
                'India', 'Maharashtra', 'Nagpur', 'Saoner',
                21.3850000, 78.9217000,
                'Tropical wet-dry', 'Black Soil'
            ),
            'body' => $this->agricultureBody(
                'University-led trial on 1 ha black cotton plot comparing green manure vs chemical fallow.',
                'Declining organic matter and crusting after repeated cotton–soybean rotation.',
                'Dhaincha sown at 25 kg/ha, incorporated at 50% flowering; soil sampled at 0–15 cm monthly.',
                'Organic carbon +0.18%; microbial biomass +22%; bulk density reduced 6%.',
                'One season insufficient for yield response; labour cost for incorporation noted.',
                'Combine green manure with reduced tillage; retest for three seasons before scaling.'
            ),
            'meta' => $this->baseCropMeta([
                'agriculture_crop_name' => 'Cotton',
                'agriculture_growing_season' => 'Kharif',
                'agriculture_soil_test_conducted' => 'yes',
                'agriculture_soil_ph' => '7.2',
                'agriculture_soil_organic_carbon' => '0.54%',
                'agriculture_soil_nitrogen' => '312 kg/ha',
                'agriculture_soil_phosphorus' => '22 kg/ha',
                'agriculture_soil_potassium' => '198 kg/ha',
                'agriculture_soil_recommendations' => 'Continue dhaincha green manure; add compost 2.5 t/ha before cotton sowing; avoid deep plough in dry crust.',
                'agriculture_farming_type' => 'Integrated Farming',
                'agriculture_documents' => [
                    $this->externalDocument('research-report-soil-microbes.pdf'),
                    $this->externalDocument('soil-test-report-nagpur-trial.pdf'),
                ],
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function baseCropMeta(array $extra): array
    {
        return $extra;
    }

    /**
     * @return array<string, list<array{path: string, url: string, name: string, type: string}>>
     */
    private function fullGallery(string $seed): array
    {
        return [
            'farm_photos' => [
                $this->externalImage($seed.'-farm', 'farm-panorama.jpg'),
            ],
            'crop_growth_stages' => [
                $this->externalImage($seed.'-crop', 'wheat-growth-stage.jpg'),
            ],
            'equipment' => [
                $this->externalImage($seed.'-equip', 'drip-equipment.jpg'),
            ],
            'irrigation_systems' => [
                $this->externalImage($seed.'-irrigation', 'drip-irrigation-system.jpg'),
            ],
            'harvest' => [
                $this->externalImage($seed.'-harvest', 'wheat-harvest.jpg'),
            ],
        ];
    }

    private function agricultureBody(
        string $background,
        string $problem,
        string $method,
        string $results,
        string $challenges,
        string $recommendations,
    ): string {
        return <<<HTML
<h2>Background</h2>
<p>{$background}</p>
<h2>Problem</h2>
<p>{$problem}</p>
<h2>Method Used</h2>
<p>{$method}</p>
<h2>Results</h2>
<p>{$results}</p>
<h2>Challenges</h2>
<p>{$challenges}</p>
<h2>Recommendations</h2>
<p>{$recommendations}</p>
HTML;
    }

    /**
     * @return array{label: string, country: string, state: string, district: string, city: string, lat: float, lng: float, climate_zone?: string, soil_type?: string}
     */
    private function location(
        string $label,
        string $country,
        string $state,
        string $district,
        string $city,
        float $lat,
        float $lng,
        ?string $climateZone = null,
        ?string $soilType = null,
    ): array {
        return [
            'label' => $label,
            'country' => $country,
            'state' => $state,
            'district' => $district,
            'city' => $city,
            'lat' => $lat,
            'lng' => $lng,
            'climate_zone' => $climateZone,
            'soil_type' => $soilType,
        ];
    }

    /**
     * @return array{path: string, url: string, name: string, type: string}
     */
    private function externalImage(string $seed, string $name): array
    {
        return [
            'path' => 'seeders/agriculture/'.$seed.'.jpg',
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
            'path' => 'seeders/agriculture/'.$name,
            'url' => 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf',
            'name' => $name,
            'type' => 'application/pdf',
        ];
    }
}

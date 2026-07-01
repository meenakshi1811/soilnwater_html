<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityReligionSpiritualityPostSeeder extends Seeder
{
    public function run(): void
    {
        $interfaithGuide = $this->user('Sister Meera Joseph', 'religion-meera@example.com');
        $templeGuide = $this->user('Pandit Ramesh Sharma', 'religion-ramesh@example.com');
        $festivalDesk = $this->user('Festival Desk SoilnWater', 'religion-festival@example.com');
        $serviceCoordinator = $this->user('Imam Yusuf Khan', 'religion-yusuf@example.com');
        $wisdomCurator = $this->user('Dr. Ananya Wisdom', 'religion-ananya@example.com');

        foreach ($this->religionSpiritualityPosts() as $post) {
            $author = match ($post['author'] ?? 'meera') {
                'ramesh' => $templeGuide,
                'festival' => $festivalDesk,
                'yusuf' => $serviceCoordinator,
                'ananya' => $wisdomCurator,
                default => $interfaithGuide,
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

        $meta = array_merge($this->baseMeta($postType, $category), $post['meta'] ?? []);

        if (isset($post['video']['label'])) {
            $meta['religion_spirituality_video_type'] = $post['video']['label'];
        }

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'religion-spirituality',
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
                'allow_poll' => $post['allow_poll'] ?? (bool) data_get($post['meta'] ?? [], 'religion_spirituality_allow_poll', false),
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function baseMeta(string $postType, string $category): array
    {
        return [
            'author_bio' => 'SoilnWater Religion & Spirituality contributor sharing respectful educational and cultural content.',
            'editor_language' => 'en',
            'religion_spirituality_post_type' => $postType,
            'religion_spirituality_category' => $category,
            'religion_spirituality_declaration_respectful' => true,
            'religion_spirituality_declaration_accurate' => true,
            'religion_spirituality_declaration_no_hatred' => true,
            'religion_spirituality_declaration_educational' => true,
            'religion_spirituality_declaration_guidelines' => true,
            'religion_spirituality_comment_settings' => CommunityContentTaxonomy::religionSpiritualityCommentSettings(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function religionSpiritualityPosts(): array
    {
        return [
            $this->goldenTemplePilgrimageGuide(),
            $this->varanasiTemplePilgrimageGuide(),
            $this->diwaliFestivalCalendar(),
            $this->christmasCommunityCelebration(),
            $this->bloodDonationServiceDirectory(),
            $this->treePlantationServiceDirectory(),
            $this->compassionWisdomLibrary(),
            $this->environmentalResponsibilityWisdomLibrary(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function goldenTemplePilgrimageGuide(): array
    {
        return [
            'slug' => 'religion-digital-pilgrimage-guide-golden-temple-amritsar',
            'author' => 'ramesh',
            'post_type' => 'Pilgrimage Guide',
            'category' => 'Pilgrimage',
            'title' => 'Digital Pilgrimage Guide — Harmandir Sahib (Golden Temple), Amritsar',
            'excerpt' => 'Verified visitor information for the Golden Temple including facilities, accommodation, and nearby SoilnWater-listed businesses.',
            'featured_image' => 'https://picsum.photos/seed/rs-golden-temple/960/540',
            'tags' => ['Pilgrimage', 'Gurudwara', 'Amritsar', 'Golden Temple', 'Guide'],
            'days_ago' => 14,
            'location' => $this->location('Amritsar, Punjab, India', 31.6200000, 74.8765000),
            'video' => $this->youtubeVideo('Historical Documentary'),
            'body' => $this->spiritualBody(
                'The Golden Temple welcomes millions of pilgrims each year. This guide shares practical, respectful information for visitors of all backgrounds.',
                'Harmandir Sahib was founded in the 16th century and remains a symbol of openness, seva (selfless service), and spiritual equality.',
                'The complex includes the sarovar (holy tank), langar (community kitchen), and Akal Takht. Visitors remove shoes and cover their heads as signs of respect.',
                'Early morning and evening ceremonies offer profound experiences. Weekends and festival seasons are busiest; weekdays are quieter for reflection.',
                'Plan modest dress, arrive with patience during peak hours, and participate in langar if comfortable — a living tradition of community service.',
                'This guide is educational. Always follow on-site instructions from temple authorities and local regulations.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Sikhism',
                'religion_spirituality_target_audience' => ['Pilgrims', 'Families', 'General Public'],
                'religion_spirituality_moral_messages' => ['Service', 'Respect', 'Unity'],
                'religion_spirituality_place_of_worship_type' => 'Gurudwara',
                'religion_spirituality_location_country' => 'India',
                'religion_spirituality_location_state' => 'Punjab',
                'religion_spirituality_location_city' => 'Amritsar',
                'religion_spirituality_location_gps' => '31.6200, 74.8765',
                'religion_spirituality_pilgrimage_name' => 'Harmandir Sahib (Golden Temple)',
                'religion_spirituality_pilgrimage_location' => 'Amritsar, Punjab',
                'religion_spirituality_pilgrimage_best_time' => 'October to March; early morning for peaceful darshan',
                'religion_spirituality_pilgrimage_facilities' => 'Langar hall, shoe deposit, drinking water, medical aid, information desk',
                'religion_spirituality_pilgrimage_accommodation' => 'Guru Ram Das Niwas and several dharamshalas near the complex',
                'religion_spirituality_enable_digital_pilgrimage_guide' => true,
                'religion_spirituality_digital_pilgrimage_site_types' => ['Gurudwara'],
                'religion_spirituality_digital_pilgrimage_site_name' => 'Harmandir Sahib (Golden Temple)',
                'religion_spirituality_digital_pilgrimage_verified_info' => 'Open to all faiths. Free langar served daily. Head covering required.',
                'religion_spirituality_digital_pilgrimage_nearby_facilities' => 'ATM, pharmacies, rail station (1.5 km), auto-rickshaw stand, shoe lockers',
                'religion_spirituality_digital_pilgrimage_accommodation' => 'Guru Ram Das Niwas pilgrim hostel; several budget lodges on Golden Temple Road',
                'religion_spirituality_digital_pilgrimage_local_businesses' => 'Amritsari Kulcha House (SoilnWater listing), Heritage Lodge, Punjab Handicrafts Cooperative',
                'religion_spirituality_digital_pilgrimage_map_url' => 'https://maps.google.com/?q=31.6200,74.8765',
                'religion_spirituality_related_service_actions' => ['Food Distribution', 'Volunteer Programs'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function varanasiTemplePilgrimageGuide(): array
    {
        return [
            'slug' => 'religion-digital-pilgrimage-guide-kashi-vishwanath',
            'author' => 'ramesh',
            'post_type' => 'Temple / Mosque / Church / Gurudwara / Monastery Information',
            'category' => 'Places of Worship',
            'title' => 'Digital Pilgrimage Guide — Kashi Vishwanath Corridor & Ghats, Varanasi',
            'excerpt' => 'A respectful guide to visiting one of Hinduism\'s most ancient sacred cities, with maps, facilities, and local SoilnWater businesses.',
            'featured_image' => 'https://picsum.photos/seed/rs-varanasi-temple/960/540',
            'tags' => ['Temple', 'Varanasi', 'Pilgrimage', 'Ganga', 'Heritage'],
            'days_ago' => 9,
            'location' => $this->location('Varanasi, Uttar Pradesh, India', 25.3176000, 82.9739000),
            'body' => $this->spiritualBody(
                'Varanasi has drawn seekers for millennia. This guide helps pilgrims plan a respectful visit to temples and ghats along the Ganga.',
                'Kashi is described in scriptures as a city of liberation and learning. The renovated corridor improves access while preserving sacred atmosphere.',
                'Morning Ganga aarti at Dashashwamedh Ghat is widely attended. Temple queues vary by season; modest dress and patience are essential.',
                'Boat rides at sunrise offer cultural context. Many visitors combine temple darshan with walks through the old city\'s narrow lanes.',
                'Support local artisans ethically, avoid littering near the river, and follow temple photography rules.',
                'Spiritual travel should deepen humility and respect — not competition between beliefs.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Hinduism',
                'religion_spirituality_target_audience' => ['Pilgrims', 'Researchers', 'Senior Citizens'],
                'religion_spirituality_moral_messages' => ['Respect', 'Compassion'],
                'religion_spirituality_place_of_worship_type' => 'Temple',
                'religion_spirituality_location_country' => 'India',
                'religion_spirituality_location_state' => 'Uttar Pradesh',
                'religion_spirituality_location_city' => 'Varanasi',
                'religion_spirituality_enable_digital_pilgrimage_guide' => true,
                'religion_spirituality_digital_pilgrimage_site_types' => ['Temple'],
                'religion_spirituality_digital_pilgrimage_site_name' => 'Kashi Vishwanath Temple',
                'religion_spirituality_digital_pilgrimage_verified_info' => 'Corridor access with security screening. Photography restrictions inside sanctum.',
                'religion_spirituality_digital_pilgrimage_nearby_facilities' => 'Ganga ghats, boat jetty, medical clinics, police assistance booth',
                'religion_spirituality_digital_pilgrimage_accommodation' => 'Guest houses near Assi Ghat; heritage homestays in old city',
                'religion_spirituality_digital_pilgrimage_local_businesses' => 'Ganga Aarti Boat Tours (SoilnWater), Banaras Silk Weavers Cooperative, Ghat View Café',
                'religion_spirituality_digital_pilgrimage_map_url' => 'https://maps.google.com/?q=25.3109,83.0107',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function diwaliFestivalCalendar(): array
    {
        return [
            'slug' => 'religion-festival-calendar-diwali-2026',
            'author' => 'festival',
            'post_type' => 'Festival Information',
            'category' => 'Festivals',
            'title' => 'Festival Calendar — Diwali 2026: Lights, Gratitude & Community',
            'excerpt' => 'Educational overview of Diwali traditions across regions, with calendar details and linked learning resources.',
            'featured_image' => 'https://picsum.photos/seed/rs-diwali-festival/960/540',
            'tags' => ['Diwali', 'Festival', 'Hinduism', 'Calendar', 'Lights'],
            'days_ago' => 5,
            'location' => $this->location('Ayodhya, Uttar Pradesh, India', 26.7922000, 82.1998000),
            'video' => $this->youtubeVideo('Festival Celebration'),
            'body' => $this->spiritualBody(
                'Diwali celebrates light over darkness and gratitude for prosperity, family, and community bonds.',
                'The festival spans five days in many traditions, with Lakshmi Puja, Govardhan Puja, and Bhai Dooj observed regionally.',
                'Homes are cleaned and illuminated. Sweets are shared; businesses close accounts; temples host special prayers.',
                'Eco-friendly diyas, reduced firecracker use, and inclusive celebrations help protect health and environment.',
                'Families may volunteer, donate food, or invite neighbours regardless of faith — promoting harmony.',
                'Learn regional variations with curiosity and respect, not comparison.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Hinduism',
                'religion_spirituality_target_audience' => ['Families', 'General Public', 'Youth'],
                'religion_spirituality_moral_messages' => ['Kindness', 'Unity', 'Service'],
                'religion_spirituality_festival_name' => 'Diwali',
                'religion_spirituality_festival_date' => 'November 2026 (Kartik Amavasya)',
                'religion_spirituality_festival_historical_significance' => 'Associated with return of Lord Rama to Ayodhya and victory of light over darkness in multiple traditions.',
                'religion_spirituality_enable_festival_calendar' => true,
                'religion_spirituality_festival_calendar_event_types' => ['Religious Festival', 'Community Celebration'],
                'religion_spirituality_festival_calendar_event_name' => 'Diwali 2026',
                'religion_spirituality_festival_calendar_event_date' => '2026-11-08',
                'religion_spirituality_festival_calendar_description' => 'Five-day festival of lights observed across India and the diaspora with regional customs.',
                'religion_spirituality_festival_calendar_linked_article_url' => 'https://soilnwater.com/community',
                'religion_spirituality_allow_poll' => true,
                'religion_spirituality_poll_question' => 'Which topic would you like to learn more about?',
                'religion_spirituality_poll_options' => CommunityContentTaxonomy::religionSpiritualityDefaultPollOptions(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function christmasCommunityCelebration(): array
    {
        return [
            'slug' => 'religion-festival-calendar-christmas-community-2026',
            'author' => 'meera',
            'post_type' => 'Festival Information',
            'category' => 'Festivals',
            'title' => 'Festival Calendar — Christmas: Faith, Charity & Community Gatherings',
            'excerpt' => 'How Christian communities celebrate Christmas with worship, charity, and interfaith goodwill — a calendar entry for December 2026.',
            'featured_image' => 'https://picsum.photos/seed/rs-christmas-festival/960/540',
            'tags' => ['Christmas', 'Christianity', 'Festival', 'Charity', 'Community'],
            'days_ago' => 7,
            'location' => $this->location('Shillong, Meghalaya, India', 25.5788000, 91.8933000),
            'body' => $this->spiritualBody(
                'Christmas commemorates the birth of Jesus Christ and inspires themes of hope, generosity, and peace.',
                'Midnight Mass, carol singing, and nativity plays are common. Many churches organize charity drives during Advent.',
                'Community meals welcome neighbours of all backgrounds. Schools and choirs perform seasonal music.',
                'Charitable giving — food, blankets, education support — reflects the season\'s emphasis on compassion.',
                'Interfaith gatherings promote understanding without diminishing distinct beliefs.',
                'Celebrate with joy while respecting diverse traditions in shared public spaces.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Christianity',
                'religion_spirituality_target_audience' => ['Families', 'General Public', 'Teachers'],
                'religion_spirituality_moral_messages' => ['Compassion', 'Service', 'Kindness'],
                'religion_spirituality_festival_name' => 'Christmas',
                'religion_spirituality_enable_festival_calendar' => true,
                'religion_spirituality_festival_calendar_event_types' => ['Religious Festival', 'Public Holiday', 'Community Celebration'],
                'religion_spirituality_festival_calendar_event_name' => 'Christmas 2026',
                'religion_spirituality_festival_calendar_event_date' => '2026-12-25',
                'religion_spirituality_festival_calendar_description' => 'Celebration of the Nativity with worship services, charity, and community gatherings.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bloodDonationServiceDirectory(): array
    {
        return [
            'slug' => 'religion-community-service-blood-donation-camp',
            'author' => 'yusuf',
            'post_type' => 'Community Service Activity',
            'category' => 'Community Service',
            'title' => 'Community Service Directory — Interfaith Blood Donation Camp, Hyderabad',
            'excerpt' => 'Volunteer opportunity organized by religious and civic groups — save lives through shared service.',
            'featured_image' => 'https://picsum.photos/seed/rs-blood-donation/960/540',
            'tags' => ['Blood Donation', 'Service', 'Volunteer', 'Interfaith', 'Health'],
            'days_ago' => 3,
            'location' => $this->location('Hyderabad, Telangana, India', 17.3850000, 78.4867000),
            'body' => $this->spiritualBody(
                'Many faith traditions encourage saving lives through service. This blood donation camp welcomes donors of all backgrounds.',
                'Organized jointly by a mosque committee, church youth group, and local hospital partner.',
                'Eligible donors aged 18–65 with minimum haemoglobin may participate after medical screening.',
                'Registration opens at 8 AM. Refreshments and certificates provided. No religious requirement to donate.',
                'Volunteers needed for registration, crowd guidance, and post-donation care.',
                'Service transcends labels — compassion is a shared human value.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Interfaith',
                'religion_spirituality_target_audience' => ['General Public', 'Youth', 'Families'],
                'religion_spirituality_moral_messages' => ['Service', 'Compassion', 'Unity'],
                'religion_spirituality_community_service_activities' => ['Blood Donation', 'Health Camp'],
                'religion_spirituality_enable_community_service_directory' => true,
                'religion_spirituality_service_directory_opportunities' => ['Blood Donation Camp'],
                'religion_spirituality_service_directory_organization' => 'Hyderabad Interfaith Seva Forum',
                'religion_spirituality_service_directory_when_where' => 'First Sunday monthly, 8 AM–2 PM, City Community Hall, Banjara Hills',
                'religion_spirituality_service_directory_volunteer_notes' => 'Bring valid ID. Eat a light meal before donating. Walk-ins welcome; appointment preferred via SoilnWater volunteer form.',
                'religion_spirituality_related_service_actions' => ['Blood Donation', 'Volunteer Programs'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function treePlantationServiceDirectory(): array
    {
        return [
            'slug' => 'religion-community-service-tree-plantation-drive',
            'author' => 'yusuf',
            'post_type' => 'Community Service Activity',
            'category' => 'Community Service',
            'title' => 'Community Service Directory — Sacred Grove Tree Plantation, Nashik',
            'excerpt' => 'Temple trust and youth groups invite volunteers for a tree plantation and water conservation drive.',
            'featured_image' => 'https://picsum.photos/seed/rs-tree-plantation/960/540',
            'tags' => ['Tree Plantation', 'Water Conservation', 'Environment', 'Service', 'Volunteer'],
            'days_ago' => 11,
            'location' => $this->location('Nashik, Maharashtra, India', 19.9975000, 73.7898000),
            'body' => $this->spiritualBody(
                'Caring for nature appears in scriptures and local traditions alike. This drive plants native species near a sacred grove.',
                'Organized by a temple trust, SoilnWater environment volunteers, and local farmers\' cooperative.',
                'Participants will plant saplings, install drip irrigation, and learn rainwater harvesting basics.',
                'Wear comfortable clothes and bring a water bottle. Tools and saplings provided.',
                'Families welcome. Educational session on environmental responsibility included.',
                'Spiritual practice and ecological stewardship can strengthen one another.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Hinduism',
                'religion_spirituality_target_audience' => ['Youth', 'Families', 'Teachers'],
                'religion_spirituality_moral_messages' => ['Service', 'Respect'],
                'religion_spirituality_community_service_activities' => ['Tree Plantation', 'Water Conservation'],
                'religion_spirituality_enable_community_service_directory' => true,
                'religion_spirituality_service_directory_opportunities' => ['Tree Plantation', 'Water Conservation Drive'],
                'religion_spirituality_service_directory_organization' => 'Nashik Temple Trust & SoilnWater Green Volunteers',
                'religion_spirituality_service_directory_when_where' => 'July 15, 2026, 7 AM–12 PM, Trimbak Road watershed area',
                'religion_spirituality_service_directory_volunteer_notes' => 'Register on SoilnWater to receive meeting point and safety briefing.',
                'religion_spirituality_related_service_actions' => ['Tree Plantation', 'Water Conservation', 'Volunteer Programs'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function compassionWisdomLibrary(): array
    {
        return [
            'slug' => 'religion-wisdom-library-compassion-across-traditions',
            'author' => 'ananya',
            'post_type' => 'Spiritual Article',
            'category' => 'Interfaith Understanding',
            'title' => 'Wisdom Library — Compassion Across Hindu, Buddhist, Christian & Islamic Teachings',
            'excerpt' => 'A searchable collection entry on karuna, rahma, agape, and metta — universal compassion expressed in different voices.',
            'featured_image' => 'https://picsum.photos/seed/rs-wisdom-compassion/960/540',
            'tags' => ['Compassion', 'Wisdom', 'Interfaith', 'Teachings', 'Library'],
            'days_ago' => 18,
            'location' => $this->location('Kolkata, West Bengal, India', 22.5726000, 88.3639000),
            'body' => $this->spiritualBody(
                'Compassion — feeling with others and acting to relieve suffering — appears at the heart of major spiritual traditions.',
                'Sanskrit karuna, Pali metta, Arabic rahma, and Greek agape each nuance universal care with cultural depth.',
                'Hindu texts speak of daya; Buddhism teaches loving-kindness meditation; Christianity emphasizes neighbour love; Islam highlights Allah\'s mercy as a model.',
                'Shared practice: listen without judgment, help where possible, forgive when safe, and protect dignity.',
                'Readers may journal one compassionate act weekly and study a teaching from another tradition with respect.',
                'Wisdom grows when we honour difference while recognizing our common humanity.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Interfaith',
                'religion_spirituality_target_audience' => ['Students', 'Teachers', 'Spiritual Seekers', 'Researchers'],
                'religion_spirituality_moral_messages' => ['Compassion', 'Forgiveness', 'Respect'],
                'religion_spirituality_enable_wisdom_library' => true,
                'religion_spirituality_wisdom_themes' => ['Compassion', 'Forgiveness', 'Respect', 'Service'],
                'religion_spirituality_wisdom_traditions' => ['Hinduism', 'Buddhism', 'Christianity', 'Islam', 'Interfaith'],
                'religion_spirituality_wisdom_collection_summary' => 'Cross-tradition anthology entry mapping compassion vocabulary and practices for classroom and personal study.',
                'religion_spirituality_ask_community' => 'How do different communities in your area express compassion during difficult times?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function environmentalResponsibilityWisdomLibrary(): array
    {
        return [
            'slug' => 'religion-wisdom-library-environmental-responsibility',
            'author' => 'ananya',
            'post_type' => 'Religious Article',
            'category' => 'Traditional Practices',
            'title' => 'Wisdom Library — Environmental Responsibility in Sacred Texts & Local Practice',
            'excerpt' => 'Teachings on rivers, trees, and stewardship from multiple traditions — linked to SoilnWater conservation mission.',
            'featured_image' => 'https://picsum.photos/seed/rs-wisdom-environment/960/540',
            'tags' => ['Environment', 'Water', 'Tradition', 'Wisdom', 'Stewardship'],
            'days_ago' => 22,
            'location' => $this->location('Rishikesh, Uttarakhand, India', 30.0869000, 78.2676000),
            'body' => $this->spiritualBody(
                'Water and soil sustain life — themes central to SoilnWater and to many spiritual traditions.',
                'Rivers like Ganga and Yamuna are revered; forests are protected in sacred groves; fasting and frugality teach restraint.',
                'Indigenous traditions worldwide honour land as kin. Abrahamic texts speak of tending the garden. Dharmic paths emphasize ahimsa toward all beings.',
                'Modern application: reduce waste, plant trees, conserve water, and advocate for clean rivers without blaming communities.',
                'Temples and mosques can model solar power, rainwater harvesting, and plastic-free festivals.',
                'Environmental responsibility is a moral value that can unite rather than divide.'
            ),
            'meta' => [
                'religion_spirituality_tradition' => 'Interfaith',
                'religion_spirituality_target_audience' => ['Teachers', 'Youth', 'General Public'],
                'religion_spirituality_moral_messages' => ['Service', 'Respect', 'Honesty'],
                'religion_spirituality_enable_wisdom_library' => true,
                'religion_spirituality_wisdom_themes' => ['Environmental Responsibility', 'Service', 'Family Values', 'Respect'],
                'religion_spirituality_wisdom_traditions' => ['Hinduism', 'Buddhism', 'Islam', 'Indigenous Traditions', 'Interfaith'],
                'religion_spirituality_wisdom_collection_summary' => 'Curriculum-friendly entry connecting scripture, festival practice, and SoilnWater water-soil conservation programs.',
                'religion_spirituality_related_service_actions' => ['Tree Plantation', 'Water Conservation'],
            ],
        ];
    }

    private function spiritualBody(
        string $introduction,
        string $historicalBackground,
        string $teachings,
        string $practicalRelevance,
        string $conclusion,
        string $references = 'Sources: community scholars, published translations, and local heritage guides — verify locally for festivals and access rules.',
    ): string {
        return <<<HTML
<h2>Introduction</h2>
<p>{$introduction}</p>
<h2>Historical Background</h2>
<p>{$historicalBackground}</p>
<h2>Teachings</h2>
<p>{$teachings}</p>
<h2>Practical Relevance</h2>
<p>{$practicalRelevance}</p>
<h2>Conclusion</h2>
<p>{$conclusion}</p>
<h2>References</h2>
<p>{$references}</p>
HTML;
    }

    /**
     * @return array{label: string, lat: float, lng: float}
     */
    private function location(string $label, float $lat, float $lng): array
    {
        return [
            'label' => $label,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    /**
     * @return array{type: string, url: string, video_id: string, label?: string}
     */
    private function youtubeVideo(string $videoTypeLabel): array
    {
        return [
            'type' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
            'video_id' => 'EngW7tLk6R8',
            'label' => $videoTypeLabel,
        ];
    }
}

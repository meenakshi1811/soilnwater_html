<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityCreativeCornerPostSeeder extends Seeder
{
    public function run(): void
    {
        $photographer = $this->user('Ananya Verma', 'creative-ananya@example.com');
        $painter = $this->user('Rohit Mehta', 'creative-rohit@example.com');
        $diyMaker = $this->user('Priya Nair', 'creative-priya@example.com');
        $digitalArtist = $this->user('Kabir Singh', 'creative-kabir@example.com');
        $musician = $this->user('Meera Joshi', 'creative-meera@example.com');
        $craftExpert = $this->user('Lakshmi Devi', 'creative-lakshmi@example.com');

        foreach ($this->creativeCornerPosts() as $post) {
            $author = match ($post['author'] ?? 'ananya') {
                'rohit' => $painter,
                'priya' => $diyMaker,
                'kabir' => $digitalArtist,
                'meera' => $musician,
                'lakshmi' => $craftExpert,
                default => $photographer,
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
            $meta['creative_corner_video_type'] = $post['video']['label'];
        }

        return CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'creative-corner',
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
                'allow_poll' => $post['allow_poll'] ?? (bool) data_get($post['meta'] ?? [], 'creative_corner_allow_poll', false),
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
            'author_bio' => 'SoilnWater Creative Corner contributor sharing original artwork, craft, and design.',
            'editor_language' => 'en',
            'creative_corner_post_type' => $postType,
            'creative_corner_category' => $category,
            'creative_corner_declaration_original' => true,
            'creative_corner_declaration_no_infringement' => true,
            'creative_corner_declaration_ai_disclosed' => true,
            'creative_corner_declaration_guidelines' => true,
            'creative_corner_comment_settings' => CommunityContentTaxonomy::creativeCornerCommentSettings(),
            'creative_corner_ai_used' => 'No',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function creativeCornerPosts(): array
    {
        return [
            $this->monsoonPhotographySeries(),
            $this->watercolorLandscapePainting(),
            $this->rainwaterDiyProject(),
            $this->digitalArtInnovationPoster(),
            $this->instrumentalMusicComposition(),
            $this->bambooCraftHandicraft(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function monsoonPhotographySeries(): array
    {
        return [
            'slug' => 'creative-corner-monsoon-cloud-photography-series',
            'author' => 'ananya',
            'post_type' => 'Photography',
            'category' => 'Photography',
            'title' => 'Monsoon Cloud Photography Series — Light Over the Western Ghats',
            'excerpt' => 'A photography series capturing monsoon clouds, village resilience, and the relationship between water and land.',
            'featured_image' => 'https://picsum.photos/seed/cc-monsoon-hero/960/540',
            'tags' => ['Photography', 'Nature', 'Monsoon', 'Water', 'Creative'],
            'days_ago' => 6,
            'location' => $this->location('Mahabaleshwar, Maharashtra, India', 17.9237000, 73.6586000),
            'video' => $this->youtubeVideo('Time-lapse'),
            'body' => $this->creativeBody(
                'This series documents dramatic monsoon cloud formations rolling over the Western Ghats during early July.',
                'Weekend hikes and early-morning shoots near village reservoirs inspired this collection.',
                'DSLR camera, 24–70mm lens, lightweight tripod, neutral density filters.',
                'Scouted locations at dawn, waited for light breaks, bracketed exposures, and edited for natural colour.',
                'Unpredictable rain and mist required waterproof gear and flexible scheduling.',
                'Twelve final images highlighting cloud texture, farmland, and seasonal water abundance.',
                'Plan a winter revisit for mist-and-sunrise contrasts.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['General Public', 'Photographers', 'Art Collectors'],
                'creative_corner_creation_type' => 'Original Work',
                'creative_corner_mediums' => ['Photography'],
                'creative_corner_software_tools' => ['Lightroom', 'Photoshop'],
                'creative_corner_time_taken' => '3 Days',
                'creative_corner_difficulty_level' => 'Intermediate',
                'creative_corner_themes' => ['Nature', 'Water', 'Agriculture', 'Travel'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Maharashtra',
                'creative_corner_location_city' => 'Mahabaleshwar',
                'creative_corner_ask_community' => 'Which frame best captures the monsoon mood — wide landscape or close cloud texture?',
                'creative_corner_allow_poll' => true,
                'creative_corner_poll_question' => 'Which version do you prefer?',
                'creative_corner_poll_options' => ['Wide landscape', 'Close cloud texture', 'Village reservoir'],
                'creative_corner_creative_licenses' => ['Free to Share', 'Educational Use Only'],
                'creative_corner_collaboration_roles' => ['Photographer', 'Video Editor'],
                'creative_corner_social_instagram' => 'https://instagram.com/ananya.creative',
                'creative_corner_social_portfolio' => 'https://ananya-creative.example.com',
                'creative_corner_gallery' => $this->galleryImages('cc-monsoon', 4),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function watercolorLandscapePainting(): array
    {
        return [
            'slug' => 'creative-corner-watercolor-river-landscape',
            'author' => 'rohit',
            'post_type' => 'Painting',
            'category' => 'Painting',
            'title' => 'Watercolor River Landscape — Chambal at Golden Hour',
            'excerpt' => 'An original watercolor painting celebrating river ecology, warm light, and quiet reflection.',
            'featured_image' => 'https://picsum.photos/seed/cc-watercolor-hero/960/540',
            'tags' => ['Painting', 'Watercolor', 'Nature', 'River', 'Art'],
            'days_ago' => 12,
            'location' => $this->location('Kota, Rajasthan, India', 25.2138000, 75.8648000),
            'body' => $this->creativeBody(
                'A serene river scene painted in transparent watercolor layers.',
                'Field sketches during a SoilnWater river conservation visit.',
                'Arches cold-press paper, professional watercolor pans, sable brushes.',
                'Loose washes for sky and water, layered glazing for banks and birds.',
                'Managing wet-on-wet timing in humid weather.',
                'Finished A3 painting suitable for home or office display.',
                'Next version will include local fishing boats in silhouette.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['Artists', 'Art Collectors', 'General Public'],
                'creative_corner_creation_type' => 'Original Work',
                'creative_corner_mediums' => ['Watercolor', 'Paper'],
                'creative_corner_materials' => ['Paper', 'Natural Materials'],
                'creative_corner_creation_date' => now()->subMonths(2)->toDateString(),
                'creative_corner_time_taken' => '8 Hours',
                'creative_corner_difficulty_level' => 'Advanced',
                'creative_corner_themes' => ['Water', 'Wildlife', 'Environment'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Rajasthan',
                'creative_corner_location_city' => 'Kota',
                'creative_corner_material_cost' => '₹850',
                'creative_corner_total_cost' => '₹1,200',
                'creative_corner_available_for_sale' => true,
                'creative_corner_sale_price' => '₹12,500',
                'creative_corner_custom_orders_accepted' => true,
                'creative_corner_shipping_available' => true,
                'creative_corner_commission_options' => ['Available for Custom Orders'],
                'creative_corner_copyright' => 'All Rights Reserved',
                'creative_corner_social_website' => 'https://rohit-watercolors.example.com',
                'creative_corner_gallery' => $this->galleryImages('cc-watercolor', 3),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rainwaterDiyProject(): array
    {
        return [
            'slug' => 'creative-corner-diy-rainwater-planter',
            'author' => 'priya',
            'post_type' => 'DIY Project',
            'category' => 'Craft & DIY',
            'title' => 'DIY Rainwater Harvesting Planter — Terrace Garden Edition',
            'excerpt' => 'A step-by-step DIY project turning recycled containers into a compact rainwater planter for urban terraces.',
            'featured_image' => 'https://picsum.photos/seed/cc-diy-hero/960/540',
            'tags' => ['DIY', 'Water', 'Sustainability', 'Craft', 'Innovation'],
            'days_ago' => 4,
            'location' => $this->location('Pune, Maharashtra, India', 18.5204000, 73.8567000),
            'video' => $this->youtubeVideo('Tutorial'),
            'body' => $this->creativeBody(
                'Urban terraces can harvest rain while growing herbs — this DIY shows how with minimal tools.',
                'Inspired by SoilnWater water conservation workshops and terrace gardening communities.',
                'Recycled plastic drums, PVC pipe, mesh filter, bamboo supports, herb seedlings.',
                'Cut inlet, install filter, connect overflow, seal joints, plant herbs, test with bucket simulation.',
                'Ensuring overflow does not damage neighbouring balconies.',
                'Functional planter collecting ~40 litres per rainfall event on a 10 sq.m. terrace.',
                'Add a drip irrigation adapter in version two.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['Students', 'General Public', 'Content Creators'],
                'creative_corner_creation_type' => 'Educational Project',
                'creative_corner_mediums' => ['Recycled Material', 'Plastic'],
                'creative_corner_materials' => ['Plastic', 'Bamboo', 'Natural Materials'],
                'creative_corner_time_taken' => '2 Days',
                'creative_corner_difficulty_level' => 'Beginner',
                'creative_corner_themes' => ['Water', 'Sustainability', 'Agriculture', 'Innovation'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Maharashtra',
                'creative_corner_location_city' => 'Pune',
                'creative_corner_material_cost' => '₹1,450',
                'creative_corner_equipment_cost' => '₹600',
                'creative_corner_total_cost' => '₹2,050',
                'creative_corner_submit_to_competition' => true,
                'creative_corner_competition_categories' => ['Innovation', 'Craft'],
                'creative_corner_ask_community' => 'Would you like a printable PDF build guide for this planter?',
                'creative_corner_creative_licenses' => ['Free to Share', 'Educational Use Only'],
                'creative_corner_commission_options' => ['Available for Workshops'],
                'creative_corner_gallery' => $this->galleryImages('cc-diy', 5),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function digitalArtInnovationPoster(): array
    {
        return [
            'slug' => 'creative-corner-digital-poster-soil-water-innovation',
            'author' => 'kabir',
            'post_type' => 'Digital Art',
            'category' => 'Digital Art',
            'title' => 'Digital Poster — Soil, Water & Innovation for Rural Schools',
            'excerpt' => 'A vibrant digital illustration series promoting soil health, water literacy, and student innovation.',
            'featured_image' => 'https://picsum.photos/seed/cc-digital-hero/960/540',
            'tags' => ['Digital Art', 'Innovation', 'Education', 'Water', 'Design'],
            'days_ago' => 9,
            'location' => $this->location('Bengaluru, Karnataka, India', 12.9716000, 77.5946000),
            'body' => $this->creativeBody(
                'Educational poster set for rural school science corners.',
                'Collaboration with a local teacher network focused on soil and water awareness.',
                'Digital tablet, vector brushes, print-ready export presets.',
                'Sketched concepts, refined characters, balanced typography, exported A2 print files.',
                'Keeping text legible in Kannada and English versions.',
                'Three poster variants: soil layers, water cycle, student innovation lab.',
                'Animate posters for classroom screens in the next release.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['Schools', 'Students', 'Designers'],
                'creative_corner_creation_type' => 'Commissioned Work',
                'creative_corner_mediums' => ['Digital Tablet'],
                'creative_corner_software_tools' => ['Illustrator', 'Figma', 'Canva'],
                'creative_corner_time_taken' => '2 Weeks',
                'creative_corner_difficulty_level' => 'Professional',
                'creative_corner_themes' => ['Innovation', 'Water', 'Agriculture', 'People'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Karnataka',
                'creative_corner_location_city' => 'Bengaluru',
                'creative_corner_commission_options' => ['Available for Freelance Projects', 'Available for Collaboration'],
                'creative_corner_copyright' => 'Commercial License',
                'creative_corner_ai_used' => 'Partially Assisted',
                'creative_corner_ai_tool' => 'Generative fill for background textures',
                'creative_corner_ai_description' => 'AI assisted background texture suggestions only; characters and layout are hand-drawn.',
                'creative_corner_social_portfolio' => 'https://kabir-design.example.com',
                'creative_corner_gallery' => $this->galleryImages('cc-digital', 3),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function instrumentalMusicComposition(): array
    {
        return [
            'slug' => 'creative-corner-instrumental-monsoon-raga',
            'author' => 'meera',
            'post_type' => 'Music Composition',
            'category' => 'Music',
            'title' => 'Instrumental Composition — Monsoon Raga Sketch for Flute & Tabla',
            'excerpt' => 'An original instrumental piece inspired by monsoon rhythms, river flow, and evening temple bells.',
            'featured_image' => 'https://picsum.photos/seed/cc-music-hero/960/540',
            'tags' => ['Music', 'Composition', 'Monsoon', 'Creative', 'Culture'],
            'days_ago' => 15,
            'location' => $this->location('Thiruvananthapuram, Kerala, India', 8.5241000, 76.9366000),
            'video' => $this->youtubeVideo('Performance'),
            'body' => $this->creativeBody(
                'A short instrumental sketch blending flute melody with soft tabla patterns.',
                'Monsoon evenings in Kerala and traditional raga listening sessions.',
                'Bansuri flute, tabla, laptop DAW, studio microphone.',
                'Recorded melody lines, layered drones, gentle percussion, mixed for warm room listening.',
                'Balancing live room noise with clean flute tone.',
                '3-minute composition suitable for community events and awareness videos.',
                'Expand to full 8-minute suite with string pad.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['General Public', 'Content Creators', 'Artists'],
                'creative_corner_creation_type' => 'Original Work',
                'creative_corner_mediums' => ['Mixed Media'],
                'creative_corner_software_tools' => ['Premiere Pro', 'DaVinci Resolve'],
                'creative_corner_time_taken' => '5 Days',
                'creative_corner_difficulty_level' => 'Advanced',
                'creative_corner_themes' => ['Culture', 'Water', 'Spirituality'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Kerala',
                'creative_corner_location_city' => 'Thiruvananthapuram',
                'creative_corner_audio_type' => 'Instrumental Music',
                'creative_corner_creative_licenses' => ['Educational Use Only'],
                'creative_corner_collaboration_roles' => ['Musician', 'Video Editor'],
                'creative_corner_social_youtube' => 'https://youtube.com/@meera.creative',
                'creative_corner_ask_community' => 'Should I release the full score for student musicians to practice?',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bambooCraftHandicraft(): array
    {
        return [
            'slug' => 'creative-corner-bamboo-lamp-handicraft',
            'author' => 'lakshmi',
            'post_type' => 'Handicraft',
            'category' => 'Woodwork',
            'title' => 'Bamboo Table Lamp — Handcrafted Heritage Lighting',
            'excerpt' => 'A handcrafted bamboo lamp combining traditional weaving with modern minimalist design — available for custom orders.',
            'featured_image' => 'https://picsum.photos/seed/cc-bamboo-hero/960/540',
            'tags' => ['Handicraft', 'Bamboo', 'Craft', 'DIY', 'Heritage'],
            'days_ago' => 20,
            'location' => $this->location('Guwahati, Assam, India', 26.1445000, 91.7362000),
            'body' => $this->creativeBody(
                'Handwoven bamboo strips form a warm, patterned lampshade with a stable wooden base.',
                'Assamese bamboo craft traditions and contemporary home décor trends.',
                'Seasoned bamboo strips, wood base, LED bulb holder, natural varnish.',
                'Split bamboo, weave hex pattern, assemble frame, wire safely, finish with low-VOC varnish.',
                'Achieving even tension in weave panels.',
                'Elegant lamp suitable for living rooms, cafés, and eco-conscious gifting.',
                'Develop a pendant-light variant next.'
            ),
            'meta' => [
                'creative_corner_target_audience' => ['Artists', 'Businesses', 'Art Collectors'],
                'creative_corner_creation_type' => 'Original Work',
                'creative_corner_mediums' => ['Bamboo', 'Wood'],
                'creative_corner_materials' => ['Bamboo', 'Wood', 'Natural Materials'],
                'creative_corner_time_taken' => '1 Week',
                'creative_corner_difficulty_level' => 'Professional',
                'creative_corner_themes' => ['Heritage', 'Sustainability', 'Culture'],
                'creative_corner_location_country' => 'India',
                'creative_corner_location_state' => 'Assam',
                'creative_corner_location_city' => 'Guwahati',
                'creative_corner_material_cost' => '₹900',
                'creative_corner_equipment_cost' => '₹400',
                'creative_corner_total_cost' => '₹1,300',
                'creative_corner_available_for_sale' => true,
                'creative_corner_sale_price' => '₹4,800',
                'creative_corner_custom_orders_accepted' => true,
                'creative_corner_limited_edition' => true,
                'creative_corner_shipping_available' => true,
                'creative_corner_commission_options' => ['Available for Custom Orders', 'Available for Workshops'],
                'creative_corner_copyright' => 'All Rights Reserved',
                'creative_corner_social_vendor_profile' => 'https://soilnwater.com/vendor/lakshmi-crafts',
                'creative_corner_gallery' => $this->galleryImages('cc-bamboo', 4),
            ],
        ];
    }

    private function creativeBody(
        string $concept,
        string $inspiration,
        string $materials,
        string $process,
        string $challenges,
        string $outcome,
        string $future,
    ): string {
        return <<<HTML
<h2>Concept</h2>
<p>{$concept}</p>
<h2>Inspiration</h2>
<p>{$inspiration}</p>
<h2>Materials Used</h2>
<p>{$materials}</p>
<h2>Creative Process</h2>
<p>{$process}</p>
<h2>Challenges</h2>
<p>{$challenges}</p>
<h2>Final Outcome</h2>
<p>{$outcome}</p>
<h2>Future Improvements</h2>
<p>{$future}</p>
HTML;
    }

    /**
     * @return list<array{path: string, url: string, name: string, type: string}>
     */
    private function galleryImages(string $seed, int $count): array
    {
        return collect(range(1, $count))
            ->map(fn (int $index): array => [
                'path' => "seeders/creative-corner/{$seed}-{$index}.jpg",
                'url' => "https://picsum.photos/seed/{$seed}-{$index}/800/600",
                'name' => "Gallery image {$index}",
                'type' => 'image/jpeg',
            ])
            ->all();
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
            'url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ',
            'video_id' => 'aqz-KE-bpKQ',
            'label' => $videoTypeLabel,
        ];
    }
}

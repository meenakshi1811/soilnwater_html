<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityBusinessPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Business Content Author',
            'email' => 'business-content@example.com',
        ]);

        foreach ($this->businessPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge([
            'author_bio' => 'Entrepreneur and business writer sharing practical startup and SME insights from Rajasthan.',
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
                'content_type' => 'business',
                'category' => $post['category'],
                'writing_purpose' => $post['writing_purpose'] ?? 'Share Knowledge',
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
                'allow_suggestions' => $post['allow_suggestions'] ?? true,
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
    private function businessPosts(): array
    {
        return [
            $this->startupSuccessStory(),
            $this->retailBusinessGuide(),
            $this->womenEntrepreneurshipCaseStudy(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function startupSuccessStory(): array
    {
        return [
            'slug' => 'business-local-water-testing-startup-success',
            'category' => 'Startup',
            'writing_purpose' => 'Share a Success Story',
            'title' => 'How We Built a Local Water-Testing Startup in Jaipur',
            'excerpt' => 'A founder shares how a small water-quality testing service grew from one lab bench to serving 40+ housing societies across Jaipur.',
            'featured_image' => 'https://picsum.photos/seed/business-water-startup/1200/630',
            'tags' => ['Startup', 'Business Growth', 'Entrepreneurship', 'Water', 'Jaipur'],
            'days_ago' => 4,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h2>Business Problem</h2>
<p>Many households and small businesses in Jaipur had no affordable way to verify drinking water quality between municipal supply cycles.</p>
<h2>Background</h2>
<p>Our team started with a single portable testing kit and visits to three housing societies in 2024. Residents wanted simple reports, not complex lab jargon.</p>
<h2>Solution</h2>
<p>We built a mobile collection model, standardized a 12-point test panel, and delivered WhatsApp-friendly PDF reports within 24 hours.</p>
<h2>Results</h2>
<p>Within 18 months we onboarded 40+ societies, partnered with two RO service vendors, and reached break-even without external funding.</p>
<h2>Lessons Learned</h2>
<ul>
    <li>Start with one neighbourhood and one repeatable service package.</li>
    <li>Trust grows faster when reports are visual and easy to share.</li>
    <li>B2B2C partnerships with maintenance vendors reduce customer acquisition cost.</li>
</ul>
<h2>Recommendations</h2>
<p>If you are launching a local service business, validate demand door-to-door before investing in fixed infrastructure.</p>
HTML,
            'meta' => [
                'business_category' => 'Startup',
                'business_content_type' => 'Success Story',
                'business_stage' => 'Growing Business',
                'business_target_audience' => ['Startup Founders', 'Small Business Owners', 'Investors', 'General Public'],
                'business_challenges' => ['Funding', 'Customer Acquisition', 'Marketing', 'Technology'],
                'business_opportunity_type' => 'Partnership',
                'business_market_segments' => ['B2B', 'B2C', 'Local Market'],
                'business_themes' => ['Innovation', 'Leadership', 'Customer Service', 'Technology'],
                'business_name' => 'AquaCheck Jaipur',
                'business_author_designation' => 'Founder & CEO',
                'business_profile_type' => 'Startup',
                'business_industry' => 'Services',
                'business_video_type' => 'Business Introduction',
                'business_ask_community' => 'What strategies helped you grow your first 100 customers in a local service business?',
                'business_useful_links' => "https://msme.gov.in/\nhttps://www.startupindia.gov.in/",
                'business_government_schemes' => 'MUDRA Loan Scheme — collateral-free loans for micro and small enterprises.',
                'business_training_programs' => 'MSME Entrepreneur Development Programme — business planning and compliance workshops.',
                'business_industry_resources' => 'FICCI Rajasthan MSME reports and local incubator mentor networks.',
                'business_contact_options' => ['Contact Author', 'Send Business Query', 'Request Guidance'],
                'business_poll_question' => 'What is the biggest challenge for small businesses?',
                'business_poll_options' => CommunityContentTaxonomy::businessDefaultPollOptions(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function retailBusinessGuide(): array
    {
        return [
            'slug' => 'business-retail-digital-marketing-guide',
            'category' => 'Retail Business',
            'writing_purpose' => 'Help Others Learn',
            'title' => 'Digital Marketing Guide for Small Retail Shops in Tier-2 Cities',
            'excerpt' => 'Practical steps for retailers to move from walk-in-only sales to WhatsApp catalogues, Google Maps visibility, and repeat customers.',
            'featured_image' => 'https://picsum.photos/seed/business-retail-guide/1200/630',
            'tags' => ['Retail', 'Marketing', 'Small Business', 'Digital Transformation'],
            'days_ago' => 9,
            'allow_poll' => false,
            'body' => <<<'HTML'
<h2>Business Problem</h2>
<p>Many family-run retail shops lose customers to online marketplaces because they lack a simple digital presence.</p>
<h2>Background</h2>
<p>Over 200 shop owners in Jaipur's old markets still rely only on foot traffic and word-of-mouth referrals.</p>
<h2>Solution</h2>
<p>A three-step approach: Google Business Profile setup, WhatsApp catalogue with weekly offers, and in-store QR codes for reviews.</p>
<h2>Results</h2>
<p>Pilot shops reported 20–35% more repeat orders within 90 days and stronger weekend footfall from map searches.</p>
<h2>Recommendations</h2>
<p>Start with one channel, measure for 30 days, then add a second — do not try every platform at once.</p>
HTML,
            'meta' => [
                'business_category' => 'Retail Business',
                'business_content_type' => 'Business Guide',
                'business_stage' => 'Established Business',
                'business_target_audience' => ['Retailers', 'Small Business Owners', 'Consultants', 'General Public'],
                'business_challenges' => ['Marketing', 'Customer Acquisition', 'Technology', 'Competition'],
                'business_opportunity_type' => 'Dealer Network',
                'business_market_segments' => ['B2C', 'Local Market', 'National Market'],
                'business_themes' => ['Marketing', 'Sales', 'Digital Transformation', 'Skill Development'],
                'business_name' => 'Rajasthan Retail Collective',
                'business_author_designation' => 'Retail Business Consultant',
                'business_profile_type' => 'Proprietorship',
                'business_industry' => 'Retail',
                'business_ask_community' => 'Which marketing channel worked best when you first took your shop online?',
                'business_useful_links' => "https://business.google.com/\nhttps://www.digitalindia.gov.in/",
                'business_government_schemes' => 'PM Vishwakarma Scheme — support for traditional artisans and small traders.',
                'business_training_programs' => 'District MSME digital literacy camps for shop owners.',
                'business_industry_resources' => 'Retailers Association of India — state chapter newsletters.',
                'business_contact_options' => ['Contact Author', 'Request Guidance'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function womenEntrepreneurshipCaseStudy(): array
    {
        return [
            'slug' => 'business-women-agri-processing-case-study',
            'category' => 'Women Entrepreneurship',
            'writing_purpose' => 'Inspire Others',
            'title' => 'Case Study: Women-Led Agri-Processing Unit in Rural Rajasthan',
            'excerpt' => 'How a women’s self-help group scaled from home-based pickle production to a registered food-processing unit supplying local stores.',
            'featured_image' => 'https://picsum.photos/seed/business-women-agri/1200/630',
            'tags' => ['Women Entrepreneurship', 'Agriculture Business', 'Case Study', 'Manufacturing'],
            'days_ago' => 15,
            'allow_poll' => true,
            'allow_suggestions' => true,
            'body' => <<<'HTML'
<h2>Business Problem</h2>
<p>Seasonal crop gluts left women farmers with unsold produce and limited income between harvests.</p>
<h2>Background</h2>
<p>A self-help group in a Jaipur district village began with shared kitchen space and FSSAI basic registration guidance.</p>
<h2>Solution</h2>
<p>They standardized recipes, introduced batch labelling, and partnered with a local distributor for weekly store deliveries.</p>
<h2>Results</h2>
<p>Monthly group income tripled in one year. Twelve women now work full-time at the unit with plans to add dehydrated vegetable lines.</p>
<h2>Lessons Learned</h2>
<p>Collective ownership, basic compliance early, and one anchor buyer beat trying to sell at every haat individually.</p>
<h2>Recommendations</h2>
<p>Apply for cluster-based food processing subsidies and connect with district industry centres before buying heavy equipment.</p>
HTML,
            'meta' => [
                'business_category' => 'Women Entrepreneurship',
                'business_content_type' => 'Case Study',
                'business_stage' => 'Expansion Phase',
                'business_target_audience' => ['Women Entrepreneurs', 'Farmers', 'Small Business Owners', 'Investors'],
                'business_challenges' => ['Funding', 'Operations', 'Supply Chain', 'Regulations'],
                'business_opportunity_type' => 'Investment Opportunity',
                'business_market_segments' => ['B2B', 'B2C', 'Local Market', 'Export'],
                'business_themes' => ['Women Empowerment', 'Agriculture', 'Sustainability', 'Skill Development'],
                'business_name' => 'Sakhi Annapurna Foods',
                'business_author_designation' => 'Group Coordinator',
                'business_profile_type' => 'Partnership',
                'business_industry' => 'Agriculture',
                'business_video_type' => 'Factory Tour',
                'business_ask_community' => 'What support did you receive when scaling from home-based production to a registered unit?',
                'business_government_schemes' => "PMFME Scheme — support for micro food processing enterprises.\nStand-Up India — bank loans for women entrepreneurs.",
                'business_training_programs' => 'NRLM SHG enterprise development training and FSSAI compliance workshops.',
                'business_industry_resources' => 'APEDA export readiness guides for processed foods.',
                'business_contact_options' => ['Contact Author', 'Send Business Query'],
                'business_poll_question' => 'What is the biggest challenge for women-led rural businesses?',
                'business_poll_options' => ['Funding', 'Marketing', 'Compliance', 'Supply Chain'],
            ],
        ];
    }
}

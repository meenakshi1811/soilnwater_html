<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityWomensWorldPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Women\'s World Author',
            'email' => 'womens-world@example.com',
        ]);

        foreach ($this->womensWorldPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge([
            'author_bio' => 'Community member sharing experiences, guidance, and encouragement for women across Rajasthan.',
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'womens_world_visibility' => 'public',
        ], $post['meta'] ?? []);

        if (($meta['womens_world_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'womens_world_private_link_token'))) {
            $meta['womens_world_private_link_token'] = Str::random(48);
        }

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'womens-world',
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
                'publish_as' => $post['publish_as'] ?? CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'pen_name' => $post['pen_name'] ?? null,
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
    private function womensWorldPosts(): array
    {
        return [
            $this->workingWomenCareerStory(),
            $this->womenEntrepreneurJourney(),
            $this->motherhoodSupportStory(),
            $this->sensitiveAnonymousStory(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workingWomenCareerStory(): array
    {
        return [
            'slug' => 'womens-world-balancing-career-and-family',
            'category' => 'Career & Professional Growth',
            'writing_purpose' => 'Personal Experience',
            'title' => 'Balancing Career Growth and Family Responsibilities',
            'excerpt' => 'A working mother in Jaipur shares how she negotiated flexible hours, built a support network, and grew into a team lead without burning out.',
            'featured_image' => 'https://picsum.photos/seed/womens-world-career/1200/630',
            'tags' => ['Women', 'Career', 'Parenting', 'Work-Life Balance', 'Leadership'],
            'days_ago' => 2,
            'allow_poll' => true,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>When I returned to work after maternity leave, I worried that asking for flexibility would slow my career. Three years later, I lead a five-person team and still pick up my daughter from school twice a week.</p>
<h2>Challenge</h2>
<p>Long commute times, unpredictable meeting schedules, and guilt on days when work ran late were the hardest parts of the transition.</p>
<h2>Experience</h2>
<p>I started with an honest conversation with my manager, proposed core hours for collaboration, and used calendar blocks for deep work. My spouse and my mother-in-law shared school runs on alternate days.</p>
<h2>Lessons Learned</h2>
<ul>
    <li>Clear boundaries at work protect energy at home.</li>
    <li>Asking for help is a strength, not a weakness.</li>
    <li>Small daily routines matter more than perfect balance every day.</li>
</ul>
<h2>Advice to Others</h2>
<p>Document your contributions, propose solutions when you request flexibility, and find one peer mentor who has walked a similar path.</p>
<h2>Conclusion</h2>
<p>Career growth and family life can coexist when workplaces and families share responsibility openly.</p>
HTML,
            'meta' => [
                'womens_world_category' => 'Career & Professional Growth',
                'womens_world_content_type' => 'Personal Story',
                'womens_world_target_audience' => ['Working Women', 'Mothers', 'Professionals', 'Young Women'],
                'womens_world_featured_topics' => ['Work-Life Balance', 'Career Growth', 'Parenting'],
                'womens_world_life_stage' => 'Mother',
                'womens_world_themes' => ['Career', 'Parenting', 'Leadership', 'Women Empowerment'],
                'womens_world_video_type' => 'Motivational Talk',
                'womens_world_ask_community' => 'How do you manage work and family responsibilities during busy seasons?',
                'womens_world_poll_question' => 'What is the biggest challenge for working women?',
                'womens_world_poll_options' => CommunityContentTaxonomy::womensWorldDefaultPollOptions(),
                'womens_world_community_groups' => ['Working Women', 'Mothers'],
                'womens_world_useful_websites' => "https://www.womensweb.in/\nhttps://www.ncwwomen.org/",
                'womens_world_training_programs' => 'Leadership workshops for women professionals — check local MSME and industry association calendars.',
                'womens_world_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function womenEntrepreneurJourney(): array
    {
        return [
            'slug' => 'womens-world-handloom-entrepreneur-journey',
            'category' => 'Women Entrepreneurship',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'From Home Weaver to Online Brand: My Handloom Journey',
            'excerpt' => 'How a Jaipur-based entrepreneur turned a family weaving skill into a sustainable online business with training support and community buyers.',
            'featured_image' => 'https://picsum.photos/seed/womens-world-entrepreneur/1200/630',
            'tags' => ['Women', 'Entrepreneurship', 'Business', 'Handloom', 'Financial Independence'],
            'days_ago' => 6,
            'allow_poll' => false,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>Our family has woven traditional Rajasthani textiles for two generations. For years we sold only through local exhibitions and word of mouth.</p>
<h2>Challenge</h2>
<p>Scaling beyond seasonal fairs required packaging, photography, digital payments, and confidence in pricing our work fairly.</p>
<h2>Experience</h2>
<p>I joined a women entrepreneur circle, completed a digital marketing workshop, and listed products on SoilnWater and WhatsApp catalogues. Repeat customers now account for 40% of monthly sales.</p>
<h2>Lessons Learned</h2>
<ul>
    <li>Start with one product line and one sales channel.</li>
    <li>Invest in good product photos — they build trust online.</li>
    <li>Track costs weekly so pricing stays sustainable.</li>
</ul>
<h2>Advice to Others</h2>
<p>Look for government schemes and local incubators early. You do not need a large loan to test your first online storefront.</p>
<h2>Conclusion</h2>
<p>Traditional skills and digital tools together can create dignified, flexible livelihoods for women entrepreneurs.</p>
HTML,
            'meta' => [
                'womens_world_category' => 'Women Entrepreneurship',
                'womens_world_content_type' => 'Success Story',
                'womens_world_target_audience' => ['Entrepreneurs', 'Homemakers', 'Working Women', 'Young Women'],
                'womens_world_featured_topics' => ['Financial Planning', 'Skill Development', 'Leadership'],
                'womens_world_life_stage' => 'Entrepreneur',
                'womens_world_themes' => ['Business', 'Financial Independence', 'Women Empowerment'],
                'womens_world_business_name' => 'Rajasthan Loom Stories',
                'womens_world_business_category' => 'Home-Based Business',
                'womens_world_website_url' => 'https://example.com/rajasthan-loom-stories',
                'womens_world_vendor_profile_url' => 'https://soilnwater.com/vendor/rajasthan-loom-stories',
                'womens_world_ask_community' => 'What helped you get your first 50 online customers as a women-led business?',
                'womens_world_community_groups' => ['Women Entrepreneurs', 'Working Women'],
                'womens_world_government_schemes' => "PM Vishwakarma Scheme — support for artisans and craftspeople.\nMUDRA Loan — collateral-free loans for micro enterprises.",
                'womens_world_training_programs' => 'MSME women entrepreneur development programmes and district handicraft design workshops.',
                'womens_world_scholarships' => 'State handicraft development board skill-upgrade stipends for women artisans.',
                'womens_world_support_organizations' => "Rajasthan Women Entrepreneur Network\nDistrict MSME facilitation centre helpline",
                'womens_world_visibility' => 'public',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function motherhoodSupportStory(): array
    {
        return [
            'slug' => 'womens-world-seeking-mentorship-new-mothers',
            'category' => 'Motherhood & Parenting',
            'writing_purpose' => 'Help Community',
            'title' => 'Seeking Mentorship: Returning to Work as a New Mother',
            'excerpt' => 'A first-time mother asks the community for guidance on childcare planning, career continuity, and emotional support during the first year.',
            'featured_image' => 'https://picsum.photos/seed/womens-world-motherhood/1200/630',
            'tags' => ['Women', 'Parenting', 'Career', 'Health', 'Mentorship'],
            'days_ago' => 11,
            'allow_poll' => false,
            'allow_suggestions' => true,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>I am preparing to return to my teaching role after six months at home with my baby. I am excited but unsure how to plan routines that work for both family and classroom responsibilities.</p>
<h2>Challenge</h2>
<p>Childcare timing, breastfeeding logistics after returning to work, and managing fatigue are my biggest concerns right now.</p>
<h2>Experience</h2>
<p>I have spoken with two colleagues who returned last year, but their school schedules differ from mine. I would value more diverse perspectives from this community.</p>
<h2>Advice to Others</h2>
<p>If you have been through this transition, please share what helped — even small practical tips matter.</p>
<h2>Conclusion</h2>
<p>I hope this post helps other new mothers feel less alone while planning their return to work.</p>
HTML,
            'publish_as' => CommunityPost::PUBLISH_AS_PEN_NAME,
            'pen_name' => 'A Caring Mother',
            'meta' => [
                'womens_world_category' => 'Motherhood & Parenting',
                'womens_world_content_type' => 'Question & Discussion',
                'womens_world_target_audience' => ['Mothers', 'Working Women', 'Young Women'],
                'womens_world_featured_topics' => ['Parenting', 'Work-Life Balance', 'Mental Health'],
                'womens_world_life_stage' => 'Mother',
                'womens_world_themes' => ['Parenting', 'Health', 'Mental Wellness', 'Career'],
                'womens_world_support_requests' => [
                    'Looking for Advice',
                    'Looking for Mentorship',
                    'Looking for Career Guidance',
                ],
                'womens_world_community_groups' => ['Mothers', 'Working Women', 'Teachers'],
                'womens_world_ask_community' => 'What support systems helped you most when you returned to work after becoming a parent?',
                'womens_world_support_organizations' => "Anganwadi worker network (local ward)\nWorking women's helpline — state women's commission",
                'womens_world_visibility' => 'registered_users',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sensitiveAnonymousStory(): array
    {
        return [
            'slug' => 'womens-world-workplace-safety-reflection',
            'category' => 'Safety & Security',
            'writing_purpose' => 'Raise Awareness',
            'title' => 'Speaking Up About Workplace Safety (Shared Anonymously)',
            'excerpt' => 'An anonymous reflection on setting boundaries, knowing rights, and finding safe channels for support in the workplace.',
            'featured_image' => 'https://picsum.photos/seed/womens-world-safety/1200/630',
            'tags' => ['Women', 'Safety', 'Legal Awareness', 'Career'],
            'days_ago' => 14,
            'allow_poll' => false,
            'allow_comments' => true,
            'allow_questions' => true,
            'allow_suggestions' => false,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>I wanted to share my experience without revealing my identity because the situation is still recent and my workplace is small.</p>
<h2>Challenge</h2>
<p>Recognizing inappropriate behaviour early and knowing which internal and external channels could help took time I did not feel I had.</p>
<h2>Experience</h2>
<p>Documenting incidents, speaking with a trusted senior colleague, and contacting the internal complaints committee were important steps. External legal counselling helped me understand my options clearly.</p>
<h2>Lessons Learned</h2>
<ul>
    <li>Keep written records with dates and witnesses where possible.</li>
    <li>You are allowed to ask for written responses from HR.</li>
    <li>Support groups reduce isolation even when cases take time to resolve.</li>
</ul>
<h2>Advice to Others</h2>
<p>If you are unsure whether behaviour crosses a line, trust your discomfort and seek confidential advice early.</p>
<h2>Conclusion</h2>
<p>Anonymous sharing can still help other women prepare, respond, and support one another with dignity.</p>
HTML,
            'publish_as' => CommunityPost::PUBLISH_AS_ANONYMOUS,
            'meta' => [
                'womens_world_category' => 'Safety & Security',
                'womens_world_content_type' => 'Awareness Post',
                'womens_world_target_audience' => ['Working Women', 'Professionals', 'Young Women'],
                'womens_world_featured_topics' => ["Women's Rights", 'Mental Health'],
                'womens_world_life_stage' => 'Young Professional',
                'womens_world_themes' => ['Women Empowerment', 'Health', 'Career'],
                'womens_world_support_requests' => ['Looking for Advice'],
                'womens_world_community_groups' => ['Working Women', 'Senior Women'],
                'womens_world_useful_websites' => 'https://www.ncwwomen.org/',
                'womens_world_government_schemes' => 'State women’s commission counselling and legal aid referrals.',
                'womens_world_support_organizations' => "National Commission for Women helpline\nLocal legal aid clinic (district court complex)",
                'womens_world_visibility' => 'private_link',
            ],
        ];
    }
}

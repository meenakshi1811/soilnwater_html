<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;

class CommunityChildrensCornerPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Children\'s Corner Parent Author',
            'email' => 'childrens-corner@example.com',
        ]);

        foreach ($this->childrensCornerPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge([
            'author_bio' => 'Parent and teacher sharing creative, safe submissions from young learners.',
            'parent_name' => 'Priya Sharma',
            'parent_relationship' => 'Mother',
            'parent_mobile' => '9876543210',
            'parent_email' => 'priya.sharma@example.com',
            'child_parent_consent_identity' => true,
            'child_parent_consent_publication' => true,
            'child_parent_consent_original' => true,
            'parent_approved' => true,
            'childrens_corner_submitted_through' => 'Parent',
            'childrens_corner_comments_moderated' => true,
            'childrens_corner_child_friendly_reactions' => true,
            'childrens_corner_privacy_setting' => CommunityContentTaxonomy::childrensCornerDefaultPrivacySetting(),
            'childrens_corner_safety_no_address' => true,
            'childrens_corner_safety_no_harmful' => true,
            'childrens_corner_safety_no_copyright' => true,
            'childrens_corner_safety_no_inappropriate_media' => true,
            'childrens_corner_safety_confirmed' => true,
            'childrens_corner_city' => 'Jaipur',
            'childrens_corner_district' => 'Jaipur',
            'childrens_corner_state' => 'Rajasthan',
        ], $post['meta'] ?? []);

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'childrens-corner',
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
                'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value) || is_array($value)),
                'allow_comments' => true,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function childrensCornerPosts(): array
    {
        return [
            $this->environmentStory(),
            $this->waterPoem(),
            $this->waterQuiz(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function environmentStory(): array
    {
        return [
            'slug' => 'childrens-corner-seed-that-saved-garden',
            'category' => 'Story',
            'title' => 'The Little Seed That Saved a Garden',
            'excerpt' => 'A moral story by Aarav about patience, teamwork, and caring for plants during a dry summer.',
            'featured_image' => 'https://picsum.photos/seed/childrens-story/960/540',
            'tags' => ['Children\'s Corner', 'Story', 'Environment', 'Water Conservation'],
            'days_ago' => 4,
            'body' => <<<'HTML'
<p>Once upon a time in a sunny neighbourhood, a small seed named Tara waited quietly in dry soil. The garden around her was thirsty, and the children wondered if anything would ever grow again.</p>
<p>Aarav and his friends decided to collect leftover water from washed vegetables and share watering duties every evening. They mulched the soil with dry leaves and cheered when the first green sprout appeared.</p>
<p>Tara grew into a strong plant that shaded smaller flowers and reminded everyone that small daily actions can save a whole garden.</p>
<p><strong>Moral:</strong> Save water, work together, and never give up on nature.</p>
HTML,
            'meta' => [
                'child_share_type' => 'Story',
                'child_first_name' => 'Aarav',
                'child_age_group' => '9-12 Years',
                'child_grade_level' => 'Class 5',
                'child_school_name' => 'Green Valley Public School',
                'childrens_corner_themes' => ['Environment', 'Water Conservation', 'Kindness'],
                'childrens_corner_talent_categories' => ['Story Writing'],
                'childrens_corner_achievement' => 'Selected for school eco-club newsletter',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function waterPoem(): array
    {
        return [
            'slug' => 'childrens-corner-drops-of-life-poem',
            'category' => 'Poem',
            'title' => 'Drops of Life',
            'excerpt' => 'A short poem by Riya celebrating every drop of water and the habits that protect it.',
            'featured_image' => 'https://picsum.photos/seed/childrens-poem/960/540',
            'tags' => ['Children\'s Corner', 'Poem', 'Water Conservation'],
            'days_ago' => 9,
            'body' => <<<'HTML'
<p><em>Drops of Life</em> — by Riya</p>
<p>Turn the tap off tight,<br>
Morning sun, golden light.<br>
Rain barrels fill with cheer,<br>
Every drop we hold dear.</p>
<p>Plants drink slow, roots grow deep,<br>
Save the water while we sleep.<br>
Share the message, loud and clear:<br>
Water is precious — handle with care!</p>
HTML,
            'meta' => [
                'child_share_type' => 'Poem',
                'child_first_name' => 'Riya',
                'child_age_group' => '6-8 Years',
                'child_grade_level' => 'Class 3',
                'child_school_name' => 'Sunrise Primary School',
                'childrens_corner_themes' => ['Water Conservation', 'Nature', 'Education'],
                'childrens_corner_talent_categories' => ['Poetry'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function waterQuiz(): array
    {
        return [
            'slug' => 'childrens-corner-water-saver-quiz',
            'category' => 'Quiz',
            'title' => 'Water Saver Quiz for Young Explorers',
            'excerpt' => 'A fun three-question quiz by Ananya to test everyday water-saving knowledge.',
            'featured_image' => 'https://picsum.photos/seed/childrens-quiz/960/540',
            'tags' => ['Children\'s Corner', 'Quiz', 'Water Conservation', 'Science'],
            'days_ago' => 15,
            'body' => <<<'HTML'
<p>Welcome to Ananya's Water Saver Quiz! Read each question carefully and pick the best answer. Good luck!</p>
<p>Try the interactive quiz below, then discuss your answers with family or classmates.</p>
HTML,
            'meta' => [
                'child_share_type' => 'Quiz',
                'child_first_name' => 'Ananya',
                'child_age_group' => '9-12 Years',
                'child_grade_level' => 'Class 6',
                'child_school_name' => 'Rajasthan Model School',
                'childrens_corner_themes' => ['Water Conservation', 'Science', 'Environment'],
                'childrens_corner_talent_categories' => ['Science', 'Innovation'],
                'childrens_corner_quiz' => [
                    [
                        'question' => 'What is the best way to water plants on a hot afternoon?',
                        'options' => ['At noon in full sun', 'Early morning or evening', 'Only once a month', 'With soap water'],
                        'correct_answer' => 'Early morning or evening',
                    ],
                    [
                        'question' => 'Which habit saves the most water at home?',
                        'options' => ['Leaving the tap running while brushing', 'Fixing leaking taps quickly', 'Washing the driveway daily', 'Using a hose for five minutes'],
                        'correct_answer' => 'Fixing leaking taps quickly',
                    ],
                    [
                        'question' => 'Rainwater harvesting helps us by —',
                        'options' => ['Wasting roof water', 'Recharging groundwater', 'Making plants dry faster', 'Increasing plastic use'],
                        'correct_answer' => 'Recharging groundwater',
                    ],
                ],
            ],
        ];
    }
}

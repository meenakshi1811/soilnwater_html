<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunitySeniorCitizensForumPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    private const SAMPLE_AUDIO_URL = 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3';

    private const SAMPLE_PDF_URL = 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Senior Citizens Forum Author',
            'email' => 'senior-citizens-forum@example.com',
        ]);

        foreach ($this->seniorCitizensForumPosts() as $post) {
            $this->upsertPost($author, $post);
        }
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function upsertPost(User $author, array $post): void
    {
        $meta = array_merge($this->sharedMetaDefaults(), $post['meta'] ?? []);

        if (($meta['senior_citizens_forum_visibility'] ?? 'public') === 'private_link' && blank(data_get($meta, 'senior_citizens_forum_private_link_token'))) {
            $meta['senior_citizens_forum_private_link_token'] = Str::random(48);
        }

        CommunityPost::query()->updateOrCreate(
            ['slug' => $post['slug']],
            [
                'user_id' => $author->id,
                'content_type' => 'senior-citizens-forum',
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
                'allow_poll' => false,
                'status' => CommunityPost::STATUS_PUBLISHED,
                'published_at' => now()->subDays($post['days_ago']),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function sharedMetaDefaults(): array
    {
        return [
            'author_bio' => 'Retired educator and community volunteer from Rajasthan, sharing life lessons for younger generations.',
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'senior_citizens_forum_visibility' => CommunityContentTaxonomy::seniorCitizensForumDefaultVisibilitySetting(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function seniorCitizensForumPosts(): array
    {
        return [
            $this->lifetimeCommunityServiceStory(),
            $this->villageHeritageAndWaterMemories(),
            $this->retirementWellnessAdvice(),
            $this->privateFamilyMemoir(),
        ];
    }

    /**
     * Full public post with every Senior Citizens Forum field populated.
     *
     * @return array<string, mixed>
     */
    private function lifetimeCommunityServiceStory(): array
    {
        return [
            'slug' => 'senior-citizens-forum-lifetime-community-service',
            'category' => 'Community Service',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Fifty Years of Teaching, Farming, and Village Water Stewardship',
            'excerpt' => 'A retired teacher and farmer reflects on community service, water conservation, family values, and the lessons he hopes every young person will carry forward.',
            'featured_image' => 'https://picsum.photos/seed/senior-citizens-forum-service/1200/630',
            'tags' => ['Senior Citizens', 'Community Service', 'Education', 'Agriculture', 'Water Conservation', 'Wisdom'],
            'days_ago' => 3,
            'video' => [
                'type' => 'youtube',
                'url' => 'https://www.youtube.com/watch?v=EngW7tLk6R8',
                'video_id' => 'EngW7tLk6R8',
            ],
            'body' => <<<'HTML'
<h2>Background</h2>
<p>I was born in a small village near Sanganer, where our family farmed wheat and taught children in the local school during harvest breaks. Over five decades I served as a government school teacher, panchayat volunteer, and later as a water literacy trainer after retirement.</p>
<h2>Experience</h2>
<p>We dug community soak pits, mapped hand pumps that failed each summer, and trained women’s self-help groups to monitor tanker schedules. The work was slow, but trust grew when we showed up every week without expecting praise.</p>
<h2>Lessons Learned</h2>
<ul>
    <li>Communities change when elders listen before they advise.</li>
    <li>Small savings and small water savings both compound over time.</li>
    <li>Teaching is not only a profession — it is a lifelong duty to the next generation.</li>
</ul>
<h2>Advice</h2>
<p>Stay useful after retirement. Mentor one young person each year. Document your village stories before memory fades.</p>
<h2>Conclusion</h2>
<p>A long life becomes meaningful when service outlives salary, titles, and recognition.</p>
HTML,
            'meta' => [
                'senior_citizens_forum_category' => 'Community Service',
                'senior_citizens_forum_content_type' => 'Personal Experience',
                'senior_citizens_forum_age_group' => '76–80 Years',
                'senior_citizens_forum_life_journey_categories' => [
                    'Teacher',
                    'Farmer',
                    'Government Employee',
                    'Social Worker',
                    'Retired Professional',
                ],
                'senior_citizens_forum_key_lessons' => [
                    'Respect your parents.',
                    'Save regularly.',
                    'Education is the best investment.',
                    'Health is more important than wealth.',
                    'Serve your community without expecting applause.',
                ],
                'senior_citizens_forum_themes' => [
                    'Family Values',
                    'Education',
                    'Health',
                    'Agriculture',
                    'Community Service',
                    'Water Conservation',
                    'Environment',
                    'Leadership',
                    'Culture',
                    'Spirituality',
                    'Retirement',
                ],
                'senior_citizens_forum_advice_to_youth' => "Work honestly every day, even when no one is watching.\nRespect water as you respect your elders — both sustain life.\nLearn a skill, teach a skill, and never stop reading.\nWhen you succeed, lift someone younger than you.",
                'senior_citizens_forum_community_contributions' => [
                    'Teacher',
                    'Volunteer',
                    'Social Worker',
                    'Community Leader',
                    'Farmer',
                    'Environmental Activist',
                ],
                'senior_citizens_forum_achievements' => [
                    [
                        'award_name' => 'State Best Teacher Award',
                        'year' => '1994',
                        'description' => 'Recognized for improving rural literacy rates and introducing science clubs in government schools.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/scf-award-teacher/200/200',
                            'name' => 'Best Teacher Award.jpg',
                            'type' => 'image',
                        ],
                        'certificate' => [
                            'url' => self::SAMPLE_PDF_URL,
                            'name' => 'State Best Teacher Certificate.pdf',
                            'type' => 'application',
                        ],
                    ],
                    [
                        'award_name' => 'Jal Shakti Community Champion',
                        'year' => '2018',
                        'description' => 'Honoured for training ward volunteers on rainwater harvesting and hand-pump maintenance.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/scf-award-water/200/200',
                            'name' => 'Jal Shakti Award.jpg',
                            'type' => 'image',
                        ],
                        'certificate' => [
                            'url' => self::SAMPLE_PDF_URL,
                            'name' => 'Jal Shakti Certificate.pdf',
                            'type' => 'application',
                        ],
                    ],
                ],
                'senior_citizens_forum_audio' => [
                    'type' => 'upload',
                    'name' => 'Village water memories.mp3',
                    'url' => self::SAMPLE_AUDIO_URL,
                ],
                'senior_citizens_forum_video_type' => 'Community History',
                'senior_citizens_forum_family_background' => 'Our family settled in Sanganer three generations ago as weavers and small farmers. My grandfather walked five kilometres daily to reach the only school in the block.',
                'senior_citizens_forum_traditions' => 'We celebrate harvest with a community meal, share the first chapati with elders, and maintain a family diary of important dates and decisions.',
                'senior_citizens_forum_cultural_practices' => 'Monsoon prayers at the village stepwell, collective cleaning of the pond before summer, and storytelling nights for grandchildren.',
                'senior_citizens_forum_family_values' => 'Honesty, humility, hospitality, and responsibility toward neighbours — especially during water shortages.',
                'senior_citizens_forum_ask_community' => 'How do you stay active and useful after retirement while also taking care of your health?',
                'senior_citizens_forum_visibility' => 'public',
                'senior_citizens_forum_intergenerational_connections' => [
                    'Advice for Students',
                    'Advice for Entrepreneurs',
                    'Advice for Parents',
                    'Advice for Society',
                ],
                'senior_citizens_forum_preserve_digital_legacy' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function villageHeritageAndWaterMemories(): array
    {
        return [
            'slug' => 'senior-citizens-forum-village-heritage-water-memories',
            'category' => 'Village Memories',
            'writing_purpose' => 'Raise Awareness',
            'title' => 'How Our Village Protected Water Before Pipelines Arrived',
            'excerpt' => 'Memories of stepwells, shared ponds, and seasonal rules that kept our village supplied — lessons for today’s conservation efforts.',
            'featured_image' => 'https://picsum.photos/seed/senior-citizens-forum-village/1200/630',
            'tags' => ['Village', 'Heritage', 'Water', 'Agriculture', 'Culture'],
            'days_ago' => 9,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>Before municipal pipelines reached our lane, every household knew which pond was for cattle, which stepwell was for drinking, and which days were reserved for washing clothes downstream.</p>
<h2>Experience</h2>
<p>Elders enforced simple rules without police or fines. Children learned by watching. When a family broke the rule, the panchayat resolved it over tea, not social media.</p>
<h2>Lessons Learned</h2>
<p>Technology helps, but discipline and shared ownership protect water longer than any tanker contract.</p>
<h2>Conclusion</h2>
<p>Younger generations can combine old community habits with new tools — that is the bridge we must build.</p>
HTML,
            'meta' => [
                'senior_citizens_forum_category' => 'Village Memories',
                'senior_citizens_forum_content_type' => 'Historical Account',
                'senior_citizens_forum_age_group' => '80+ Years',
                'senior_citizens_forum_life_journey_categories' => ['Farmer', 'Community Leader'],
                'senior_citizens_forum_key_lessons' => [
                    'Shared rules protect shared resources.',
                    'Children copy what elders do, not only what elders say.',
                ],
                'senior_citizens_forum_themes' => [
                    'Agriculture',
                    'Water Conservation',
                    'Environment',
                    'Culture',
                    'Patriotism',
                ],
                'senior_citizens_forum_advice_to_youth' => 'Visit your ancestral village if you can. Ask elders how water was managed before taps. Write it down.',
                'senior_citizens_forum_community_contributions' => ['Farmer', 'Community Leader', 'Volunteer'],
                'senior_citizens_forum_achievements' => [
                    [
                        'award_name' => 'Village Heritage Preservation Certificate',
                        'year' => '2010',
                        'description' => 'Documented oral histories of stepwell maintenance for the district archives.',
                        'photo' => [
                            'url' => 'https://picsum.photos/seed/scf-heritage-award/200/200',
                            'name' => 'Heritage certificate photo.jpg',
                            'type' => 'image',
                        ],
                    ],
                ],
                'senior_citizens_forum_video_type' => 'Life Story Recording',
                'senior_citizens_forum_family_background' => 'We belong to a weaving community that migrated seasonally between farm and loom work.',
                'senior_citizens_forum_traditions' => 'Cleaning the stepwell together on Akshaya Tritiya and marking water levels on a shared slate.',
                'senior_citizens_forum_cultural_practices' => 'Songs during irrigation turns and folk tales about drought years.',
                'senior_citizens_forum_family_values' => 'Never waste water in front of guests; always offer drinking water first.',
                'senior_citizens_forum_ask_community' => 'What life lesson changed your life when you were young?',
                'senior_citizens_forum_visibility' => 'senior_citizens_community',
                'senior_citizens_forum_intergenerational_connections' => [
                    'Advice for Students',
                    'Advice for Society',
                ],
                'senior_citizens_forum_preserve_digital_legacy' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function retirementWellnessAdvice(): array
    {
        return [
            'slug' => 'senior-citizens-forum-retirement-wellness-routine',
            'category' => 'Health & Wellness',
            'writing_purpose' => 'Help Community',
            'title' => 'Staying Active After Retirement: My Daily Routine at 68',
            'excerpt' => 'A retired engineer shares a practical wellness routine — walking, reading, volunteering, and staying socially connected.',
            'featured_image' => 'https://picsum.photos/seed/senior-citizens-forum-wellness/1200/630',
            'tags' => ['Health', 'Retirement', 'Wellness', 'Advice'],
            'days_ago' => 14,
            'allow_suggestions' => true,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>When I retired from the public works department, I feared idle mornings more than financial change. Structure saved me.</p>
<h2>Experience</h2>
<p>I walk 40 minutes after sunrise, tutor neighbourhood children in maths twice a week, and attend a senior citizens’ discussion group every Friday.</p>
<h2>Advice</h2>
<p>Start with one habit you enjoy. Add social contact before you add complicated fitness goals.</p>
<h2>Conclusion</h2>
<p>Retirement is not an ending — it is a different kind of contribution.</p>
HTML,
            'meta' => [
                'senior_citizens_forum_category' => 'Health & Wellness',
                'senior_citizens_forum_content_type' => 'Advice',
                'senior_citizens_forum_age_group' => '66–70 Years',
                'senior_citizens_forum_life_journey_categories' => ['Engineer', 'Retired Professional', 'Volunteer'],
                'senior_citizens_forum_key_lessons' => [
                    'Routine protects mental health after retirement.',
                    'Teaching keeps the mind young.',
                ],
                'senior_citizens_forum_themes' => ['Health', 'Retirement', 'Education', 'Leadership'],
                'senior_citizens_forum_advice_to_youth' => "Do not wait for retirement to build friendships outside your office.\nInvest in health in your 30s and 40s — your 70-year-old self will thank you.",
                'senior_citizens_forum_community_contributions' => ['Volunteer', 'Teacher'],
                'senior_citizens_forum_audio' => [
                    'type' => 'recording',
                    'name' => 'Morning walk reflection.webm',
                    'url' => self::SAMPLE_AUDIO_URL,
                ],
                'senior_citizens_forum_ask_community' => 'How do you stay active after retirement?',
                'senior_citizens_forum_visibility' => 'registered_users',
                'senior_citizens_forum_intergenerational_connections' => [
                    'Advice for Parents',
                    'Advice for Entrepreneurs',
                ],
                'senior_citizens_forum_preserve_digital_legacy' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function privateFamilyMemoir(): array
    {
        return [
            'slug' => 'senior-citizens-forum-family-memoir-private',
            'category' => 'Memoirs',
            'writing_purpose' => 'Personal Experience',
            'title' => 'Letters to My Grandchildren (Family Memoir — Private Link)',
            'excerpt' => 'A grandmother’s private memoir shared only with family through a secure link — stories of migration, loss, faith, and perseverance.',
            'featured_image' => 'https://picsum.photos/seed/senior-citizens-forum-memoir/1200/630',
            'tags' => ['Memoir', 'Family', 'Private'],
            'days_ago' => 21,
            'allow_sharing' => false,
            'body' => <<<'HTML'
<h2>Background</h2>
<p>I wrote these letters during the pandemic for my grandchildren, hoping they would know our family story even if I am not there to tell it in person.</p>
<h2>Experience</h2>
<p>We left our home district during a difficult drought year. Your great-grandfather sold his cart to pay for the train journey. We rebuilt slowly, one school fee and one festival at a time.</p>
<h2>Lessons Learned</h2>
<p>Faith without effort is incomplete. Effort without gratitude becomes bitterness.</p>
<h2>Conclusion</h2>
<p>Keep this story in the family. Share it when you are ready.</p>
HTML,
            'meta' => [
                'senior_citizens_forum_category' => 'Memoirs',
                'senior_citizens_forum_content_type' => 'Memoir',
                'senior_citizens_forum_age_group' => '71–75 Years',
                'senior_citizens_forum_life_journey_categories' => ['Homemaker', 'Social Worker'],
                'senior_citizens_forum_key_lessons' => [
                    'Family stories are inheritance too.',
                    'Forgiveness is a daily practice.',
                ],
                'senior_citizens_forum_themes' => ['Family Values', 'Spirituality', 'Culture'],
                'senior_citizens_forum_advice_to_youth' => 'Call your grandparents while you can. Ask one question about their childhood every month.',
                'senior_citizens_forum_community_contributions' => ['Volunteer'],
                'senior_citizens_forum_family_background' => 'Our family migrated from Nagaur district during the 1960s drought and settled in Jaipur’s old city.',
                'senior_citizens_forum_traditions' => 'Writing a blessing note for each grandchild on their birthday.',
                'senior_citizens_forum_cultural_practices' => 'Evening prayers together and reading one page from our family notebook.',
                'senior_citizens_forum_family_values' => 'Loyalty, prayer, education for girls, and honesty in business dealings.',
                'senior_citizens_forum_video_type' => 'Family Message',
                'senior_citizens_forum_visibility' => 'private_link',
                'senior_citizens_forum_intergenerational_connections' => ['Advice for Students', 'Advice for Parents'],
                'senior_citizens_forum_preserve_digital_legacy' => true,
            ],
        ];
    }
}

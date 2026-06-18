<?php

namespace Database\Seeders;

use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityPostFormFields;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityBookPostSeeder extends Seeder
{
    private const LOCATION = 'Jaipur, Rajasthan, India';

    private const LOCATION_LAT = 26.9124000;

    private const LOCATION_LNG = 75.7873000;

    public function run(): void
    {
        $author = User::query()->first() ?? User::factory()->create([
            'name' => 'Community Book Author',
            'email' => 'community-books@example.com',
        ]);

        foreach ($this->bookPosts() as $post) {
            $bookPages = $post['book_pages'];
            $meta = array_merge($post['meta'], [
                'book_pages' => $bookPages,
                'author_bio' => 'Demo author for multi-page book-style community posts.',
            ]);

            foreach (CommunityPostFormFields::fieldsFor($post['content_type']) as $field) {
                if (! array_key_exists($field['name'], $meta)) {
                    $meta[$field['name']] = $this->defaultFieldValue($field);
                }
            }

            CommunityPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'user_id' => $author->id,
                    'content_type' => $post['content_type'],
                    'category' => $post['category'],
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'body' => CommunityPost::bodyFromBookPages($bookPages),
                    'featured_image_path' => $post['featured_image'],
                    'featured_images' => [$post['featured_image']],
                    'tags' => $post['tags'],
                    'location_type' => CommunityPost::LOCATION_TYPE_CITY,
                    'location' => self::LOCATION,
                    'location_lat' => self::LOCATION_LAT,
                    'location_lng' => self::LOCATION_LNG,
                    'video' => null,
                    'meta' => array_filter($meta, fn ($value) => filled($value) || is_bool($value)),
                    'allow_comments' => true,
                    'status' => CommunityPost::STATUS_PUBLISHED,
                    'published_at' => now()->subDays($post['days_ago']),
                ]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function bookPosts(): array
    {
        return [
            [
                'slug' => 'book-story-neighbourhood-rain',
                'content_type' => 'stories',
                'category' => 'Inspirational Stories',
                'title' => 'The Night the Neighbourhood Waited for Rain',
                'excerpt' => 'A three-page story about hope, patience, and a community that learned to listen to the sky again.',
                'featured_image' => 'https://picsum.photos/seed/soil-water-book-story/960/540',
                'tags' => ['Stories', 'Book Layout', 'Inspirational', 'Demo'],
                'days_ago' => 2,
                'meta' => [
                    'story_genre' => 'Inspirational',
                    'mood_or_theme' => 'Hope and resilience',
                    'reading_time' => '8',
                ],
                'book_pages' => [
                    ['content' => '<p>The summer had stretched longer than anyone in Ward 14 remembered. The hand pump near the temple coughed out muddy sips in the morning and fell silent by noon. Children still laughed, but their games ended earlier now, and mothers counted every bucket before sunset.</p><p>On the fourth evening of waiting, Mrs. Kapoor placed a brass plate of water outside her door and invited the lane to do the same. Nobody called it a ritual. They simply wanted to see, together, how little remained.</p>'],
                    ['content' => '<p>When the clouds finally gathered, the lane did not rush indoors. Old men brought chairs into the courtyard. Teenagers switched off their music. Even the stray dogs settled beneath the neem tree as if they too understood that some moments ask for stillness.</p><p>The first drops were shy. Then the sky opened with a sound like applause. People cheered, then laughed at themselves for cheering. Water is never only water in a dry town. It is memory, relief, and the promise that tomorrow can be ordinary again.</p>'],
                    ['content' => '<p>By morning the gutters were running clear. Children sailed paper boats through puddles. The hand pump returned to life with a steady, grateful rhythm. Mrs. Kapoor said little, but she smiled more than she had in months.</p><p>Years later, when newcomers asked why the lane still kept a shared water chart on the temple wall, elders would point to that night and say: we learned to wait together. That, they agreed, was the real rain.</p>'],
                ],
            ],
            [
                'slug' => 'book-biography-dr-mehta',
                'content_type' => 'biography',
                'category' => 'Scientists',
                'title' => 'Dr. Rajesh Mehta: The Scientist Who Taught Wells to Listen',
                'excerpt' => 'A short biography in four pages on the watershed pioneer who changed how Rajasthan villages measure groundwater.',
                'featured_image' => 'https://picsum.photos/seed/soil-water-book-bio/960/540',
                'tags' => ['Biography', 'Book Layout', 'Science', 'Demo'],
                'days_ago' => 4,
                'meta' => [
                    'subject_name' => 'Dr. Rajesh Mehta',
                    'subject_field' => 'Water conservation scientist',
                    'time_period' => '1948-2024',
                    'key_achievements' => "Designed low-cost recharge pits adopted in 120 villages.\nTrained more than 2,000 student volunteers.\nPublished field manuals in Hindi and English.",
                ],
                'book_pages' => [
                    ['content' => '<p>Dr. Rajesh Mehta was born in a farming family outside Sikar, where drought was discussed at dinner the way other children discussed cricket scores. He studied agricultural science in Jaipur, but his notebooks were always filled with sketches of channels, berms, and the quiet geometry of water moving underground.</p><p>Colleagues remember him as soft-spoken and stubborn in equal measure. He believed communities did not need grand promises. They needed tools they could carry, repair, and explain to their neighbours without embarrassment.</p>'],
                    ['content' => '<p>His breakthrough came not in a laboratory, but in a field where farmers had stopped trusting official measurements. Dr. Mehta built a simple monitoring frame from bamboo, wire, and painted markers that even schoolchildren could read. Within a season, three neighbouring villages asked for copies.</p><p>He refused patents and personal credit. "A well belongs to everyone who drinks from it," he told a local reporter. That sentence became the motto of the volunteer network that later spread his method across the district.</p>'],
                    ['content' => '<p>By the 1990s, recharge pits based on his designs were protecting monsoon runoff before it escaped down paved roads. Government officers visited, took notes, and sometimes took photos, but Dr. Mehta remained happiest in muddy boots, showing teenagers how to measure depth with nothing more than a rope and a stone.</p><p>He wrote at night, translating technical guidance into plain language. His manuals still circulate in village libraries, their pages softened by use, their margins filled with careful handwriting from readers who found one more useful detail to add.</p>'],
                    ['content' => '<p>When he died in 2024, mourners lined the road to his old demonstration farm. Former students spoke, farmers nodded, and a group of women from the first pilot village placed a clay pot of water on the gate. It was the simplest tribute he could have wanted.</p><p>Today, younger engineers cite his name when they argue for humble technology and local ownership. Dr. Mehta never became famous in the way television prefers. He became necessary in the way good water does.</p>'],
                ],
            ],
            [
                'slug' => 'book-autobiography-village-student',
                'content_type' => 'autobiography',
                'category' => 'Educational Journey',
                'title' => 'From Village Student to Water Volunteer',
                'excerpt' => 'An autobiographical account in three pages about learning, leaving, and returning home with a purpose.',
                'featured_image' => 'https://picsum.photos/seed/soil-water-book-auto/960/540',
                'tags' => ['Autobiography', 'Book Layout', 'Education', 'Demo'],
                'days_ago' => 6,
                'meta' => [
                    'autobiography_type' => 'Complete Life Story',
                ],
                'book_pages' => [
                    [
                        'title' => 'Chapter 1 – Village Student',
                        'summary' => 'Early school years and learning to care about water.',
                        'content' => '<p>I was the first in my family to attend college, and I carried my village with me in every notebook. Classmates talked about internships in tall glass buildings. I thought about the cracked tank behind our school and the afternoons we missed when the supply failed.</p><p>In my second year, a professor asked us to write about a resource crisis in our hometown. I wrote twelve pages without stopping. That assignment did not make me a writer. It made me honest about what I wanted to do with my degree.</p>',
                        'language' => 'en',
                    ],
                    [
                        'title' => 'Chapter 2 – City Lessons',
                        'summary' => 'Working away from home and realizing what mattered.',
                        'content' => '<p>After graduation I took a job in the city because I thought experience had to happen far away to count. I learned spreadsheets, presentation decks, and how to speak in meetings where everyone sounded certain. I also learned loneliness. Success tasted thinner than I expected when nobody around me knew the well where I had filled buckets as a child.</p><p>On a visit home, my mother did not ask about salary. She asked whether I still knew how to fix the hand pump. I did not. That answer sat with me for weeks.</p>',
                        'language' => 'en',
                    ],
                    [
                        'title' => 'Chapter 3 – Returning Home',
                        'summary' => 'Coming back to serve the community with purpose.',
                        'content' => '<p>I returned to Jaipur and joined a small nonprofit working on community water literacy. My city skills finally found a use: mapping complaints, training volunteers, translating reports. But the work that mattered most was simpler. Sitting with neighbours. Listening. Showing up when the tanker was late.</p><p>I am not a hero in this story. I am a student who came back. If these pages help one young reader believe that local work is real work, then every awkward meeting and every long bus ride home was worth it.</p>',
                        'language' => 'en',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $field
     */
    private function defaultFieldValue(array $field): mixed
    {
        if (($field['type'] ?? '') === 'checkbox') {
            return true;
        }

        if (($field['type'] ?? '') === 'select') {
            return $field['options'][0] ?? 'General';
        }

        if (($field['type'] ?? '') === 'textarea') {
            return 'Sample '.$field['label'].' for book layout demo.';
        }

        return Str::headline(str_replace('_', ' ', $field['name'])).' sample';
    }
}

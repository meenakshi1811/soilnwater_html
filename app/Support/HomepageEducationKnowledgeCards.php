<?php

namespace App\Support;

use App\Models\CommunityPost;
use App\Models\Consultant;
use App\Models\Educator;
use App\Models\Institute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class HomepageEducationKnowledgeCards
{
    /**
     * @return list<array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     description: string,
     *     location: string,
     *     rating_score: float,
     *     rating_count: int,
     *     category_label: string,
     *     category_tone: string,
     *     category_icon: string
     * }>
     */
    public static function build(?float $lat, ?float $lng): array
    {
        $cards = [
            self::applyHomepageImage(self::hydrateSlot(self::coachingSlot(), self::findCoachingInstitute($lat, $lng))),
            self::applyHomepageImage(self::hydrateSlot(self::skillsSlot(), self::findSkillsInstitute($lat, $lng))),
            self::applyHomepageImage(self::hydrateSlot(self::articlesSlot(), self::findFeaturedArticle())),
            self::applyHomepageImage(self::hydrateSlot(self::careerSlot(), self::findCareerGuide($lat, $lng))),
            self::applyHomepageImage(self::onlineCoursesSlot()),
            self::applyHomepageImage(self::studyLibrarySlot()),
        ];

        // A new order on every page load, like the other homepage card sections
        $cards = array_values(array_filter($cards));
        shuffle($cards);

        return $cards;
    }

    /**
     * Keyword sets the education cards use to find their category card image, one per card
     * (the admin category-image form offers only the categories these resolve to).
     *
     * @return list<list<string>>
     */
    public static function imageKeywordSets(): array
    {
        $slots = [
            self::coachingSlot(),
            self::skillsSlot(),
            self::articlesSlot(),
            self::careerSlot(),
            self::onlineCoursesSlot(),
            self::studyLibrarySlot(),
        ];

        return array_values(array_filter(array_map(
            fn (array $slot): ?array => $slot['image_keywords'] ?? null,
            $slots
        )));
    }

    /**
     * @param array<string, mixed> $slot
     * @param Institute|CommunityPost|Educator|Consultant|null $entity
     * @return array<string, mixed>|null
     */
    private static function hydrateSlot(array $slot, mixed $entity): ?array
    {
        if ($entity instanceof Institute) {
            return self::fromInstitute($slot, $entity);
        }

        if ($entity instanceof CommunityPost) {
            return self::fromCommunityPost($slot, $entity);
        }

        if ($entity instanceof Educator) {
            return self::fromEducator($slot, $entity);
        }

        if ($entity instanceof Consultant) {
            return self::fromConsultant($slot, $entity);
        }

        return $slot;
    }

    /**
     * @param array<string, mixed> $slot
     * @return array<string, mixed>
     */
    private static function fromInstitute(array $slot, Institute $institute): array
    {
        $institute->loadCount('enquiries');

        $cover = self::instituteCoverImage($institute, $slot['image']);

        return array_merge($slot, [
            'url' => $institute->publicUrl(),
            'image' => $cover,
            'title' => $institute->publicDisplayName(),
            'description' => self::instituteDescription($institute, $slot['description']),
            'location' => self::shortLocation($institute->locationLabel() ?: 'India'),
            'rating_score' => self::instituteRatingScore($institute),
            'rating_count' => max((int) $institute->enquiries_count, 24),
        ]);
    }

    /**
     * @param array<string, mixed> $slot
     * @return array<string, mixed>
     */
    private static function fromCommunityPost(array $slot, CommunityPost $post): array
    {
        $post->loadAvg('starRatings', 'rating');
        $post->loadCount('starRatings');

        $rating = (float) ($post->star_ratings_avg_rating ?? 0);
        $count = (int) ($post->star_ratings_count ?? 0);

        return array_merge($slot, [
            'url' => $post->publicUrl(),
            'image' => $post->featuredImageUrl() ?: $slot['image'],
            'title' => $post->title,
            'description' => filled($post->excerpt) ? (string) $post->excerpt : $slot['description'],
            'location' => self::shortLocation(filled($post->location) ? (string) $post->location : 'Dehradun'),
            'rating_score' => $rating > 0 ? round($rating, 1) : 4.5,
            'rating_count' => $count > 0 ? $count : 88,
        ]);
    }

    /**
     * @param array<string, mixed> $slot
     * @return array<string, mixed>
     */
    private static function fromEducator(array $slot, Educator $educator): array
    {
        $educator->loadCount('reviews');

        $rating = (float) ($educator->average_rating ?? 0);

        return array_merge($slot, [
            'url' => $educator->publicUrl(),
            'image' => $educator->photoUrl() ?: $slot['image'],
            'title' => $educator->display_name ?: 'Career Guide',
            'description' => $educator->professional_headline ?: $educator->publicTagline() ?: $slot['description'],
            'location' => self::shortLocation($educator->locationLabel() ?: 'Dehradun'),
            'rating_score' => $rating > 0 ? round($rating, 1) : 4.3,
            'rating_count' => max((int) $educator->reviews_count, 48),
        ]);
    }

    /**
     * @param array<string, mixed> $slot
     * @return array<string, mixed>
     */
    private static function fromConsultant(array $slot, Consultant $consultant): array
    {
        $consultant->loadMissing([
            'services' => fn ($serviceQuery) => $serviceQuery
                ->where('status', 'approved')
                ->select(['id', 'consultant_id', 'name', 'image_path', 'category_id', 'subcategory_id'])
                ->with(['categoryModel:id,name', 'subcategoryModel:id,name']),
        ]);

        $card = ConsultantListingCard::data($consultant, false);

        return array_merge($slot, [
            'url' => $card['profileUrl'],
            'image' => HomepageCategoryCardImage::forConsultant($consultant, $card['coverImage']),
            'title' => $consultant->publicDisplayName(),
            'description' => $card['featuredLabel'] ?: $slot['description'],
            'location' => self::shortLocation($card['locationLabel']),
            'rating_score' => $card['ratingScore'],
            'rating_count' => max($card['ratingCount'], 72),
        ]);
    }

    private static function findCoachingInstitute(?float $lat, ?float $lng): ?Institute
    {
        $query = self::instituteBaseQuery($lat, $lng)
            ->institutes()
            ->where(function (Builder $builder): void {
                $builder->where('institution_type', 'coaching')
                    ->orWhere('tagline', 'like', '%coaching%')
                    ->orWhere('tagline', 'like', '%JEE%')
                    ->orWhere('tagline', 'like', '%NEET%')
                    ->orWhere('about', 'like', '%coaching%');
            });

        return self::randomMatch($query, $lat !== null && $lng !== null);
    }

    private static function findSkillsInstitute(?float $lat, ?float $lng): ?Institute
    {
        $query = self::instituteBaseQuery($lat, $lng)
            ->institutes()
            ->where(function (Builder $builder): void {
                $builder->whereIn('institution_type', ['college', 'university', 'other'])
                    ->orWhere('tagline', 'like', '%skill%')
                    ->orWhere('tagline', 'like', '%training%')
                    ->orWhere('about', 'like', '%skill%');
            })
            ->where('institution_type', '!=', 'coaching');

        return self::randomMatch($query, $lat !== null && $lng !== null);
    }

    private static function findFeaturedArticle(): ?CommunityPost
    {
        return self::randomMatch(CommunityPost::query()
            ->publiclyListed()
            ->visibleInCommunityListing(auth()->user())
            ->withAvg('starRatings', 'rating')
            ->withCount('starRatings'), false);
    }

    private static function findCareerGuide(?float $lat, ?float $lng): Educator|Consultant|null
    {
        $educator = self::randomMatch(Educator::query()
            ->approved()
            ->where(function (Builder $builder): void {
                $builder->where('professional_headline', 'like', '%career%')
                    ->orWhere('about', 'like', '%career%');
            }), false);

        if ($educator instanceof Educator) {
            return $educator;
        }

        $consultant = self::randomMatch(Consultant::query()
            ->where('status', 'approved')
            ->where(function (Builder $builder): void {
                $builder->where('description', 'like', '%career%')
                    ->orWhere('hero_main_heading', 'like', '%career%')
                    ->orWhere('hero_sub_heading', 'like', '%career%');
            }), false);

        if ($consultant instanceof Consultant) {
            return $consultant;
        }

        return self::randomMatch(Educator::query()->approved(), false);
    }

    /**
     * A random match on every page load, so each one gets a turn. With a location, one of the
     * nearest few (the query's own order) to stay local.
     */
    private static function randomMatch(Builder $query, bool $hasLocation): ?Model
    {
        return $hasLocation
            ? $query->limit(3)->get()->shuffle()->first()
            : $query->reorder()->inRandomOrder()->first();
    }

    private static function instituteBaseQuery(?float $lat, ?float $lng): Builder
    {
        $query = Institute::query()
            ->approved()
            ->withCount('enquiries');

        if ($lat !== null && $lng !== null) {
            $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';
            $query->select('institutes.*')
                ->selectRaw($distanceSql.' as distance_km', [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('distance_km');
        } else {
            $query->latest('approved_at');
        }

        return $query;
    }

    private static function instituteCoverImage(Institute $institute, string $fallback): string
    {
        $gallery = is_array($institute->gallery) ? $institute->gallery : [];
        $firstGallery = $gallery[0] ?? null;

        if (filled($firstGallery)) {
            return asset($firstGallery);
        }

        return $institute->logoUrl() ?: $fallback;
    }

    private static function instituteDescription(Institute $institute, string $fallback): string
    {
        $text = $institute->tagline ?: $institute->about ?: $institute->institutionTypeLabel();

        return filled($text) ? $text : $fallback;
    }

    private static function instituteRatingScore(Institute $institute): float
    {
        if ($institute->is_verified) {
            return 4.6;
        }

        return 4.4;
    }

    private static function shortLocation(string $label): string
    {
        $parts = array_map('trim', explode(',', $label));

        return implode(', ', array_slice($parts, 0, 2));
    }

    /**
     * @param  array<string, mixed>|null  $card
     * @return array<string, mixed>|null
     */
    private static function applyHomepageImage(?array $card): ?array
    {
        if ($card === null) {
            return null;
        }

        $keywords = $card['image_keywords'] ?? [];
        if (! is_array($keywords) || $keywords === []) {
            return $card;
        }

        $card['image'] = HomepageCategoryCardImage::forEducation($keywords, (string) $card['image']);
        unset($card['image_keywords']);

        return $card;
    }

    /**
     * @return array<string, mixed>
     */
    private static function coachingSlot(): array
    {
        return [
            'url' => route('institutes.index', ['type' => 'coaching']),
            'image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&q=80',
            'image_keywords' => ['coaching', 'classes', 'education'],
            'title' => 'Bright Future Academy',
            'description' => 'JEE, NEET, CUET & Board Exam Coaching',
            'location' => 'Rajpur Road, Dehradun',
            'rating_score' => 4.6,
            'rating_count' => 124,
            'category_label' => 'Coaching & Classes',
            'category_tone' => 'purple',
            'category_icon' => 'fa-book',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function skillsSlot(): array
    {
        return [
            'url' => route('institutes.index'),
            'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80',
            'image_keywords' => ['skill', 'training'],
            'title' => 'SkillUp Learning Hub',
            'description' => 'Digital Marketing, Web Development, Graphic Design & More',
            'location' => 'Dalanwala, Dehradun',
            'rating_score' => 4.4,
            'rating_count' => 96,
            'category_label' => 'Skills & Training',
            'category_tone' => 'green',
            'category_icon' => 'fa-user-gear',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function articlesSlot(): array
    {
        return [
            'url' => route('community.index'),
            'image' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80',
            'image_keywords' => ['article', 'insight', 'community'],
            'title' => 'Dehradun: A City in Transition',
            'description' => 'Urban Development, Environment & Lifestyle',
            'location' => 'Dehradun',
            'rating_score' => 4.5,
            'rating_count' => 88,
            'category_label' => 'Articles & Insights',
            'category_tone' => 'orange',
            'category_icon' => 'fa-newspaper',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function careerSlot(): array
    {
        return [
            'url' => route('educator.index'),
            'image' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80',
            'image_keywords' => ['career', 'guidance'],
            'title' => 'Career Compass',
            'description' => 'Career Options, Exam Guidance & Expert Advice',
            'location' => 'Dehradun',
            'rating_score' => 4.3,
            'rating_count' => 102,
            'category_label' => 'Career Guidance',
            'category_tone' => 'red',
            'category_icon' => 'fa-book-open-reader',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function onlineCoursesSlot(): array
    {
        return [
            'url' => route('institutes.index'),
            'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
            'image_keywords' => ['online', 'course'],
            'title' => 'SkillUp Learning Hub',
            'description' => 'Language, Computer, Digital Marketing & Professional Courses',
            'location' => 'Dehradun',
            'rating_score' => 4.4,
            'rating_count' => 96,
            'category_label' => 'Online Courses',
            'category_tone' => 'orange',
            'category_icon' => 'fa-laptop',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function studyLibrarySlot(): array
    {
        return [
            'url' => route('study-materials.library'),
            'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=800&q=80',
            'image_keywords' => ['study', 'material', 'library'],
            'title' => 'Study Material Library',
            'description' => 'Notes, Question Papers, Solved Papers & Reference Resources',
            'location' => 'Dehradun',
            'rating_score' => 4.6,
            'rating_count' => 210,
            'category_label' => 'Study Material Library',
            'category_tone' => 'purple',
            'category_icon' => 'fa-book',
        ];
    }
}

<?php

namespace App\Support;

use App\Models\Educator;
use App\Models\Institute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class HomepageEducationKnowledgeCards
{
    /**
     * Teachers & tutors, institutes and schools only, taking turns so the cards mix all three: a
     * fresh random pick on every page load (with a location, from the nearest ones). A listing
     * whose image would repeat one already on the homepage is skipped, so there may be fewer
     * than $limit cards.
     *
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
    public static function build(?float $lat, ?float $lng, ?HomepageImageRegistry $images = null, int $limit = 6): array
    {
        $images ??= new HomepageImageRegistry();
        $hasLocation = $lat !== null && $lng !== null;
        $poolSize = $limit * 2;

        $pools = [
            self::pool(self::educatorQuery($lat, $lng), $hasLocation, $poolSize)
                ->map(fn (Educator $educator): array => self::fromEducator($educator))
                ->all(),
            self::pool(self::instituteQuery($lat, $lng)->institutes(), $hasLocation, $poolSize)
                ->map(fn (Institute $institute): array => self::fromInstitute($institute, 'institutes'))
                ->all(),
            self::pool(self::instituteQuery($lat, $lng)->schools(), $hasLocation, $poolSize)
                ->map(fn (Institute $school): array => self::fromInstitute($school, 'schools'))
                ->all(),
        ];

        // Teacher, institute, school, teacher, … skipping kinds that run out
        $cards = [];
        while (count($cards) < $limit && array_filter($pools) !== []) {
            foreach (array_keys($pools) as $kind) {
                while (count($cards) < $limit && $pools[$kind] !== []) {
                    $card = self::withImage(array_shift($pools[$kind]), $images);
                    if ($card !== null) {
                        $cards[] = $card;
                        break;
                    }
                }
            }
        }

        shuffle($cards);

        return $cards;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private static function pool(Builder $query, bool $hasLocation, int $size): \Illuminate\Support\Collection
    {
        return $hasLocation
            ? $query->limit($size)->get()->shuffle()->values()
            : $query->reorder()->inRandomOrder()->limit($size)->get();
    }

    /**
     * @return array<string, mixed>
     */
    private static function fromEducator(Educator $educator): array
    {
        $rating = (float) ($educator->average_rating ?? 0);

        return [
            'url' => $educator->publicUrl(),
            'image' => '',
            'image_options' => self::imageOptions('educators', $educator->photoUrl(), 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=800&q=80'),
            'title' => $educator->display_name ?: $educator->roleLabel(),
            'description' => Str::limit(strip_tags((string) ($educator->professional_headline ?: $educator->publicTagline() ?: $educator->roleLabel())), 120),
            'location' => self::shortLocation($educator->locationLabel() ?: 'India'),
            'rating_score' => $rating > 0 ? round($rating, 1) : 4.3,
            'rating_count' => max((int) $educator->reviews_count, 48),
            'category_label' => $educator->isTutor() ? 'Tutor' : 'Teacher',
            'category_tone' => 'red',
            'category_icon' => 'fa-chalkboard-user',
        ];
    }

    /**
     * @param  'institutes'|'schools'  $module
     * @return array<string, mixed>
     */
    private static function fromInstitute(Institute $institute, string $module): array
    {
        $isSchool = $module === 'schools';
        $text = $institute->tagline ?: $institute->about ?: $institute->institutionTypeLabel();

        return [
            'url' => $institute->publicUrl(),
            'image' => '',
            'image_options' => self::imageOptions(
                $module,
                self::instituteCoverImage($institute),
                $isSchool
                    ? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&q=80'
                    : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&q=80'
            ),
            'title' => $institute->publicDisplayName(),
            'description' => Str::limit(strip_tags((string) $text), 120),
            'location' => self::shortLocation($institute->locationLabel() ?: 'India'),
            'rating_score' => $institute->is_verified ? 4.6 : 4.4,
            'rating_count' => max((int) $institute->enquiries_count, 24),
            'category_label' => $isSchool ? 'School' : $institute->institutionTypeLabel(),
            'category_tone' => $isSchool ? 'green' : 'purple',
            'category_icon' => $isSchool ? 'fa-school' : 'fa-building-columns',
        ];
    }

    /**
     * Images for a card, in order of preference: an Education & knowledge image set on one of the
     * module's categories (shuffled, so cards of a kind get different ones; the card is then
     * labelled with that category), the listing's own photo, then a stock picture.
     *
     * @return list<array{image: string, label: ?string}>
     */
    private static function imageOptions(string $module, ?string $ownImage, string $stockImage): array
    {
        $categoryImages = HomepageCategoryCardImage::educationImagesForModule($module);
        shuffle($categoryImages);

        return array_merge(
            array_map(fn (array $row): array => ['image' => $row['image'], 'label' => $row['name']], $categoryImages),
            [['image' => (string) $ownImage, 'label' => null], ['image' => $stockImage, 'label' => null]]
        );
    }

    /**
     * The card with the first of its images not already on the homepage; null when all are.
     *
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>|null
     */
    private static function withImage(array $card, HomepageImageRegistry $images): ?array
    {
        $options = $card['image_options'];
        unset($card['image_options']);

        foreach ($options as $option) {
            if (trim($option['image']) === '' || $images->isUsed($option['image'])) {
                continue;
            }

            $images->claim($option['image']);
            $card['image'] = $option['image'];
            if ($option['label'] !== null) {
                // Full name: the card's tag shortens long ones with "…" itself
                $card['category_label'] = $option['label'];
            }

            return $card;
        }

        return null;
    }

    private static function educatorQuery(?float $lat, ?float $lng): Builder
    {
        $query = Educator::query()
            ->approved()
            ->with(['user:id,name,profile_image'])
            ->withCount('reviews');

        if ($lat !== null && $lng !== null) {
            // Their own location, else their institute's (as on the teachers listing page)
            $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(COALESCE(latitude, institute_latitude))) * cos(radians(COALESCE(longitude, institute_longitude)) - radians(?)) + sin(radians(?)) * sin(radians(COALESCE(latitude, institute_latitude)))))';
            $query->select('educators.*')
                ->selectRaw($distanceSql.' as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        } else {
            $query->latest('id');
        }

        return $query;
    }

    private static function instituteQuery(?float $lat, ?float $lng): Builder
    {
        $query = Institute::query()
            ->approved()
            ->with(['user:id,name,profile_image,role'])
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

    private static function instituteCoverImage(Institute $institute): ?string
    {
        $gallery = is_array($institute->gallery) ? $institute->gallery : [];
        $firstGallery = $gallery[0] ?? null;

        if (filled($firstGallery)) {
            return asset($firstGallery);
        }

        return $institute->logoUrl();
    }

    private static function shortLocation(string $label): string
    {
        $parts = array_map('trim', explode(',', $label));

        return implode(', ', array_slice($parts, 0, 2));
    }
}

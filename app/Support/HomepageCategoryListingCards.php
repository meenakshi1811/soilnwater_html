<?php

namespace App\Support;

use App\Models\Category;
use App\Models\CategoryHomepageImage;
use Illuminate\Support\Str;

final class HomepageCategoryListingCards
{
    /**
     * Homepage entity cards: category images for empty categories first, then live listings, then
     * any remaining admin category images to fill the row.
     *
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @param  list<array<string, mixed>>  $entityCards
     * @return list<array<string, mixed>>
     */
    public static function mergeEntityCards(string $context, array $entityCards, int $limit, callable $categoryMeta): array
    {
        return self::mergeInternal([$context], $entityCards, $limit, $categoryMeta, 'entity');
    }

    /**
     * @param  list<string>  $contexts
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @param  list<array<string, mixed>>  $entityCards
     * @return list<array<string, mixed>>
     */
    public static function mergeEntityCardsMulti(array $contexts, array $entityCards, int $limit, callable $categoryMeta): array
    {
        return self::mergeInternal($contexts, $entityCards, $limit, $categoryMeta, 'entity');
    }

    /**
     * @param  list<array<string, mixed>>  $entityCards
     * @return list<array<string, mixed>>
     */
    public static function mergePromoCards(string $context, array $entityCards, int $limit): array
    {
        return self::mergeInternal(
            [$context],
            $entityCards,
            $limit,
            fn (string $name): array => HomepageMarketplacePromoCards::categoryMetaForName($name),
            'promo'
        );
    }

    /**
     * Category listing cards for carousels when there are no (or few) live vendors/services.
     *
     * @return list<array<string, mixed>>
     */
    public static function carouselEntityCards(string $context, int $limit): array
    {
        return self::mergeEntityCards($context, [], $limit, fn (string $name): array => self::defaultCategoryMeta($name));
    }

    /**
     * Cards built only from Admin → Category Card Images (no live vendor/consultant photos).
     *
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @return list<array<string, mixed>>
     */
    public static function adminCategoryEntityCards(string $context, int $limit, callable $categoryMeta): array
    {
        if ($limit <= 0) {
            return [];
        }

        $cards = [];
        foreach (self::rowsForContext($context) as $row) {
            $card = self::toCard($row, $context, $categoryMeta, 'entity');
            if ($card === null) {
                continue;
            }

            $cards[] = $card;
        }

        return array_slice(self::shuffleCards($cards), 0, $limit);
    }

    /**
     * Admin category images for categories that currently have no listings in this homepage section.
     *
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @return list<array<string, mixed>>
     */
    public static function priorityEntityCards(string $context, int $limit, callable $categoryMeta): array
    {
        $counts = CategoryHomepagePostingCounts::forContext($context);
        if ($counts === null || $limit <= 0) {
            return [];
        }

        $cards = [];
        foreach (self::rowsForContext($context) as $row) {
            if (($counts[$row->category_id] ?? 0) !== 0) {
                continue;
            }

            $card = self::toCard($row, $context, $categoryMeta, 'entity');
            if ($card === null) {
                continue;
            }

            $cards[] = $card;
        }

        return array_slice(self::shuffleCards($cards), 0, $limit);
    }

    /**
     * @param  list<string>  $contexts
     * @param  list<array<string, mixed>>  $entityCards
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @return list<array<string, mixed>>
     */
    private static function mergeInternal(array $contexts, array $entityCards, int $limit, callable $categoryMeta, string $shape): array
    {
        if ($limit <= 0) {
            return [];
        }

        $countsByContext = [];
        foreach ($contexts as $context) {
            $countsByContext[$context] = CategoryHomepagePostingCounts::forContext($context);
        }

        $priority = [];
        $fill = [];

        foreach ($contexts as $context) {
            $counts = $countsByContext[$context];
            foreach (self::rowsForContext($context) as $row) {
                $card = self::toCard($row, $context, $categoryMeta, $shape);
                if ($card === null) {
                    continue;
                }

                $fill[] = $card;
                if ($counts === null || ($counts[$row->category_id] ?? 0) === 0) {
                    $priority[] = $card;
                }
            }
        }

        $merged = self::uniqueByUrl(array_merge(
            self::shuffleCards($priority),
            self::shuffleCards($entityCards)
        ));

        $fill = self::shuffleCards($fill);

        foreach ($fill as $card) {
            if (count($merged) >= $limit) {
                break;
            }
            if (! self::containsUrl($merged, (string) $card['url'])) {
                $merged[] = $card;
            }
        }

        return array_slice(self::shuffleCards($merged), 0, $limit);
    }

    /**
     * @return list<CategoryHomepageImage>
     */
    private static function rowsForContext(string $context): array
    {
        $rows = CategoryHomepageImage::query()
            ->where('context', $context)
            ->with(['category:id,name,parent_id', 'category.parent:id,name'])
            ->get()
            ->all();

        shuffle($rows);

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    private static function shuffleCards(array $cards): array
    {
        if ($cards === []) {
            return [];
        }

        shuffle($cards);

        return array_values($cards);
    }

    /**
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @return array<string, mixed>|null
     */
    private static function toCard(CategoryHomepageImage $row, string $context, callable $categoryMeta, string $shape): ?array
    {
        $category = $row->category;
        if (! $category instanceof Category) {
            return null;
        }

        $image = HomepageCategoryCardImage::resolve($context, HomepageCardCategory::hierarchyIds($category), '');
        if ($image === '') {
            return null;
        }

        $displayName = self::displayName($category);
        $category->loadMissing('parent');
        $badgeName = $category->parent?->name ?? $category->name;
        $meta = $categoryMeta($badgeName);
        $url = self::listingUrl($context, $category);

        if ($shape === 'promo') {
            return [
                'url' => $url,
                'image' => $image,
                'discount_badge' => 'EXPLORE',
                'category_label' => $meta['label'],
                'category_tone' => $meta['tone'],
                'category_icon' => $meta['icon'],
                'title' => $displayName,
                'description' => 'Browse '.$displayName.' listings near you',
                'location' => 'Near you',
                'valid_till' => 'Open listings',
            ];
        }

        return [
            'url' => $url,
            'image' => $image,
            'title' => $displayName,
            'description' => 'Browse '.$displayName.' listings',
            'location' => 'Near you',
            'rating_score' => 0.0,
            'rating_count' => 0,
            'category_label' => $meta['label'],
            'category_tone' => $meta['tone'],
            'category_icon' => $meta['icon'],
            'offer_badge' => null,
            'offer_badge_highlight' => null,
        ];
    }

    public static function listingUrl(string $context, Category $category): string
    {
        $params = array_filter(
            HomepageCardCategory::hierarchyIds($category),
            fn ($id) => $id !== null
        );

        return match ($context) {
            CategoryHomepageImage::CONTEXT_VENDORS => route('frontend.vendors.listings', $params),
            CategoryHomepageImage::CONTEXT_SERVICES => route('frontend.service_providers.listings', $params),
            CategoryHomepageImage::CONTEXT_CONSULTANTS => route('frontend.consultants.listings', $params),
            CategoryHomepageImage::CONTEXT_OFFERS => route('frontend.offers.index', $params),
            CategoryHomepageImage::CONTEXT_ADS => route('frontend.ads.index', $params),
            CategoryHomepageImage::CONTEXT_EDUCATION => route('frontend.service_providers.index', $params),
            default => route('frontend.offers.index'),
        };
    }

    private static function displayName(Category $category): string
    {
        $category->loadMissing('parent');

        if ($category->parent) {
            return $category->name;
        }

        return $category->name;
    }

    /**
     * @return array{label: string, tone: string, icon: string}
     */
    private static function defaultCategoryMeta(string $rawName): array
    {
        return [
            'label' => Str::limit($rawName, 22, ''),
            'tone' => 'slate',
            'icon' => 'fa-store',
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     */
    private static function containsUrl(array $cards, string $url): bool
    {
        foreach ($cards as $card) {
            if (($card['url'] ?? '') === $url) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    private static function uniqueByUrl(array $cards): array
    {
        $seen = [];
        $unique = [];

        foreach ($cards as $card) {
            $url = (string) ($card['url'] ?? '');
            if ($url === '' || isset($seen[$url])) {
                continue;
            }
            $seen[$url] = true;
            $unique[] = $card;
        }

        return $unique;
    }
}

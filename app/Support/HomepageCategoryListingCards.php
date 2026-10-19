<?php

namespace App\Support;

use App\Models\Category;
use App\Models\CategoryHomepageImage;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class HomepageCategoryListingCards
{
    /**
     * Homepage entity cards: category images for empty categories first, then live listings, then
     * any remaining admin category images to fill the row.
     *
     * @param  callable(string): array{label: string, tone: string, icon: string}  $categoryMeta
     * @param  list<array<string, mixed>>  $entityCards
     * @param  HomepageImageRegistry|null  $images  images already on the page, not to be shown again
     * @return list<array<string, mixed>>
     */
    public static function mergeEntityCards(string $context, array $entityCards, int $limit, callable $categoryMeta, ?HomepageImageRegistry $images = null): array
    {
        return self::mergeInternal([$context], $entityCards, $limit, $categoryMeta, 'entity', $images);
    }

    /**
     * @param  list<array<string, mixed>>  $entityCards
     * @param  HomepageImageRegistry|null  $images  images already on the page, not to be shown again
     * @return list<array<string, mixed>>
     */
    public static function mergePromoCards(string $context, array $entityCards, int $limit, ?HomepageImageRegistry $images = null): array
    {
        return self::mergeInternal(
            [$context],
            $entityCards,
            $limit,
            fn (string $name): array => HomepageMarketplacePromoCards::categoryMetaForName($name),
            'promo',
            $images
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
     * @param  HomepageImageRegistry|null  $images  images already on the page, not to be shown again
     * @return list<array<string, mixed>>
     */
    public static function adminCategoryEntityCards(string $context, int $limit, callable $categoryMeta, ?HomepageImageRegistry $images = null): array
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

        return ($images ?? new HomepageImageRegistry())->pick(self::shuffleCards($cards), $limit);
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
    private static function mergeInternal(array $contexts, array $entityCards, int $limit, callable $categoryMeta, string $shape, ?HomepageImageRegistry $images): array
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
            $rows = self::rowsForContext($context);

            // Vendor category cards are titled with a vendor in that category, not its name
            $vendorNames = $shape === 'entity' && $context === CategoryHomepageImage::CONTEXT_VENDORS
                ? self::vendorNamesByCategory(array_map(fn (CategoryHomepageImage $row): int => (int) $row->category_id, $rows))
                : null;

            foreach ($rows as $row) {
                $card = self::toCard($row, $context, $categoryMeta, $shape);
                if ($card === null) {
                    continue;
                }

                if ($vendorNames !== null) {
                    // Titled once the section's cards are picked (titleWithVendorNames)
                    $card['vendor_names'] = $vendorNames[(int) $row->category_id] ?? [];
                }

                $fill[] = $card;
                if ($counts === null || ($counts[$row->category_id] ?? 0) === 0) {
                    $priority[] = $card;
                }
            }
        }

        // Empty categories first, then live listings, then the other category images. One card
        // per image: listings in the same category share its image.
        $candidates = self::uniqueByUrl(array_merge(
            self::shuffleCards($priority),
            self::shuffleCards($entityCards),
            self::shuffleCards($fill)
        ));

        $cards = ($images ?? new HomepageImageRegistry())->pick($candidates, $limit);

        return self::shuffleCards(self::titleWithVendorNames($cards));
    }

    /**
     * Names of the live vendors in each category: a product in it (at any level) or it set as
     * their business category/subcategory.
     *
     * @param  list<int>  $categoryIds
     * @return array<int, list<string>> category id => vendor names
     */
    private static function vendorNamesByCategory(array $categoryIds): array
    {
        $categoryIds = array_values(array_unique($categoryIds));
        if ($categoryIds === []) {
            return [];
        }

        $vendors = Vendor::query()
            ->where('status', 'approved')
            ->publiclyVisible()
            ->where(function (Builder $query) use ($categoryIds): void {
                $query->whereIn('business_category_id', $categoryIds)
                    ->orWhereIn('business_subcategory_id', $categoryIds)
                    ->orWhereHas('products', fn (Builder $products) => $products
                        ->where('status', 'approved')
                        ->where(fn (Builder $columns) => $columns
                            ->whereIn('category_id', $categoryIds)
                            ->orWhereIn('subcategory_id', $categoryIds)
                            ->orWhereIn('child_category_id', $categoryIds)));
            })
            ->with(['products' => fn ($products) => $products
                ->where('status', 'approved')
                ->select(['id', 'vendor_id', 'category_id', 'subcategory_id', 'child_category_id'])])
            ->get(['id', 'company_name', 'display_name', 'business_category_id', 'business_subcategory_id']);

        $names = [];
        foreach ($vendors as $vendor) {
            $vendorCategoryIds = [$vendor->business_category_id, $vendor->business_subcategory_id];
            foreach ($vendor->products as $product) {
                array_push($vendorCategoryIds, $product->category_id, $product->subcategory_id, $product->child_category_id);
            }

            foreach (array_unique(array_filter(array_map('intval', $vendorCategoryIds))) as $categoryId) {
                if (in_array($categoryId, $categoryIds, true)) {
                    $names[$categoryId][] = $vendor->publicDisplayName();
                }
            }
        }

        return array_map(fn (array $list): array => array_values(array_unique($list)), $names);
    }

    /**
     * Titles vendor category cards with one of the vendors in that category, a random one on each
     * page load, skipping vendors that already have a card in the section. None left leaves the
     * title empty.
     *
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    private static function titleWithVendorNames(array $cards): array
    {
        $shown = [];
        foreach ($cards as $card) {
            if (! array_key_exists('vendor_names', $card)) {
                $shown[] = (string) $card['title'];
            }
        }

        foreach ($cards as $index => $card) {
            if (! array_key_exists('vendor_names', $card)) {
                continue;
            }

            $names = array_values(array_diff($card['vendor_names'], $shown));
            $card['title'] = $names === [] ? '' : $names[array_rand($names)];
            unset($card['vendor_names']);

            if ($card['title'] !== '') {
                $shown[] = $card['title'];
            }
            $cards[$index] = $card;
        }

        return $cards;
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

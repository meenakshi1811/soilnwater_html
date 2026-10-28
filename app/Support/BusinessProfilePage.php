<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The details a vendor's, consultant's or service provider's single-page profile shows, in one
 * shape for all three.
 */
class BusinessProfilePage
{
    private const TYPES = [
        'vendor' => [
            'label' => 'Vendor',
            'showRoute' => 'store.show',
            'listingRoute' => 'frontend.vendors.index',
            'listingLabel' => 'Vendors',
            'icon' => 'fa-store',
            'reportRoute' => null,
        ],
        'consultant' => [
            'label' => 'Consultant',
            'showRoute' => 'consultant.show',
            'listingRoute' => 'frontend.consultants.index',
            'listingLabel' => 'Consultants',
            'icon' => 'fa-user-tie',
            'reportRoute' => 'consultant.report',
        ],
        'service' => [
            'label' => 'Service Provider',
            'showRoute' => 'service_provider.show',
            'listingRoute' => 'frontend.service_providers.index',
            'listingLabel' => 'Services',
            'icon' => 'fa-screwdriver-wrench',
            'reportRoute' => 'service_provider.report',
        ],
    ];

    /**
     * @param  'vendor'|'consultant'|'service'  $type
     */
    public static function for(string $type, Model $profile): array
    {
        $config = self::TYPES[$type];
        $name = $profile->publicDisplayName();
        $branches = collect($profile->branches ?? []);
        $primaryBranch = $branches->firstWhere('is_primary', true) ?: $branches->first();
        $phones = $profile->phoneNumbersList();
        $isOwner = auth()->check() && (int) auth()->id() === (int) $profile->user_id;

        $headline = trim(strip_tags((string) $profile->hero_main_heading));
        if (Str::lower($headline) === Str::lower($name)) {
            $headline = '';
        }

        $address = $profile->formattedAddress();
        $place = collect([
            $primaryBranch?->city ?: $profile->city,
            $primaryBranch?->state ?: $profile->state,
        ])->filter(fn ($part) => filled($part))->unique()->implode(', ');

        return [
            'type' => $type,
            'typeLabel' => $config['label'],
            'typeIcon' => $config['icon'],
            'name' => $name,
            'url' => route($config['showRoute'], $profile->slug),
            'listingUrl' => route($config['listingRoute']),
            'listingLabel' => $config['listingLabel'],
            'logo' => filled($profile->logo) ? asset($profile->logo) : null,
            'initials' => self::initials($name),
            'banners' => collect($profile->bannerSlides ?? [])
                ->pluck('image_path')
                ->filter(fn ($path) => filled($path))
                ->map(fn ($path) => asset($path))
                ->values(),
            'headline' => $headline,
            'taglineHtml' => self::isWritten($profile->hero_sub_heading)
                ? self::richHtml($profile->hero_sub_heading)
                : '',
            'aboutHtml' => filled(trim(strip_tags((string) $profile->description)))
                ? self::richHtml($profile->description)
                : '',
            'category' => $type === 'vendor' ? $profile->businessCategory?->name : null,
            'address' => $address,
            'place' => $place,
            'mapQuery' => $address !== '' ? urlencode($address) : null,
            'phones' => $phones,
            'whatsappUrl' => self::whatsappUrl($profile->whatsapp ?: ($phones[0] ?? null)),
            'email' => $profile->email,
            'contactPerson' => $profile->contact_person,
            'since' => self::since($profile->date_of_incorporation),
            'facebook' => $profile->facebook_url,
            'instagram' => $profile->instagram_url,
            'isPremium' => (bool) $profile->is_premium,
            'isOwner' => $isOwner,
            'branches' => $branches,
            'expertise' => $branches
                ->filter(fn ($branch) => filled($branch->professional_experience) || filled($branch->services_offered))
                ->values(),
            'reportRoute' => $config['reportRoute'] && auth()->check() && ! $isOwner
                ? route($config['reportRoute'], $profile->slug)
                : null,
        ];
    }

    /**
     * Whether the owner wrote this text (and did not just keep the page editor's placeholder).
     */
    private static function isWritten(?string $html): bool
    {
        $text = trim(html_entity_decode(strip_tags((string) $html)));

        return $text !== '' && Str::lower($text) !== 'your sub heading appears here';
    }

    /**
     * Rich text written in the profile editor, in the site's typography and without the empty
     * paragraphs editors leave at its start and end.
     */
    public static function richHtml(?string $html): string
    {
        $emptyParagraph = '(?:\s*<p[^>]*>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>)+';

        return trim(preg_replace(['/^'.$emptyParagraph.'/i', '/'.$emptyParagraph.'\s*$/i'], '', StoreRichText::normalizeTypography($html)));
    }

    private static function initials(string $name): string
    {
        return Str::upper(collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode(''));
    }

    private static function whatsappUrl(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            $digits = '91'.$digits;
        }

        return 'https://wa.me/'.$digits;
    }

    private static function since(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * The categories of a profile's services or products, for the filter chips: id => [name, count].
     *
     * @param  Collection<int, Model>  $items
     * @param  callable(Model): array{0: int|string|null, 1: string|null}  $categoryOf
     * @return Collection<int|string, array{name: string, count: int}>
     */
    public static function categoryChips(Collection $items, callable $categoryOf): Collection
    {
        return $items
            ->map(fn ($item) => $categoryOf($item))
            ->filter(fn ($category) => filled($category[0]) && filled($category[1]))
            ->groupBy(fn ($category) => $category[0])
            ->map(fn ($group) => ['name' => $group->first()[1], 'count' => $group->count()])
            ->sortByDesc('count');
    }
}

<?php

namespace App\Support;

/**
 * The images already shown on the homepage, so no image appears twice on the page: each card
 * section picks cards whose image isn't used yet, in page order.
 */
final class HomepageImageRegistry
{
    /** @var array<string, true> */
    private array $used = [];

    public function isUsed(string $url): bool
    {
        $key = self::key($url);

        return $key !== '' && isset($this->used[$key]);
    }

    public function claim(string $url): void
    {
        $key = self::key($url);
        if ($key !== '') {
            $this->used[$key] = true;
        }
    }

    /**
     * Marks the images of cards that are always shown (admin-curated ones).
     *
     * @param  iterable<array<string, mixed>>  $cards
     */
    public function claimCards(iterable $cards): void
    {
        foreach ($cards as $card) {
            $this->claim((string) ($card['image'] ?? ''));
        }
    }

    /**
     * The first $limit cards, in order, whose image isn't shown yet (on the page or earlier in
     * this list), claiming their images.
     *
     * @param  iterable<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    public function pick(iterable $cards, int $limit): array
    {
        $picked = [];
        foreach ($cards as $card) {
            if (count($picked) >= $limit) {
                break;
            }

            $image = (string) ($card['image'] ?? '');
            if ($this->isUsed($image)) {
                continue;
            }

            $this->claim($image);
            $picked[] = $card;
        }

        return $picked;
    }

    /**
     * The first candidate image not shown yet (the first one if all are), claimed.
     *
     * @param  list<string>  $candidates
     */
    public function firstUnused(array $candidates): string
    {
        $candidates = array_values(array_filter($candidates, fn (string $url): bool => trim($url) !== ''));
        if ($candidates === []) {
            return '';
        }

        $image = $candidates[0];
        foreach ($candidates as $candidate) {
            if (! $this->isUsed($candidate)) {
                $image = $candidate;
                break;
            }
        }

        $this->claim($image);

        return $image;
    }

    /**
     * The same file whatever the host or query string: asset() adds the host, and photo URLs
     * may carry size parameters (?w=800).
     */
    private static function key(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);

        return strtolower(ltrim(is_string($path) && $path !== '' ? $path : $url, '/'));
    }
}

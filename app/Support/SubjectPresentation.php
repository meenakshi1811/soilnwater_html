<?php

namespace App\Support;

class SubjectPresentation
{
    /**
     * @var array<string, string>
     */
    private const ICON_MAP = [
        'physics' => 'fa-atom',
        'mathematics' => 'fa-square-root-variable',
        'maths' => 'fa-square-root-variable',
        'math' => 'fa-square-root-variable',
        'science' => 'fa-flask',
        'chemistry' => 'fa-flask',
        'biology' => 'fa-dna',
        'english' => 'fa-book-open',
        'hindi' => 'fa-language',
        'sanskrit' => 'fa-om',
        'history' => 'fa-landmark',
        'geography' => 'fa-earth-americas',
        'social studies' => 'fa-globe',
        'social science' => 'fa-globe',
        'economics' => 'fa-chart-line',
        'accountancy' => 'fa-calculator',
        'accounts' => 'fa-calculator',
        'commerce' => 'fa-briefcase',
        'business studies' => 'fa-briefcase',
        'computer science' => 'fa-laptop-code',
        'information technology' => 'fa-laptop-code',
        'programming' => 'fa-code',
        'art' => 'fa-palette',
        'music' => 'fa-music',
        'physical education' => 'fa-person-running',
    ];

    /**
     * @var list<string>
     */
    private const FALLBACK_ICONS = [
        'fa-book-open',
        'fa-graduation-cap',
        'fa-pen',
        'fa-globe',
        'fa-language',
        'fa-laptop-code',
        'fa-palette',
        'fa-calculator',
    ];

    public static function iconFor(?string $subjectName, int $fallbackIndex = 0): string
    {
        $normalized = strtolower(trim((string) $subjectName));

        if ($normalized === '') {
            return self::fallbackIcon($fallbackIndex);
        }

        if (isset(self::ICON_MAP[$normalized])) {
            return self::ICON_MAP[$normalized];
        }

        foreach (self::ICON_MAP as $needle => $icon) {
            if (str_contains($normalized, $needle)) {
                return $icon;
            }
        }

        return self::fallbackIcon($fallbackIndex);
    }

    private static function fallbackIcon(int $fallbackIndex): string
    {
        return self::FALLBACK_ICONS[$fallbackIndex % count(self::FALLBACK_ICONS)];
    }
}

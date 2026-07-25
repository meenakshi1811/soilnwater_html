<?php

namespace App\Support;

class DiscussionReactions
{
    /**
     * @return list<string>
     */
    public static function labels(): array
    {
        return [
            'Like',
            'Love',
            'Insightful',
            'Agree',
        ];
    }

    public static function isValid(string $reaction): bool
    {
        return in_array($reaction, self::labels(), true);
    }

    /**
     * @return array<string, string>
     */
    public static function icons(): array
    {
        return [
            'Like' => 'fa-thumbs-up',
            'Love' => 'fa-heart',
            'Insightful' => 'fa-lightbulb',
            'Agree' => 'fa-check',
        ];
    }
}

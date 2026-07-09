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
}

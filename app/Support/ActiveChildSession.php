<?php

namespace App\Support;

use App\Models\ChildProfile;
use Illuminate\Support\Facades\Session;

final class ActiveChildSession
{
    public const SESSION_KEY = 'acting_as_child_profile_id';

    public static function activate(ChildProfile $childProfile): void
    {
        Session::put(self::SESSION_KEY, $childProfile->id);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return is_numeric($id) ? (int) $id : null;
    }

    public static function profile(): ?ChildProfile
    {
        $id = self::id();

        if (! $id) {
            return null;
        }

        return ChildProfile::query()
            ->with(['childUser', 'parentUser'])
            ->find($id);
    }

    public static function isActive(): bool
    {
        return self::id() !== null;
    }

    public static function belongsToParent(int $parentUserId): bool
    {
        $profile = self::profile();

        return $profile !== null && $profile->parent_user_id === $parentUserId;
    }
}

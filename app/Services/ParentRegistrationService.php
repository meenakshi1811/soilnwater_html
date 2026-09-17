<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\User;

class ParentRegistrationService
{
    public static function createProfileForUser(User $user, array $registrationData = []): ParentProfile
    {
        if ($user->parentProfile) {
            return $user->parentProfile;
        }

        $profile = ParentProfile::query()->create([
            'user_id' => $user->id,
            'is_enabled' => false,
            'status' => 'pending',
            'location' => $user->city,
            'converted_from_user' => (bool) ($registrationData['converted_from_user'] ?? false),
        ]);

        $profile->recalculateCompletion();

        return $profile;
    }

    public static function displayName(User $user): string
    {
        return $user->full_name ?: $user->name ?: 'Parent profile';
    }
}

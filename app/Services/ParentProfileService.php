<?php

namespace App\Services;

use App\Models\ParentProfile;
use App\Models\User;

class ParentProfileService
{
    public function ensureProfile(User $user): ParentProfile
    {
        return ParentProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['is_enabled' => false]
        );
    }

    public function setEnabled(User $user, bool $enabled): ParentProfile
    {
        $profile = $this->ensureProfile($user);
        $profile->update(['is_enabled' => $enabled]);
        $profile->recalculateCompletion();

        return $profile->fresh();
    }

    public function updateProfile(User $user, array $data): ParentProfile
    {
        $profile = $this->ensureProfile($user);

        $languages = $data['languages'] ?? $profile->languages;
        if (is_string($languages)) {
            $languages = array_values(array_filter(array_map('trim', explode(',', $languages))));
        }

        $profile->update([
            'bio' => $data['bio'] ?? $profile->bio,
            'languages' => is_array($languages) ? $languages : $profile->languages,
            'location' => $data['location'] ?? $profile->location,
        ]);

        $profile->recalculateCompletion();

        return $profile->fresh();
    }
}

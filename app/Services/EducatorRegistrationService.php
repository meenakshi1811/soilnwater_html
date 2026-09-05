<?php

namespace App\Services;

use App\Models\Educator;
use App\Models\User;
use App\Support\EducatorFileUploader;
use Illuminate\Http\UploadedFile;

class EducatorRegistrationService
{
    public static function createProfileForUser(User $user, array $registrationData = []): Educator
    {
        if ($user->educator) {
            return $user->educator;
        }

        $type = in_array($user->role, ['teacher', 'tutor'], true) ? $user->role : 'teacher';
        $displayName = $user->full_name ?: $user->name;
        $slug = Educator::generateUniqueSlug($displayName);
        $profileImage = $registrationData['profile_image'] ?? null;
        $profileImagePath = $profileImage instanceof UploadedFile
            ? EducatorFileUploader::storeImage($profileImage, 'photos')
            : ($registrationData['profile_image_path'] ?? $user->profile_image);

        return Educator::create([
            'user_id' => $user->id,
            'type' => $type,
            'display_name' => $displayName,
            'slug' => $slug,
            'profile_photo' => $profileImagePath,
            'professional_headline' => $registrationData['professional_headline']
                ?? ($type === 'tutor' ? 'Tutor' : 'Teacher'),
            'associated_institute' => $registrationData['associated_institute'] ?? null,
            'city' => $registrationData['city'] ?? $user->city,
            'pincode' => $registrationData['pincode'] ?? $user->pincode,
            'residential_address' => $registrationData['address'] ?? $user->address,
            'latitude' => $registrationData['latitude'] ?? $user->latitude,
            'longitude' => $registrationData['longitude'] ?? $user->longitude,
            'phone' => $user->phone_number,
            'whatsapp' => $registrationData['whatsapp_number'] ?? $user->whatsapp_number,
            'email' => $user->email,
            'take_tuitions' => $type === 'tutor',
            'teaching_modes' => $type === 'tutor'
                ? ['Online', 'Home Tuition', 'Offline Tuition']
                : ['Online', 'At Home', 'At My Center'],
            'languages' => ['English', 'Hindi'],
            'status' => 'pending',
            'converted_from_user' => (bool) ($registrationData['converted_from_user'] ?? false),
        ]);
    }
}

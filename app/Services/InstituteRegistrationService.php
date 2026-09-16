<?php

namespace App\Services;

use App\Models\Institute;
use App\Models\User;
use App\Support\InstituteFileUploader;
use Illuminate\Http\UploadedFile;

class InstituteRegistrationService
{
    public static function createProfileForUser(User $user, array $registrationData = []): Institute
    {
        if ($user->institute) {
            return $user->institute;
        }

        $institutionName = $registrationData['institution_name'] ?? $user->full_name ?: $user->name;
        $slug = Institute::generateUniqueSlug($institutionName);
        $whatsapp = $registrationData['whatsapp_number'] ?? $user->whatsapp_number ?? null;
        $address = $registrationData['address'] ?? $user->address ?? null;
        $city = $registrationData['city'] ?? $user->city ?? null;
        $pincode = $registrationData['pincode'] ?? $user->pincode ?? null;
        $panNumber = $registrationData['pan_number'] ?? null;
        $gstNumber = ($registrationData['has_gst'] ?? '0') === '1' ? ($registrationData['gst_number'] ?? null) : null;
        $governmentCertificateNumber = $registrationData['government_certificate_number'] ?? null;
        $dateOfEstablishment = $registrationData['date_of_establishment'] ?? null;
        $profileImage = $registrationData['profile_image'] ?? null;
        $profileImagePath = $profileImage instanceof UploadedFile
            ? InstituteFileUploader::storeImage($profileImage, 'logos')
            : ($registrationData['profile_image_path'] ?? null);

        $institutionType = $registrationData['institution_type']
            ?? \App\Support\SchoolInstituteHelper::defaultInstitutionTypeForRole((string) $user->role);

        return Institute::create([
            'user_id' => $user->id,
            'institution_name' => $institutionName,
            'contact_person' => $user->name,
            'display_name' => $institutionName,
            'slug' => $slug,
            'logo' => $profileImagePath,
            'phone' => $user->phone_number,
            'whatsapp' => $whatsapp,
            'email' => $user->email,
            'address' => $address,
            'city' => $city,
            'pincode' => $pincode,
            'institution_type' => $institutionType,
            'pan_number' => $panNumber,
            'gst_number' => $gstNumber,
            'government_certificate_number' => $governmentCertificateNumber,
            'date_of_establishment' => $dateOfEstablishment,
            'status' => 'pending',
            'converted_from_user' => (bool) ($registrationData['converted_from_user'] ?? false),
        ]);
    }
}

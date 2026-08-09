<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBranch;
use App\Support\VendorFileUploader;
use Illuminate\Http\UploadedFile;

class VendorRegistrationService
{
    public static function createProfileForUser(User $user, array $registrationData = []): Vendor
    {
        if ($user->vendor) {
            return $user->vendor;
        }

        $companyName = $user->full_name ?: $user->name;
        $slug = Vendor::generateUniqueSlug($companyName);
        $whatsapp = $registrationData['whatsapp_number'] ?? $user->whatsapp_number ?? null;
        $address = $registrationData['address'] ?? $user->address ?? null;
        $city = $registrationData['city'] ?? $user->city ?? null;
        $pincode = $registrationData['pincode'] ?? $user->pincode ?? null;
        $panNumber = $registrationData['pan_number'] ?? null;
        $gstNumber = ($registrationData['has_gst'] ?? '0') === '1' ? ($registrationData['gst_number'] ?? null) : null;
        $governmentCertificateNumber = $registrationData['government_certificate_number'] ?? null;
        $dateOfIncorporation = $registrationData['date_of_incorporation'] ?? null;
        $profileImage = $registrationData['profile_image'] ?? null;
        $profileImagePath = $profileImage instanceof UploadedFile
            ? VendorFileUploader::storeImage($profileImage, 'logos')
            : ($registrationData['profile_image_path'] ?? null);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'company_name' => $companyName,
            'contact_person' => $user->name,
            'display_name' => $companyName,
            'slug' => $slug,
            'logo' => $profileImagePath,
            'phone' => $user->phone_number,
            'whatsapp' => $whatsapp,
            'email' => $user->email,
            'address' => $address,
            'city' => $city,
            'pincode' => $pincode,
            'pan_number' => $panNumber,
            'gst_number' => $gstNumber,
            'government_certificate_number' => $governmentCertificateNumber,
            'date_of_incorporation' => $dateOfIncorporation,
            'status' => 'pending',
            'converted_from_user' => (bool) ($registrationData['converted_from_user'] ?? false),
        ]);

        VendorBranch::create([
            'vendor_id' => $vendor->id,
            'branch_name' => $companyName.' – Main Branch',
            'contact_person' => $user->name,
            'phone' => $user->phone_number,
            'whatsapp' => $whatsapp,
            'email' => $user->email,
            'address' => $address,
            'city' => $city,
            'pincode' => $pincode,
            'pan_number' => $panNumber,
            'gst_number' => $gstNumber,
            'is_primary' => true,
        ]);

        return $vendor;
    }
}

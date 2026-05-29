<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBranch;

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

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'company_name' => $companyName,
            'contact_person' => $user->name,
            'display_name' => $companyName,
            'slug' => $slug,
            'phone' => $user->phone_number,
            'whatsapp' => $whatsapp,
            'email' => $user->email,
            'address' => $address,
            'city' => $city,
            'pincode' => $pincode,
            'status' => 'pending',
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
            'is_primary' => true,
        ]);

        return $vendor;
    }
}

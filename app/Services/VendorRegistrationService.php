<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorBranch;

class VendorRegistrationService
{
    public static function createProfileForUser(User $user): Vendor
    {
        if ($user->vendor) {
            return $user->vendor;
        }

        $companyName = $user->full_name ?: $user->name;
        $slug = Vendor::generateUniqueSlug($companyName);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'company_name' => $companyName,
            'contact_person' => $user->name,
            'display_name' => $companyName,
            'slug' => $slug,
            'phone' => $user->phone_number,
            'email' => $user->email,
            'status' => 'pending',
        ]);

        VendorBranch::create([
            'vendor_id' => $vendor->id,
            'branch_name' => $companyName.' – Main Branch',
            'contact_person' => $user->name,
            'phone' => $user->phone_number,
            'email' => $user->email,
            'is_primary' => true,
        ]);

        return $vendor;
    }
}

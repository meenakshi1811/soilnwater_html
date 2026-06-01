<?php

namespace App\Services;

use App\Models\User;
use App\Models\Consultant;
use App\Models\ConsultantBranch;

class ConsultantRegistrationService
{
    public static function createProfileForUser(User $user, array $registrationData = []): Consultant
    {
        if ($user->consultant) {
            return $user->consultant;
        }

        $companyName = $user->full_name ?: $user->name;
        $slug = Consultant::generateUniqueSlug($companyName);
        $whatsapp = $registrationData['whatsapp_number'] ?? $user->whatsapp_number ?? null;
        $address = $registrationData['address'] ?? $user->address ?? null;
        $city = $registrationData['city'] ?? $user->city ?? null;
        $pincode = $registrationData['pincode'] ?? $user->pincode ?? null;

        $consultant = Consultant::create([
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

        ConsultantBranch::create([
            'consultant_id' => $consultant->id,
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

        return $consultant;
    }
}

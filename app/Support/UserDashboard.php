<?php

namespace App\Support;

use App\Models\User;

class UserDashboard
{
    public static function url(?User $user): string
    {
        if (! $user) {
            return route('user.dashboard');
        }

        return match (true) {
            $user->isAdmin() => route('admin.dashboard'),
            $user->isEmployee() => route('employee.dashboard'),
            $user->isStudent() => route('child.dashboard'),
            $user->isVendor() => $user->vendor?->isApproved()
                ? route('vendor.dashboard')
                : route('vendor.pending'),
            $user->isConsultant() => $user->consultant?->isApproved()
                ? route('consultant.dashboard')
                : route('consultant.pending'),
            $user->isServiceProvider() => $user->serviceProvider?->isApproved()
                ? route('service_provider.dashboard')
                : route('service_provider.pending'),
            $user->isEducator() => $user->educator?->isApproved()
                ? route('educator.dashboard')
                : route('educator.pending'),
            $user->isSchoolOrInstitute() => $user->institute?->isApproved()
                ? $user->portalRoute('dashboard')
                : $user->portalRoute('pending'),
            $user->isParent() => $user->hasParentProfileEnabled()
                ? route('parent.dashboard')
                : route('parent.pending'),
            default => route('user.dashboard'),
        };
    }
}

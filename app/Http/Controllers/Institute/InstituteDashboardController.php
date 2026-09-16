<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Support\SchoolInstituteHelper;
use Illuminate\View\View;

class InstituteDashboardController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $institute = $user->institute;
        $institute->loadCount(['enquiries', 'notices', 'achievements', 'topPerformers', 'schoolClasses', 'books']);

        $completeness = $this->profileCompleteness($institute);
        $portalPrefix = $user->portalRoutePrefix();

        return view('backend.institute.dashboard', [
            'institute' => $institute,
            'completeness' => $completeness,
            'portalPrefix' => $portalPrefix,
            'stats' => [
                [
                    'label' => 'Enquiries',
                    'value' => $institute->enquiries_count,
                    'detail' => 'Messages from students and parents',
                    'url' => SchoolInstituteHelper::routeForPrefix($portalPrefix, 'enquiries.index'),
                    'icon' => 'fa-envelope-open-text',
                ],
                [
                    'label' => 'Public page items',
                    'value' => $institute->notices_count + $institute->achievements_count + $institute->top_performers_count + $institute->school_classes_count + $institute->books_count,
                    'detail' => 'Notices, classes, performers, achievements, books',
                    'url' => SchoolInstituteHelper::routeForPrefix($portalPrefix, 'public-page.edit'),
                    'icon' => 'fa-globe',
                ],
                [
                    'label' => 'Profile completeness',
                    'value' => $completeness.'%',
                    'detail' => 'Keep your public profile up to date',
                    'url' => SchoolInstituteHelper::routeForPrefix($portalPrefix, 'profile.edit'),
                    'icon' => 'fa-user-check',
                ],
                [
                    'label' => 'Institution type',
                    'value' => $institute->institutionTypeLabel(),
                    'detail' => $institute->board_affiliation ?: 'Board not set',
                    'url' => SchoolInstituteHelper::routeForPrefix($portalPrefix, 'profile.edit'),
                    'icon' => 'fa-school',
                ],
                [
                    'label' => 'Location',
                    'value' => $institute->city ?: '—',
                    'detail' => $institute->locationLabel() ?: 'Add your city and state',
                    'url' => SchoolInstituteHelper::routeForPrefix($portalPrefix, 'profile.edit'),
                    'icon' => 'fa-location-dot',
                ],
            ],
        ]);
    }

    private function profileCompleteness($institute): int
    {
        $fields = [
            $institute->institution_name,
            $institute->logo,
            $institute->institution_type,
            $institute->board_affiliation,
            $institute->city,
            $institute->pincode,
            $institute->address,
            $institute->about,
            $institute->grades_offered,
            $institute->facilities,
            $institute->phone,
            $institute->email,
        ];

        $filled = collect($fields)->filter(function ($value) {
            if (is_array($value)) {
                return count($value) > 0;
            }

            return filled($value);
        })->count();

        return (int) round(($filled / max(count($fields), 1)) * 100);
    }
}

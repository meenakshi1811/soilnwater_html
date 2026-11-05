<?php

namespace App\Support;

use App\Models\Institute;
use App\Models\User;

class InstituteDiaryConfig
{
    /** @return list<string> */
    public static function holidayTypes(): array
    {
        return [
            'national' => 'National / public',
            'religious' => 'Religious / festival',
            'academic' => 'Academic break',
            'institution' => 'Institution-specific',
            'other' => 'Other',
        ];
    }

    /** @return list<string> */
    public static function leaveTypes(): array
    {
        return [
            'casual' => 'Casual Leave',
            'sick' => 'Sick Leave',
            'earned' => 'Earned Leave',
            'academic' => 'Academic Leave',
            'emergency' => 'Emergency Leave',
            'other' => 'Other',
        ];
    }

    /** @return list<string> */
    public static function applicableAudiences(User $user): array
    {
        if ($user->isSchool()) {
            return [
                'students' => 'Students',
                'teachers' => 'Teachers',
                'staff' => 'Staff',
            ];
        }

        return [
            'students' => 'Students / trainees',
            'faculty' => 'Faculty',
            'staff' => 'Staff',
        ];
    }

    public static function defaultAcademicYear(): string
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');

        if ($month >= 4) {
            return $year.'-'.substr((string) ($year + 1), -2);
        }

        return ($year - 1).'-'.substr((string) $year, -2);
    }

    public static function moduleTitle(User $user): string
    {
        return \App\Support\SchoolInstituteHelper::label($user->role).' Diary';
    }

    public static function moduleDescription(User $user): string
    {
        return 'Manage holidays, leave rules, and day-to-day '.strtolower(\App\Support\SchoolInstituteHelper::label($user->role)).' configuration in one place.';
    }

    public static function entityLabel(Institute $institute): string
    {
        $institute->loadMissing('user');

        return \App\Support\SchoolInstituteHelper::label($institute->user?->role);
    }
}

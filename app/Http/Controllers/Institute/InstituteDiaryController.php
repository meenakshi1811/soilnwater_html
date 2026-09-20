<?php

namespace App\Http\Controllers\Institute;

use App\Http\Controllers\Controller;
use App\Support\InstituteDiaryConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstituteDiaryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $institute = $user->institute;
        abort_unless($institute, 404);

        $academicYear = (string) $request->query('academic_year', InstituteDiaryConfig::defaultAcademicYear());
        $search = trim((string) $request->query('q', ''));
        $holidayType = (string) $request->query('holiday_type', '');
        $status = (string) $request->query('status', 'all');

        $holidaysQuery = $institute->diaryHolidays()
            ->where('academic_year', $academicYear)
            ->orderBy('start_date');

        if ($search !== '') {
            $holidaysQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($holidayType !== '' && array_key_exists($holidayType, InstituteDiaryConfig::holidayTypes())) {
            $holidaysQuery->where('holiday_type', $holidayType);
        }

        if ($status === 'active') {
            $holidaysQuery->where('is_active', true);
        } elseif ($status === 'inactive') {
            $holidaysQuery->where('is_active', false);
        }

        $holidays = $holidaysQuery->get();

        $academicYears = $institute->diaryHolidays()
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->all();

        if (! in_array($academicYear, $academicYears, true)) {
            array_unshift($academicYears, $academicYear);
            $academicYears = array_values(array_unique($academicYears));
        }

        $calendarMonth = (string) $request->query('calendar_month', now()->format('Y-m'));
        try {
            $calendarStart = Carbon::createFromFormat('Y-m', $calendarMonth)->startOfMonth();
        } catch (\Throwable) {
            $calendarStart = now()->startOfMonth();
            $calendarMonth = $calendarStart->format('Y-m');
        }

        $leaveRules = $institute->leaveRules()->orderBy('sort_order')->orderBy('id')->get();

        $config = [
            'moduleTitle' => InstituteDiaryConfig::moduleTitle($user),
            'moduleDescription' => InstituteDiaryConfig::moduleDescription($user),
            'holidayTypes' => InstituteDiaryConfig::holidayTypes(),
            'leaveTypes' => InstituteDiaryConfig::leaveTypes(),
            'audiences' => InstituteDiaryConfig::applicableAudiences($user),
            'defaultAcademicYear' => InstituteDiaryConfig::defaultAcademicYear(),
        ];

        return view('backend.institute.diary.index', compact(
            'institute',
            'user',
            'holidays',
            'leaveRules',
            'academicYear',
            'search',
            'holidayType',
            'status',
            'academicYears',
            'calendarStart',
            'calendarMonth',
            'config',
        ));
    }
}

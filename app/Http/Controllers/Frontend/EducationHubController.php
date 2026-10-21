<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\EducatorNotice;
use App\Models\Institute;
use App\Models\InstituteNotice;
use App\Models\StudyMaterial;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Education & Knowledge Hub (/education-hub): a starting point to study materials, teachers,
 * schools and institutes, with live counts and listings.
 */
class EducationHubController extends Controller
{
    public function index(): View
    {
        $publicMaterials = StudyMaterial::query()->approved()->publiclyListed();

        return view('frontend.education-hub.index', [
            'stats' => [
                ['label' => 'Study Materials', 'count' => (clone $publicMaterials)->count(), 'icon' => 'fa-file-lines', 'tone' => 'blue', 'url' => route('study-materials.notes')],
                ['label' => 'Teachers & Tutors', 'count' => Educator::query()->approved()->count(), 'icon' => 'fa-chalkboard-user', 'tone' => 'green', 'url' => route('educator.index')],
                ['label' => 'Institutions', 'count' => Institute::query()->approved()->count(), 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => route('institutes.index')],
                ['label' => 'Learners', 'count' => User::query()->where('role', 'student')->count(), 'icon' => 'fa-users', 'tone' => 'orange', 'url' => null],
            ],
            'trendingMaterials' => (clone $publicMaterials)
                ->with(['educator:id,display_name,slug', 'user:id,name'])
                ->orderByDesc('views_count')
                ->orderByDesc('downloads_count')
                ->latest('id')
                ->limit(3)
                ->get(),
            'topTeachers' => Educator::query()
                ->approved()
                ->with('user:id,name,profile_image')
                ->orderByDesc('average_rating')
                ->orderByDesc('is_verified')
                ->latest('id')
                ->limit(4)
                ->get(),
            'announcements' => $this->announcements(3),
            'institutionGroups' => $this->institutionGroups(4),
        ]);
    }

    /**
     * Schools, colleges (and universities) and other institutes, each with how many are listed
     * and a few of them (verified first, then the latest).
     *
     * @return list<array<string, mixed>>
     */
    private function institutionGroups(int $limit): array
    {
        $collegeTypes = ['college', 'university'];

        $groups = [
            [
                'title' => 'Schools', 'hint' => 'Classes 1 – 12', 'noun' => 'school', 'icon' => 'fa-school', 'tone' => 'blue',
                'url' => route('schools.index'),
                'query' => fn () => Institute::query()->schools(),
            ],
            [
                'title' => 'Colleges', 'hint' => 'Colleges & universities', 'noun' => 'college', 'icon' => 'fa-building-columns', 'tone' => 'green',
                'url' => route('institutes.index', ['type' => 'college']),
                'query' => fn () => Institute::query()->institutes()->whereIn('institution_type', $collegeTypes),
            ],
            [
                'title' => 'Institutes', 'hint' => 'Coaching & training', 'noun' => 'institute', 'icon' => 'fa-chalkboard-user', 'tone' => 'purple',
                'url' => route('institutes.index'),
                'query' => fn () => Institute::query()->institutes()->where(fn ($query) => $query
                    ->whereNull('institution_type')
                    ->orWhereNotIn('institution_type', $collegeTypes)),
            ],
        ];

        return array_map(function (array $group) use ($limit): array {
            $query = ($group['query'])()->approved();
            unset($group['query']);

            $group['count'] = (clone $query)->count();
            $group['items'] = $query
                ->with('user:id,role')
                ->orderByDesc('is_verified')
                ->latest('approved_at')
                ->limit($limit)
                ->get();

            return $group;
        }, $groups);
    }

    /**
     * The latest notices of approved schools, institutes and teachers that haven't expired.
     *
     * @return Collection<int, array{title: string, source: string, date: ?\Illuminate\Support\Carbon, url: string}>
     */
    private function announcements(int $limit): Collection
    {
        $instituteNotices = InstituteNotice::query()
            ->active()
            ->whereHas('institute', fn ($query) => $query->approved())
            ->with('institute.user:id,role')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (InstituteNotice $notice): array => [
                'title' => $notice->displayTitle(),
                'source' => $notice->institute->publicDisplayName(),
                'date' => $notice->created_at,
                'url' => $notice->institute->publicSectionUrl('notices'),
            ]);

        $educatorNotices = EducatorNotice::query()
            ->active()
            ->whereHas('educator', fn ($query) => $query->approved())
            ->with('educator:id,display_name,slug')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (EducatorNotice $notice): array => [
                'title' => $notice->displayTitle(),
                'source' => (string) $notice->educator->display_name,
                'date' => $notice->created_at,
                'url' => $notice->educator->publicNoticesUrl(),
            ]);

        return $instituteNotices->concat($educatorNotices)
            ->sortByDesc(fn (array $notice) => $notice['date']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }
}

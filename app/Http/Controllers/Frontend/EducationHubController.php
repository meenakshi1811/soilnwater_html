<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\EducatorNotice;
use App\Models\Institute;
use App\Models\InstituteJob;
use App\Models\InstituteNotice;
use App\Models\InstituteScholarship;
use App\Models\StudyMaterial;
use App\Support\ImageThumbnail;
use App\Support\InstituteCourseWings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Education & Knowledge Hub (/education-hub): a starting point to study materials, teachers,
 * schools, colleges, institutes, jobs and notices, with live counts and listings.
 */
class EducationHubController extends Controller
{
    /** Kinds of notice (who posted it): kind => [tab label, icon, tone, one of them] */
    public const NOTICE_KINDS = [
        'school' => ['Schools', 'fa-school', 'blue', 'School'],
        'college' => ['Colleges', 'fa-graduation-cap', 'purple', 'College'],
        'institute' => ['Institutes', 'fa-building-columns', 'green', 'Institute'],
        'teacher' => ['Teachers & Tutors', 'fa-chalkboard-user', 'orange', 'Teacher'],
    ];

    /** Who offers a course (the courses page's tabs): kind => [tab label, icon, tone, one of them] */
    public const COURSE_KINDS = [
        'college' => ['Colleges', 'fa-graduation-cap', 'purple', 'College'],
        'university' => ['Universities', 'fa-building-columns', 'blue', 'University'],
        'institute' => ['Institutes & Coaching', 'fa-chalkboard-user', 'orange', 'Institute'],
    ];

    /**
     * What a notice is about, from words in its title (first match wins): [pattern, label, icon, tone].
     * Its icon in Announcements & Updates; a notice matching none keeps the icon of who posted it.
     */
    public const NOTICE_TOPICS = [
        ['/olympiad|competition|contest|quiz|hackathon|championship/i', 'Competition', 'fa-trophy', 'amber'],
        ['/\bexam|date ?sheet|\bresults?\b|\btests?\b|\bboards?\b|time ?table/i', 'Examinations', 'fa-calendar-days', 'orange'],
        ['/admission|enrol|apply|application/i', 'Admissions', 'fa-graduation-cap', 'purple'],
        ['/webinar|workshop|seminar|event|\bfest|celebrat|annual day|sports day|\bmeet|ceremony|function/i', 'Event', 'fa-users', 'purple'],
        ['/holiday|vacation|closed|leave/i', 'Holiday', 'fa-umbrella-beach', 'teal'],
        ['/batch|class(es)? (start|begin)|registration|course/i', 'Batches & Courses', 'fa-book-open', 'blue'],
    ];

    /** Trending Study Materials' tabs: key => [label, the material types it shows, icon, the cards' picture tint] */
    public const TRENDING_TABS = [
        'question_papers' => ['Question Papers', ['question_papers'], 'fa-file-lines', 'blue'],
        'notes' => ['Study Notes', ['notes'], 'fa-file-pen', 'green'],
        'worksheets' => ['Worksheets', ['worksheets'], 'fa-table-list', 'purple'],
        'assignments' => ['Assignments', ['assignments'], 'fa-clipboard-list', 'orange'],
        'sample_papers' => ['Solved Papers', ['sample_papers'], 'fa-file-circle-check', 'pink'],
        'reference_books' => ['Reference Books', ['reference_books', 'study_guides'], 'fa-book', 'teal'],
        'videos' => ['Videos & Audio', ['videos', 'audio'], 'fa-photo-film', 'sky'],
    ];

    public function index(): View
    {
        $publicMaterials = StudyMaterial::query()->approved()->publiclyListed();

        return view('frontend.education-hub.index', [
            'stats' => [
                ['label' => 'Study Materials', 'count' => (clone $publicMaterials)->count(), 'icon' => 'fa-book-open', 'tone' => 'blue', 'url' => route('study-materials.notes')],
                ['label' => 'Teachers & Tutors', 'count' => Educator::query()->approved()->count(), 'icon' => 'fa-users', 'tone' => 'green', 'url' => route('educator.index')],
                ['label' => 'Institutions', 'count' => Institute::query()->approved()->count(), 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => route('schools.index')],
                ['label' => 'Jobs & Opportunities', 'count' => $this->openJobsQuery()->count(), 'icon' => 'fa-briefcase', 'tone' => 'orange', 'url' => route('education.jobs')],
            ],
            'trendingTabs' => $this->trendingTabs($publicMaterials, 10),
            'topTeachers' => $this->topTeachers(5),
            'institutionRows' => $this->institutionRows(),
            // (all active ones, newest first, moving upward in the box)
            'announcements' => $this->announcements()->take(50)->values(),
            'openJobsCount' => $this->openJobsQuery()->count(),
        ]);
    }

    /**
     * The hero's search: the listing with the most matches for the words (study materials,
     * teachers, schools, colleges, institutes or jobs), searched there.
     */
    public function search(Request $request): RedirectResponse
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return redirect()->route('study-materials.notes');
        }
        $like = '%'.$q.'%';

        $matches = [
            'materials' => StudyMaterial::query()->approved()->publiclyListed()->where(fn ($query) => $query
                ->where('title', 'like', $like)->orWhere('subject', 'like', $like)->orWhere('topic_chapter', 'like', $like))->count(),
            'teachers' => Educator::query()->approved()->where(fn ($query) => $query
                ->where('display_name', 'like', $like)->orWhere('professional_headline', 'like', $like)->orWhere('city', 'like', $like))->count(),
            'school' => Institute::query()->schools()->approved()->where(fn ($query) => $this->instituteMatch($query, $like))->count(),
            'college' => Institute::query()->colleges()->approved()->where(fn ($query) => $this->instituteMatch($query, $like))->count(),
            'institute' => Institute::query()->institutes()->approved()->where(fn ($query) => $this->instituteMatch($query, $like))->count(),
            'jobs' => $this->openJobsQuery()->where(fn ($query) => $query->where('title', 'like', $like)->orWhere('department', 'like', $like))->count(),
        ];
        // (study materials first on a tie or no match)
        $best = collect($matches)->filter()->sortDesc()->keys()->first() ?? 'materials';

        return match ($best) {
            'teachers' => redirect()->route('educator.index', ['search' => $q]),
            'school' => redirect()->route('schools.index', ['search' => $q]),
            'college' => redirect()->route('colleges.index', ['search' => $q]),
            'institute' => redirect()->route('institutes.index', ['search' => $q]),
            'jobs' => redirect()->route('education.jobs'),
            default => redirect()->route('study-materials.notes', ['q' => $q]),
        };
    }

    /** Every open job of approved schools, colleges and institutes, newest first, by who posted it */
    public function jobs(Request $request): View
    {
        $everything = $this->openJobsQuery()->get()->map(fn (InstituteJob $job): array => $this->jobCard($job));
        $search = $this->searchTerm($request);
        $all = $this->matching($everything, $search, ['title', 'department', 'institute', 'location', 'type']);
        $kinds = array_diff_key(self::NOTICE_KINDS, ['teacher' => true]);
        $kind = array_key_exists((string) $request->query('kind'), $kinds) ? (string) $request->query('kind') : null;
        $shown = $kind ? $all->where('kind', $kind)->values() : $all;

        return view('frontend.education-hub.jobs', [
            'jobs' => $this->paginate($shown, $request, $kind, 12, $search),
            'kind' => $kind,
            'kinds' => $kinds,
            'search' => $search,
            'counts' => $all->countBy('kind')->all() + ['all' => $all->count()],
            'totals' => [
                'jobs' => $everything->count(),
                'employers' => $everything->pluck('institute')->unique()->count(),
            ],
        ]);
    }

    /**
     * Scholarships (/education-hub/scholarships): every open scholarship of the approved schools,
     * colleges and institutes, the nearest last date first; tabs by who offers it and a search.
     */
    public function scholarships(Request $request): View
    {
        $everything = InstituteScholarship::query()
            ->open()
            ->whereHas('institute', fn ($query) => $query->approved())
            ->with('institute.user:id,role')
            ->orderByRaw('application_deadline is null')
            ->orderBy('application_deadline')
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(fn (InstituteScholarship $scholarship): array => $this->scholarshipCard($scholarship));
        $search = $this->searchTerm($request);
        $all = $this->matching($everything, $search, ['title', 'type', 'amount', 'for', 'criteria', 'institute', 'location']);
        $kinds = array_diff_key(self::NOTICE_KINDS, ['teacher' => true]);
        $kind = array_key_exists((string) $request->query('kind'), $kinds) ? (string) $request->query('kind') : null;
        $shown = $kind ? $all->where('kind', $kind)->values() : $all;

        return view('frontend.education-hub.scholarships', [
            'scholarships' => $this->paginate($shown, $request, $kind, 12, $search),
            'kind' => $kind,
            'kinds' => $kinds,
            'search' => $search,
            'counts' => $all->countBy('kind')->all() + ['all' => $all->count()],
            'totals' => [
                'scholarships' => $everything->count(),
                'providers' => $everything->pluck('institute_id')->unique()->count(),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function scholarshipCard(InstituteScholarship $scholarship): array
    {
        $institute = $scholarship->institute;

        return [
            'kind' => in_array($institute->user?->role, ['school', 'college'], true) ? $institute->user->role : 'institute',
            'title' => (string) $scholarship->title,
            'type' => $scholarship->typeLabel(),
            'icon' => $scholarship->typeIcon(),
            'amount' => $scholarship->amount_label,
            'for' => $scholarship->eligible_for,
            'awards' => $scholarship->awards_count,
            'criteria' => implode(' · ', $scholarship->lines('eligibility')),
            'start' => $scholarship->opensLater() ? $scholarship->application_start : null,
            'deadline' => $scholarship->application_deadline,
            'institute_id' => $institute->id,
            'institute' => $institute->publicDisplayName(),
            'logo' => $institute->logoUrl(),
            'verified' => (bool) $institute->is_verified,
            'location' => $institute->locationLabel(),
            'url' => $institute->publicSectionUrl('scholarships').'#sch-scholarship-'.$scholarship->id,
        ];
    }

    /**
     * Courses & Online Learning (/education-hub/courses): every course or program that a college,
     * university or institute has added to its profile, with tabs by who offers it and a search.
     */
    public function courses(Request $request): View|JsonResponse
    {
        $everything = $this->allCourses();
        $search = $this->searchTerm($request);
        $kind = array_key_exists((string) $request->query('kind'), self::COURSE_KINDS) ? (string) $request->query('kind') : null;
        $cities = $everything->pluck('city')->filter()->unique()->sort(SORT_NATURAL | SORT_FLAG_CASE)->values();
        $city = $cities->first(fn (string $name) => strcasecmp($name, trim((string) $request->query('city'))) === 0);
        $sort = in_array($request->query('sort'), ['course', 'institution'], true) ? (string) $request->query('sort') : 'recommended';

        $shown = $this->matching($everything, $search, ['title', 'grades', 'description', 'institute', 'location'])
            ->when($kind, fn (Collection $cards) => $cards->where('kind', $kind))
            ->when($city, fn (Collection $cards) => $cards->filter(fn (array $card) => strcasecmp((string) $card['city'], $city) === 0))
            ->when($sort === 'course', fn (Collection $cards) => $cards->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE))
            ->when($sort === 'institution', fn (Collection $cards) => $cards->sortBy([['institute', 'asc'], ['title', 'asc']]))
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 24;
        $courses = new LengthAwarePaginator(
            $shown->forPage($page, $perPage)->values(),
            $shown->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => array_filter(['q' => $search, 'kind' => $kind, 'city' => $city, 'sort' => $sort === 'recommended' ? null : $sort])]
        );

        // (filtering, sorting and the next cards while scrolling)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('frontend.education-hub.partials.course-cards', ['courses' => $courses])->render(),
                'next_page_url' => $courses->nextPageUrl(),
                'loaded_to' => $courses->lastItem() ?? 0,
                'total' => $courses->total(),
            ]);
        }

        return view('frontend.education-hub.courses', [
            'courses' => $courses,
            'kind' => $kind,
            'kinds' => self::COURSE_KINDS,
            'search' => $search,
            'city' => $city,
            'cities' => $cities,
            'sort' => $sort,
            'totals' => [
                'courses' => $everything->count(),
                'providers' => $everything->pluck('institute_id')->unique()->count(),
            ],
        ]);
    }

    /** Every active notice of approved schools, colleges, institutes and teachers, newest first, by kind */
    public function notices(Request $request): View
    {
        $all = $this->announcements();
        $kind = array_key_exists((string) $request->query('kind'), self::NOTICE_KINDS) ? (string) $request->query('kind') : null;
        $shown = $kind ? $all->where('kind', $kind)->values() : $all;

        return view('frontend.education-hub.notices', [
            'notices' => $this->paginate($shown, $request, $kind),
            'kind' => $kind,
            'kinds' => self::NOTICE_KINDS,
            'counts' => $all->countBy('kind')->all() + ['all' => $all->count()],
        ]);
    }

    private function paginate(Collection $items, Request $request, ?string $kind, int $perPage = 12, string $search = ''): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => array_filter(['kind' => $kind, 'q' => $search])]
        );
    }

    /** The search box's words (?q=), trimmed and capped */
    private function searchTerm(Request $request): string
    {
        return Str::limit(trim((string) $request->query('q', '')), 80, '');
    }

    /**
     * The cards whose fields contain every word searched for (any case); all of them for no search.
     *
     * @param  Collection<int, array<string, mixed>>  $cards
     * @param  list<string>  $fields
     * @return Collection<int, array<string, mixed>>
     */
    private function matching(Collection $cards, string $search, array $fields): Collection
    {
        $words = array_filter(preg_split('/\s+/', mb_strtolower($search)) ?: []);
        if ($words === []) {
            return $cards->values();
        }

        return $cards->filter(function (array $card) use ($words, $fields): bool {
            $text = mb_strtolower(implode(' ', array_map(fn ($field) => (string) ($card[$field] ?? ''), $fields)));
            foreach ($words as $word) {
                if (! str_contains($text, $word)) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    /**
     * Every course card: the courses & programs saved on the profiles of approved colleges,
     * universities and institutes (verified ones first, then the newest), in each one's own order.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function allCourses(): Collection
    {
        return Institute::query()
            ->approved()
            ->whereHas('user', fn (Builder $query) => $query->whereIn('role', ['college', 'institute']))
            ->whereNotNull('profile_meta')
            ->with('user:id,role')
            ->orderByDesc('is_verified')
            ->latest('approved_at')
            ->latest('id')
            ->get()
            ->flatMap(function (Institute $institute): array {
                $kind = $institute->user?->role === 'college'
                    ? ($institute->institution_type === 'university' ? 'university' : 'college')
                    : 'institute';

                return array_map(fn (array $wing): array => [
                    'kind' => $kind,
                    'title' => $wing['title'],
                    'grades' => $wing['grades'],
                    'description' => Str::limit($wing['description'], 150),
                    'icon' => $wing['icon'],
                    'image' => $wing['image'] !== '' ? ImageThumbnail::url(asset($wing['image'])) : null,
                    'institute_id' => $institute->id,
                    'institute' => $institute->publicDisplayName(),
                    'logo' => $institute->logoUrl(),
                    'verified' => (bool) $institute->is_verified,
                    'location' => $institute->locationLabel(),
                    'city' => trim((string) $institute->city),
                    'url' => $institute->publicSectionUrl('courses'),
                ], InstituteCourseWings::cleanWings(data_get($institute->profile_meta, 'course_wings')));
            })
            ->values();
    }

    private function instituteMatch($query, string $like): void
    {
        $query->where('institution_name', 'like', $like)
            ->orWhere('display_name', 'like', $like)
            ->orWhere('city', 'like', $like)
            ->orWhere('board_affiliation', 'like', $like);
    }

    /**
     * Trending Study Materials by tab (question papers, notes, worksheets…): each tab's most viewed
     * (then downloaded) as cards for a sliding row, a different set on each visit, and a link to all.
     *
     * @return list<array{key: string, label: string, icon: string, tint: string, url: string, cards: list<array<string, mixed>>}>
     */
    private function trendingTabs($publicMaterials, int $limit): array
    {
        $tabs = [];
        foreach (self::TRENDING_TABS as $key => [$label, $types, $icon, $tint]) {
            $tabs[] = [
                'key' => $key,
                'label' => $label,
                'icon' => $icon,
                'tint' => $tint,
                'url' => route('study-materials.notes', ['material_type' => $types[0], 'sort' => 'downloads']),
                'cards' => $this->freshPick(
                    (clone $publicMaterials)->whereIn('material_type', $types)
                        ->orderByDesc('views_count')->orderByDesc('downloads_count')->latest('id'),
                    'trending.'.$key,
                    $limit,
                )
                    ->map(fn (StudyMaterial $material): array => $this->materialCard($material))
                    ->all(),
            ];
        }

        return $tabs;
    }

    /**
     * The top rated teachers and tutors (verified first among equals): name, subject, their
     * qualification and experience, rating and profile.
     *
     * @return list<array<string, mixed>>
     */
    private function topTeachers(int $limit): array
    {
        return Educator::query()
            ->approved()
            ->with('user:id,name,profile_image')
            ->orderByDesc('average_rating')
            ->orderByDesc('is_verified')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Educator $teacher): array {
                $qualification = collect($teacher->qualifications ?? [])->pluck('degree')->filter()->take(2)->implode(', ');
                $experience = (int) $teacher->years_experience > 0 ? $teacher->years_experience.'+ yrs exp.' : null;
                $classes = collect($teacher->classes ?? [])->filter()->values();
                $subject = $teacher->primarySubject();
                // "Class 9" … "Class 12" → "Classes 9–12"
                $classRange = $classes->count() > 1
                    ? (preg_match('/^Class\s+(.+)$/i', $classes->first(), $from) && preg_match('/^Class\s+(.+)$/i', $classes->last(), $to)
                        ? 'Classes '.$from[1].'–'.$to[1]
                        : $classes->first().'–'.$classes->last())
                    : $classes->first();

                return [
                    'name' => $teacher->display_name ?: $teacher->roleLabel(),
                    'photo' => $teacher->photoUrl() ?: asset('assets/images/logo_soilnwater.webp'),
                    'verified' => $teacher->isVerified(),
                    'subject' => $subject
                        ? $subject.($classRange ? ' ('.$classRange.')' : '')
                        : ($teacher->professional_headline ?: $teacher->roleLabel()),
                    'detail' => collect([$qualification ?: null, $experience])->filter()->implode(' | '),
                    'rating' => (float) $teacher->average_rating,
                    'url' => $teacher->publicUrl(),
                ];
            })
            ->all();
    }

    /**
     * Educational Institutions: schools, colleges, training institutes, universities and coaching centres, each with a building
     * photo (public/assets/images/education-hub/place-*.webp), what they cover and the listing.
     *
     * @return list<array<string, mixed>>
     */
    private function institutionRows(): array
    {
        $rows = [
            ['title' => 'Top Schools', 'hint' => 'CBSE, ICSE, State Boards', 'photo' => 'school', 'icon' => 'fa-school', 'tone' => 'blue', 'url' => route('schools.index')],
            ['title' => 'Top Colleges', 'hint' => 'UG, PG & Professional Courses', 'photo' => 'college', 'icon' => 'fa-graduation-cap', 'tone' => 'purple', 'url' => route('colleges.index')],
            ['title' => 'Training Institutes', 'hint' => 'Coaching, Skills & Vocational', 'photo' => 'institute', 'icon' => 'fa-building-columns', 'tone' => 'orange', 'url' => route('institutes.index')],
            ['title' => 'Universities', 'hint' => 'Central, State & Deemed Universities', 'photo' => 'university', 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => route('colleges.index', ['type' => 'university'])],
            ['title' => 'Coaching Centres', 'hint' => 'JEE, NEET, UPSC & More', 'photo' => 'coaching', 'icon' => 'fa-chalkboard-user', 'tone' => 'orange', 'url' => route('institutes.index', ['type' => 'coaching'])],
        ];

        return array_map(fn (array $row): array => $row + ['image' => asset('assets/images/education-hub/place-'.$row['photo'].'.webp')], $rows);
    }

    /**
     * A box's records for this visit: $limit picked at random from the first $pool of the ordered
     * query, leaving out the ones this visitor was shown before (kept in the session) until none
     * are left, then starting over. A box with no more than $limit shows them all, shuffled.
     *
     * @param  \Closure(Builder): mixed|null  $load  eager loads for the picked records
     * @return Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    private function freshPick(Builder $ordered, string $box, int $limit, ?\Closure $load = null, int $pool = 60): Collection
    {
        $model = $ordered->getModel();
        $ids = (clone $ordered)->limit($pool)->pluck($model->getQualifiedKeyName())->map(fn ($id) => (int) $id);
        if ($ids->isEmpty()) {
            return collect();
        }

        $sessionKey = 'education_hub_seen.'.$box;
        $seen = collect(session($sessionKey, []))->map(fn ($id) => (int) $id);
        $unseen = $ids->diff($seen)->shuffle();

        if ($unseen->count() >= $limit) {
            $picked = $unseen->take($limit)->values();
            $seen = $seen->merge($picked)->intersect($ids)->values();
        } else {
            // (too few left unseen: those first, then the others; the round starts over)
            $picked = $unseen->merge($ids->diff($unseen)->shuffle())->take($limit)->values();
            $seen = $picked;
        }
        session([$sessionKey => $seen->all()]);

        $query = $model->newQuery()->whereKey($picked->all());
        if ($load) {
            $load($query);
        }
        $records = $query->get()->keyBy(fn ($record) => (int) $record->getKey());

        return $picked->map(fn (int $id) => $records->get($id))->filter()->values();
    }

    /**
     * A trending study material: its cover (small copy), file type, title, board or school and
     * class, views and downloads.
     *
     * @return array<string, mixed>
     */
    private function materialCard(StudyMaterial $material): array
    {
        $image = $material->cardImageUrl();
        $source = $material->board_university ?: $material->school_name;

        return [
            'url' => $material->publicUrl(),
            'image' => $image ? ImageThumbnail::url($image) : null,
            'file' => $material->fileTypeMeta(),
            'title' => (string) $material->title,
            'meta' => collect([$source, $material->class_course])->filter()->values()->all() ?: [$material->materialTypeLabel()],
            'views' => StudyMaterial::formatCompactCount((int) $material->views_count),
            'downloads' => StudyMaterial::formatCompactCount((int) $material->downloads_count),
        ];
    }

    /** Open jobs (status open, last date not passed) of approved schools, colleges and institutes, newest first */
    private function openJobsQuery(): Builder
    {
        return InstituteJob::query()
            ->open()
            ->whereHas('institute', fn ($query) => $query->approved())
            ->with('institute.user:id,role')
            ->orderByDesc('published_at')
            ->latest('id');
    }

    /**
     * A job as a card: who posted it (and their jobs page, where people apply), the job's type,
     * title, department, place, experience, salary and last date.
     *
     * @return array<string, mixed>
     */
    private function jobCard(InstituteJob $job): array
    {
        $institute = $job->institute;

        return [
            'kind' => in_array($institute->user?->role, ['school', 'college'], true) ? $institute->user->role : 'institute',
            'title' => (string) $job->title,
            'type' => $job->employmentTypeLabel(),
            'department' => $job->department,
            'institute' => $institute->publicDisplayName(),
            'logo' => $institute->logoUrl(),
            'location' => $job->location ?: $institute->locationLabel(),
            'experience' => $job->experience_label,
            'salary' => $job->salary_label,
            'deadline' => $job->application_deadline,
            'posted' => $job->published_at ?? $job->created_at,
            'url' => $institute->publicSectionUrl('jobs'),
        ];
    }

    /**
     * The active notices of approved schools, colleges, institutes and teachers, newest first: each
     * with its kind, title, short text, picture, who posted it, the dates and their notices page.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function announcements(): Collection
    {
        $instituteNotices = InstituteNotice::query()
            ->active()
            ->whereHas('institute', fn ($query) => $query->approved())
            ->with('institute.user:id,role')
            ->get()
            ->map(fn (InstituteNotice $notice): array => [
                'kind' => in_array($notice->institute->user?->role, ['school', 'college'], true) ? $notice->institute->user->role : 'institute',
                'title' => $notice->displayTitle(),
                'excerpt' => $notice->excerpt(160),
                'image' => $notice->imageUrl(),
                'source' => $notice->institute->publicDisplayName(),
                'date' => $notice->created_at,
                'expires' => $notice->expires_at,
                'url' => $notice->institute->publicSectionUrl('notices'),
            ]);

        $educatorNotices = EducatorNotice::query()
            ->active()
            ->whereHas('educator', fn ($query) => $query->approved())
            ->with('educator:id,display_name,slug')
            ->get()
            ->map(fn (EducatorNotice $notice): array => [
                'kind' => 'teacher',
                'title' => $notice->displayTitle(),
                'excerpt' => $notice->excerpt(160),
                'image' => $notice->imageUrl(),
                'source' => (string) $notice->educator->display_name,
                'date' => $notice->created_at,
                'expires' => $notice->expires_at,
                'url' => $notice->educator->publicNoticesUrl(),
            ]);

        return $instituteNotices->concat($educatorNotices)
            ->map(fn (array $notice): array => $notice + ['topic' => self::noticeTopic($notice['title'])])
            ->sortByDesc(fn (array $notice) => $notice['date']?->timestamp ?? 0)
            ->values();
    }

    /**
     * A notice's topic from its title: [label, icon, tone], or null when no topic matches.
     *
     * @return array{0: string, 1: string, 2: string}|null
     */
    public static function noticeTopic(string $title): ?array
    {
        foreach (self::NOTICE_TOPICS as [$pattern, $label, $icon, $tone]) {
            if (preg_match($pattern, $title) === 1) {
                return [$label, $icon, $tone];
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\EducatorNotice;
use App\Models\Institute;
use App\Models\InstituteJob;
use App\Models\InstituteNotice;
use App\Models\StudyMaterial;
use App\Support\ImageThumbnail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

    /** Trending Study Materials' tabs: key => [label, the material types it shows] */
    public const TRENDING_TABS = [
        'question_papers' => ['Question Papers', ['question_papers']],
        'notes' => ['Study Notes', ['notes']],
        'worksheets' => ['Worksheets', ['worksheets']],
        'assignments' => ['Assignments', ['assignments']],
        'sample_papers' => ['Solved Papers', ['sample_papers']],
        'reference_books' => ['Reference Books', ['reference_books', 'study_guides']],
        'videos' => ['Videos & Audio', ['videos', 'audio']],
    ];

    public function index(): View
    {
        $publicMaterials = StudyMaterial::query()->approved()->publiclyListed();

        return view('frontend.education-hub.index', [
            'stats' => [
                ['label' => 'Study Materials', 'count' => (clone $publicMaterials)->count(), 'icon' => 'fa-book-open', 'tone' => 'green', 'url' => route('study-materials.notes')],
                ['label' => 'Teachers & Tutors', 'count' => Educator::query()->approved()->count(), 'icon' => 'fa-chalkboard-user', 'tone' => 'blue', 'url' => route('educator.index')],
                ['label' => 'Institutions', 'count' => Institute::query()->approved()->count(), 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => route('schools.index')],
                ['label' => 'Jobs & Opportunities', 'count' => $this->openJobsQuery()->count(), 'icon' => 'fa-briefcase', 'tone' => 'orange', 'url' => route('education.jobs')],
            ],
            'trendingTabs' => $this->trendingTabs($publicMaterials, 6),
            'topTeachers' => $this->topTeachers(3),
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
        $all = $this->openJobsQuery()->get()->map(fn (InstituteJob $job): array => $this->jobCard($job));
        $kinds = array_diff_key(self::NOTICE_KINDS, ['teacher' => true]);
        $kind = array_key_exists((string) $request->query('kind'), $kinds) ? (string) $request->query('kind') : null;
        $shown = $kind ? $all->where('kind', $kind)->values() : $all;

        return view('frontend.education-hub.jobs', [
            'jobs' => $this->paginate($shown, $request, $kind),
            'kind' => $kind,
            'kinds' => $kinds,
            'counts' => $all->countBy('kind')->all() + ['all' => $all->count()],
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

    private function paginate(Collection $items, Request $request, ?string $kind, int $perPage = 12): LengthAwarePaginator
    {
        $page = max(1, (int) $request->query('page', 1));

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => array_filter(['kind' => $kind])]
        );
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
     * (then downloaded) as cards, a different six of them on each visit, and a link to all.
     *
     * @return list<array{key: string, label: string, url: string, cards: list<array<string, mixed>>}>
     */
    private function trendingTabs($publicMaterials, int $limit): array
    {
        $tabs = [];
        foreach (self::TRENDING_TABS as $key => [$label, $types]) {
            $tabs[] = [
                'key' => $key,
                'label' => $label,
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
                $experience = (int) $teacher->years_experience > 0 ? $teacher->years_experience.'+ yrs' : null;
                $classes = collect($teacher->classes ?? [])->filter()->values();
                $subject = $teacher->primarySubject();

                return [
                    'name' => $teacher->display_name ?: $teacher->roleLabel(),
                    'photo' => $teacher->photoUrl() ?: asset('assets/images/logo_soilnwater.webp'),
                    'verified' => $teacher->isVerified(),
                    'subject' => $subject
                        ? $subject.($classes->isNotEmpty() ? ' ('.($classes->count() > 1 ? $classes->first().'–'.$classes->last() : $classes->first()).')' : '')
                        : ($teacher->professional_headline ?: $teacher->roleLabel()),
                    'detail' => collect([$qualification ?: null, $experience])->filter()->implode(' | '),
                    'rating' => (float) $teacher->average_rating,
                    'url' => $teacher->publicUrl(),
                ];
            })
            ->all();
    }

    /**
     * Educational Institutions: schools, colleges and training institutes, each with a photo of
     * one of the top ones, what they cover, how many are listed and the listing.
     *
     * @return list<array<string, mixed>>
     */
    private function institutionRows(): array
    {
        $rows = [
            ['title' => 'Top Schools', 'hint' => 'CBSE, ICSE, State Boards', 'icon' => 'fa-school', 'tone' => 'blue', 'url' => route('schools.index'), 'query' => fn () => Institute::query()->schools()],
            ['title' => 'Top Colleges', 'hint' => 'UG, PG & Professional Courses', 'icon' => 'fa-graduation-cap', 'tone' => 'purple', 'url' => route('colleges.index'), 'query' => fn () => Institute::query()->colleges()],
            ['title' => 'Training Institutes', 'hint' => 'Coaching, Skills & Vocational', 'icon' => 'fa-building-columns', 'tone' => 'orange', 'url' => route('institutes.index'), 'query' => fn () => Institute::query()->institutes()],
        ];

        return array_map(function (array $row): array {
            $query = ($row['query'])()->approved();
            unset($row['query']);
            $row['count'] = (clone $query)->count();
            // (a photo: the gallery's first, else a JPEG logo, which is nearly always a photo)
            $row['image'] = null;
            foreach ((clone $query)->orderByDesc('is_verified')->latest('approved_at')->limit(12)->get(['id', 'gallery', 'logo']) as $institution) {
                $gallery = is_array($institution->gallery) ? array_values(array_filter($institution->gallery)) : [];
                if ($gallery !== []) {
                    $row['image'] = ImageThumbnail::url(asset($gallery[0]));
                    break;
                }
                if (preg_match('/\.jpe?g$/i', (string) $institution->logo) === 1) {
                    $row['image'] = ImageThumbnail::url(asset($institution->logo));
                    break;
                }
            }

            return $row;
        }, $rows);
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
            'meta' => collect([$source, $material->class_course])->filter()->implode(' · ') ?: $material->materialTypeLabel(),
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
            ->sortByDesc(fn (array $notice) => $notice['date']?->timestamp ?? 0)
            ->values();
    }
}

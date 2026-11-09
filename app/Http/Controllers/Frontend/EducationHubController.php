<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\EducatorNotice;
use App\Models\Institute;
use App\Models\InstituteNotice;
use App\Models\StudyMaterial;
use App\Models\User;
use App\Support\ImageThumbnail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Education & Knowledge Hub (/education-hub): a starting point to study materials, teachers,
 * schools and institutes, with live counts and listings.
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

    /** Every active notice of approved schools, colleges, institutes and teachers, newest first, by kind */
    public function notices(Request $request): View
    {
        $all = $this->announcements();
        $kind = array_key_exists((string) $request->query('kind'), self::NOTICE_KINDS) ? (string) $request->query('kind') : null;
        $shown = $kind ? $all->where('kind', $kind)->values() : $all;

        $perPage = 12;
        $page = max(1, (int) $request->query('page', 1));
        $notices = new LengthAwarePaginator(
            $shown->forPage($page, $perPage)->values(),
            $shown->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => array_filter(['kind' => $kind])]
        );

        return view('frontend.education-hub.notices', [
            'notices' => $notices,
            'kind' => $kind,
            'kinds' => self::NOTICE_KINDS,
            'counts' => $all->countBy('kind')->all() + ['all' => $all->count()],
        ]);
    }

    public function index(): View
    {
        $publicMaterials = StudyMaterial::query()->approved()->publiclyListed();
        $institutionGroups = $this->institutionGroups(6);

        // Cards show their images at about 300px: small copies, not the originals
        $withThumbnails = fn (array $cards): array => array_map(
            fn (array $card): array => ['image' => ImageThumbnail::url((string) $card['image'])] + $card,
            $cards
        );

        return view('frontend.education-hub.index', [
            'stats' => [
                ['label' => 'Study Materials', 'count' => (clone $publicMaterials)->count(), 'icon' => 'fa-file-lines', 'tone' => 'blue', 'url' => route('study-materials.notes')],
                ['label' => 'Teachers & Tutors', 'count' => Educator::query()->approved()->count(), 'icon' => 'fa-chalkboard-user', 'tone' => 'green', 'url' => route('educator.index')],
                ['label' => 'Learners', 'count' => User::query()->where('role', 'student')->count(), 'icon' => 'fa-users', 'tone' => 'orange', 'url' => null],
                // (each kind on its own: a second row)
                ['label' => 'Schools', 'count' => Institute::query()->schools()->approved()->count(), 'icon' => 'fa-school', 'tone' => 'pink', 'url' => route('schools.index')],
                ['label' => 'Colleges', 'count' => Institute::query()->colleges()->approved()->count(), 'icon' => 'fa-graduation-cap', 'tone' => 'teal', 'url' => route('colleges.index')],
                ['label' => 'Institutes', 'count' => Institute::query()->institutes()->approved()->count(), 'icon' => 'fa-building-columns', 'tone' => 'purple', 'url' => route('institutes.index')],
            ],
            'trendingGroups' => $this->trendingGroups($publicMaterials, 6),
            'topTeachers' => Educator::query()
                ->approved()
                ->with('user:id,name,profile_image')
                ->orderByDesc('average_rating')
                ->orderByDesc('is_verified')
                ->latest('id')
                ->limit(4)
                ->get(),
            // (all active ones, newest first, moving upward in the box)
            'announcements' => $this->announcements()->take(50)->values(),
            'institutionGroups' => array_map(
                fn (array $group): array => ['cards' => $withThumbnails($group['cards'])] + $group,
                $institutionGroups
            ),
        ]);
    }

    /**
     * Schools, colleges (and universities) and institutes, each with how many are listed and a
     * few of them as cards (verified first, then the latest).
     *
     * @return list<array<string, mixed>>
     */
    private function institutionGroups(int $limit): array
    {
        $groups = [
            [
                'title' => 'Schools', 'hint' => 'Classes 1 – 12', 'noun' => 'school', 'icon' => 'fa-school', 'theme' => 'schools',
                'url' => route('schools.index'),
                'query' => fn () => Institute::query()->schools(),
            ],
            [
                'title' => 'Colleges', 'hint' => 'Colleges & universities', 'noun' => 'college', 'icon' => 'fa-building-columns', 'theme' => 'colleges',
                'url' => route('colleges.index'),
                'query' => fn () => Institute::query()->colleges(),
            ],
            [
                'title' => 'Institutes', 'hint' => 'Coaching & training', 'noun' => 'institute', 'icon' => 'fa-chalkboard-user', 'theme' => 'institutes',
                'url' => route('institutes.index'),
                'query' => fn () => Institute::query()->institutes(),
            ],
        ];

        return array_map(function (array $group) use ($limit): array {
            $query = ($group['query'])()->approved();
            unset($group['query']);

            $group['count'] = (clone $query)->count();
            // (the verified first, then the latest: a different six of them on each visit)
            $group['cards'] = $this->freshPick(
                $query->orderByDesc('is_verified')->latest('approved_at'),
                'institutions.'.$group['theme'],
                $limit,
                fn (Builder $picked) => $picked->with('user:id,role'),
            )
                ->map(fn (Institute $institution): array => $this->institutionCard($institution, $group['noun'] === 'school'))
                ->all();

            return $group;
        }, $groups);
    }

    /**
     * Trending Study Materials in three groups: board question papers, school question papers and
     * notes, each its most viewed (then downloaded) as cards for a colourful panel (like the top
     * schools, colleges and institutes) and a link to all of them.
     *
     * @return list<array{key: string, lead: string, accent: string, hint: string, icon: string, theme: string, url: string, empty: string, cards: list<array<string, mixed>>}>
     */
    private function trendingGroups($publicMaterials, int $limit): array
    {
        $groups = [
            [
                'key' => 'board', 'lead' => 'Board', 'accent' => 'Question Papers', 'hint' => 'CBSE, ICSE, state boards and more',
                'icon' => 'fa-building-columns', 'theme' => 'board', 'empty' => 'Board question papers will appear here soon.',
                'url' => route('study-materials.notes', ['material_type' => 'question_papers', 'paper_source' => 'board', 'sort' => 'downloads']),
                'query' => fn () => (clone $publicMaterials)->boardPapers(),
            ],
            [
                'key' => 'school', 'lead' => 'School', 'accent' => 'Question Papers', 'hint' => 'Exams set by schools',
                'icon' => 'fa-school', 'theme' => 'study', 'empty' => 'School question papers will appear here soon.',
                'url' => route('study-materials.notes', ['material_type' => 'question_papers', 'paper_source' => 'school', 'sort' => 'downloads']),
                'query' => fn () => (clone $publicMaterials)->schoolPapers(),
            ],
            [
                'key' => 'notes', 'lead' => 'Study', 'accent' => 'Notes', 'hint' => 'Chapter-wise notes and summaries',
                'icon' => 'fa-note-sticky', 'theme' => 'notes', 'empty' => 'Notes will appear here soon.',
                'url' => route('study-materials.notes', ['material_type' => 'notes', 'sort' => 'downloads']),
                'query' => fn () => (clone $publicMaterials)->where('material_type', 'notes'),
            ],
        ];

        return array_map(function (array $group) use ($limit): array {
            // (the most viewed, then downloaded: a different six of them on each visit)
            $group['cards'] = $this->freshPick(
                ($group['query'])()->orderByDesc('views_count')->orderByDesc('downloads_count')->latest('id'),
                'trending.'.$group['key'],
                $limit,
            )
                ->map(fn (StudyMaterial $material): array => $this->materialCard($material))
                ->all();
            unset($group['query']);

            return $group;
        }, $groups);
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
     * A study material as a colourful panel's card: its cover (small copy), title, subject as the
     * pill, and the board or school, class and year.
     *
     * @return array{url: string, image: string, logo: null, title: string, pill: string, line: string, verified: bool}
     */
    private function materialCard(StudyMaterial $material): array
    {
        $image = $material->cardImageUrl();
        $source = $material->material_type === 'question_papers' ? ($material->board_university ?: $material->school_name) : null;

        return [
            'url' => $material->publicUrl(),
            'image' => $image ? ImageThumbnail::url($image) : '',
            'logo' => null,
            'title' => (string) $material->title,
            'pill' => (string) ($material->subject ?: $material->materialTypeLabel()),
            'line' => collect([$source, $material->class_course, $material->academic_year])->filter()->implode(' · '),
            'verified' => false,
        ];
    }

    /**
     * A school, college or institute as a card: its first gallery photo as the picture (a JPEG
     * "logo" is nearly always a photo, e.g. of the building, so it is used as one), else its logo.
     *
     * @return array{url: string, image: string, logo: ?string, title: string, pill: string, place: string, verified: bool}
     */
    private function institutionCard(Institute $institution, bool $isSchool): array
    {
        $gallery = is_array($institution->gallery) ? array_values(array_filter($institution->gallery)) : [];
        $logo = $institution->logoUrl();
        $logoIsPhoto = $logo !== null && preg_match('/\.jpe?g$/i', (string) $institution->logo) === 1;

        return [
            'url' => $institution->publicUrl(),
            'image' => $gallery !== [] ? asset($gallery[0]) : ($logoIsPhoto ? $logo : ''),
            'logo' => $logoIsPhoto ? null : $logo,
            'title' => $institution->publicDisplayName(),
            'pill' => $isSchool
                ? ($institution->board_affiliation ?: 'School')
                : ($institution->institutionTypeLabel() ?: 'Institute'),
            'place' => $institution->locationLabel(),
            'verified' => (bool) $institution->is_verified,
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

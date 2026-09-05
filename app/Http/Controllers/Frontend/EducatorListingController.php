<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Educator;
use App\Models\StudyMaterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EducatorListingController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $listingData = $this->listingPageData($request, 12);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->listingJsonResponse($request, $listingData, includeFeatured: true);
        }

        $featuredEducators = $this->featuredQuery($request)
            ->limit(5)
            ->get();

        return view('frontend.educator.index', [
            'educators' => $listingData['educators'],
            'featuredEducators' => $featuredEducators,
            'topSubjects' => $listingData['topSubjects'],
            'cities' => $listingData['cities'],
            'subjects' => $listingData['subjects'],
            'educatorStats' => $this->listingStats(),
            'hasLocation' => $listingData['hasLocation'],
        ]);
    }

    public function listings(Request $request): View|JsonResponse
    {
        $listingData = $this->listingPageData($request, 24);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->listingJsonResponse($request, $listingData, includeFeatured: false);
        }

        return view('frontend.educator.listings', [
            'educators' => $listingData['educators'],
            'topSubjects' => $listingData['topSubjects'],
            'cities' => $listingData['cities'],
            'subjects' => $listingData['subjects'],
            'educatorStats' => $this->listingStats(),
            'hasLocation' => $listingData['hasLocation'],
        ]);
    }

    /**
     * @return array{
     *     educators: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     topSubjects: Collection<int, object>,
     *     cities: Collection<int, string>,
     *     subjects: Collection<int, string>,
     *     hasLocation: bool,
     *     lat: ?float,
     *     lng: ?float
     * }
     */
    private function listingPageData(Request $request, int $perPage): array
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $educators = $this->baseQuery($request, $hasLocation ? (float) $lat : null, $hasLocation ? (float) $lng : null)
            ->paginate($perPage)
            ->appends($request->query());

        return [
            'educators' => $educators,
            'topSubjects' => $this->topSubjects(8),
            'cities' => $this->availableCities(),
            'subjects' => $this->availableSubjects(),
            'hasLocation' => $hasLocation,
            'lat' => $hasLocation ? (float) $lat : null,
            'lng' => $hasLocation ? (float) $lng : null,
        ];
    }

    /**
     * @param  array{
     *     educators: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     hasLocation: bool
     * }  $listingData
     */
    private function listingJsonResponse(Request $request, array $listingData, bool $includeFeatured): JsonResponse
    {
        $payload = [
            'html' => view('frontend.educator.partials.cards', [
                'educators' => $listingData['educators'],
                'hasLocation' => $listingData['hasLocation'],
            ])->render(),
            'next_page_url' => $listingData['educators']->nextPageUrl(),
            'loaded_to' => $listingData['educators']->lastItem() ?? 0,
            'total' => $listingData['educators']->total(),
        ];

        if ($includeFeatured) {
            $featured = $this->featuredQuery($request)->limit(5)->get();
            $payload['featured_html'] = view('frontend.educator.partials.featured-cards', [
                'featuredEducators' => $featured,
                'hasLocation' => $listingData['hasLocation'],
            ])->render();
            $payload['featured_total'] = $featured->count();
        }

        return response()->json($payload);
    }

    private function baseQuery(Request $request, ?float $lat = null, ?float $lng = null): Builder
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $city = trim((string) $request->input('city', ''));
        $subject = trim((string) $request->input('subject', ''));
        $minRating = $request->filled('min_rating') ? (float) $request->input('min_rating') : null;
        $radius = $request->filled('radius') ? (float) $request->input('radius') : null;
        $sort = (string) $request->input('sort', 'recent');

        $query = Educator::query()
            ->approved()
            ->with(['user:id,name,profile_image'])
            ->withCount(['studyMaterials as materials_count' => fn ($q) => $q->where('status', 'approved')]);

        if ($lat !== null && $lng !== null) {
            $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(COALESCE(latitude, institute_latitude))) * cos(radians(COALESCE(longitude, institute_longitude)) - radians(?)) + sin(radians(?)) * sin(radians(COALESCE(latitude, institute_latitude)))))';
            $query->select('educators.*')
                ->selectRaw($distanceSql.' as distance_km', [$lat, $lng, $lat]);

            if ($radius !== null && $radius > 0) {
                $query->where(function (Builder $q): void {
                    $q->whereNotNull('latitude')->orWhereNotNull('institute_latitude');
                })->whereRaw($distanceSql.' <= ?', [$lat, $lng, $lat, $radius]);
            }
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('display_name', 'like', '%'.$search.'%')
                    ->orWhere('professional_headline', 'like', '%'.$search.'%')
                    ->orWhere('tagline', 'like', '%'.$search.'%')
                    ->orWhere('associated_institute', 'like', '%'.$search.'%')
                    ->orWhere('subjects', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        if ($city !== '') {
            $query->where('city', 'like', '%'.$city.'%');
        }

        if ($subject !== '') {
            $query->where(function (Builder $q) use ($subject): void {
                $q->where('subjects', 'like', '%'.$subject.'%')
                    ->orWhere('tuition_subjects', 'like', '%'.$subject.'%');
            });
        }

        if ($request->boolean('verified')) {
            $query->where('is_verified', true);
        }

        if ($request->input('takes_tuitions') === '1' || $request->boolean('takes_tuitions')) {
            $query->where('take_tuitions', true);
        } elseif ($request->input('takes_tuitions') === '0') {
            $query->where('take_tuitions', false);
        }

        if ($request->boolean('available_now')) {
            $query->where('is_available_now', true);
        }

        if ($minRating !== null && $minRating > 0) {
            $query->where('average_rating', '>=', $minRating);
        }

        return match ($sort) {
            'rating' => $query->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            'experience' => $query->orderByDesc('years_experience'),
            'students' => $query->orderByDesc('students_taught'),
            'distance' => ($lat !== null && $lng !== null)
                ? $query->orderBy('distance_km')
                : $query->latest('approved_at'),
            default => $query->latest('approved_at'),
        };
    }

    private function featuredQuery(Request $request): Builder
    {
        return $this->baseQuery($request)
            ->where('is_verified', true)
            ->orderByDesc('average_rating')
            ->orderByDesc('reviews_count');
    }

    /**
     * @return array{verified: int, trusted: int, subjects: int, materials: int}
     */
    private function listingStats(): array
    {
        $approved = Educator::query()->approved();

        return [
            'verified' => (clone $approved)->where('is_verified', true)->count(),
            'trusted' => (clone $approved)->count(),
            'subjects' => $this->availableSubjects()->count(),
            'materials' => StudyMaterial::query()->approved()->count(),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function availableCities(): Collection
    {
        return Educator::query()
            ->approved()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function availableSubjects(): Collection
    {
        return $this->extractSubjectNames(
            Educator::query()->approved()->whereNotNull('subjects')->pluck('subjects')
        )->sort()->values();
    }

    /**
     * @return Collection<int, object{name: string, total: int}>
     */
    private function topSubjects(int $limit = 8): Collection
    {
        $counts = [];

        Educator::query()
            ->approved()
            ->whereNotNull('subjects')
            ->pluck('subjects')
            ->each(function ($subjects) use (&$counts): void {
                foreach ($this->normalizeSubjectList($subjects) as $name) {
                    $counts[$name] = ($counts[$name] ?? 0) + 1;
                }
            });

        arsort($counts);

        return collect($counts)
            ->take($limit)
            ->map(fn (int $total, string $name) => (object) ['name' => $name, 'total' => $total])
            ->values();
    }

    /**
     * @param  Collection<int, mixed>  $rows
     * @return Collection<int, string>
     */
    private function extractSubjectNames(Collection $rows): Collection
    {
        return $rows
            ->flatMap(fn ($subjects) => $this->normalizeSubjectList($subjects))
            ->unique(fn (string $name) => mb_strtolower($name))
            ->values();
    }

    /**
     * @return list<string>
     */
    private function normalizeSubjectList(mixed $subjects): array
    {
        if (! is_array($subjects)) {
            return [];
        }

        $names = [];
        foreach ($subjects as $item) {
            if (is_string($item) && trim($item) !== '') {
                $names[] = trim($item);
            } elseif (is_array($item) && filled($item['name'] ?? null)) {
                $names[] = trim((string) $item['name']);
            }
        }

        return array_values(array_unique($names));
    }
}

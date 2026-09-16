<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InstituteListingController extends Controller
{
    public function schoolIndex(Request $request): View|JsonResponse
    {
        return $this->index($request, 'school');
    }

    public function schoolListings(Request $request): View|JsonResponse
    {
        return $this->listings($request, 'school');
    }

    public function instituteIndex(Request $request): View|JsonResponse
    {
        return $this->index($request, 'institute');
    }

    public function instituteListings(Request $request): View|JsonResponse
    {
        return $this->listings($request, 'institute');
    }

    public function index(Request $request, string $ownerRole = 'school'): View|JsonResponse
    {
        $listingData = $this->listingPageData($request, 12, $ownerRole);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->listingJsonResponse($listingData);
        }

        return view('frontend.institutes.index', $this->listingViewData($listingData, $ownerRole));
    }

    public function listings(Request $request, string $ownerRole = 'school'): View|JsonResponse
    {
        $listingData = $this->listingPageData($request, 24, $ownerRole);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->listingJsonResponse($listingData);
        }

        return view('frontend.institutes.listings', $this->listingViewData($listingData, $ownerRole));
    }

    /**
     * @param  array<string, mixed>  $listingData
     * @return array<string, mixed>
     */
    private function listingViewData(array $listingData, string $ownerRole): array
    {
        return array_merge($listingData, [
            'ownerRole' => $ownerRole,
            'listingContext' => $ownerRole === 'school' ? 'schools' : 'institutes',
            'instituteStats' => $this->listingStats($ownerRole),
        ]);
    }

    /**
     * @return array{
     *     institutes: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cities: Collection<int, string>,
     *     types: Collection<int, string>,
     *     boards: Collection<int, string>,
     *     hasLocation: bool
     * }
     */
    private function listingPageData(Request $request, int $perPage, string $ownerRole = 'school'): array
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $institutes = $this->baseQuery(
            $request,
            $hasLocation ? (float) $lat : null,
            $hasLocation ? (float) $lng : null,
            $ownerRole
        )
            ->paginate($perPage)
            ->appends($request->query());

        return [
            'institutes' => $institutes,
            'cities' => $this->availableCities($ownerRole),
            'types' => $this->availableTypes($ownerRole),
            'boards' => $this->availableBoards($ownerRole),
            'hasLocation' => $hasLocation,
        ];
    }

    /**
     * @param  array{institutes: \Illuminate\Contracts\Pagination\LengthAwarePaginator, hasLocation: bool}  $listingData
     */
    private function listingJsonResponse(array $listingData): JsonResponse
    {
        return response()->json([
            'html' => view('frontend.institutes.partials.cards', [
                'institutes' => $listingData['institutes'],
                'hasLocation' => $listingData['hasLocation'],
            ])->render(),
            'next_page_url' => $listingData['institutes']->nextPageUrl(),
            'loaded_to' => $listingData['institutes']->lastItem() ?? 0,
            'total' => $listingData['institutes']->total(),
        ]);
    }

    private function baseQuery(Request $request, ?float $lat = null, ?float $lng = null, string $ownerRole = 'school'): Builder
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $city = trim((string) $request->input('city', ''));
        $type = trim((string) $request->input('type', ''));
        $board = trim((string) $request->input('board', ''));
        $radius = $request->filled('radius') ? (float) $request->input('radius') : null;
        $sort = (string) $request->input('sort', 'recent');

        $query = Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
            ->with(['user:id,name,profile_image,role']);

        if ($lat !== null && $lng !== null) {
            $distanceSql = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';
            $query->select('institutes.*')
                ->selectRaw($distanceSql.' as distance_km', [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($radius !== null && $radius > 0) {
                $query->whereRaw($distanceSql.' <= ?', [$lat, $lng, $lat, $radius]);
            }
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('institution_name', 'like', '%'.$search.'%')
                    ->orWhere('display_name', 'like', '%'.$search.'%')
                    ->orWhere('tagline', 'like', '%'.$search.'%')
                    ->orWhere('board_affiliation', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%');
            });
        }

        if ($city !== '') {
            $query->where('city', 'like', '%'.$city.'%');
        }

        if ($type !== '') {
            $query->where('institution_type', $type);
        }

        if ($board !== '') {
            $query->where('board_affiliation', 'like', '%'.$board.'%');
        }

        if ($request->boolean('verified')) {
            $query->where('is_verified', true);
        }

        return match ($sort) {
            'name' => $query->orderBy('institution_name'),
            'distance' => ($lat !== null && $lng !== null)
                ? $query->orderBy('distance_km')
                : $query->latest('approved_at'),
            default => $query->latest('approved_at'),
        };
    }

    /**
     * @return array{verified: int, total: int, cities: int}
     */
    private function listingStats(string $ownerRole = 'school'): array
    {
        $approved = Institute::query()->approved()->forOwnerRole($ownerRole);

        return [
            'verified' => (clone $approved)->where('is_verified', true)->count(),
            'total' => (clone $approved)->count(),
            'cities' => $this->availableCities()->count(),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function availableCities(string $ownerRole = 'school'): Collection
    {
        return Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
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
    private function availableTypes(string $ownerRole = 'school'): Collection
    {
        return Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
            ->whereNotNull('institution_type')
            ->where('institution_type', '!=', '')
            ->distinct()
            ->orderBy('institution_type')
            ->pluck('institution_type')
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function availableBoards(string $ownerRole = 'school'): Collection
    {
        return Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
            ->whereNotNull('board_affiliation')
            ->where('board_affiliation', '!=', '')
            ->distinct()
            ->orderBy('board_affiliation')
            ->pluck('board_affiliation')
            ->filter()
            ->values();
    }
}

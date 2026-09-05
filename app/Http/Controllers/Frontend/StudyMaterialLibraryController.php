<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialReview;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudyMaterialLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $base = StudyMaterial::query()->approved()->with(['educator:id,display_name,slug,profile_photo,is_verified']);

        $filters = $this->filtersFromRequest($request);
        $filtered = (clone $base);
        $this->applyFilters($filtered, $filters);

        $trending = (clone $base)->where('is_trending', true)->latest('downloads_count')->limit(8)->get();
        if ($trending->isEmpty()) {
            $trending = (clone $base)->orderByDesc('downloads_count')->limit(8)->get();
        }

        $recent = (clone $base)->latest()->limit(8)->get();

        $categories = StudyMaterial::query()
            ->approved()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $materialTypes = StudyMaterial::query()
            ->approved()
            ->select('material_type', DB::raw('COUNT(*) as total'))
            ->groupBy('material_type')
            ->orderByDesc('total')
            ->get();

        $topContributors = StudyMaterial::query()
            ->approved()
            ->select('educator_id', DB::raw('COUNT(*) as materials_count'), DB::raw('SUM(downloads_count) as downloads_sum'))
            ->groupBy('educator_id')
            ->orderByDesc('materials_count')
            ->limit(6)
            ->with('educator:id,display_name,slug,profile_photo,is_verified,type')
            ->get();

        $materials = $filtered->latest()->paginate(12)->withQueryString();

        return view('frontend.study-materials.library', compact(
            'materials',
            'trending',
            'recent',
            'categories',
            'materialTypes',
            'topContributors',
            'filters'
        ));
    }

    public function notes(Request $request): View
    {
        $base = StudyMaterial::query()->approved()->notes()->with(['educator:id,display_name,slug,profile_photo,is_verified']);

        $filters = $this->filtersFromRequest($request);
        $filtered = (clone $base);
        $this->applyFilters($filtered, $filters);

        $stats = [
            'total' => (clone $base)->count(),
            'subjects' => (int) StudyMaterial::query()->approved()->notes()->whereNotNull('subject')->where('subject', '!=', '')->distinct()->count('subject'),
            'downloads' => (int) (clone $base)->sum('downloads_count'),
            'contributors' => (int) StudyMaterial::query()->approved()->notes()->distinct()->count('educator_id'),
        ];

        $popularSubjects = StudyMaterial::query()
            ->approved()
            ->notes()
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->select('subject', DB::raw('COUNT(*) as total'))
            ->groupBy('subject')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topContributors = StudyMaterial::query()
            ->approved()
            ->notes()
            ->select('educator_id', DB::raw('COUNT(*) as materials_count'), DB::raw('SUM(downloads_count) as downloads_sum'))
            ->groupBy('educator_id')
            ->orderByDesc('materials_count')
            ->limit(6)
            ->with('educator:id,display_name,slug,profile_photo,is_verified,type')
            ->get();

        $viewMode = $request->string('view')->toString() === 'grid' ? 'grid' : 'list';
        $materials = $filtered->latest()->paginate(12)->withQueryString();

        return view('frontend.study-materials.notes', compact(
            'materials',
            'stats',
            'popularSubjects',
            'topContributors',
            'filters',
            'viewMode'
        ));
    }

    public function show(string $slug): View
    {
        $material = StudyMaterial::query()
            ->approved()
            ->where('slug', $slug)
            ->with([
                'educator:id,display_name,slug,profile_photo,is_verified,type,professional_headline,city',
                'reviews' => fn ($q) => $q->with('user:id,name,profile_image')->latest()->limit(20),
            ])
            ->firstOrFail();

        $material->increment('views_count');

        $related = StudyMaterial::query()
            ->approved()
            ->where('id', '!=', $material->id)
            ->where(function ($q) use ($material) {
                $q->where('subject', $material->subject)
                    ->orWhere('category', $material->category)
                    ->orWhere('material_type', $material->material_type);
            })
            ->with('educator:id,display_name,slug')
            ->latest()
            ->limit(6)
            ->get();

        $isBookmarked = auth()->check()
            && $material->bookmarkedBy()->where('user_id', auth()->id())->exists();

        $userReview = auth()->check()
            ? StudyMaterialReview::query()
                ->where('study_material_id', $material->id)
                ->where('user_id', auth()->id())
                ->first()
            : null;

        return view('frontend.study-materials.show', compact('material', 'related', 'isBookmarked', 'userReview'));
    }

    public function download(string $slug): BinaryFileResponse|RedirectResponse
    {
        $material = StudyMaterial::query()->approved()->where('slug', $slug)->firstOrFail();

        abort_unless(filled($material->file_path) && is_file(public_path($material->file_path)), 404);

        $material->increment('downloads_count');

        return response()->download(
            public_path($material->file_path),
            $material->file_name ?: basename($material->file_path)
        );
    }

    public function bookmark(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $material = StudyMaterial::query()->approved()->where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        $exists = $material->bookmarkedBy()->where('user_id', $userId)->exists();
        if ($exists) {
            $material->bookmarkedBy()->detach($userId);
            $material->decrement('saves_count');
            $bookmarked = false;
            $message = 'Removed from saved.';
        } else {
            $material->bookmarkedBy()->attach($userId);
            $material->increment('saves_count');
            $bookmarked = true;
            $message = 'Saved successfully.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'saved' => $bookmarked,
                'bookmarked' => $bookmarked,
                'saves_count' => (int) $material->fresh()->saves_count,
            ]);
        }

        return back()->with('status', $message);
    }

    public function review(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $material = StudyMaterial::query()
            ->approved()
            ->with(['educator.user', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $wasExisting = StudyMaterialReview::query()
            ->where('study_material_id', $material->id)
            ->where('user_id', auth()->id())
            ->exists();

        $review = StudyMaterialReview::updateOrCreate(
            [
                'study_material_id' => $material->id,
                'user_id' => auth()->id(),
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        $review->load('user:id,name,profile_image');
        $material->recalculateRating();
        $material->refresh();

        $owner = $material->educator?->user ?: $material->user;
        if ($owner && (int) $owner->id !== (int) auth()->id() && ! $wasExisting) {
            $reviewerName = auth()->user()?->name ?: 'Someone';
            PortalNotificationService::notifyUser(
                $owner,
                'New review on your study material',
                $reviewerName.' left a '.$validated['rating'].'-star review on "'.$material->title.'".',
                route('educator.materials.show', $material),
                'engagement'
            );
        }

        $message = $wasExisting ? 'Review updated.' : 'Review submitted.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'average_rating' => number_format((float) $material->average_rating, 1),
                'reviews_count' => (int) $material->reviews_count,
                'review_html' => view('frontend.study-materials.partials.review-item', [
                    'review' => $review,
                ])->render(),
                'review_id' => $review->id,
            ]);
        }

        return back()->with('status', $message);
    }

    /**
     * @return array<string, string|null>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => $request->string('q')->toString() ?: null,
            'category' => $request->string('category')->toString() ?: null,
            'material_type' => $request->string('material_type')->toString() ?: null,
            'subject' => $request->string('subject')->toString() ?: null,
            'class_course' => $request->string('class_course')->toString() ?: null,
            'board_university' => $request->string('board_university')->toString() ?: null,
            'language' => $request->string('language')->toString() ?: null,
            'difficulty' => $request->string('difficulty')->toString() ?: null,
        ];
    }

    /**
     * @param  array<string, string|null>  $filters
     */
    private function applyFilters($query, array $filters): void
    {
        if ($filters['q']) {
            $q = '%'.$filters['q'].'%';
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', $q)
                    ->orWhere('description', 'like', $q)
                    ->orWhere('subject', 'like', $q)
                    ->orWhere('topic_chapter', 'like', $q);
            });
        }

        foreach (['category', 'material_type', 'subject', 'class_course', 'board_university', 'language', 'difficulty'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
    }
}

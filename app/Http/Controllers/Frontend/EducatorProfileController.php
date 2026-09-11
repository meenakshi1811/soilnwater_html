<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\EducatorEnquiryReceivedMail;
use App\Models\Educator;
use App\Models\EducatorEnquiry;
use App\Models\EducatorReview;
use App\Models\StudyMaterial;
use App\Models\StudyMaterialReview;
use Illuminate\Support\Facades\DB;
use App\Services\PortalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EducatorProfileController extends Controller
{
    public function show(string $slug): View
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->with([
                'user:id,name,profile_image',
            ])
            ->withCount(['followers', 'studyMaterials as approved_materials_count' => fn ($q) => $q->where('status', 'approved')])
            ->firstOrFail();

        $isFollowing = auth()->check()
            && $educator->followers()->where('user_id', auth()->id())->exists();

        $notesQuery = $educator->notesQuery();
        $notesTotal = (clone $notesQuery)->count();
        $notes = $notesQuery->latest()->limit(3)->get();
        $coursesQuery = $educator->coursesQuery();
        $coursesTotal = (clone $coursesQuery)->count();
        $courses = $coursesQuery->latest()->limit(3)->get();
        $questionPapersQuery = $educator->questionPapersQuery();
        $questionPapersTotal = (clone $questionPapersQuery)->count();
        $questionPapers = $questionPapersQuery->latest()->limit(3)->get();

        $profileReviewsPage = $this->profileReviewsPaginated($educator, 0, 10);
        $profileReviews = $profileReviewsPage['items'];
        $profileReviewsTotal = $profileReviewsPage['total'];
        $profileReviewsHasMore = $profileReviewsPage['has_more'];
        $testimonials = $this->profileTestimonialsFor($educator, 6);
        $educator->recalculateRating();
        $educator->refresh();

        $userReview = auth()->check()
            ? EducatorReview::query()
                ->where('educator_id', $educator->id)
                ->where('user_id', auth()->id())
                ->first()
            : null;

        return view('frontend.educator.show', compact(
            'educator',
            'isFollowing',
            'notes',
            'notesTotal',
            'courses',
            'coursesTotal',
            'questionPapers',
            'questionPapersTotal',
            'profileReviews',
            'profileReviewsTotal',
            'profileReviewsHasMore',
            'testimonials',
            'userReview'
        ));
    }

    public function reviews(Request $request, string $slug): JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->firstOrFail();

        $offset = max(0, (int) $request->input('offset', 0));
        $limit = 10;
        $page = $this->profileReviewsPaginated($educator, $offset, $limit);

        return response()->json([
            'ok' => true,
            'reviews_html' => view('frontend.educator.partials.reviews-results', [
                'reviews' => $page['items'],
            ])->render(),
            'has_more' => $page['has_more'],
            'next_offset' => $page['next_offset'],
            'loaded_count' => min($offset + $page['items']->count(), $page['total']),
            'total_count' => $page['total'],
        ]);
    }

    public function courses(Request $request, string $slug): View|JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $this->buildCoursesPageData($request, $educator);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'results_html' => view('frontend.educator.partials.courses-results', $data)->render(),
                'summary_html' => view('frontend.educator.partials.courses-summary', $data)->render(),
                'url' => route('educator.courses', array_merge(['slug' => $educator->slug], $request->query())),
            ]);
        }

        return view('frontend.educator.courses', $data);
    }

    public function notes(Request $request, string $slug): View|JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $this->buildNotesPageData($request, $educator);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'results_html' => view('frontend.educator.partials.notes-results', $data)->render(),
                'summary_html' => view('frontend.educator.partials.notes-summary', $data)->render(),
                'url' => route('educator.notes', array_merge(['slug' => $educator->slug], $request->query())),
            ]);
        }

        return view('frontend.educator.notes', $data);
    }

    public function questionPapers(Request $request, string $slug): View|JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->where('slug', $slug)
            ->firstOrFail();

        $data = $this->buildQuestionPapersPageData($request, $educator);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'results_html' => view('frontend.educator.partials.question-papers-results', $data)->render(),
                'summary_html' => view('frontend.educator.partials.question-papers-summary', $data)->render(),
                'url' => route('educator.question-papers', array_merge(['slug' => $educator->slug], $request->query())),
            ]);
        }

        return view('frontend.educator.question-papers', $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNotesPageData(Request $request, Educator $educator): array
    {
        $base = $educator->notesQuery();
        $filters = [
            'search' => trim($request->string('search')->toString()),
            'subject' => $request->string('subject')->toString(),
            'class_course' => $request->string('class_course')->toString(),
            'file_type' => $request->string('file_type')->toString(),
            'pricing' => $request->string('pricing')->toString(),
        ];

        $filtered = clone $base;

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $filtered->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('topic_chapter', 'like', '%'.$search.'%');
            });
        }

        if ($filters['subject'] !== '') {
            $filtered->where('subject', $filters['subject']);
        }

        if ($filters['class_course'] !== '') {
            $filtered->where('class_course', $filters['class_course']);
        }

        if ($filters['file_type'] !== '') {
            $this->applyFileTypeFilter($filtered, $filters['file_type']);
        }

        if ($filters['pricing'] === 'free') {
            $filtered->where('is_free', true);
        } elseif ($filters['pricing'] === 'premium') {
            $filtered->where('is_free', false);
        }

        $sort = $request->string('sort')->toString() ?: 'recent';
        match ($sort) {
            'rating' => $filtered->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            'downloads' => $filtered->orderByDesc('downloads_count')->orderByDesc('created_at'),
            'title' => $filtered->orderBy('title'),
            default => $filtered->latest(),
        };

        if (auth()->check()) {
            $filtered->withExists([
                'bookmarkedBy as is_bookmarked' => fn ($query) => $query->where('user_id', auth()->id()),
            ]);
        }

        $notes = $filtered->paginate(12)->withQueryString();

        $subjects = (clone $base)
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->select('subject', DB::raw('COUNT(*) as total'))
            ->groupBy('subject')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $classOptions = (clone $base)
            ->whereNotNull('class_course')
            ->where('class_course', '!=', '')
            ->distinct()
            ->orderBy('class_course')
            ->pluck('class_course');

        $fileTypeCounts = $this->fileTypeCountsForMaterials($base);

        $stats = [
            'total' => (clone $base)->count(),
            'free' => (clone $base)->where('is_free', true)->count(),
            'premium' => (clone $base)->where('is_free', false)->count(),
            'downloads' => (int) (clone $base)->sum('downloads_count'),
        ];

        return compact(
            'educator',
            'notes',
            'filters',
            'sort',
            'subjects',
            'classOptions',
            'fileTypeCounts',
            'stats'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildQuestionPapersPageData(Request $request, Educator $educator): array
    {
        $base = $educator->questionPapersQuery();
        $filters = [
            'search' => trim($request->string('search')->toString()),
            'subject' => $request->string('subject')->toString(),
            'class_course' => $request->string('class_course')->toString(),
            'file_type' => $request->string('file_type')->toString(),
            'pricing' => $request->string('pricing')->toString(),
        ];

        $filtered = clone $base;

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $filtered->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('topic_chapter', 'like', '%'.$search.'%')
                    ->orWhere('exam_test', 'like', '%'.$search.'%');
            });
        }

        if ($filters['subject'] !== '') {
            $filtered->where('subject', $filters['subject']);
        }

        if ($filters['class_course'] !== '') {
            $filtered->where('class_course', $filters['class_course']);
        }

        if ($filters['file_type'] !== '') {
            $this->applyFileTypeFilter($filtered, $filters['file_type']);
        }

        if ($filters['pricing'] === 'free') {
            $filtered->where('is_free', true);
        } elseif ($filters['pricing'] === 'premium') {
            $filtered->where('is_free', false);
        }

        $sort = $request->string('sort')->toString() ?: 'recent';
        match ($sort) {
            'rating' => $filtered->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            'downloads' => $filtered->orderByDesc('downloads_count')->orderByDesc('created_at'),
            'title' => $filtered->orderBy('title'),
            default => $filtered->latest(),
        };

        if (auth()->check()) {
            $filtered->withExists([
                'bookmarkedBy as is_bookmarked' => fn ($query) => $query->where('user_id', auth()->id()),
            ]);
        }

        $questionPapers = $filtered->paginate(12)->withQueryString();

        $subjects = (clone $base)
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->select('subject', DB::raw('COUNT(*) as total'))
            ->groupBy('subject')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $classOptions = (clone $base)
            ->whereNotNull('class_course')
            ->where('class_course', '!=', '')
            ->distinct()
            ->orderBy('class_course')
            ->pluck('class_course');

        $fileTypeCounts = $this->fileTypeCountsForMaterials($base);

        $stats = [
            'total' => (clone $base)->count(),
            'free' => (clone $base)->where('is_free', true)->count(),
            'premium' => (clone $base)->where('is_free', false)->count(),
            'downloads' => (int) (clone $base)->sum('downloads_count'),
        ];

        return compact(
            'educator',
            'questionPapers',
            'filters',
            'sort',
            'subjects',
            'classOptions',
            'fileTypeCounts',
            'stats'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCoursesPageData(Request $request, Educator $educator): array
    {
        $base = $educator->coursesQuery();
        $filters = [
            'search' => trim($request->string('search')->toString()),
            'material_type' => $request->string('material_type')->toString(),
            'subject' => $request->string('subject')->toString(),
            'class_course' => $request->string('class_course')->toString(),
            'pricing' => $request->string('pricing')->toString(),
        ];

        $filtered = clone $base;

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $filtered->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('topic_chapter', 'like', '%'.$search.'%');
            });
        }

        if ($filters['material_type'] !== '') {
            $filtered->where('material_type', $filters['material_type']);
        }

        if ($filters['subject'] !== '') {
            $filtered->where('subject', $filters['subject']);
        }

        if ($filters['class_course'] !== '') {
            $filtered->where('class_course', $filters['class_course']);
        }

        if ($filters['pricing'] === 'free') {
            $filtered->where('is_free', true);
        } elseif ($filters['pricing'] === 'premium') {
            $filtered->where('is_free', false);
        }

        $sort = $request->string('sort')->toString() ?: 'recent';
        match ($sort) {
            'rating' => $filtered->orderByDesc('average_rating')->orderByDesc('reviews_count'),
            'downloads' => $filtered->orderByDesc('downloads_count')->orderByDesc('created_at'),
            'title' => $filtered->orderBy('title'),
            default => $filtered->latest(),
        };

        if (auth()->check()) {
            $filtered->withExists([
                'bookmarkedBy as is_bookmarked' => fn ($query) => $query->where('user_id', auth()->id()),
            ]);
        }

        $courses = $filtered->paginate(12)->withQueryString();

        $materialTypes = (clone $base)
            ->select('material_type', DB::raw('COUNT(*) as total'))
            ->groupBy('material_type')
            ->orderByDesc('total')
            ->get();

        $subjects = (clone $base)
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->select('subject', DB::raw('COUNT(*) as total'))
            ->groupBy('subject')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $classOptions = (clone $base)
            ->whereNotNull('class_course')
            ->where('class_course', '!=', '')
            ->distinct()
            ->orderBy('class_course')
            ->pluck('class_course');

        $stats = [
            'total' => (clone $base)->count(),
            'free' => (clone $base)->where('is_free', true)->count(),
            'premium' => (clone $base)->where('is_free', false)->count(),
            'downloads' => (int) (clone $base)->sum('downloads_count'),
        ];

        return compact(
            'educator',
            'courses',
            'filters',
            'sort',
            'materialTypes',
            'subjects',
            'classOptions',
            'stats'
        );
    }

    private function applyFileTypeFilter(\Illuminate\Database\Eloquent\Builder $query, string $group): void
    {
        match ($group) {
            'pdf' => $query->where('file_type', 'like', '%pdf%'),
            'doc' => $query->where(function ($inner) {
                $inner->where('file_type', 'like', '%doc%');
            }),
            'ppt' => $query->where('file_type', 'like', '%ppt%'),
            'xls' => $query->where('file_type', 'like', '%xls%'),
            'image' => $query->where(function ($inner) {
                $inner->where('file_type', 'like', '%jpg%')
                    ->orWhere('file_type', 'like', '%jpeg%')
                    ->orWhere('file_type', 'like', '%png%')
                    ->orWhere('file_type', 'like', '%webp%')
                    ->orWhere('file_type', 'like', '%gif%');
            }),
            default => null,
        };
    }

    /**
     * @return array<string, int>
     */
    private function fileTypeCountsForMaterials(\Illuminate\Database\Eloquent\Builder $base): array
    {
        $counts = [
            'pdf' => 0,
            'doc' => 0,
            'ppt' => 0,
            'xls' => 0,
            'image' => 0,
        ];

        (clone $base)
            ->whereNotNull('file_type')
            ->where('file_type', '!=', '')
            ->select('file_type')
            ->cursor()
            ->each(function ($row) use (&$counts) {
                $group = StudyMaterial::fileTypeGroup($row->file_type);
                if (isset($counts[$group])) {
                    $counts[$group]++;
                }
            });

        return $counts;
    }

    public function enquiry(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->with('user')
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $enquiry = EducatorEnquiry::create([
            'educator_id' => $educator->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? auth()->user()?->email,
            'phone' => $validated['phone'] ?? auth()->user()?->phone_number,
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        $owner = $educator->user;
        $fromName = $enquiry->name ?: 'Someone';

        PortalNotificationService::notifyUser(
            $owner,
            'New enquiry received',
            $fromName.' sent you an enquiry'.($enquiry->subject ? ': '.$enquiry->subject : '.'),
            route('educator.enquiries.index'),
            'engagement'
        );

        $emailSent = false;
        $recipient = $educator->email ?: $owner?->email;
        if ($recipient) {
            try {
                Mail::to($recipient)->send(EducatorEnquiryReceivedMail::forEnquiry($enquiry));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::error('Failed to send educator enquiry mail', [
                    'enquiry_id' => $enquiry->id,
                    'email' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = 'Enquiry sent successfully.'
            .($emailSent ? ' The educator has been notified by email and portal.' : ' The educator has been notified in the portal.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    public function follow(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $educator = Educator::query()->approved()->where('slug', $slug)->firstOrFail();
        $userId = auth()->id();

        abort_if((int) $educator->user_id === (int) $userId, 422, 'You cannot follow your own profile.');

        $attached = $educator->followers()->where('user_id', $userId)->exists();
        if ($attached) {
            $educator->followers()->detach($userId);
            $following = false;
            $message = 'Unfollowed '.$educator->display_name.'.';
        } else {
            $educator->followers()->attach($userId);
            $following = true;
            $message = 'You are now following '.$educator->display_name.'. You will get email and portal updates when they post new notes.';

            if ($educator->user && (int) $educator->user_id !== (int) $userId) {
                PortalNotificationService::notifyUser(
                    $educator->user,
                    'New follower',
                    (auth()->user()?->name ?: 'Someone').' started following your Teacher / Tutor profile.',
                    route('educator.show', $educator->slug),
                    'engagement'
                );
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'following' => $following,
                'followers_count' => $educator->followers()->count(),
            ]);
        }

        return back()->with('status', $message);
    }

    public function review(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $educator = Educator::query()
            ->approved()
            ->with('user')
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
            'student_class' => ['nullable', 'string', 'max:120'],
        ]);

        $wasExisting = EducatorReview::query()
            ->where('educator_id', $educator->id)
            ->where('user_id', auth()->id())
            ->exists();

        $review = EducatorReview::updateOrCreate(
            [
                'educator_id' => $educator->id,
                'user_id' => auth()->id(),
            ],
            [
                'student_name' => auth()->user()?->name,
                'student_class' => $validated['student_class'] ?? null,
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        $review->load('user:id,name,profile_image');
        $educator->recalculateRating();
        $educator->refresh();

        if ($educator->user && (int) $educator->user->id !== (int) auth()->id() && ! $wasExisting) {
            PortalNotificationService::notifyUser(
                $educator->user,
                'New review on your profile',
                (auth()->user()?->name ?: 'Someone').' left a '.$validated['rating'].'-star review on your Teacher / Tutor profile.',
                route('educator.show', $educator->slug),
                'engagement'
            );
        }

        $message = $wasExisting ? 'Review updated.' : 'Review submitted.';

        if ($request->expectsJson() || $request->ajax()) {
            $reviewItem = (object) [
                'id' => 'profile-'.$review->id,
                'source' => 'profile',
                'author' => $review->student_name ?: ($review->user?->name ?: 'Student'),
                'rating' => (int) $review->rating,
                'body' => $review->review,
                'meta' => $review->student_class,
                'date' => $review->updated_at,
                'material_title' => null,
                'material_url' => null,
            ];

            return response()->json([
                'ok' => true,
                'message' => $message,
                'average_rating' => number_format((float) $educator->average_rating, 1),
                'reviews_count' => (int) $educator->reviews_count,
                'review_html' => view('frontend.educator.partials.review-item', [
                    'item' => $reviewItem,
                ])->render(),
                'review_key' => 'profile-'.$review->id,
                'testimonial_html' => filled(trim((string) $review->review))
                    ? view('frontend.educator.partials.testimonial-item', ['item' => $reviewItem])->render()
                    : null,
            ]);
        }

        return back()->with('status', $message);
    }

    /**
     * @return array{items: Collection<int, object>, total: int, has_more: bool, next_offset: int|null}
     */
    private function profileReviewsPaginated(Educator $educator, int $offset = 0, int $limit = 10): array
    {
        $stubs = $this->profileReviewStubs($educator);
        $total = $stubs->count();
        $pageStubs = $stubs->slice($offset, $limit)->values();
        $items = $this->loadProfileReviewItems($pageStubs);
        $loaded = $offset + $items->count();

        return [
            'items' => $items,
            'total' => $total,
            'has_more' => $loaded < $total,
            'next_offset' => $loaded < $total ? $loaded : null,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function profileTestimonialsFor(Educator $educator, int $limit = 6): Collection
    {
        $stubs = $this->profileReviewStubs($educator)
            ->filter(fn ($stub) => $stub->has_body)
            ->take($limit)
            ->values();

        return $this->loadProfileReviewItems($stubs);
    }

    /**
     * @return Collection<int, object>
     */
    private function profileReviewStubs(Educator $educator): Collection
    {
        $profileStubs = EducatorReview::query()
            ->where('educator_id', $educator->id)
            ->get(['id', 'updated_at', 'review'])
            ->map(fn (EducatorReview $review) => (object) [
                'type' => 'profile',
                'id' => $review->id,
                'sort_at' => $review->updated_at,
                'has_body' => filled(trim((string) $review->review)),
            ]);

        $materialStubs = StudyMaterialReview::query()
            ->whereHas('studyMaterial', fn ($q) => $q->where('educator_id', $educator->id)->where('status', 'approved'))
            ->get(['id', 'updated_at', 'review'])
            ->map(fn (StudyMaterialReview $review) => (object) [
                'type' => 'material',
                'id' => $review->id,
                'sort_at' => $review->updated_at,
                'has_body' => filled(trim((string) $review->review)),
            ]);

        return $profileStubs
            ->concat($materialStubs)
            ->sortByDesc(fn ($stub) => optional($stub->sort_at)->timestamp ?? 0)
            ->values();
    }

    /**
     * @param  Collection<int, object>  $stubs
     * @return Collection<int, object>
     */
    private function loadProfileReviewItems(Collection $stubs): Collection
    {
        if ($stubs->isEmpty()) {
            return collect();
        }

        $profileIds = $stubs->where('type', 'profile')->pluck('id');
        $materialIds = $stubs->where('type', 'material')->pluck('id');

        $profileMap = EducatorReview::query()
            ->whereIn('id', $profileIds)
            ->with('user:id,name')
            ->get()
            ->keyBy(fn (EducatorReview $review) => 'profile-'.$review->id);

        $materialMap = StudyMaterialReview::query()
            ->whereIn('id', $materialIds)
            ->with(['user:id,name', 'studyMaterial:id,title,slug,educator_id'])
            ->get()
            ->keyBy(fn (StudyMaterialReview $review) => 'material-'.$review->id);

        return $stubs
            ->map(function ($stub) use ($profileMap, $materialMap) {
                $key = $stub->type.'-'.$stub->id;

                if ($stub->type === 'profile' && $profileMap->has($key)) {
                    return $this->mapProfileReview($profileMap->get($key));
                }

                if ($stub->type === 'material' && $materialMap->has($key)) {
                    return $this->mapMaterialReview($materialMap->get($key));
                }

                return null;
            })
            ->filter()
            ->values();
    }

    private function mapProfileReview(EducatorReview $review): object
    {
        return (object) [
            'id' => 'profile-'.$review->id,
            'source' => 'profile',
            'author' => $review->student_name ?: ($review->user?->name ?: 'Student'),
            'rating' => (int) $review->rating,
            'body' => $review->review,
            'meta' => $review->student_class,
            'date' => $review->updated_at,
            'material_title' => null,
            'material_url' => null,
            'sort_at' => $review->updated_at,
        ];
    }

    private function mapMaterialReview(StudyMaterialReview $review): object
    {
        return (object) [
            'id' => 'material-'.$review->id,
            'source' => 'material',
            'author' => $review->user?->name ?: 'Student',
            'rating' => (int) $review->rating,
            'body' => $review->review,
            'meta' => null,
            'date' => $review->updated_at,
            'material_title' => $review->studyMaterial?->title,
            'material_url' => $review->studyMaterial?->publicUrl(),
            'sort_at' => $review->updated_at,
        ];
    }
}

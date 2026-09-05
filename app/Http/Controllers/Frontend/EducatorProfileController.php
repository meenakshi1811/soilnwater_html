<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\EducatorEnquiryReceivedMail;
use App\Models\Educator;
use App\Models\EducatorEnquiry;
use App\Models\EducatorReview;
use App\Models\StudyMaterialReview;
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
                'studyMaterials' => fn ($q) => $q->approved()->latest()->limit(8),
            ])
            ->withCount(['followers', 'studyMaterials as approved_materials_count' => fn ($q) => $q->where('status', 'approved')])
            ->firstOrFail();

        $isFollowing = auth()->check()
            && $educator->followers()->where('user_id', auth()->id())->exists();

        $notes = $educator->studyMaterials->where('material_type', 'notes')->values();
        $courses = $educator->studyMaterials->where('material_type', '!=', 'notes')->values();

        $profileReviews = $this->profileReviewsFor($educator);
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
            'courses',
            'profileReviews',
            'userReview'
        ));
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
            return response()->json([
                'ok' => true,
                'message' => $message,
                'average_rating' => number_format((float) $educator->average_rating, 1),
                'reviews_count' => (int) $educator->reviews_count,
                'review_html' => view('frontend.educator.partials.review-item', [
                    'item' => (object) [
                        'id' => 'profile-'.$review->id,
                        'source' => 'profile',
                        'author' => $review->student_name ?: ($review->user?->name ?: 'Student'),
                        'rating' => (int) $review->rating,
                        'body' => $review->review,
                        'meta' => $review->student_class,
                        'date' => $review->updated_at,
                        'material_title' => null,
                        'material_url' => null,
                    ],
                ])->render(),
                'review_key' => 'profile-'.$review->id,
            ]);
        }

        return back()->with('status', $message);
    }

    /**
     * @return Collection<int, object>
     */
    private function profileReviewsFor(Educator $educator): Collection
    {
        $profileReviews = EducatorReview::query()
            ->where('educator_id', $educator->id)
            ->with('user:id,name')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (EducatorReview $review) => (object) [
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
            ]);

        $materialReviews = StudyMaterialReview::query()
            ->whereHas('studyMaterial', fn ($q) => $q->where('educator_id', $educator->id)->where('status', 'approved'))
            ->with(['user:id,name', 'studyMaterial:id,title,slug,educator_id'])
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (StudyMaterialReview $review) => (object) [
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
            ]);

        return $profileReviews
            ->concat($materialReviews)
            ->sortByDesc(fn ($item) => optional($item->sort_at)->timestamp ?? 0)
            ->values()
            ->take(24);
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\InstituteNewFollowerMail;
use App\Models\Institute;
use App\Models\InstituteCompareItem;
use App\Models\InstituteEngagement;
use App\Models\InstituteProfileFeedback;
use App\Services\PortalNotificationService;
use App\Support\SchoolInstituteHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InstituteEngagementController extends Controller
{
    private const COMPARE_LIMIT = 3;

    public function schoolFollow(Request $request, string $slug): JsonResponse
    {
        return $this->follow($request, $slug, 'school');
    }

    public function instituteFollow(Request $request, string $slug): JsonResponse
    {
        return $this->follow($request, $slug, 'institute');
    }

    public function schoolBookmark(Request $request, string $slug): JsonResponse
    {
        return $this->bookmark($request, $slug, 'school');
    }

    public function instituteBookmark(Request $request, string $slug): JsonResponse
    {
        return $this->bookmark($request, $slug, 'institute');
    }

    public function schoolCompare(Request $request, string $slug): JsonResponse
    {
        return $this->compare($request, $slug, 'school');
    }

    public function instituteCompare(Request $request, string $slug): JsonResponse
    {
        return $this->compare($request, $slug, 'institute');
    }

    public function schoolBrochure(Request $request, string $slug): JsonResponse
    {
        return $this->brochure($request, $slug, 'school');
    }

    public function instituteBrochure(Request $request, string $slug): JsonResponse
    {
        return $this->brochure($request, $slug, 'institute');
    }

    public function schoolComparePage(Request $request): View
    {
        return $this->comparePage($request, 'school');
    }

    public function instituteComparePage(Request $request): View
    {
        return $this->comparePage($request, 'institute');
    }

    public function schoolHelpful(Request $request, string $slug): JsonResponse
    {
        return $this->helpful($request, $slug, 'school');
    }

    public function instituteHelpful(Request $request, string $slug): JsonResponse
    {
        return $this->helpful($request, $slug, 'institute');
    }

    public function helpful(Request $request, string $slug, string $ownerRole): JsonResponse
    {
        $institute = $this->findProfile($slug, $ownerRole);
        $userId = (int) auth()->id();

        abort_if((int) $institute->user_id === $userId, 422, 'You cannot rate your own profile.');

        $validated = $request->validate([
            'vote' => ['required', 'in:yes,no'],
        ]);

        $vote = $validated['vote'];
        $existing = InstituteProfileFeedback::query()
            ->where('institute_id', $institute->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && $existing->vote === $vote) {
            return response()->json([
                'ok' => true,
                'message' => 'Thanks — your feedback is already recorded.',
                'vote' => $vote,
            ]);
        }

        InstituteProfileFeedback::query()->updateOrCreate(
            [
                'institute_id' => $institute->id,
                'user_id' => $userId,
            ],
            ['vote' => $vote]
        );

        $this->logEngagement(
            $institute,
            $userId,
            $vote === 'yes' ? InstituteEngagement::ACTION_HELPFUL_YES : InstituteEngagement::ACTION_HELPFUL_NO
        );

        return response()->json([
            'ok' => true,
            'message' => $vote === 'yes' ? 'Thanks for your feedback!' : 'Thanks — we will use this to improve listings.',
            'vote' => $vote,
        ]);
    }

    public function follow(Request $request, string $slug, string $ownerRole): JsonResponse
    {
        $institute = $this->findProfile($slug, $ownerRole);
        $userId = (int) auth()->id();

        abort_if((int) $institute->user_id === $userId, 422, 'You cannot follow your own profile.');

        $attached = $institute->followers()->where('user_id', $userId)->exists();
        if ($attached) {
            $institute->followers()->detach($userId);
            $following = false;
            $message = 'Unfollowed '.$institute->displayName().'.';
        } else {
            $institute->followers()->attach($userId);
            $following = true;
            $message = 'You are now following '.$institute->displayName().'.';

            $this->logEngagement($institute, $userId, InstituteEngagement::ACTION_FOLLOW);
            $this->notifyOwner(
                $institute,
                'New follower',
                (auth()->user()?->name ?: 'Someone').' started following your public profile.',
                $ownerRole
            );

            $recipient = $institute->email ?: $institute->user?->email;
            if ($recipient) {
                try {
                    Mail::to($recipient)->send(InstituteNewFollowerMail::forFollow($institute, auth()->user()));
                } catch (\Throwable $e) {
                    Log::error('Failed to send institute follower mail', [
                        'institute_id' => $institute->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'following' => $following,
            'followers_count' => $institute->followers()->count(),
        ]);
    }

    public function bookmark(Request $request, string $slug, string $ownerRole): JsonResponse
    {
        $institute = $this->findProfile($slug, $ownerRole);
        $userId = (int) auth()->id();

        abort_if((int) $institute->user_id === $userId, 422, 'You cannot bookmark your own profile.');

        $attached = $institute->bookmarks()->where('user_id', $userId)->exists();
        if ($attached) {
            $institute->bookmarks()->detach($userId);
            $bookmarked = false;
            $message = 'Removed bookmark.';
        } else {
            $institute->bookmarks()->attach($userId);
            $bookmarked = true;
            $message = 'School saved to your bookmarks.';

            $this->logEngagement($institute, $userId, InstituteEngagement::ACTION_BOOKMARK);
            $this->notifyOwner(
                $institute,
                'Profile bookmarked',
                (auth()->user()?->name ?: 'Someone').' bookmarked your public profile.',
                $ownerRole
            );
        }

        return response()->json([
            'ok' => true,
            'message' => $message,
            'bookmarked' => $bookmarked,
        ]);
    }

    public function compare(Request $request, string $slug, string $ownerRole): JsonResponse
    {
        $institute = $this->findProfile($slug, $ownerRole);
        $userId = (int) auth()->id();

        $existing = InstituteCompareItem::query()
            ->where('user_id', $userId)
            ->where('institute_id', $institute->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $inCompare = false;
            $message = 'Removed from compare list.';
        } else {
            $count = InstituteCompareItem::query()->where('user_id', $userId)->count();
            if ($count >= self::COMPARE_LIMIT) {
                return response()->json([
                    'ok' => false,
                    'message' => 'You can compare up to '.self::COMPARE_LIMIT.' schools at a time. Remove one from your compare list first.',
                ], 422);
            }

            InstituteCompareItem::query()->create([
                'user_id' => $userId,
                'institute_id' => $institute->id,
                'sort_order' => $count + 1,
            ]);
            $inCompare = true;
            $message = 'Added to compare list.';

            $this->logEngagement($institute, $userId, InstituteEngagement::ACTION_COMPARE_ADD);
        }

        $compareUrl = $ownerRole === 'school'
            ? route('schools.compare')
            : route('institutes.compare');

        return response()->json([
            'ok' => true,
            'message' => $message,
            'in_compare' => $inCompare,
            'compare_count' => InstituteCompareItem::query()->where('user_id', $userId)->count(),
            'compare_url' => $compareUrl,
        ]);
    }

    public function brochure(Request $request, string $slug, string $ownerRole): JsonResponse
    {
        $institute = $this->findProfile($slug, $ownerRole);
        $userId = auth()->id();

        if (! filled($institute->brochure_path)) {
            return response()->json([
                'ok' => false,
                'message' => 'Brochure is not available yet. Please send an enquiry to request one.',
            ], 422);
        }

        $this->logEngagement($institute, $userId, InstituteEngagement::ACTION_BROCHURE_DOWNLOAD);
        $this->notifyOwner(
            $institute,
            'Brochure downloaded',
            (auth()->user()?->name ?: 'Someone').' downloaded your profile brochure.',
            $ownerRole
        );

        return response()->json([
            'ok' => true,
            'message' => 'Starting download…',
            'download_url' => asset($institute->brochure_path),
        ]);
    }

    public function comparePage(Request $request, string $ownerRole): View
    {
        $items = InstituteCompareItem::query()
            ->where('user_id', auth()->id())
            ->with(['institute.user:id,role'])
            ->orderBy('sort_order')
            ->get()
            ->filter(function (InstituteCompareItem $item) use ($ownerRole) {
                $institute = $item->institute;
                if (! $institute || ! $institute->isApproved()) {
                    return false;
                }

                return $ownerRole === 'school'
                    ? $institute->user?->isSchool()
                    : $institute->user?->isInstitute();
            })
            ->values();

        return view('frontend.institutes.compare', [
            'items' => $items,
            'ownerRole' => $ownerRole,
            'listingContext' => $ownerRole === 'school' ? 'schools' : 'institutes',
        ]);
    }

    private function findProfile(string $slug, string $ownerRole): Institute
    {
        return Institute::query()
            ->approved()
            ->forOwnerRole($ownerRole)
            ->where('slug', $slug)
            ->with('user:id,name,email,role')
            ->firstOrFail();
    }

    private function logEngagement(Institute $institute, ?int $userId, string $action): void
    {
        InstituteEngagement::query()->create([
            'institute_id' => $institute->id,
            'user_id' => $userId,
            'action' => $action,
        ]);
    }

    private function notifyOwner(Institute $institute, string $title, string $message, string $ownerRole): void
    {
        $owner = $institute->user;
        if (! $owner) {
            return;
        }

        $url = $owner->portalRoute('engagement.index')
            ?? SchoolInstituteHelper::routeForPrefix($ownerRole, 'engagement.index');

        PortalNotificationService::notifyUser($owner, $title, $message, $url, 'engagement');
    }
}

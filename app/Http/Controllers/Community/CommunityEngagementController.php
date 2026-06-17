<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityCategorySubscription;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Models\CommunityPostSave;
use App\Models\CommunityTopicFollow;
use App\Services\CommunityReportTrustScoreService;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityEngagementController extends Controller
{
    public function toggleSave(Request $request, CommunityPost $post): JsonResponse|RedirectResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);

        $userId = $request->user()->id;
        $existing = CommunityPostSave::query()
            ->where('user_id', $userId)
            ->where('community_post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved = false;
            $message = 'Post removed from your saved list.';
        } else {
            CommunityPostSave::query()->create([
                'user_id' => $userId,
                'community_post_id' => $post->id,
            ]);
            $saved = true;
            $message = 'Post saved successfully.';
        }

        if ($post->isReportContent()) {
            CommunityReportTrustScoreService::syncToMeta($post->fresh());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'saved' => $saved,
            ]);
        }

        return back()->with('success', $message);
    }

    public function trackShare(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);

        $post->increment('shares_count');

        return response()->json([
            'message' => 'Share recorded.',
            'shares_count' => (int) $post->fresh()->shares_count,
        ]);
    }

    public function savedPosts(Request $request): View
    {
        return view('backend.community-saved.index');
    }

    public function savedPostsData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = CommunityPostSave::query()
            ->where('user_id', $request->user()->id)
            ->with(['post:id,slug,title,content_type,category,status,published_at'])
            ->latest();

        return DataTables::of($query)
            ->addColumn('title', fn (CommunityPostSave $save): string => e($save->post?->title ?? 'Deleted post'))
            ->addColumn('type_label', fn (CommunityPostSave $save): string => e($save->post?->typeLabel() ?? '—'))
            ->addColumn('category_display', fn (CommunityPostSave $save): string => e($save->post?->category ?? '—'))
            ->addColumn('published_display', function (CommunityPostSave $save): string {
                $publishedAt = $save->post?->published_at;

                return $publishedAt
                    ? $publishedAt->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '—';
            })
            ->addColumn('saved_display', fn (CommunityPostSave $save): string => $save->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '—')
            ->addColumn('actions', function (CommunityPostSave $save): string {
                if (! $save->post) {
                    return '<span class="text-muted">Unavailable</span>';
                }

                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('community.show', $save->post).'" class="btn btn-sm btn-outline-primary" title="View"><i class="fa-solid fa-eye"></i></a>'
                    .'<button type="button" class="btn btn-sm btn-outline-danger js-unsave-post" data-slug="'.e($save->post->slug).'" title="Remove"><i class="fa-solid fa-bookmark-slash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function report(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_if((int) $request->user()->id === (int) $post->user_id, 403, 'You cannot report your own post.');

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        CommunityPostReport::query()->create([
            'community_post_id' => $post->id,
            'reported_by' => $request->user()->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Post reported successfully.',
        ]);
    }

    public function toggleCategorySubscription(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'content_type' => ['required', 'string', 'max:80'],
            'category' => ['required', 'string', 'max:120'],
        ]);

        abort_unless(
            CommunityContentTaxonomy::isValidCategory($validated['content_type'], $validated['category']),
            422,
            'Please choose a valid category.'
        );

        $existing = CommunityCategorySubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('content_type', $validated['content_type'])
            ->where('category', $validated['category'])
            ->first();

        if ($existing) {
            $existing->delete();
            $subscribed = false;
            $message = 'Category subscription removed.';
        } else {
            CommunityCategorySubscription::query()->create([
                'user_id' => $request->user()->id,
                'content_type' => $validated['content_type'],
                'category' => $validated['category'],
            ]);
            $subscribed = true;
            $message = 'You are now subscribed to this category.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'subscribed' => $subscribed,
            ]);
        }

        return back()->with('success', $message);
    }

    public function toggleTopicFollow(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:120'],
        ]);

        $topic = CommunityTopicFollow::normalizeTopic($validated['topic']);
        abort_if($topic === '', 422, 'Please enter a valid topic.');

        $existing = CommunityTopicFollow::query()
            ->where('user_id', $request->user()->id)
            ->where('topic', $topic)
            ->first();

        if ($existing) {
            $existing->delete();
            $following = false;
            $message = 'Topic unfollowed.';
        } else {
            CommunityTopicFollow::query()->create([
                'user_id' => $request->user()->id,
                'topic' => $topic,
            ]);
            $following = true;
            $message = 'You are now following this topic.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'following' => $following,
                'topic' => $topic,
            ]);
        }

        return back()->with('success', $message);
    }

    public function subscriptions(Request $request): View
    {
        $userId = $request->user()->id;

        return view('backend.community-subscriptions.index', [
            'categorySubscriptions' => CommunityCategorySubscription::query()
                ->where('user_id', $userId)
                ->latest()
                ->get(),
            'topicFollows' => CommunityTopicFollow::query()
                ->where('user_id', $userId)
                ->latest()
                ->get(),
            'types' => CommunityContentTaxonomy::formTypes(),
        ]);
    }

    /**
     * @return array{
     *     saved_post_ids: list<int>,
     *     subscribed_categories: list<array{content_type: string, category: string}>,
     *     followed_topics: list<string>
     * }
     */
    public static function engagementStateForUser(?int $userId): array
    {
        if ($userId === null) {
            return [
                'saved_post_ids' => [],
                'subscribed_categories' => [],
                'followed_topics' => [],
            ];
        }

        return [
            'saved_post_ids' => CommunityPostSave::query()
                ->where('user_id', $userId)
                ->pluck('community_post_id')
                ->all(),
            'subscribed_categories' => CommunityCategorySubscription::query()
                ->where('user_id', $userId)
                ->get(['content_type', 'category'])
                ->map(fn (CommunityCategorySubscription $subscription): array => [
                    'content_type' => $subscription->content_type,
                    'category' => $subscription->category,
                ])
                ->all(),
            'followed_topics' => CommunityTopicFollow::query()
                ->where('user_id', $userId)
                ->pluck('topic')
                ->all(),
        ];
    }

    public static function applySubscriptionPriority($query, ?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        $subscriptions = CommunityCategorySubscription::query()
            ->where('user_id', $userId)
            ->get(['content_type', 'category']);

        $topics = CommunityTopicFollow::query()
            ->where('user_id', $userId)
            ->pluck('topic');

        if ($subscriptions->isEmpty() && $topics->isEmpty()) {
            return;
        }

        $parts = [];
        $bindings = [];

        foreach ($subscriptions as $subscription) {
            $parts[] = '(content_type = ? AND category = ?)';
            $bindings[] = $subscription->content_type;
            $bindings[] = $subscription->category;
        }

        foreach ($topics as $topic) {
            $parts[] = 'JSON_CONTAINS(COALESCE(tags, JSON_ARRAY()), ?)';
            $bindings[] = json_encode($topic, JSON_THROW_ON_ERROR);
        }

        $query->orderByRaw('CASE WHEN '.implode(' OR ', $parts).' THEN 1 ELSE 0 END DESC', $bindings);
    }
}

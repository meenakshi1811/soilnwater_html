<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Community\CommunityEngagementController;
use App\Http\Controllers\Community\CommunityPostController;
use App\Models\CommunityAuthorQuestion;
use App\Mail\CommunityPostReviewMail;
use App\Models\CommunityPost;
use App\Services\CommunityArticleScoreService;
use App\Services\CommunityReportTrustScoreService;
use App\Services\CommunityEngagementNotificationService;
use App\Services\CommunityReportEngagementNotificationService;
use App\Services\CommunitySeniorCitizensForumEngagementNotificationService;
use App\Services\CommunityStudentCornerEngagementNotificationService;
use App\Services\CommunityYouthCornerEngagementNotificationService;
use App\Services\CommunityWomensWorldEngagementNotificationService;
use App\Services\CommunityAgricultureEngagementNotificationService;
use App\Services\CommunityAstroConsultancyEngagementNotificationService;
use App\Services\CommunityCreativeCornerEngagementNotificationService;
use App\Services\CommunityReligionSpiritualityEngagementNotificationService;
use App\Services\CommunityEnvironmentEngagementNotificationService;
use App\Services\PortalNotificationService;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommunityPostApprovalController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.community-posts.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = CommunityPost::query()
            ->with('user:id,name,full_name')
            ->pendingApproval()
            ->select([
                'id',
                'slug',
                'user_id',
                'content_type',
                'category',
                'title',
                'status',
                'submitted_at',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('type_label', fn (CommunityPost $post): string => e($post->typeLabel()))
            ->addColumn('owner_name', fn (CommunityPost $post): string => e($post->user?->full_name ?: ($post->user?->name ?? 'Unknown user')))
            ->addColumn('status_badge', fn (CommunityPost $post): string => '<span class="badge '.$post->statusBadgeClass().'">'.e($post->statusLabel()).'</span>')
            ->addColumn('actions', fn (CommunityPost $post): string => $this->renderActionButtons($post, true))
            ->editColumn('submitted_at', function (CommunityPost $post): string {
                $timestamp = $post->submitted_at ?: $post->created_at;

                return $timestamp
                    ? $timestamp->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '-';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(CommunityPost $post): View
    {
        $post->load([
            'user',
            'reviewer:id,name,full_name',
            'auditLogs.user:id,name,full_name',
            'discussionComments.user:id,name,full_name',
            'authorQuestions.asker:id,name,full_name',
        ]);
        $post->loadCount([
            'reactions as likes_count' => fn ($query) => $query->where('reaction', '!=', 'Dislike'),
            'comments',
            'saves',
            'reportSupports',
            'reportAgreements',
            'reportFollows',
            'reportEvidence',
            'awarenessSupports',
            'awarenessPledges',
            'awarenessVolunteers',
            'authorQuestions',
            'starRatings',
        ]);
        $post->load(['starRatings']);

        return view('backend.admin.community-posts.show', array_merge([
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'scoreMetrics' => CommunityArticleScoreService::metricSummary($post),
            'scoreBreakdown' => CommunityArticleScoreService::breakdown($post),
            'reportEngagementActivity' => $post->supportsCivicEngagement()
                ? [
                    'supports' => $post->reportSupports()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                    'agreements' => $post->reportAgreements()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                    'follows' => $post->reportFollows()->with('user:id,name,full_name')->latest()->limit(10)->get(),
                ]
                : null,
        ], app(CommunityPostController::class)->participationViewData($post, 20)));
    }

    public function preview(CommunityPost $post): View
    {
        abort_unless($post->isPendingApproval() || auth()->user()?->isAdmin(), 404);

        $post->load([
            'user',
            'reactions',
            'pollVotes',
            'starRatings',
            'discussionComments.user',
            'discussionComments.replies.user',
        ])->loadCount('starRatings');

        return view('community.show', array_merge([
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'preview' => true,
            'answeredAuthorQuestions' => $post->user_id
                ? CommunityAuthorQuestion::query()
                    ->where('community_post_id', $post->id)
                    ->whereNotNull('answered_at')
                    ->with(['asker:id,name,full_name'])
                    ->latest()
                    ->limit(10)
                    ->get()
                : collect(),
            'engagement' => CommunityEngagementController::engagementStateForUser(auth()->id()),
        ], app(CommunityPostController::class)->participationViewData($post)));
    }

    public function approve(Request $request, CommunityPost $post): JsonResponse
    {
        abort_if($post->isArchived(), 422, 'Archived posts must be restored before approval.');

        $post->update([
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => $post->published_at ?? now(),
            'submitted_at' => $post->isPendingApproval() ? ($post->submitted_at ?? now()) : null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_note' => null,
        ]);

        if ($post->wasChanged('status')) {
            $post = $post->fresh('user');
            $this->notifyOwnerOfReview($post, 'approved');
            CommunityEngagementNotificationService::notifySubscribersOfPublishedPost($post);
            if ($post->isWomensWorldPost()) {
                CommunityWomensWorldEngagementNotificationService::notifyAuthorOfPublishedPost($post);
            }
            if ($post->isSeniorCitizensForumPost()) {
                CommunitySeniorCitizensForumEngagementNotificationService::notifyAuthorOfPublishedPost($post);
            }
            if ($post->isStudentCornerPost()) {
                CommunityStudentCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post);
            }
            if ($post->isYouthCornerPost()) {
                CommunityYouthCornerEngagementNotificationService::notifyAuthorOfPublishedPost($post);
            }
            if ($post->isAgriculturePost()) {
                CommunityAgricultureEngagementNotificationService::notifyOnPublishedPost($post);
            }
            if ($post->isEnvironmentPost()) {
                CommunityEnvironmentEngagementNotificationService::notifyOnPublishedPost($post);
            }
            if ($post->isAstroConsultancyPost()) {
                CommunityAstroConsultancyEngagementNotificationService::notifyOnPublishedPost($post);
            }
            if ($post->isReligionSpiritualityPost()) {
                CommunityReligionSpiritualityEngagementNotificationService::notifyOnPublishedPost($post);
            }
            if ($post->isCreativeCornerPost()) {
                CommunityCreativeCornerEngagementNotificationService::notifyOnPublishedPost($post);
            }
            CommunityReportEngagementNotificationService::notifyFollowersOfReportUpdate(
                $post,
                $post->isMyAreaPost()
                    ? 'This My Area post has been reviewed and published.'
                    : 'This report has been reviewed and published.'
            );
            CommunityArticleScoreService::recalculate($post);
            CommunityReportTrustScoreService::syncToMeta($post);
        }

        return response()->json(['message' => 'Community post approved and published.']);
    }

    public function decline(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->reject($request, $post);
    }

    public function reject(Request $request, CommunityPost $post): JsonResponse
    {
        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $wasPending = $post->isPendingApproval();

        $post->update([
            'status' => CommunityPost::STATUS_DECLINED,
            'published_at' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_note' => $data['review_note'] ?? 'Rejected by admin.',
        ]);

        if ($wasPending || $post->wasChanged('status')) {
            $this->notifyOwnerOfReview($post->fresh('user'), 'declined');
        }

        return response()->json(['message' => 'Community post rejected.']);
    }

    public function moveToDraft(Request $request, CommunityPost $post): JsonResponse
    {
        $post->update([
            'status' => CommunityPost::STATUS_DRAFT,
            'published_at' => null,
            'submitted_at' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
            'review_note' => null,
        ]);

        return response()->json(['message' => 'Community post moved to draft.']);
    }

    public function archive(Request $request, CommunityPost $post): JsonResponse
    {
        $post->update([
            'status' => CommunityPost::STATUS_ARCHIVED,
            'published_at' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Community post archived.']);
    }

    public function feature(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->togglePromotionFlag($request, $post, 'is_featured', 'Featured');
    }

    public function sponsor(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->togglePromotionFlag($request, $post, 'is_sponsored', 'Sponsored');
    }

    public function highlight(Request $request, CommunityPost $post): JsonResponse
    {
        return $this->togglePromotionFlag($request, $post, 'is_highlighted', 'Highlighted');
    }

    public function updateQualityScore(Request $request, CommunityPost $post): JsonResponse
    {
        $data = $request->validate([
            'quality_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $post->update([
            'quality_score' => $data['quality_score'] ?? null,
        ]);

        $post = CommunityArticleScoreService::recalculate($post->fresh(), autoAssignBadges: false);

        return response()->json([
            'message' => 'Quality score updated and article score recalculated.',
            'article_score' => (float) $post->article_score,
            'quality_score' => $post->quality_score,
        ]);
    }

    public function recalculateScore(Request $request, CommunityPost $post): JsonResponse
    {
        $autoAssign = $request->boolean('auto_assign_badges', true);
        $post = CommunityArticleScoreService::recalculate($post->fresh(), autoAssignBadges: $autoAssign);

        return response()->json([
            'message' => 'Article score recalculated successfully.',
            'article_score' => (float) $post->article_score,
            'badges' => $post->articleScoreBadgeLabels(),
            'metrics' => CommunityArticleScoreService::metricSummary($post),
        ]);
    }

    public function toggleArticleBadge(Request $request, CommunityPost $post): JsonResponse
    {
        $data = $request->validate([
            'badge' => ['required', 'in:badge_trending,badge_editors_choice,badge_most_read,badge_community_pick,is_featured'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $field = $data['badge'];
        $enabled = array_key_exists('enabled', $data)
            ? (bool) $data['enabled']
            : ! (bool) $post->{$field};

        $post->update([$field => $enabled]);

        $label = CommunityArticleScoreService::BADGE_LABELS[$field] ?? Str::headline($field);

        return response()->json([
            'message' => $enabled ? "{$label} badge enabled." : "{$label} badge removed.",
            'enabled' => $enabled,
            'badge' => $field,
        ]);
    }

    public function allIndex(): View
    {
        return view('backend.admin.community-posts.all-posts');
    }

    public function allData(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $status = $request->string('status')->toString();

        $query = CommunityPost::query()
            ->with('user:id,name,full_name,role')
            ->select([
                'id',
                'slug',
                'user_id',
                'content_type',
                'category',
                'meta',
                'title',
                'status',
                'is_featured',
                'is_sponsored',
                'is_highlighted',
                'article_score',
                'views_count',
                'published_at',
                'submitted_at',
                'created_at',
            ]);

        if (in_array($status, [
            CommunityPost::STATUS_DRAFT,
            CommunityPost::STATUS_PENDING,
            CommunityPost::STATUS_PUBLISHED,
            CommunityPost::STATUS_DECLINED,
            CommunityPost::STATUS_ARCHIVED,
        ], true)) {
            $query->where('status', $status);
        }

        return DataTables::of($query)
            ->addColumn('type_label', fn (CommunityPost $post): string => e($post->typeLabel()))
            ->addColumn('category_display', fn (CommunityPost $post): string => e($post->listingCategoryLabel()))
            ->addColumn('owner_name', fn (CommunityPost $post): string => e($post->user?->full_name ?: ($post->user?->name ?? 'Unknown user')))
            ->addColumn('owner_role', fn (CommunityPost $post): string => '<span class="badge bg-light text-dark border">'.e($this->roleLabel($post->user?->role)).'</span>')
            ->addColumn('status_badge', fn (CommunityPost $post): string => '<span class="badge '.$post->statusBadgeClass().'">'.e($post->statusLabel()).'</span>')
            ->addColumn('promotion_badges', fn (CommunityPost $post): string => $this->renderPromotionBadges($post))
            ->addColumn('article_score_display', fn (CommunityPost $post): string => number_format((float) $post->article_score, 1))
            ->addColumn('trust_score_display', fn (CommunityPost $post): string => CommunityReportTrustScoreService::badgeHtml($post))
            ->addColumn('published_display', function (CommunityPost $post): string {
                if ($post->published_at) {
                    return $post->published_at->timezone(config('app.timezone'))->format('d M Y, h:i A');
                }

                if ($post->submitted_at) {
                    return 'Submitted '.$post->submitted_at->timezone(config('app.timezone'))->format('d M Y, h:i A');
                }

                return '—';
            })
            ->addColumn('actions', fn (CommunityPost $post): string => $this->renderActionButtons($post, false))
            ->rawColumns(['owner_role', 'status_badge', 'promotion_badges', 'trust_score_display', 'actions'])
            ->make(true);
    }

    private function togglePromotionFlag(Request $request, CommunityPost $post, string $field, string $label): JsonResponse
    {
        abort_unless(in_array($field, ['is_featured', 'is_sponsored', 'is_highlighted'], true), 500);

        $enabled = $request->has('enabled')
            ? $request->boolean('enabled')
            : ! (bool) $post->{$field};

        $post->update([$field => $enabled]);

        return response()->json([
            'message' => $enabled
                ? "Community post marked as {$label}."
                : "{$label} flag removed from community post.",
            'enabled' => $enabled,
            'field' => $field,
        ]);
    }

    private function renderPromotionBadges(CommunityPost $post): string
    {
        $badges = [];

        if ($post->is_sponsored) {
            $badges[] = '<span class="badge bg-info text-dark">Sponsored</span>';
        }

        if ($post->is_highlighted) {
            $badges[] = '<span class="badge bg-warning text-dark">Highlighted</span>';
        }

        foreach ($post->articleScoreBadges() as $badge) {
            $badges[] = '<span class="badge bg-light text-dark border">'.e($badge['label']).'</span>';
        }

        return $badges === [] ? '<span class="text-muted">—</span>' : implode(' ', $badges);
    }

    private function renderActionButtons(CommunityPost $post, bool $compact): string
    {
        $actions = '<div class="d-flex gap-2 justify-content-end flex-wrap">'
            .'<a href="'.route('admin.community-posts.show', $post).'" class="btn btn-sm btn-outline-secondary">'.($compact ? 'Review' : 'Manage').'</a>';

        if ($post->isPendingApproval()) {
            $actions .= '<button type="button" class="btn btn-sm btn-success js-approve" data-slug="'.e($post->slug).'">Approve</button>'
                .'<button type="button" class="btn btn-sm btn-outline-danger js-reject" data-slug="'.e($post->slug).'">Reject</button>';
        }

        if (! $compact && ! $post->isArchived()) {
            if ($post->status !== CommunityPost::STATUS_DRAFT) {
                $actions .= '<button type="button" class="btn btn-sm btn-outline-secondary js-draft" data-slug="'.e($post->slug).'">Draft</button>';
            }

            if ($post->isPubliclyVisible() || $post->status === CommunityPost::STATUS_DECLINED) {
                $actions .= '<button type="button" class="btn btn-sm btn-outline-dark js-archive" data-slug="'.e($post->slug).'">Archive</button>';
            }
        }

        return $actions.'</div>';
    }

    private function roleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'employee' => 'Employee',
            'vendor' => 'Vendor',
            'consultant' => 'Consultant',
            'service_provider' => 'Service Provider',
            'user' => 'User',
            default => ucfirst(str_replace('_', ' ', (string) $role ?: 'user')),
        };
    }

    private function notifyOwnerOfReview(CommunityPost $post, string $status): void
    {
        $post->loadMissing('user');

        PortalNotificationService::notifyOwnerOfReview(
            $post->user,
            'Community post',
            $post->title,
            $status,
            route('community.posts.show', $post)
        );

        $recipient = $post->user?->email;
        if (! $recipient) {
            return;
        }

        Mail::to($recipient)->send(CommunityPostReviewMail::forPost($post, $status));
    }
}

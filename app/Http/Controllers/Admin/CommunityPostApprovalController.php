<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CommunityPostReviewMail;
use App\Models\CommunityPost;
use App\Services\PortalNotificationService;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
        $post->load(['user', 'reviewer:id,name,full_name']);

        return view('backend.admin.community-posts.show', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
        ]);
    }

    public function preview(CommunityPost $post): View
    {
        abort_unless($post->isPendingApproval() || auth()->user()?->isAdmin(), 404);

        $post->load([
            'user',
            'reactions',
            'pollVotes',
            'discussionComments.user',
            'discussionComments.replies.user',
        ]);

        return view('community.show', [
            'post' => $post,
            'types' => CommunityContentTaxonomy::formTypes(),
            'preview' => true,
        ]);
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
            $this->notifyOwnerOfReview($post->fresh('user'), 'approved');
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
            ->addColumn('category_display', fn (CommunityPost $post): string => e(
                filled(data_get($post->meta, 'report_type'))
                    ? data_get($post->meta, 'report_type', $post->category)
                    : $post->category
            ))
            ->addColumn('owner_name', fn (CommunityPost $post): string => e($post->user?->full_name ?: ($post->user?->name ?? 'Unknown user')))
            ->addColumn('owner_role', fn (CommunityPost $post): string => '<span class="badge bg-light text-dark border">'.e($this->roleLabel($post->user?->role)).'</span>')
            ->addColumn('status_badge', fn (CommunityPost $post): string => '<span class="badge '.$post->statusBadgeClass().'">'.e($post->statusLabel()).'</span>')
            ->addColumn('promotion_badges', fn (CommunityPost $post): string => $this->renderPromotionBadges($post))
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
            ->rawColumns(['owner_role', 'status_badge', 'promotion_badges', 'actions'])
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

        if ($post->is_featured) {
            $badges[] = '<span class="badge bg-primary">Featured</span>';
        }

        if ($post->is_sponsored) {
            $badges[] = '<span class="badge bg-info text-dark">Sponsored</span>';
        }

        if ($post->is_highlighted) {
            $badges[] = '<span class="badge bg-warning text-dark">Highlighted</span>';
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumPaymentSubmission;
use App\Services\PremiumPaymentReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PremiumPaymentSubmissionController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.premium-payments.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = PremiumPaymentSubmission::query()
            ->with(['user:id,name,full_name,email', 'reviewer:id,name'])
            ->select([
                'id',
                'user_id',
                'profile_type',
                'profile_id',
                'expected_amount',
                'transaction_reference',
                'status',
                'submitted_at',
                'reviewed_at',
                'reviewed_by',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('profile_type_label', function (PremiumPaymentSubmission $submission): string {
                $icon = match ($submission->profile_type) {
                    'vendor' => 'fa-store',
                    'consultant' => 'fa-user-tie',
                    'service' => 'fa-screwdriver-wrench',
                    default => 'fa-building',
                };

                return '<span class="badge text-bg-light border">'
                    .'<i class="fa-solid '.$icon.' me-1"></i>'
                    .e($submission->profileTypeLabel())
                    .'</span>';
            })
            ->addColumn('expected_amount_display', function (PremiumPaymentSubmission $submission): string {
                $amount = $submission->expected_amount ?? \App\Models\PremiumPrice::amountFor($submission->profile_type);

                return '<span class="fw-semibold">'.e(\App\Models\PremiumPrice::formatAmount($amount)).'</span>';
            })
            ->addColumn('profile_name', fn (PremiumPaymentSubmission $submission): string => e($submission->profileDisplayName()))
            ->addColumn('user_name', fn (PremiumPaymentSubmission $submission): string => e($submission->user?->full_name ?: ($submission->user?->name ?? '—')))
            ->addColumn('user_email', fn (PremiumPaymentSubmission $submission): string => e($submission->user?->email ?? '—'))
            ->addColumn('transaction_reference_display', fn (PremiumPaymentSubmission $submission): string => e($submission->transaction_reference ?: '—'))
            ->addColumn('status_badge', function (PremiumPaymentSubmission $submission): string {
                $badge = match ($submission->status) {
                    PremiumPaymentSubmission::STATUS_APPROVED => 'success',
                    PremiumPaymentSubmission::STATUS_REJECTED => 'danger',
                    default => 'warning',
                };
                $label = ucfirst($submission->status);

                return '<span class="badge text-bg-'.$badge.'">'.$label.'</span>';
            })
            ->editColumn('submitted_at', function (PremiumPaymentSubmission $submission): string {
                $timestamp = $submission->submitted_at ?: $submission->created_at;

                return $timestamp
                    ? $timestamp->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '—';
            })
            ->addColumn('reviewed_display', function (PremiumPaymentSubmission $submission): string {
                if (! $submission->reviewed_at) {
                    return '<span class="text-muted">—</span>';
                }

                $reviewer = $submission->reviewer?->name;
                $when = $submission->reviewed_at->timezone(config('app.timezone'))->format('d M Y, h:i A');

                return '<div class="small">'.$when.($reviewer ? '<br><span class="text-muted">by '.e($reviewer).'</span>' : '').'</div>';
            })
            ->addColumn('actions', function (PremiumPaymentSubmission $submission): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.premium-payments.show', $submission).'" class="btn btn-sm btn-outline-primary" title="View payment">'
                    .'<i class="fa-solid fa-eye"></i> View'
                    .'</a>'
                    .'</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'approve')) {
                    $query->where('status', PremiumPaymentSubmission::STATUS_APPROVED);
                } elseif (str_contains($k, 'reject') || str_contains($k, 'declin')) {
                    $query->where('status', PremiumPaymentSubmission::STATUS_REJECTED);
                } elseif (str_contains($k, 'pending')) {
                    $query->where('status', PremiumPaymentSubmission::STATUS_PENDING);
                }
            })
            ->rawColumns(['profile_type_label', 'expected_amount_display', 'status_badge', 'reviewed_display', 'actions'])
            ->make(true);
    }

    public function show(PremiumPaymentSubmission $submission): View
    {
        $submission->load(['user', 'reviewer']);

        return view('backend.admin.premium-payments.show', [
            'submission' => $submission,
        ]);
    }

    public function approve(Request $request, PremiumPaymentSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNote = $validated['admin_note'] ?? $validated['review_note'] ?? null;

        try {
            PremiumPaymentReviewService::approve($submission, $request->user(), $adminNote);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment verified and profile marked as premium.',
        ]);
    }

    public function reject(Request $request, PremiumPaymentSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNote = $validated['admin_note'] ?? $validated['review_note'] ?? null;

        try {
            PremiumPaymentReviewService::reject($submission, $request->user(), $adminNote);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment proof declined.',
        ]);
    }
}

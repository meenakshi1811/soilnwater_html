<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ListingPaymentSubmission;
use App\Services\ListingPaymentReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ListingPaymentSubmissionController extends Controller
{
    public function index(): View
    {
        return view('backend.admin.listing-payments.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->ajax(), 404);

        $query = ListingPaymentSubmission::query()
            ->with(['user:id,name,full_name,email', 'reviewer:id,name'])
            ->select([
                'id',
                'user_id',
                'listing_type',
                'listing_id',
                'amount',
                'transaction_reference',
                'status',
                'submitted_at',
                'reviewed_at',
                'reviewed_by',
                'created_at',
            ]);

        return DataTables::of($query)
            ->addColumn('listing_type_label', function (ListingPaymentSubmission $submission): string {
                $icon = $submission->listing_type === ListingPaymentSubmission::TYPE_AD ? 'fa-rectangle-ad' : 'fa-tags';

                return '<span class="badge text-bg-light border">'
                    .'<i class="fa-solid '.$icon.' me-1"></i>'
                    .e($submission->listingTypeLabel())
                    .'</span>';
            })
            ->addColumn('listing_name', fn (ListingPaymentSubmission $submission): string => e($submission->listingDisplayName()))
            ->addColumn('user_name', fn (ListingPaymentSubmission $submission): string => e($submission->user?->full_name ?: ($submission->user?->name ?? '—')))
            ->addColumn('user_email', fn (ListingPaymentSubmission $submission): string => e($submission->user?->email ?? '—'))
            ->addColumn('amount_display', fn (ListingPaymentSubmission $submission): string => $submission->amount ? '₹'.number_format((float) $submission->amount, 2) : '—')
            ->addColumn('transaction_reference_display', fn (ListingPaymentSubmission $submission): string => e($submission->transaction_reference ?: '—'))
            ->addColumn('status_badge', function (ListingPaymentSubmission $submission): string {
                $badge = match ($submission->status) {
                    ListingPaymentSubmission::STATUS_APPROVED => 'success',
                    ListingPaymentSubmission::STATUS_REJECTED => 'danger',
                    default => 'warning',
                };

                return '<span class="badge text-bg-'.$badge.'">'.ucfirst($submission->status).'</span>';
            })
            ->editColumn('submitted_at', function (ListingPaymentSubmission $submission): string {
                $timestamp = $submission->submitted_at ?: $submission->created_at;

                return $timestamp
                    ? $timestamp->timezone(config('app.timezone'))->format('d M Y, h:i A')
                    : '—';
            })
            ->addColumn('reviewed_display', function (ListingPaymentSubmission $submission): string {
                if (! $submission->reviewed_at) {
                    return '<span class="text-muted">—</span>';
                }

                $reviewer = $submission->reviewer?->name;
                $when = $submission->reviewed_at->timezone(config('app.timezone'))->format('d M Y, h:i A');

                return '<div class="small">'.$when.($reviewer ? '<br><span class="text-muted">by '.e($reviewer).'</span>' : '').'</div>';
            })
            ->addColumn('actions', function (ListingPaymentSubmission $submission): string {
                return '<div class="d-flex gap-2 justify-content-end">'
                    .'<a href="'.route('admin.listing-payments.show', $submission).'" class="btn btn-sm btn-outline-primary" title="View payment">'
                    .'<i class="fa-solid fa-eye"></i> View'
                    .'</a>'
                    .'</div>';
            })
            ->filterColumn('status_badge', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'approve')) {
                    $query->where('status', ListingPaymentSubmission::STATUS_APPROVED);
                } elseif (str_contains($k, 'reject') || str_contains($k, 'declin')) {
                    $query->where('status', ListingPaymentSubmission::STATUS_REJECTED);
                } elseif (str_contains($k, 'pending')) {
                    $query->where('status', ListingPaymentSubmission::STATUS_PENDING);
                }
            })
            ->filterColumn('listing_type_label', function ($query, $keyword): void {
                $k = strtolower((string) $keyword);
                if (str_contains($k, 'ad')) {
                    $query->where('listing_type', ListingPaymentSubmission::TYPE_AD);
                } elseif (str_contains($k, 'offer')) {
                    $query->where('listing_type', ListingPaymentSubmission::TYPE_OFFER);
                }
            })
            ->rawColumns(['listing_type_label', 'status_badge', 'reviewed_display', 'actions'])
            ->make(true);
    }

    public function show(ListingPaymentSubmission $submission): View
    {
        $submission->load(['user', 'reviewer']);

        return view('backend.admin.listing-payments.show', [
            'submission' => $submission,
        ]);
    }

    public function approve(Request $request, ListingPaymentSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNote = $validated['admin_note'] ?? $validated['review_note'] ?? null;

        try {
            ListingPaymentReviewService::approve($submission, $request->user(), $adminNote);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment verified and the '.strtolower($submission->listingTypeLabel()).' is now active.',
        ]);
    }

    public function reject(Request $request, ListingPaymentSubmission $submission): JsonResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $adminNote = $validated['admin_note'] ?? $validated['review_note'] ?? null;

        try {
            ListingPaymentReviewService::reject($submission, $request->user(), $adminNote);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Payment proof declined.',
        ]);
    }
}

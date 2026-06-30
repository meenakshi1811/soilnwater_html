<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremiumPaymentSubmission;
use App\Services\PremiumPaymentReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PremiumPaymentSubmissionController extends Controller
{
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

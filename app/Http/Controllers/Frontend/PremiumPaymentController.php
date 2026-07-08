<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PremiumPaymentSubmission;
use App\Models\PremiumPrice;
use App\Services\PremiumPaymentReviewService;
use App\Support\PremiumPaymentFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumPaymentController extends Controller
{
    public function store(Request $request, string $type): JsonResponse
    {
        abort_unless(in_array($type, ['vendor', 'consultant', 'service'], true), 404);

        $validated = $request->validate([
            'screenshot' => ['required', 'image', 'max:5120'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $resolved = PremiumPaymentReviewService::resolveProfileForUser($user, $type);

        if ($resolved === null) {
            return response()->json([
                'message' => 'Your account is not linked to this profile type.',
            ], 403);
        }

        $profile = $resolved['profile'];

        if (method_exists($profile, 'isApproved') && ! $profile->isApproved()) {
            return response()->json([
                'message' => 'Your profile must be approved before you can upgrade to premium.',
            ], 403);
        }

        if ($profile->is_premium ?? false) {
            return response()->json([
                'message' => 'Your profile is already premium.',
            ], 422);
        }

        $hasPending = PremiumPaymentSubmission::query()
            ->where('user_id', $user->id)
            ->where('profile_type', $type)
            ->where('profile_id', $profile->id)
            ->where('status', PremiumPaymentSubmission::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'message' => 'You already have a pending payment confirmation. Please wait for admin verification.',
            ], 422);
        }

        $screenshotPath = PremiumPaymentFileUploader::storeScreenshot($request->file('screenshot'));

        $submission = PremiumPaymentSubmission::query()->create([
            'user_id' => $user->id,
            'profile_type' => $type,
            'profile_id' => $profile->id,
            'expected_amount' => number_format(PremiumPrice::amountFor($type), 2, '.', ''),
            'screenshot_path' => $screenshotPath,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'user_note' => $validated['user_note'] ?? null,
            'status' => PremiumPaymentSubmission::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        PremiumPaymentReviewService::notifyAdminsOfSubmission($submission);

        return response()->json([
            'message' => 'Payment proof submitted successfully. Admin will verify it shortly.',
            'status' => PremiumPaymentSubmission::STATUS_PENDING,
        ]);
    }
}

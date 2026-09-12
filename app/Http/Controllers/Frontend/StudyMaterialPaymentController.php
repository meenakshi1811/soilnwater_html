<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ListingPaymentSubmission;
use App\Models\StudyMaterial;
use App\Services\ListingPaymentReviewService;
use App\Support\ListingPaymentFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudyMaterialPaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'study_material_id' => ['required', 'integer', 'exists:study_materials,id'],
            'screenshot' => ['required', 'image', 'max:5120'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $material = StudyMaterial::query()
            ->approved()
            ->publiclyListed()
            ->findOrFail((int) $validated['study_material_id']);

        abort_unless($material->isPaidNote(), 422, 'This note is free and does not require payment.');
        abort_if($material->isOwnedBy($user), 422, 'You already own this note.');
        abort_if($material->hasPurchasedBy($user), 422, 'You already have access to this note.');

        $hasPending = ListingPaymentSubmission::query()
            ->where('listing_type', ListingPaymentSubmission::TYPE_STUDY_MATERIAL)
            ->where('listing_id', $material->id)
            ->where('user_id', $user->id)
            ->where('status', ListingPaymentSubmission::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'message' => 'You already have a pending payment confirmation for this note. Please wait for admin verification.',
            ], 422);
        }

        $screenshotPath = ListingPaymentFileUploader::storeScreenshot($request->file('screenshot'));

        $submission = ListingPaymentSubmission::query()->create([
            'user_id' => $user->id,
            'listing_type' => ListingPaymentSubmission::TYPE_STUDY_MATERIAL,
            'listing_id' => $material->id,
            'amount' => (float) $material->price,
            'screenshot_path' => $screenshotPath,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'user_note' => $validated['user_note'] ?? null,
            'status' => ListingPaymentSubmission::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        ListingPaymentReviewService::notifyAdminsOfSubmission($submission);

        return response()->json([
            'message' => 'Payment proof submitted successfully. Admin will verify it shortly.',
            'status' => ListingPaymentSubmission::STATUS_PENDING,
        ]);
    }
}

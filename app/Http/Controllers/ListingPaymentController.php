<?php

namespace App\Http\Controllers;

use App\Models\ListingPaymentSubmission;
use App\Models\Offer;
use App\Models\UserAd;
use App\Services\ListingPaymentReviewService;
use App\Support\ListingPaymentFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingPaymentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_type' => ['required', Rule::in([ListingPaymentSubmission::TYPE_AD, ListingPaymentSubmission::TYPE_OFFER])],
            'listing_id' => ['required', 'integer'],
            'screenshot' => ['required', 'image', 'max:5120'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'user_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $type = $validated['listing_type'];

        $resolved = $this->resolveListing($type, (int) $validated['listing_id'], $user->id);

        if ($resolved === null) {
            return response()->json([
                'message' => 'This '.($type === ListingPaymentSubmission::TYPE_AD ? 'ad' : 'offer').' could not be found or does not belong to your account.',
            ], 403);
        }

        [$listing, $amount, $alreadyActive] = $resolved;

        if ($alreadyActive) {
            return response()->json([
                'message' => 'This '.($type === ListingPaymentSubmission::TYPE_AD ? 'ad' : 'offer').' is already active.',
            ], 422);
        }

        if ($amount <= 0) {
            return response()->json([
                'message' => 'This '.($type === ListingPaymentSubmission::TYPE_AD ? 'ad' : 'offer').' is free and does not require a payment.',
            ], 422);
        }

        $hasPending = ListingPaymentSubmission::query()
            ->where('listing_type', $type)
            ->where('listing_id', $listing->id)
            ->where('status', ListingPaymentSubmission::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return response()->json([
                'message' => 'You already have a pending payment confirmation for this. Please wait for admin verification.',
            ], 422);
        }

        $screenshotPath = ListingPaymentFileUploader::storeScreenshot($request->file('screenshot'));

        $submission = ListingPaymentSubmission::query()->create([
            'user_id' => $user->id,
            'listing_type' => $type,
            'listing_id' => $listing->id,
            'amount' => $amount,
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

    /**
     * @return array{0: UserAd|Offer, 1: float, 2: bool}|null
     */
    private function resolveListing(string $type, int $listingId, int $userId): ?array
    {
        if ($type === ListingPaymentSubmission::TYPE_AD) {
            $ad = UserAd::query()->where('id', $listingId)->where('user_id', $userId)->first();
            if (! $ad) {
                return null;
            }

            return [$ad, (float) ($ad->grand_total ?? 0), $ad->status === 'approved'];
        }

        $offer = Offer::query()->where('id', $listingId)->where('user_id', $userId)->first();
        if (! $offer) {
            return null;
        }

        return [$offer, ListingPaymentReviewService::offerAmount($offer), $offer->status === 'active'];
    }
}

<?php

namespace App\Services;

use App\Mail\ListingPaymentSubmittedMail;
use App\Models\Category;
use App\Models\ListingPaymentSubmission;
use App\Models\Offer;
use App\Models\User;
use App\Models\UserAd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class ListingPaymentReviewService
{
    public static function approve(ListingPaymentSubmission $submission, User $admin, ?string $adminNote = null): void
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('This payment submission has already been reviewed.');
        }

        $listing = $submission->resolveListing();
        if (! $listing) {
            throw new InvalidArgumentException('The linked ad/offer could not be found.');
        }

        $submission->update([
            'status' => ListingPaymentSubmission::STATUS_APPROVED,
            'admin_note' => $adminNote,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        if ($listing instanceof UserAd) {
            $listing->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_note' => $adminNote,
            ]);
        } elseif ($listing instanceof Offer) {
            $listing->update([
                'status' => 'active',
                'approval_status' => 'approved',
                'approval_reviewed_at' => now(),
                'approval_reviewed_by' => $admin->id,
            ]);
        }

        PortalNotificationService::notifyUser(
            $submission->user,
            'Payment verified',
            'Your payment for the '.strtolower($submission->listingTypeLabel()).' "'.$submission->listingDisplayName().'" has been verified. It is now active.',
            self::ownerListingUrl($submission),
            'reviewed'
        );
    }

    public static function reject(ListingPaymentSubmission $submission, User $admin, ?string $adminNote = null): void
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('This payment submission has already been reviewed.');
        }

        $submission->update([
            'status' => ListingPaymentSubmission::STATUS_REJECTED,
            'admin_note' => $adminNote,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        PortalNotificationService::notifyUser(
            $submission->user,
            'Payment declined',
            'Your payment proof for the '.strtolower($submission->listingTypeLabel()).' "'.$submission->listingDisplayName().'" could not be verified. Please review the note and submit again if needed.',
            self::ownerListingUrl($submission),
            'reviewed'
        );
    }

    public static function notifyAdminsOfSubmission(ListingPaymentSubmission $submission): void
    {
        $userName = $submission->user?->full_name ?: ($submission->user?->name ?? 'A user');

        PortalNotificationService::notifyAdmins(
            'Payment proof submitted',
            $userName.' submitted payment proof for the '.$submission->listingTypeLabel().' "'.$submission->listingDisplayName().'".',
            route('admin.listing-payments.show', $submission),
            'approval'
        );

        $adminEmail = config('services.email.admin_email');
        if (! $adminEmail) {
            $adminEmail = User::query()->where('role', 'admin')->value('email');
        }

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new ListingPaymentSubmittedMail($submission->fresh(['user'])));
        }
    }

    public static function ownerListingUrl(ListingPaymentSubmission $submission): string
    {
        return match ($submission->listing_type) {
            ListingPaymentSubmission::TYPE_AD => route('ads.index'),
            ListingPaymentSubmission::TYPE_OFFER => route('offers.index'),
            default => route('home'),
        };
    }

    /**
     * Compute the payable amount (incl. 5% GST) for a paid offer.
     */
    public static function offerAmount(Offer $offer): float
    {
        $categoryPrice = (float) (Category::query()->where('id', $offer->category_id)->value('offer_price') ?? 0);

        $appliedPrice = $categoryPrice;
        if ($offer->subcategory_id) {
            $subcategoryPrice = (float) (Category::query()
                ->where('id', $offer->subcategory_id)
                ->where('parent_id', $offer->category_id)
                ->value('offer_price') ?? 0);

            if ($subcategoryPrice > 0) {
                $appliedPrice = $subcategoryPrice;
            }
        }

        if ($appliedPrice <= 0) {
            return 0.0;
        }

        $days = 1;
        if ($offer->valid_until) {
            $days = max(1, Carbon::today()->diffInDays(Carbon::parse($offer->valid_until)->startOfDay()) + 1);
        }

        $subtotal = $appliedPrice * $days;

        return round($subtotal * 1.05, 2);
    }
}

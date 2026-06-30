<?php

namespace App\Services;

use App\Mail\PremiumPaymentSubmittedMail;
use App\Models\Consultant;
use App\Models\PremiumPaymentSubmission;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class PremiumPaymentReviewService
{
    public static function approve(PremiumPaymentSubmission $submission, User $admin, ?string $adminNote = null): void
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('This payment submission has already been reviewed.');
        }

        $profile = $submission->resolveProfile();
        if (! $profile) {
            throw new InvalidArgumentException('The linked profile could not be found.');
        }

        $submission->update([
            'status' => PremiumPaymentSubmission::STATUS_APPROVED,
            'admin_note' => $adminNote,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        $profile->update(['is_premium' => true]);

        PortalNotificationService::notifyUser(
            $submission->user,
            'Premium activated',
            'Your premium payment has been verified. Your '.$submission->profileTypeLabel().' profile is now premium.',
            self::ownerDashboardUrl($submission),
            'reviewed'
        );
    }

    public static function reject(PremiumPaymentSubmission $submission, User $admin, ?string $adminNote = null): void
    {
        if (! $submission->isPending()) {
            throw new InvalidArgumentException('This payment submission has already been reviewed.');
        }

        $submission->update([
            'status' => PremiumPaymentSubmission::STATUS_REJECTED,
            'admin_note' => $adminNote,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
        ]);

        PortalNotificationService::notifyUser(
            $submission->user,
            'Premium payment declined',
            'Your premium payment proof could not be verified. Please review the note and submit again if needed.',
            route('frontend.premium.show', $submission->profile_type),
            'reviewed'
        );
    }

    public static function notifyAdminsOfSubmission(PremiumPaymentSubmission $submission): void
    {
        $profileName = $submission->profileDisplayName();

        PortalNotificationService::notifyAdmins(
            'Premium payment proof submitted',
            $submission->user?->full_name ?: ($submission->user?->name ?? 'A user')
                .' submitted payment proof for '.$submission->profileTypeLabel().' "'.$profileName.'".',
            route('admin.premium-payments.show', $submission),
            'approval'
        );

        $adminEmail = config('services.email.admin_email');
        if (! $adminEmail) {
            $adminEmail = User::query()->where('role', 'admin')->value('email');
        }

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new PremiumPaymentSubmittedMail($submission->fresh(['user'])));
        }
    }

    public static function ownerDashboardUrl(PremiumPaymentSubmission $submission): string
    {
        return match ($submission->profile_type) {
            'vendor' => route('vendor.dashboard'),
            'consultant' => route('consultant.dashboard'),
            'service' => route('service_provider.dashboard'),
            default => route('frontend.premium.show', $submission->profile_type),
        };
    }

    /**
     * @return array{profile: Vendor|Consultant|ServiceProvider, profile_type: string}|null
     */
    public static function resolveProfileForUser(User $user, string $type): ?array
    {
        return match ($type) {
            'vendor' => $user->isVendor() && $user->vendor
                ? ['profile' => $user->vendor, 'profile_type' => 'vendor']
                : null,
            'consultant' => $user->isConsultant() && $user->consultant
                ? ['profile' => $user->consultant, 'profile_type' => 'consultant']
                : null,
            'service' => $user->isServiceProvider() && $user->serviceProvider
                ? ['profile' => $user->serviceProvider, 'profile_type' => 'service']
                : null,
            default => null,
        };
    }
}

<?php

namespace App\Services;

use App\Models\PremiumPaymentSubmission;
use App\Models\PremiumPrice;
use App\Models\User;

class PremiumPromptService
{
    /**
     * @return array<string, mixed>|null
     */
    public static function resolveForUser(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        $profileType = match (true) {
            $user->isVendor() => 'vendor',
            $user->isConsultant() => 'consultant',
            $user->isServiceProvider() => 'service',
            default => null,
        };

        if ($profileType === null) {
            return null;
        }

        $profile = match ($profileType) {
            'vendor' => $user->vendor,
            'consultant' => $user->consultant,
            'service' => $user->serviceProvider,
            default => null,
        };

        if (! $profile || ! $profile->isApproved() || $profile->is_premium) {
            return null;
        }

        $hasPendingPayment = PremiumPaymentSubmission::query()
            ->where('user_id', $user->id)
            ->where('profile_type', $profileType)
            ->where('profile_id', $profile->id)
            ->where('status', PremiumPaymentSubmission::STATUS_PENDING)
            ->exists();

        if ($hasPendingPayment) {
            return null;
        }

        $meta = PremiumPrice::typeMeta($profileType);
        $amount = PremiumPrice::amountFor($profileType);

        return [
            'type' => $profileType,
            'singular' => $meta['singular'],
            'icon' => $meta['icon'],
            'color' => $meta['color'],
            'amount' => $amount,
            'formatted_amount' => PremiumPrice::formatAmount($amount),
            'upgrade_url' => route('frontend.premium.show', $profileType),
            'headline' => match ($profileType) {
                'vendor' => 'Grow your vendor business with Premium',
                'consultant' => 'Win more clients with Premium',
                'service' => 'Get more service leads with Premium',
                default => 'Upgrade to Premium membership',
            },
            'subtitle' => 'Unlock higher visibility, a trusted premium badge, and tools that help you convert more enquiries into business.',
            'highlights' => [
                [
                    'icon' => 'fa-crown',
                    'title' => 'Premium badge',
                    'text' => 'Stand out with a trusted premium tag on your public profile.',
                ],
                [
                    'icon' => 'fa-magnifying-glass-chart',
                    'title' => 'Top placement',
                    'text' => 'Appear higher when customers search in your category and location.',
                ],
                [
                    'icon' => 'fa-ban',
                    'title' => 'Ad-free listing',
                    'text' => 'Present a cleaner, more professional experience to visitors.',
                ],
                [
                    'icon' => 'fa-chart-line',
                    'title' => 'Insights & enquiries',
                    'text' => 'Access analytics and quotation tools built for premium members.',
                ],
            ],
        ];
    }

    public static function flashForUser(User $user): void
    {
        $prompt = self::resolveForUser($user);

        if ($prompt !== null) {
            session()->flash('premium_upgrade_prompt', $prompt);
        }
    }
}

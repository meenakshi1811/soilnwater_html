<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Support\Collection;

class PortalNotificationService
{
    public static function notifyAdmins(string $title, string $message, ?string $url = null, string $category = 'approval'): void
    {
        self::adminRecipients()->each(
            fn (User $admin): mixed => $admin->notify(new PortalNotification($title, $message, $url, $category))
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function notifyUser(?User $user, string $title, string $message, ?string $url = null, string $category = 'reviewed', array $meta = []): void
    {
        if (! $user) {
            return;
        }

        $user->notify(new PortalNotification($title, $message, $url, $category, $meta));
    }

    public static function notifyAdminsOfApprovalRequest(string $itemType, string $itemName, ?string $url = null): void
    {
        self::notifyAdmins(
            $itemType.' awaiting approval',
            $itemName.' has been submitted and needs admin approval.',
            $url,
            'approval'
        );
    }

    public static function notifyOwnerOfReview(?User $user, string $itemType, string $itemName, string $status, ?string $url = null, ?string $reason = null): void
    {
        $normalizedStatus = $status === 'rejected' ? 'declined' : $status;
        $message = $itemName.' has been '.$normalizedStatus.'.';

        if (filled($reason)) {
            $message .= ' Reason: '.$reason;
        }

        self::notifyUser(
            $user,
            $itemType.' '.$normalizedStatus,
            $message,
            $url,
            'reviewed'
        );
    }

    /**
     * @return Collection<int, User>
     */
    private static function adminRecipients(): Collection
    {
        return User::query()
            ->where('role', 'admin')
            ->where(function ($query): void {
                $query->where('is_active', true)->orWhereNull('is_active');
            })
            ->get();
    }
}

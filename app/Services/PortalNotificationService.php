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

    public static function notifyUser(?User $user, string $title, string $message, ?string $url = null, string $category = 'reviewed'): void
    {
        if (! $user) {
            return;
        }

        $user->notify(new PortalNotification($title, $message, $url, $category));
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

    public static function notifyOwnerOfReview(?User $user, string $itemType, string $itemName, string $status, ?string $url = null): void
    {
        $normalizedStatus = $status === 'rejected' ? 'declined' : $status;

        self::notifyUser(
            $user,
            $itemType.' '.$normalizedStatus,
            $itemName.' has been '.$normalizedStatus.'.',
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

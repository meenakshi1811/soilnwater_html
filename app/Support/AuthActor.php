<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

/**
 * Resolve the signed-in portal actor from web (admin/users) or employee guards.
 */
final class AuthActor
{
    public static function user(): ?Authenticatable
    {
        return Auth::guard('employee')->user() ?? Auth::guard('web')->user();
    }

    public static function shouldUseActiveGuard(): void
    {
        if (Auth::guard('employee')->check()) {
            Auth::shouldUse('employee');

            return;
        }

        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');
        }
    }

    /**
     * Resolve a users-table id for FK columns constrained to users.id.
     * Employees authenticate separately, so return null for employee actors.
     */
    public static function usersTableId(): ?int
    {
        $userId = Auth::guard('web')->id();

        return is_numeric($userId) ? (int) $userId : null;
    }
}

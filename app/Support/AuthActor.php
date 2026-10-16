<?php

namespace App\Support;

use App\Models\User;
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

    /**
     * A users-table id to own a record the current actor creates (e.g. offers.user_id, which is
     * required). Employees post on behalf of the admin team: their records are owned by the admin
     * who created the employee account, or else the first admin.
     */
    public static function ownerUserId(): ?int
    {
        $employee = Auth::guard('employee')->user();

        if (! $employee) {
            return self::usersTableId();
        }

        $creatorId = $employee->created_by ?? null;
        if ($creatorId && User::query()->whereKey($creatorId)->exists()) {
            return (int) $creatorId;
        }

        $adminId = User::query()->where('role', 'admin')->orderBy('id')->value('id');

        return $adminId !== null ? (int) $adminId : null;
    }
}

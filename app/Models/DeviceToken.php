<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A device's push notification token (Firebase Cloud Messaging), registered by the app through
 * POST /api/device-tokens; linked to the user who was logged in, if any.
 */
class DeviceToken extends Model
{
    /** @var list<string> */
    public const PLATFORMS = ['android', 'ios', 'web'];

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_id',
        'device_name',
        'app_version',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

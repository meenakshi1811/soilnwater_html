<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionGroupInvitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'discussion_topic_id',
        'inviter_id',
        'invitee_id',
        'invitee_phone',
        'token',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(DiscussionTopic::class, 'discussion_topic_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPhoneInvite(): bool
    {
        return $this->invitee_id === null && filled($this->invitee_phone);
    }

    public function isInvitee(User $user): bool
    {
        if ((int) $this->invitee_id === (int) $user->id) {
            return true;
        }

        if (! $this->isPhoneInvite()) {
            return false;
        }

        return self::phonesMatch($this->invitee_phone, $user->phone_number)
            || self::phonesMatch($this->invitee_phone, $user->whatsapp_number);
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (strlen($digits) < 10) {
            return null;
        }

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    public static function phonesMatch(?string $left, ?string $right): bool
    {
        $leftPhone = self::normalizePhone($left);
        $rightPhone = self::normalizePhone($right);

        return $leftPhone !== null && $leftPhone === $rightPhone;
    }

    public function invitationUrl(): string
    {
        if ($this->isPhoneInvite() && filled($this->token)) {
            return route('discussions.invitations.join', $this->token);
        }

        return route('discussions.invitations.show', $this);
    }

    /**
     * @param  Builder<DiscussionGroupInvitation>  $query
     * @return Builder<DiscussionGroupInvitation>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastArray(): array
    {
        $this->loadMissing(['topic', 'inviter', 'invitee']);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'group_id' => $this->discussion_topic_id,
            'group_title' => $this->topic?->title,
            'group_image_url' => $this->topic?->groupImageUrl(),
            'inviter' => [
                'id' => $this->inviter_id,
                'name' => $this->inviter?->authorDisplayName(),
            ],
            'invitee' => [
                'id' => $this->invitee_id,
                'name' => $this->invitee?->authorDisplayName() ?: ($this->invitee_phone ? 'Pending member' : null),
                'phone' => $this->invitee?->phone_number ?? $this->invitee_phone,
            ],
            'is_phone_invite' => $this->isPhoneInvite(),
            'url' => $this->invitationUrl(),
            'accept_url' => route('discussions.invitations.accept', $this),
            'reject_url' => route('discussions.invitations.reject', $this),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}

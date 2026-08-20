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

    public function isInvitee(User $user): bool
    {
        return (int) $this->invitee_id === (int) $user->id;
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
                'name' => $this->invitee?->authorDisplayName(),
                'phone' => $this->invitee?->phone_number,
            ],
            'url' => route('discussions.invitations.show', $this),
            'accept_url' => route('discussions.invitations.accept', $this),
            'reject_url' => route('discussions.invitations.reject', $this),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}

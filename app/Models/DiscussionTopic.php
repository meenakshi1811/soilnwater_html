<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionTopic extends Model
{
    /** @use HasFactory<DiscussionTopicFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'is_group',
        'body',
        'attachments',
        'is_pinned',
        'pinned_by',
        'pinned_at',
        'replies_count',
    ];

    protected function casts(): array
    {
        return [
            'is_group' => 'boolean',
            'attachments' => 'array',
            'is_pinned' => 'boolean',
            'pinned_at' => 'datetime',
            'replies_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pinnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class)->oldest();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'discussion_topic_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function memberRecords(): HasMany
    {
        return $this->hasMany(DiscussionTopicMember::class);
    }

    public function canAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->is_group) {
            return true;
        }

        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function isOwner(User $user): bool
    {
        return (int) $this->user_id === (int) $user->id;
    }

    /**
     * @param  list<int>  $memberIds
     */
    public function syncGroupMembers(User $owner, array $memberIds): void
    {
        $records = collect($memberIds)
            ->filter(fn ($id) => (int) $id !== (int) $owner->id)
            ->unique()
            ->mapWithKeys(fn ($id) => [(int) $id => ['role' => 'member']])
            ->all();

        $records[(int) $owner->id] = ['role' => 'owner'];

        $this->members()->sync($records);
    }

    /**
     * @param  list<int>  $memberIds
     * @return list<int>
     */
    public function addGroupMembers(array $memberIds): array
    {
        $existingIds = $this->members()->pluck('users.id')->all();
        $existingIds[] = (int) $this->user_id;

        $added = [];

        foreach (array_unique($memberIds) as $memberId) {
            $memberId = (int) $memberId;

            if (in_array($memberId, $existingIds, true)) {
                continue;
            }

            $this->members()->attach($memberId, ['role' => 'member']);
            $existingIds[] = $memberId;
            $added[] = $memberId;
        }

        return $added;
    }

    /**
     * @param  Builder<DiscussionTopic>  $query
     * @return Builder<DiscussionTopic>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('is_group', false)
                ->orWhere('user_id', $user->id)
                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function memberSummaries(): array
    {
        $this->loadMissing('members');

        return $this->members
            ->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->authorDisplayName(),
                'role' => $member->pivot->role ?? 'member',
                'is_owner' => ($member->pivot->role ?? 'member') === 'owner' || (int) $member->id === (int) $this->user_id,
            ])
            ->values()
            ->all();
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(DiscussionReaction::class, 'reactable');
    }

    /**
     * @return array<string, int>
     */
    public function reactionCounts(): array
    {
        return $this->reactions()
            ->selectRaw('reaction, count(*) as total')
            ->groupBy('reaction')
            ->pluck('total', 'reaction')
            ->all();
    }

    public function displayAuthorName(): string
    {
        return $this->user?->name
            ?? $this->user?->full_name
            ?? 'Community member';
    }

    /**
     * @return array<string, mixed>
     */
    public function toBroadcastArray(): array
    {
        $user = auth()->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_group' => $this->is_group,
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'is_pinned' => $this->is_pinned,
            'replies_count' => $this->replies_count,
            'members_count' => $this->members_count ?? ($this->relationLoaded('members') ? $this->members->count() : null),
            'member_ids' => $this->is_group
                ? ($this->relationLoaded('members')
                    ? $this->members->pluck('id')->all()
                    : $this->members()->pluck('users.id')->all())
                : [],
            'can_manage_members' => $user ? $user->can('manageMembers', $this) : false,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
            'created_at_date' => $this->created_at?->format('d M Y'),
            'created_at_time' => $this->created_at?->format('h:i A'),
            'author' => [
                'id' => $this->user_id,
                'name' => $this->displayAuthorName(),
            ],
            'reaction_counts' => $this->reactionCounts(),
            'url' => route('discussions.show', $this),
        ];
    }
}

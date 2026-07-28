<?php

namespace App\Models;

use App\Support\DiscussionFileUploader;
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
        'parent_topic_id',
        'group_image',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_topic_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_topic_id')
            ->where('is_group', false)
            ->latest();
    }

    public function canAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->parent_topic_id) {
            return $this->parent?->canAccess($user) ?? false;
        }

        if (! $this->is_group) {
            return true;
        }

        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function isGroupContainer(): bool
    {
        return $this->is_group && $this->parent_topic_id === null;
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

    public function removeGroupMember(int $userId): bool
    {
        if ((int) $this->user_id === $userId) {
            return false;
        }

        if (! $this->members()->where('users.id', $userId)->exists()) {
            return false;
        }

        $this->members()->detach($userId);

        return true;
    }

    public function groupImageUrl(): ?string
    {
        return DiscussionFileUploader::url($this->group_image);
    }

    public function deleteStoredGroupImage(): void
    {
        DiscussionFileUploader::deleteStoredFile($this->group_image);
    }

    public function purgeDiscussionContent(): void
    {
        DiscussionFileUploader::deleteAttachmentFiles($this->attachments);

        foreach ($this->replies()->get() as $reply) {
            DiscussionFileUploader::deleteAttachmentFiles($reply->attachments);
            $reply->reactions()->delete();
            $reply->delete();
        }

        $this->reactions()->delete();
        DiscussionTopicRead::query()->where('discussion_topic_id', $this->id)->delete();
    }

    public function deleteGroupCompletely(): void
    {
        if (! $this->isGroupContainer()) {
            return;
        }

        foreach ($this->children()->get() as $child) {
            $child->purgeDiscussionContent();
            $child->delete();
        }

        $this->deleteStoredGroupImage();
        $this->purgeDiscussionContent();
        $this->members()->detach();
        $this->memberRecords()->delete();
        $this->delete();
    }

    public function leaveGroup(User $user): bool
    {
        if (! $this->isGroupContainer() || $this->isOwner($user)) {
            return false;
        }

        if (! $this->members()->where('users.id', $user->id)->exists()) {
            return false;
        }

        $this->members()->detach($user->id);
        DiscussionTopicRead::query()
            ->where('discussion_topic_id', $this->id)
            ->where('user_id', $user->id)
            ->delete();

        return true;
    }

    /**
     * @param  Builder<DiscussionTopic>  $query
     * @return Builder<DiscussionTopic>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where(function (Builder $publicBuilder): void {
                $publicBuilder->where('is_group', false)
                    ->whereNull('parent_topic_id');
            })
                ->orWhere(function (Builder $groupBuilder) use ($user): void {
                    $groupBuilder->where('is_group', true)
                        ->whereNull('parent_topic_id')
                        ->where(function (Builder $accessBuilder) use ($user): void {
                            $accessBuilder->where('user_id', $user->id)
                                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));
                        });
                })
                ->orWhereHas('parent', function (Builder $parentQuery) use ($user): void {
                    $parentQuery->where('is_group', true)
                        ->where(function (Builder $accessBuilder) use ($user): void {
                            $accessBuilder->where('user_id', $user->id)
                                ->orWhereHas('members', fn (Builder $memberQuery) => $memberQuery->where('users.id', $user->id));
                        });
                });
        });
    }

    /**
     * @param  Builder<DiscussionTopic>  $query
     * @return Builder<DiscussionTopic>
     */
    public function scopeRootOnly(Builder $query): Builder
    {
        return $query->whereNull('parent_topic_id');
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
            'parent_topic_id' => $this->parent_topic_id,
            'group_image_url' => $this->is_group ? $this->groupImageUrl() : null,
            'body' => $this->body,
            'attachments' => $this->attachments ?? [],
            'is_pinned' => $this->is_pinned,
            'replies_count' => $this->replies_count,
            'children_count' => $this->children_count ?? ($this->relationLoaded('children') ? $this->children->count() : null),
            'members_count' => $this->members_count ?? ($this->relationLoaded('members') ? $this->members->count() : null),
            'member_ids' => $this->is_group
                ? ($this->relationLoaded('members')
                    ? $this->members->pluck('id')->all()
                    : $this->members()->pluck('users.id')->all())
                : [],
            'can_manage_members' => $user && $this->isGroupContainer() ? $user->can('manageMembers', $this) : false,
            'can_delete_group' => $user && $this->isGroupContainer() ? $user->can('deleteGroup', $this) : false,
            'can_leave_group' => $user && $this->isGroupContainer() ? $user->can('leaveGroup', $this) : false,
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

@php
    use Illuminate\Support\Str;
    $unreadCount = $unreadCounts[$topic->id] ?? 0;
    $authorName = $topic->displayAuthorName();
    $initials = collect(explode(' ', $authorName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))
        ->join('');
@endphp
<article class="discussion-topic-card {{ $topic->is_pinned ? 'discussion-topic-card--pinned' : '' }} {{ $unreadCount > 0 ? 'discussion-topic-card--unread' : '' }}"
     data-topic-id="{{ $topic->id }}"
     id="discussion-topic-{{ $topic->id }}">
    <span class="discussion-avatar discussion-avatar--sm" aria-hidden="true">{{ $initials ?: 'M' }}</span>
    <div class="discussion-topic-card__body">
        @if($topic->is_pinned)
            <span class="discussion-pin-badge"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
        @endif
        <div class="discussion-topic-card__top">
            <a href="{{ route('discussions.messenger', $topic) }}" class="discussion-topic-link">
                {{ $topic->title }}
            </a>
            <span class="discussion-widget-topic__time">{{ $topic->created_at->diffForHumans(null, true) }}</span>
        </div>
        @if($topic->body)
            <p class="discussion-topic-excerpt">{{ Str::limit($topic->body, 120) }}</p>
        @endif
        <div class="discussion-topic-meta">
            <span>{{ $authorName }}</span>
            <span>{{ $topic->replies_count }} {{ Str::plural('reply', $topic->replies_count) }}</span>
        </div>
    </div>
    <div class="d-flex flex-column align-items-end gap-2">
        @if($unreadCount > 0)
            <span class="discussion-topic-unread-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('discussions.messenger', $topic) }}" class="discussion-btn discussion-btn--outline discussion-btn--sm">Open</a>
            <button type="button" class="discussion-btn discussion-btn--outline discussion-btn--sm" data-open-popup="1">Popup</button>
        </div>
    </div>
</article>

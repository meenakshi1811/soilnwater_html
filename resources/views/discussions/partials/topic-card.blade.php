@php
    use Illuminate\Support\Str;
    $unreadCount = $unreadCounts[$topic->id] ?? 0;
    $authorName = $topic->displayAuthorName();
    $isGroup = (bool) $topic->is_group;
@endphp
<article class="discussion-topic-card {{ $topic->is_pinned ? 'discussion-topic-card--pinned' : '' }} {{ $unreadCount > 0 ? 'discussion-topic-card--unread' : '' }}"
     data-topic-id="{{ $topic->id }}"
     id="discussion-topic-{{ $topic->id }}">
    @if($isGroup)
        @if($topic->groupImageUrl())
            <span class="discussion-avatar discussion-avatar--photo" aria-hidden="true">
                <img src="{{ $topic->groupImageUrl() }}" alt="">
            </span>
        @else
            <span class="discussion-avatar discussion-avatar--icon discussion-avatar--group" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
        @endif
    @else
        <span class="discussion-avatar discussion-avatar--icon discussion-avatar--topic" aria-hidden="true"><i class="fa-solid fa-hashtag"></i></span>
    @endif
    <div class="discussion-topic-card__body">
        @if($topic->is_pinned)
            <span class="discussion-pin-badge"><i class="fa-solid fa-thumbtack"></i> Pinned</span>
        @endif
        <div class="discussion-topic-card__top">
            <a href="{{ route('discussions.messenger', $topic) }}" class="discussion-topic-link">
                {{ $topic->title }}
            </a>
            @if($unreadCount > 0)
                <span class="discussion-topic-unread-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </div>
        @if($topic->body)
            <p class="discussion-topic-excerpt">{{ Str::limit($topic->body, 120) }}</p>
        @endif
        <div class="discussion-topic-meta">
            <span>
                @if($isGroup)
                    <i class="fa-solid fa-users"></i> Group
                @else
                    <i class="fa-solid fa-hashtag"></i> Topic
                @endif
                · {{ $authorName }}
            </span>
            <span>{{ $topic->replies_count }} {{ Str::plural('reply', $topic->replies_count) }}</span>
            <time class="discussion-widget-topic__date" datetime="{{ $topic->created_at->toIso8601String() }}">
                {{ $topic->created_at->format('d M Y') }} · {{ $topic->created_at->format('h:i A') }}
            </time>
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

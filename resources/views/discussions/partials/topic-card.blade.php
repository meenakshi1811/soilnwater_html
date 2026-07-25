@php
    $unreadCount = $unreadCounts[$topic->id] ?? 0;
@endphp
<div class="discussion-topic-card border rounded-3 p-3 mb-3 {{ $topic->is_pinned ? 'discussion-topic-card--pinned' : '' }} {{ $unreadCount > 0 ? 'discussion-topic-card--unread' : '' }}"
     data-topic-id="{{ $topic->id }}"
     id="discussion-topic-{{ $topic->id }}">
    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
        <div class="flex-grow-1">
            @if($topic->is_pinned)
                <span class="badge bg-warning text-dark mb-2 discussion-pin-badge"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>
            @endif
            <h3 class="h6 mb-1 d-flex align-items-center gap-2">
                <a href="{{ route('discussions.show', $topic) }}" class="text-decoration-none discussion-topic-link">
                    {{ $topic->title }}
                </a>
                @if($unreadCount > 0)
                    <span class="discussion-topic-unread-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            </h3>
            @if($topic->body)
                <p class="text-muted small mb-2 discussion-topic-excerpt">{{ \Illuminate\Support\Str::limit($topic->body, 140) }}</p>
            @endif
            <div class="d-flex flex-wrap gap-3 small text-muted">
                <span><i class="fa-solid fa-user me-1"></i>{{ $topic->displayAuthorName() }}</span>
                <span><i class="fa-regular fa-clock me-1"></i>{{ $topic->created_at->diffForHumans() }}</span>
                <span><i class="fa-solid fa-reply me-1"></i>{{ $topic->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $topic->replies_count) }}</span>
            </div>
        </div>
        <a href="{{ route('discussions.show', $topic) }}" class="btn btn-sm btn-outline-success">Open</a>
    </div>
</div>

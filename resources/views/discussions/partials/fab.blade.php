@auth
<button type="button"
        class="discussion-banner-chat"
        id="discussionFab"
        aria-label="Open Community Chat"
        aria-controls="discussionWidget"
        aria-expanded="false"
        title="Community Chat">
    <span class="discussion-banner-chat__badge" id="discussionFabBadge" hidden aria-label="Unread community messages">0</span>
    <span class="discussion-banner-chat__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false">
            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V18h14v-1.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V18h6v-1.5c0-2.33-4.67-3.5-7-3.5z"/>
        </svg>
        <i class="fa-solid fa-people-group"></i>
    </span>
    <span class="discussion-banner-chat__label">Community Chat</span>
    <span class="discussion-banner-chat__close" aria-hidden="true">
        <svg viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false">
            <path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 1 0 5.7 7.11L10.59 12 5.7 16.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z"/>
        </svg>
        <i class="fa-solid fa-xmark"></i>
    </span>
</button>

@include('discussions.partials.widget-shell', ['standalone' => false])
@include('discussions.partials.chat-scripts', ['discussionPageMode' => false])
@endauth

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
        <i class="fa-solid fa-people-group"></i>
    </span>
    <span class="discussion-banner-chat__label">Community Chat</span>
    <span class="discussion-banner-chat__close" aria-hidden="true">
        <i class="fa-solid fa-xmark"></i>
    </span>
</button>

@include('discussions.partials.widget-shell', ['standalone' => false])
@include('discussions.partials.chat-scripts', ['discussionPageMode' => false])
@endauth

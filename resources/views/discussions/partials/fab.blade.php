@auth
<button type="button"
        class="discussion-fab"
        id="discussionFab"
        aria-label="Open chats"
        aria-controls="discussionWidget"
        aria-expanded="false"
        title="Chats">
    <span class="discussion-fab__badge" id="discussionFabBadge" hidden aria-label="Unread messages">0</span>
    <span class="discussion-fab__icon" aria-hidden="true">
        <i class="fa-solid fa-comments"></i>
    </span>
    <span class="discussion-fab__close" aria-hidden="true">
        <i class="fa-solid fa-xmark"></i>
    </span>
</button>

@include('discussions.partials.widget-shell', ['standalone' => false])
@include('discussions.partials.chat-scripts', ['discussionPageMode' => false])
@endauth

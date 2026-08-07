@auth
<button type="button"
        class="header-sticky-chat-btn"
        id="discussionHeaderBtn"
        aria-label="Open Community Chat"
        aria-controls="discussionWidget"
        aria-expanded="false"
        aria-describedby="communityChatPopoverContent"
        title="Community Chat">
    <span class="header-sticky-chat-btn__icon" aria-hidden="true">
        <i class="fa-solid fa-comments"></i>
    </span>
    <span class="header-sticky-chat-btn__label">Community Chat</span>
    <span class="header-sticky-chat-btn__badge" id="discussionHeaderBadge" hidden aria-label="Unread community messages">0</span>
</button>

<div id="communityChatPopoverContent" class="d-none">
    <div class="community-chat-popover">
        <p class="community-chat-popover__lead"><strong>SoilnWater Community Chat</strong> is for member discussions — not live customer support.</p>
        <ul class="community-chat-popover__list">
            <li><i class="fa-solid fa-hashtag" aria-hidden="true"></i> Join topic threads and interest groups</li>
            <li><i class="fa-solid fa-user-group" aria-hidden="true"></i> Connect with vendors, consultants, and neighbours</li>
            <li><i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Share local voices, ideas, and community updates</li>
        </ul>
        <p class="community-chat-popover__hint mb-0">Click Community Chat to open your conversations.</p>
    </div>
</div>
@endauth

@auth
<div class="hero-chat-launcher-wrap" id="heroChatLauncherWrap">
    <button type="button"
            class="hero-chat-launcher"
            id="discussionHeroBtn"
            aria-label="Open Community Chat"
            aria-controls="discussionWidget"
            aria-expanded="false"
            title="Community Chat">
        <span class="hero-chat-launcher__icon" aria-hidden="true">
            <i class="fa-solid fa-people-group"></i>
        </span>
        <span class="hero-chat-launcher__copy">
            <strong class="hero-chat-launcher__title">Community Chat</strong>
            <span class="hero-chat-launcher__subtitle">Join topics, groups, and local discussions</span>
        </span>
        <span class="hero-chat-launcher__action" aria-hidden="true">
            <span class="hero-chat-launcher__action-label">Open</span>
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </span>
        <span class="hero-chat-launcher__badge" id="discussionHeroBadge" hidden aria-label="Unread community messages">0</span>
    </button>
</div>
@endauth

@auth
@php
    $broadcastEnabled = config('broadcasting.default') !== 'log' && config('broadcasting.default') !== 'null';
    $replyReactTemplate = str_replace('999999999', '__REPLY__', route('discussions.replies.react', ['reply' => 999999999]));
    $topicShowTemplate = str_replace('999999999', '__TOPIC__', route('discussions.show', ['topic' => 999999999]));
    $topicRepliesTemplate = str_replace('999999999', '__TOPIC__', route('discussions.replies.store', ['topic' => 999999999]));
    $topicPinTemplate = str_replace('999999999', '__TOPIC__', route('discussions.pin', ['topic' => 999999999]));
    $topicReactTemplate = str_replace('999999999', '__TOPIC__', route('discussions.react', ['topic' => 999999999]));
    $topicReadTemplate = str_replace('999999999', '__TOPIC__', route('discussions.read', ['topic' => 999999999]));
@endphp

<button type="button"
        class="discussion-fab"
        id="discussionFab"
        aria-label="Open chats"
        aria-controls="discussionWidget"
        aria-expanded="false"
        title="Chats">
    <span class="discussion-fab__badge" id="discussionFabBadge" hidden aria-label="Unread messages">0</span>
    <span class="discussion-fab__icon" aria-hidden="true">
        <i class="fa-brands fa-whatsapp"></i>
    </span>
    <span class="discussion-fab__close" aria-hidden="true">
        <i class="fa-solid fa-xmark"></i>
    </span>
</button>

<div class="discussion-widget"
     id="discussionWidget"
     role="dialog"
     aria-modal="true"
     aria-labelledby="discussionWidgetTitle"
     hidden>
    <header class="discussion-widget__header">
        <button type="button"
                class="discussion-widget__header-back"
                id="discussionWidgetBackBtn"
                hidden
                aria-label="Back">
            <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="discussion-widget__header-main">
            <span class="discussion-widget__brand-mark" id="discussionWidgetHeaderAvatar" aria-hidden="true">
                <i class="fa-solid fa-comments"></i>
            </span>
            <div class="discussion-widget__header-text">
                <h2 class="discussion-widget__title" id="discussionWidgetTitle">Chats</h2>
                <p class="discussion-widget__subtitle" id="discussionWidgetSubtitle">Community discussions</p>
            </div>
        </div>
        <div class="discussion-widget__header-actions">
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetNewTopicBtn"
                    title="New chat"
                    aria-label="New chat">
                <i class="fa-solid fa-comment-medical"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetPinBtn"
                    hidden
                    title="Pin topic"
                    aria-label="Pin topic">
                <i class="fa-solid fa-thumbtack"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetCloseBtn"
                    title="Close"
                    aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </header>

    <div class="discussion-widget__body">
        {{-- Chat list --}}
        <section class="discussion-widget__panel is-active" id="discussionWidgetTopics" data-panel="topics">
            <div class="discussion-widget__search-bar">
                <div class="discussion-widget__search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search"
                           class="discussion-widget__search"
                           id="discussionWidgetSearch"
                           placeholder="Search or start new chat"
                           autocomplete="off"
                           aria-label="Search chats">
                </div>
            </div>
            <div class="discussion-widget__scroll discussion-widget__scroll--list" id="discussionWidgetTopicList">
                <div class="discussion-widget__loading" id="discussionWidgetTopicsLoading">
                    <span class="discussion-widget__spinner" aria-hidden="true"></span>
                    <span>Loading chats…</span>
                </div>
            </div>
        </section>

        {{-- Chat thread --}}
        <section class="discussion-widget__panel" id="discussionWidgetThread" data-panel="thread" hidden>
            <div class="discussion-widget__scroll discussion-widget__scroll--thread" id="discussionWidgetMessages">
                <div class="discussion-widget__loading" id="discussionWidgetThreadLoading">
                    <span class="discussion-widget__spinner" aria-hidden="true"></span>
                    <span>Loading messages…</span>
                </div>
            </div>
            <form class="discussion-widget__composer" id="discussionWidgetReplyForm" enctype="multipart/form-data">
                <label class="visually-hidden" for="discussionWidgetReplyBody">Type a message</label>
                <div class="discussion-widget__composer-inner">
                    <label class="discussion-widget__attach-btn" for="discussionWidgetReplyAttachments" title="Attach">
                        <i class="fa-solid fa-plus"></i>
                    </label>
                    <input type="file"
                           id="discussionWidgetReplyAttachments"
                           name="attachments[]"
                           class="visually-hidden"
                           accept="image/*,video/mp4,video/webm"
                           multiple>
                    <div class="discussion-widget__composer-field">
                        <textarea id="discussionWidgetReplyBody"
                                  name="body"
                                  rows="1"
                                  maxlength="5000"
                                  placeholder="Type a message"></textarea>
                    </div>
                    <button type="submit" class="discussion-widget__send" aria-label="Send">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                <div class="discussion-media-preview" id="discussionWidgetReplyPreview" hidden></div>
            </form>
        </section>

        {{-- New chat --}}
        <section class="discussion-widget__panel" id="discussionWidgetCompose" data-panel="compose" hidden>
            <form class="discussion-widget__compose-form" id="discussionWidgetNewTopicForm" data-url="{{ route('discussions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="discussion-widget__field">
                    <label for="discussionWidgetTopicTitle">Group / topic name</label>
                    <input type="text"
                           id="discussionWidgetTopicTitle"
                           name="title"
                           maxlength="200"
                           required
                           placeholder="e.g. Soil health tips">
                </div>
                <div class="discussion-widget__field">
                    <label for="discussionWidgetTopicBody">First message <span>(optional)</span></label>
                    <textarea id="discussionWidgetTopicBody"
                              name="body"
                              rows="4"
                              maxlength="5000"
                              placeholder="Write your first message…"></textarea>
                </div>
                <div class="discussion-widget__field">
                    <label class="discussion-media-btn" for="discussionWidgetTopicAttachments">
                        <i class="fa-solid fa-paperclip"></i>
                        Photos &amp; videos
                    </label>
                    <input type="file"
                           id="discussionWidgetTopicAttachments"
                           name="attachments[]"
                           class="visually-hidden"
                           accept="image/*,video/mp4,video/webm"
                           multiple>
                    <div class="discussion-media-preview" id="discussionWidgetTopicPreview" hidden></div>
                </div>
                <button type="submit" class="discussion-widget__primary-btn">
                    <i class="fa-brands fa-whatsapp"></i>
                    Create chat
                </button>
            </form>
        </section>
    </div>
</div>

<script>
    window.soilnwaterDiscussion = {
        broadcastEnabled: @json($broadcastEnabled),
        pusher: {
            key: @json(config('broadcasting.connections.pusher.key')),
            cluster: @json(config('broadcasting.connections.pusher.options.cluster')),
            host: @json(env('PUSHER_HOST')),
            port: @json(config('broadcasting.connections.pusher.options.port', 443)),
            scheme: @json(config('broadcasting.connections.pusher.options.scheme', 'https')),
        },
        authEndpoint: @json(url('/broadcasting/auth')),
        csrfToken: @json(csrf_token()),
        routes: {
            discussionsIndex: @json(route('discussions.index')),
            discussionsStore: @json(route('discussions.store')),
            topicShowTemplate: @json($topicShowTemplate),
            topicRepliesTemplate: @json($topicRepliesTemplate),
            topicPinTemplate: @json($topicPinTemplate),
            topicReactTemplate: @json($topicReactTemplate),
            topicReadTemplate: @json($topicReadTemplate),
            unreadSummary: @json(route('discussions.unread-summary')),
            replyReactTemplate: @json($replyReactTemplate),
        },
        currentUserId: @json(auth()->id()),
        currentUserName: @json(auth()->user()?->name ?? auth()->user()?->full_name ?? 'You'),
        canPin: @json(auth()->user()?->isAdmin() ?? false),
        topicId: null,
        reactionLabels: @json(\App\Support\DiscussionReactions::labels()),
        reactionIcons: @json(\App\Support\DiscussionReactions::icons()),
        unreadTopics: {},
        globalUnread: 0,
    };
</script>
<script src="{{ asset('assets/js/discussion.js') }}?v={{ now()->timestamp }}" defer></script>
<script src="{{ asset('assets/js/discussion-widget.js') }}?v={{ now()->timestamp }}" defer></script>
@if($broadcastEnabled)
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.19.0/dist/echo.iife.js" defer></script>
<script src="{{ asset('assets/js/discussion-echo.js') }}?v={{ now()->timestamp }}" defer></script>
@endif
@endauth

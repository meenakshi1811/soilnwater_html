@php
    $standalone = $standalone ?? false;
@endphp
<div class="discussion-widget {{ $standalone ? 'discussion-widget--standalone is-open' : '' }}"
     id="discussionWidget"
     role="{{ $standalone ? 'main' : 'dialog' }}"
     @unless($standalone) aria-modal="true" @endunless
     aria-labelledby="discussionWidgetTitle"
     @unless($standalone) hidden @endunless>
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
            @if($standalone)
                <a href="{{ route('home') }}"
                   class="discussion-widget__icon-btn discussion-widget__icon-btn--link"
                   title="Back to SoilnWater"
                   aria-label="Back to SoilnWater">
                    <i class="fa-solid fa-house"></i>
                </a>
            @else
                <a href="{{ route('discussions.messenger') }}"
                   class="discussion-widget__icon-btn discussion-widget__icon-btn--link"
                   id="discussionWidgetFullPageBtn"
                   title="Open full page"
                   aria-label="Open full page">
                    <i class="fa-solid fa-up-right-from-square"></i>
                </a>
                <button type="button"
                        class="discussion-widget__icon-btn"
                        id="discussionWidgetSizeBtn"
                        title="Increase popup size"
                        aria-label="Increase popup size">
                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                </button>
            @endif
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetNewTopicBtn"
                    title="New chat"
                    aria-label="New chat">
                <i class="fa-solid fa-comment-medical"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetMembersBtn"
                    hidden
                    title="Manage group members"
                    aria-label="Manage group members">
                <i class="fa-solid fa-user-plus"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetPinBtn"
                    hidden
                    title="Pin topic"
                    aria-label="Pin topic">
                <i class="fa-solid fa-thumbtack"></i>
            </button>
            @unless($standalone)
                <button type="button"
                        class="discussion-widget__icon-btn"
                        id="discussionWidgetCloseBtn"
                        title="Close"
                        aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            @endunless
        </div>
    </header>

    <div class="discussion-widget__body">
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
                    <div class="discussion-widget__attach-group">
                        <button type="button"
                                class="discussion-widget__attach-btn"
                                id="discussionWidgetReplyAttachImageBtn"
                                title="Attach image"
                                aria-label="Attach image">
                            <i class="fa-solid fa-image"></i>
                        </button>
                        <button type="button"
                                class="discussion-widget__attach-btn"
                                id="discussionWidgetReplyAttachVideoBtn"
                                title="Attach video"
                                aria-label="Attach video">
                            <i class="fa-solid fa-video"></i>
                        </button>
                        <button type="button"
                                class="discussion-widget__attach-btn"
                                id="discussionWidgetReplyAttachDocumentBtn"
                                title="Attach document"
                                aria-label="Attach document">
                            <i class="fa-solid fa-file-lines"></i>
                        </button>
                    </div>
                    <input type="file"
                           id="discussionWidgetReplyAttachments"
                           name="attachments[]"
                           class="visually-hidden"
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

        <section class="discussion-widget__panel" id="discussionWidgetCompose" data-panel="compose" hidden>
            <form class="discussion-widget__compose-form" id="discussionWidgetNewTopicForm" data-url="{{ route('discussions.store') }}" enctype="multipart/form-data">
                @csrf
                @include('discussions.partials.new-chat-compose-fields', ['prefix' => 'discussionWidget'])
                <button type="submit" class="discussion-widget__primary-btn">
                    <i class="fa-solid fa-comments"></i>
                    Create chat
                </button>
            </form>
        </section>

        <div class="discussion-widget__members-modal" id="discussionWidgetMembersModal" hidden>
            <div class="discussion-widget__members-card">
                <div class="discussion-widget__members-head">
                    <h3>Group members</h3>
                    <button type="button" class="discussion-widget__icon-btn" id="discussionWidgetMembersCloseBtn" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="discussion-widget__members-list" id="discussionWidgetMembersList"></div>
                <div class="discussion-widget__members-add" id="discussionWidgetMembersAddSection" hidden>
                    <label for="discussionWidgetMembersAddSearch">Add members</label>
                    <div class="discussion-widget__member-search-wrap">
                        <input type="search"
                               id="discussionWidgetMembersAddSearch"
                               class="form-control"
                               placeholder="Search members by name or email"
                               autocomplete="off">
                        <div class="discussion-widget__member-results" id="discussionWidgetMembersAddResults" hidden></div>
                    </div>
                    <div class="discussion-widget__member-chips" id="discussionWidgetMembersAddChips"></div>
                    <button type="button" class="discussion-widget__primary-btn" id="discussionWidgetMembersAddBtn">
                        <i class="fa-solid fa-user-plus"></i>
                        Add selected members
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
        <div class="discussion-widget__header-start">
            <button type="button"
                    class="discussion-widget__header-back"
                    id="discussionWidgetBackBtn"
                    hidden
                    aria-label="Back">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <span class="discussion-widget__brand-mark" id="discussionWidgetHeaderAvatar" aria-hidden="true">
                <i class="fa-solid fa-people-group"></i>
            </span>
        </div>
        <div class="discussion-widget__header-center">
            <div class="discussion-widget__header-text">
                <h2 class="discussion-widget__title" id="discussionWidgetTitle">Community Chat</h2>
                <p class="discussion-widget__subtitle" id="discussionWidgetSubtitle">Member topics, groups, and discussions</p>
                <div class="discussion-widget__online" id="discussionWidgetOnline" hidden aria-live="polite"></div>
            </div>
        </div>
        <div class="discussion-widget__header-actions">
            @if($standalone)
                <a href="{{ route('frontend.index') }}"
                   class="discussion-widget__icon-btn discussion-widget__icon-btn--link"
                   title="Back to SoilnWater"
                   aria-label="Back to SoilnWater">
                    <i class="fa-solid fa-house"></i>
                </a>
            @else
                <a href="{{ route('discussions.messenger') }}"
                   class="discussion-widget__icon-btn discussion-widget__icon-btn--link"
                   id="discussionWidgetFullPageBtn"
                   title="Open full page in new tab"
                   aria-label="Open full page in new tab"
                   target="_blank"
                   rel="noopener noreferrer">
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
                    id="discussionWidgetNewGroupBtn"
                    title="New group"
                    aria-label="New group">
                <i class="fa-solid fa-user-group"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetNewTopicBtn"
                    title="New topic"
                    aria-label="New topic">
                <i class="fa-solid fa-hashtag"></i>
            </button>
            <button type="button"
                    class="discussion-widget__icon-btn"
                    id="discussionWidgetMembersBtn"
                    hidden
                    title="Group settings"
                    aria-label="Group settings">
                <i class="fa-solid fa-gear"></i>
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
                           placeholder="Search topics or groups"
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

        <section class="discussion-widget__panel" id="discussionWidgetGroupTopics" data-panel="groupTopics" hidden>
            <div class="discussion-widget__group-profile" id="discussionWidgetGroupProfile"></div>
            <div class="discussion-widget__group-topics-bar">
                <button type="button" class="discussion-widget__outline-btn" id="discussionWidgetGroupInfoBtn">
                    <i class="fa-solid fa-circle-info"></i>
                    Group info
                </button>
                <button type="button" class="discussion-widget__primary-btn" id="discussionWidgetNewGroupTopicBtn">
                    <i class="fa-solid fa-plus"></i>
                    New topic
                </button>
            </div>
            <div class="discussion-widget__scroll discussion-widget__scroll--list" id="discussionWidgetGroupTopicsList">
                <div class="discussion-widget__empty" id="discussionWidgetGroupTopicsEmpty" hidden>
                    <div class="discussion-widget__empty-icon"><i class="fa-solid fa-hashtag"></i></div>
                    <h4>No topics yet</h4>
                    <p>Start the first discussion in this group.</p>
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
                <div class="discussion-widget__emoji-picker" id="discussionWidgetReplyEmojiPicker" hidden>
                    <div class="discussion-widget__emoji-search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search"
                               class="discussion-widget__emoji-search-input"
                               placeholder="Search emoji"
                               autocomplete="off"
                               aria-label="Search emoji">
                    </div>
                    <div class="discussion-widget__emoji-grid" role="grid" aria-label="Emoji"></div>
                    <div class="discussion-widget__emoji-tabs" role="tablist" aria-label="Emoji categories"></div>
                </div>
                <div class="discussion-widget__composer-inner">
                    <button type="button"
                            class="discussion-widget__attach-btn discussion-widget__emoji-btn"
                            id="discussionWidgetReplyEmojiBtn"
                            title="Emoji"
                            aria-label="Emoji"
                            aria-expanded="false"
                            aria-controls="discussionWidgetReplyEmojiPicker">
                        <i class="fa-regular fa-face-smile"></i>
                    </button>
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
                <div class="discussion-composer-notice" id="discussionWidgetComposerNotice" hidden role="alert"></div>
                <div class="discussion-upload-progress" id="discussionWidgetUploadProgress" hidden aria-live="polite">
                    <div class="discussion-upload-progress__meta">
                        <span class="discussion-upload-progress__label">Uploading…</span>
                        <span class="discussion-upload-progress__percent">0%</span>
                    </div>
                    <div class="discussion-upload-progress__track">
                        <div class="discussion-upload-progress__bar"></div>
                    </div>
                </div>
            </form>
        </section>

        <section class="discussion-widget__panel" id="discussionWidgetGroupPick" data-panel="groupPick" hidden>
            @include('discussions.partials.group-member-picker', ['prefix' => 'discussionWidget'])
        </section>

        <section class="discussion-widget__panel" id="discussionWidgetCompose" data-panel="compose" hidden>
            <form class="discussion-widget__compose-form" id="discussionWidgetNewTopicForm" data-url="{{ route('discussions.store') }}" data-compose-mode="topic" enctype="multipart/form-data">
                @csrf
                @include('discussions.partials.new-chat-compose-fields', ['prefix' => 'discussionWidget', 'mode' => 'topic'])
                <button type="submit" class="discussion-widget__primary-btn">
                    <i class="fa-solid fa-hashtag"></i>
                    Create topic
                </button>
            </form>
        </section>

        <section class="discussion-widget__panel" id="discussionWidgetGroupCompose" data-panel="groupCompose" hidden>
            <form class="discussion-widget__compose-form" id="discussionWidgetNewGroupForm" data-url="{{ route('discussions.store') }}" data-compose-mode="group" enctype="multipart/form-data">
                @csrf
                @include('discussions.partials.new-chat-compose-fields', ['prefix' => 'discussionWidgetGroup', 'mode' => 'group'])
                <button type="submit" class="discussion-widget__primary-btn">
                    <i class="fa-solid fa-user-group"></i>
                    Create group
                </button>
            </form>
        </section>

        <section class="discussion-widget__panel" id="discussionWidgetGroupTopicCompose" data-panel="groupTopicCompose" hidden>
            <form class="discussion-widget__compose-form" id="discussionWidgetNewGroupTopicForm" data-url="{{ route('discussions.store') }}" data-compose-mode="group-topic" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_topic_id" id="discussionWidgetGroupTopicParentId" value="">
                @include('discussions.partials.new-chat-compose-fields', ['prefix' => 'discussionWidgetGroupTopic', 'mode' => 'topic'])
                <button type="submit" class="discussion-widget__primary-btn">
                    <i class="fa-solid fa-hashtag"></i>
                    Create topic
                </button>
            </form>
        </section>

        <aside class="discussion-widget__presence-sidebar" id="discussionWidgetPresenceSidebar" hidden aria-label="Online users and members">
            <div class="discussion-widget__presence-sidebar-inner" id="discussionWidgetPresenceSidebarInner"></div>
        </aside>

        <div class="discussion-widget__members-modal" id="discussionWidgetMembersModal" hidden>
            <div class="discussion-widget__members-card">
                <div class="discussion-widget__members-head">
                    <h3>Group settings</h3>
                    <button type="button" class="discussion-widget__icon-btn" id="discussionWidgetMembersCloseBtn" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="discussion-widget__members-photo" id="discussionWidgetMembersPhotoSection" hidden>
                    <div class="discussion-widget__members-photo-preview" id="discussionWidgetMembersPhotoPreview">
                        <span class="discussion-avatar discussion-avatar--icon discussion-avatar--group discussion-avatar--lg" aria-hidden="true">
                            <i class="fa-solid fa-users"></i>
                        </span>
                    </div>
                    <div class="discussion-widget__members-photo-actions">
                        <label class="discussion-widget__members-photo-btn" for="discussionWidgetMembersPhotoInput">
                            <i class="fa-solid fa-camera"></i>
                            <span id="discussionWidgetMembersPhotoActionLabel">Add group photo</span>
                        </label>
                        <input type="file"
                               id="discussionWidgetMembersPhotoInput"
                               class="visually-hidden"
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <button type="button"
                                class="discussion-widget__members-photo-remove"
                                id="discussionWidgetMembersPhotoRemoveBtn"
                                hidden>
                            Remove photo
                        </button>
                    </div>
                </div>
                <div class="discussion-widget__members-details" id="discussionWidgetMembersDetailsSection">
                    <div class="discussion-widget__members-details-view" id="discussionWidgetMembersDetailsView"></div>
                    <div class="discussion-widget__members-details-edit" id="discussionWidgetMembersDetailsEdit" hidden>
                        <label for="discussionWidgetMembersGroupTitle">Group name</label>
                        <input type="text"
                               id="discussionWidgetMembersGroupTitle"
                               class="discussion-widget__field-input"
                               maxlength="200"
                               autocomplete="off">
                        <label for="discussionWidgetMembersGroupDetails">Group details</label>
                        <textarea id="discussionWidgetMembersGroupDetails"
                                  class="discussion-widget__field-input"
                                  rows="3"
                                  maxlength="1000"
                                  placeholder="What is this group about?"></textarea>
                        <button type="button" class="discussion-widget__primary-btn" id="discussionWidgetMembersSaveDetailsBtn">
                            Save group details
                        </button>
                    </div>
                </div>
                <h4 class="discussion-widget__members-section-title">Members</h4>
                <div class="discussion-widget__members-list" id="discussionWidgetMembersList"></div>
                <div class="discussion-widget__members-pending" id="discussionWidgetMembersPendingSection" hidden>
                    <h4 class="discussion-widget__members-section-title">Pending invitations</h4>
                    <div class="discussion-widget__members-pending-list" id="discussionWidgetMembersPendingList"></div>
                </div>
                <div class="discussion-widget__members-add" id="discussionWidgetMembersAddSection" hidden>
                    <label class="discussion-widget__members-add-label" for="discussionWidgetMembersAddSearch">Invite by mobile number</label>
                    <div class="discussion-widget__member-search-wrap">
                        <div class="discussion-widget__member-search-field">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input type="search"
                                   id="discussionWidgetMembersAddSearch"
                                   class="discussion-widget__member-search-input"
                                   placeholder="Enter mobile number"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   aria-expanded="false"
                                   aria-controls="discussionWidgetMembersAddResults"
                                   aria-autocomplete="list"
                                   role="combobox">
                            <div class="discussion-widget__member-results" id="discussionWidgetMembersAddResults" hidden role="listbox"></div>
                        </div>
                    </div>
                    <div class="discussion-widget__member-chips" id="discussionWidgetMembersAddChips"></div>
                    <button type="button" class="discussion-widget__primary-btn discussion-widget__members-add-btn" id="discussionWidgetMembersAddBtn">
                        <i class="fa-solid fa-paper-plane"></i>
                        Send invitation
                    </button>
                </div>
                <div class="discussion-widget__members-footer" id="discussionWidgetMembersFooter">
                    <button type="button"
                            class="discussion-widget__outline-btn discussion-widget__outline-btn--danger"
                            id="discussionWidgetLeaveGroupBtn"
                            hidden>
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Leave group
                    </button>
                    <button type="button"
                            class="discussion-widget__danger-btn"
                            id="discussionWidgetDeleteGroupBtn"
                            hidden>
                        <i class="fa-solid fa-trash"></i>
                        Delete group
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

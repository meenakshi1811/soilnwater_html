(function () {
    // discussion-widget.js — membersUrl helper restored (2026-08-21)
    const config = window.soilnwaterDiscussion || {};
    const ui = () => window.soilnwaterDiscussionUi || {};

    const fab = document.getElementById('discussionFab');
    const widget = document.getElementById('discussionWidget');
    const isPageMode = Boolean(config.pageMode);

    if (!widget) {
        return;
    }

    if (!isPageMode && !fab) {
        return;
    }

    const panels = {
        topics: document.getElementById('discussionWidgetTopics'),
        groupTopics: document.getElementById('discussionWidgetGroupTopics'),
        thread: document.getElementById('discussionWidgetThread'),
        groupPick: document.getElementById('discussionWidgetGroupPick'),
        compose: document.getElementById('discussionWidgetCompose'),
        groupCompose: document.getElementById('discussionWidgetGroupCompose'),
        groupTopicCompose: document.getElementById('discussionWidgetGroupTopicCompose'),
    };

    const els = {
        title: document.getElementById('discussionWidgetTitle'),
        subtitle: document.getElementById('discussionWidgetSubtitle'),
        topicList: document.getElementById('discussionWidgetTopicList'),
        messages: document.getElementById('discussionWidgetMessages'),
        replyForm: document.getElementById('discussionWidgetReplyForm'),
        replyBody: document.getElementById('discussionWidgetReplyBody'),
        newTopicForm: document.getElementById('discussionWidgetNewTopicForm'),
        newGroupForm: document.getElementById('discussionWidgetNewGroupForm'),
        newGroupTopicForm: document.getElementById('discussionWidgetNewGroupTopicForm'),
        groupTopicsList: document.getElementById('discussionWidgetGroupTopicsList'),
        groupProfile: document.getElementById('discussionWidgetGroupProfile'),
        groupInfoBtn: document.getElementById('discussionWidgetGroupInfoBtn'),
        newGroupTopicBtn: document.getElementById('discussionWidgetNewGroupTopicBtn'),
        groupTopicParentInput: document.getElementById('discussionWidgetGroupTopicParentId'),
        membersDetailsView: document.getElementById('discussionWidgetMembersDetailsView'),
        membersDetailsEdit: document.getElementById('discussionWidgetMembersDetailsEdit'),
        membersGroupTitle: document.getElementById('discussionWidgetMembersGroupTitle'),
        membersGroupDetails: document.getElementById('discussionWidgetMembersGroupDetails'),
        membersSaveDetailsBtn: document.getElementById('discussionWidgetMembersSaveDetailsBtn'),
        leaveGroupBtn: document.getElementById('discussionWidgetLeaveGroupBtn'),
        deleteGroupBtn: document.getElementById('discussionWidgetDeleteGroupBtn'),
        pinBtn: document.getElementById('discussionWidgetPinBtn'),
        membersBtn: document.getElementById('discussionWidgetMembersBtn'),
        membersModal: document.getElementById('discussionWidgetMembersModal'),
        membersList: document.getElementById('discussionWidgetMembersList'),
        membersPhotoSection: document.getElementById('discussionWidgetMembersPhotoSection'),
        membersPhotoPreview: document.getElementById('discussionWidgetMembersPhotoPreview'),
        membersPhotoInput: document.getElementById('discussionWidgetMembersPhotoInput'),
        membersPhotoRemoveBtn: document.getElementById('discussionWidgetMembersPhotoRemoveBtn'),
        membersPhotoActionLabel: document.getElementById('discussionWidgetMembersPhotoActionLabel'),
        membersAddSection: document.getElementById('discussionWidgetMembersAddSection'),
        membersAddBtn: document.getElementById('discussionWidgetMembersAddBtn'),
        membersPendingSection: document.getElementById('discussionWidgetMembersPendingSection'),
        membersPendingList: document.getElementById('discussionWidgetMembersPendingList'),
        membersCloseBtn: document.getElementById('discussionWidgetMembersCloseBtn'),
        newTopicBtn: document.getElementById('discussionWidgetNewTopicBtn'),
        newGroupBtn: document.getElementById('discussionWidgetNewGroupBtn'),
        closeBtn: document.getElementById('discussionWidgetCloseBtn'),
        backBtn: document.getElementById('discussionWidgetBackBtn'),
        headerAvatar: document.getElementById('discussionWidgetHeaderAvatar'),
        sizeBtn: document.getElementById('discussionWidgetSizeBtn'),
        fullPageBtn: document.getElementById('discussionWidgetFullPageBtn'),
        online: document.getElementById('discussionWidgetOnline'),
        presenceSidebar: document.getElementById('discussionWidgetPresenceSidebar'),
        presenceSidebarInner: document.getElementById('discussionWidgetPresenceSidebarInner'),
    };

    const WIDGET_SIZE_ORDER = ['default', 'md', 'lg', 'xl'];
    const WIDGET_SIZE_LABELS = {
        default: 'Increase popup size',
        md: 'Increase popup size',
        lg: 'Increase popup size',
        xl: 'Reset popup size',
    };
    const WIDGET_SIZE_STORAGE_KEY = 'soilnwaterDiscussionWidgetSize';

    const reactionIcons = config.reactionIcons || {
        Like: 'fa-thumbs-up',
        Love: 'fa-heart',
        Insightful: 'fa-lightbulb',
        Agree: 'fa-check',
    };

    const fabBadge = document.getElementById('discussionFabBadge');

    function chatTriggers() {
        return [fab].filter(Boolean);
    }

    const BANNER_CHAT_DOCK_MARGIN = () => (isMobileViewport() ? 16 : 24);
    const BANNER_CHAT_OVERLAP = 20;
    const WIDGET_ANCHOR_GAP = 14;
    const WIDGET_VIEWPORT_MARGIN = 16;
    const WIDGET_MIN_HEIGHT = 280;
    const MOBILE_BREAKPOINT = 768;

    function isMobileViewport() {
        return window.innerWidth < MOBILE_BREAKPOINT;
    }

    let activePanelName = 'topics';

    function findPageBanner() {
        return document.querySelector('.hero')
            || document.querySelector('.vendor-store-hero')
            || document.querySelector('.vendor-store-page-hero')
            || document.querySelector('section.mb-4:has(img.w-100)')
            || document.querySelector('.premium-listing-cta');
    }

    function clearWidgetAnchorStyles() {
        if (!widget) {
            return;
        }

        widget.style.right = '';
        widget.style.top = '';
        widget.style.bottom = '';
        widget.style.height = '';
        widget.style.maxHeight = '';
    }

    function syncWidgetAnchor() {
        if (!fab || !widget || isPageMode) {
            return;
        }

        if (isMobileViewport()) {
            clearWidgetAnchorStyles();
            return;
        }

        const rect = fab.getBoundingClientRect();
        const sideMargin = BANNER_CHAT_DOCK_MARGIN();
        const viewportMargin = WIDGET_VIEWPORT_MARGIN;
        const docked = fab.classList.contains('discussion-banner-chat--docked');

        // When docked (or chat is open), use the same full-height layout as opening from the bottom button.
        if (docked || widget.classList.contains('is-open')) {
            const fabSpace = Math.max(sideMargin, window.innerHeight - rect.top + WIDGET_ANCHOR_GAP);
            const preferredHeight = Math.min(680, window.innerHeight - fabSpace - viewportMargin);

            widget.style.right = `${sideMargin}px`;
            widget.style.top = '';
            widget.style.bottom = `${fabSpace}px`;
            widget.style.height = '';
            widget.style.maxHeight = `${Math.max(WIDGET_MIN_HEIGHT, preferredHeight)}px`;
            return;
        }

        const spaceAbove = rect.top - WIDGET_ANCHOR_GAP - viewportMargin;
        const spaceBelow = window.innerHeight - rect.bottom - WIDGET_ANCHOR_GAP - viewportMargin;
        const viewportCap = window.innerHeight - (viewportMargin * 2);
        const openAbove = spaceAbove >= spaceBelow && spaceAbove >= WIDGET_MIN_HEIGHT;

        widget.style.right = `${Math.max(sideMargin, window.innerWidth - rect.right)}px`;

        if (openAbove) {
            widget.style.top = '';
            widget.style.bottom = `${Math.max(sideMargin, window.innerHeight - rect.top + WIDGET_ANCHOR_GAP)}px`;
        } else {
            widget.style.bottom = '';
            widget.style.top = `${Math.max(viewportMargin, rect.bottom + WIDGET_ANCHOR_GAP)}px`;
        }

        const availableHeight = openAbove ? spaceAbove : spaceBelow;
        const clampedHeight = Math.max(
            Math.min(WIDGET_MIN_HEIGHT, availableHeight),
            Math.min(availableHeight, viewportCap)
        );

        widget.style.maxHeight = `${clampedHeight}px`;

        const currentHeight = widget.getBoundingClientRect().height;
        if (currentHeight > 0 && clampedHeight < currentHeight - 1) {
            widget.style.height = `${clampedHeight}px`;
        } else {
            widget.style.height = '';
        }
    }

    function dockFabToCorner() {
        if (!fab) {
            return;
        }

        fab.classList.remove('discussion-banner-chat--on-banner');
        fab.classList.add('discussion-banner-chat--docked');
        fab.style.left = 'auto';
        fab.style.top = 'auto';
        fab.style.right = '';
        fab.style.bottom = '';
    }

    function resetFabState() {
        if (!fab || isPageMode) {
            return;
        }

        const widgetOpen = Boolean(widget?.classList.contains('is-open') && !widget.hidden);
        fab.classList.toggle('is-open', widgetOpen);
        fab.setAttribute('aria-expanded', widgetOpen ? 'true' : 'false');
    }

    function syncBannerChatPosition() {
        if (!fab || isPageMode) {
            return;
        }

        if (isMobileViewport()) {
            dockFabToCorner();
            syncWidgetAnchor();
            return;
        }

        // Keep the close button docked at the bottom while chat is open so height stays full.
        if (widget?.classList.contains('is-open')) {
            dockFabToCorner();
            syncWidgetAnchor();
            return;
        }

        const margin = BANNER_CHAT_DOCK_MARGIN();
        const banner = findPageBanner();

        fab.classList.remove('discussion-banner-chat--docked', 'discussion-banner-chat--on-banner');
        fab.style.left = 'auto';

        if (!banner) {
            dockFabToCorner();
            syncWidgetAnchor();
            return;
        }

        const rect = banner.getBoundingClientRect();

        if (rect.bottom <= BANNER_CHAT_OVERLAP) {
            dockFabToCorner();
        } else {
            fab.classList.add('discussion-banner-chat--on-banner');
            fab.style.right = `${margin}px`;
            fab.style.bottom = 'auto';
            fab.style.top = `${Math.max(margin, rect.bottom - BANNER_CHAT_OVERLAP)}px`;
        }

        syncWidgetAnchor();
    }

    function initBannerChatPosition() {
        if (!fab || isPageMode) {
            return;
        }

        syncBannerChatPosition();

        let rafId = 0;
        const scheduleSync = () => {
            if (rafId) {
                return;
            }

            rafId = window.requestAnimationFrame(() => {
                rafId = 0;
                syncBannerChatPosition();
            });
        };

        window.addEventListener('scroll', scheduleSync, { passive: true });
        window.addEventListener('resize', scheduleSync);
    }

    let topicsLoaded = false;
    let currentTopic = null;
    let currentGroup = null;
    let openRequestId = 0;
    const modalMemberSelection = new Map();
    const modalGroupMemberIds = new Set();
    const modalPendingInviteeIds = new Set();
    const modalPendingInviteePhones = new Set();
    let canManageMembersModal = false;
    let canLeaveGroupModal = false;
    let canDeleteGroupModal = false;
    let memberSearchTimer = null;
    let widgetComposeController = null;
    let widgetGroupComposeController = null;
    let widgetGroupPicker = null;
    let composeMode = 'topic';

    function isComposePanel(name) {
        return name === 'compose' || name === 'groupCompose' || name === 'groupTopicCompose';
    }
    const attachmentHelpersRef = () => window.soilnwaterDiscussionAttachments || {};
    let replyAttachmentPool = null;
    let replyEmojiPicker = null;
    let onlinePollTimer = null;
    let onlineTopicId = null;
    let currentPresenceMembers = [];

    function csrfToken() {
        return config.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }

    function notify(type, message) {
        try {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
            }
        } catch (error) {
            // fall through
        }

        showComposerNotice(type, message);

        if (type === 'error') {
            console.error(message);
        }
    }

    function showComposerNotice(type, message) {
        const notice = document.getElementById('discussionWidgetComposerNotice');
        if (!notice || !message) {
            return;
        }

        notice.hidden = false;
        notice.className = `discussion-composer-notice discussion-composer-notice--${type === 'error' ? 'error' : 'success'}`;
        notice.textContent = message;

        window.clearTimeout(showComposerNotice._timer);
        showComposerNotice._timer = window.setTimeout(() => {
            notice.hidden = true;
            notice.textContent = '';
        }, type === 'error' ? 8000 : 4000);
    }

    async function sendFormDataWithUpload(url, formData, progressEl, callbacks = {}) {
        const helpers = attachmentHelpersRef();
        const hasUploadProgress = formData instanceof FormData
            && Array.from(formData.entries()).some(([key, value]) => key.startsWith('attachments') && value instanceof File);
        const onProgress = callbacks.onProgress;

        if (hasUploadProgress && typeof helpers.uploadFormData === 'function') {
            if (progressEl) {
                helpers.showUploadProgress?.(progressEl, 'Uploading attachments…', 0);
            }

            return helpers.uploadFormData(url, formData, {
                csrfToken: csrfToken(),
                onProgress(percent, loaded, total, phase) {
                    if (typeof onProgress === 'function') {
                        onProgress(percent, loaded, total, phase);
                        return;
                    }

                    const label = phase === 'processing'
                        ? 'Sending message…'
                        : phase === 'done'
                            ? 'Sent'
                            : 'Uploading attachments…';
                    helpers.showUploadProgress?.(progressEl, label, Math.min(percent, 100));
                },
            });
        }

        return requestFormData(url, formData);
    }

    async function confirmDestructiveAction(options = {}) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            const result = await window.Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || '',
                icon: options.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: options.confirmText || 'Confirm',
                cancelButtonText: options.cancelText || 'Cancel',
                confirmButtonColor: options.confirmColor || '#d9534f',
                reverseButtons: true,
            });

            return Boolean(result.isConfirmed);
        }

        const message = [options.title, options.text].filter(Boolean).join('\n\n');

        return window.confirm(message || 'Are you sure?');
    }

    async function requestFormJson(url, formData, method = 'POST') {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                || data.message
                || 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                || data.message
                || 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function avatarInitials(name) {
        const parts = String(name || 'M').trim().split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }

        return (parts[0]?.[0] || 'M').toUpperCase();
    }

    function avatarHtml(name, extraClass = '') {
        return `<span class="discussion-avatar ${extraClass}" aria-hidden="true">${escapeHtml(avatarInitials(name))}</span>`;
    }

    function canAccessTopic(topic) {
        if (!topic?.is_group) {
            return true;
        }

        const userId = Number(config.currentUserId);
        if (Number(topic.author?.id) === userId) {
            return true;
        }

        return (topic.member_ids || []).map(Number).includes(userId);
    }

    function groupAvatarHtml(topic, extraClass = '') {
        const imageUrl = topic?.group_image_url;
        if (imageUrl) {
            return `<span class="discussion-avatar discussion-avatar--photo ${extraClass}" aria-hidden="true"><img src="${escapeHtml(imageUrl)}" alt=""></span>`;
        }

        return `<span class="discussion-avatar discussion-avatar--icon discussion-avatar--group ${extraClass}" aria-hidden="true"><i class="fa-solid fa-users"></i></span>`;
    }

    function topicListAvatarHtml(topic) {
        if (topic?.is_group) {
            return groupAvatarHtml(topic);
        }

        return '<span class="discussion-avatar discussion-avatar--icon discussion-avatar--topic" aria-hidden="true"><i class="fa-solid fa-hashtag"></i></span>';
    }

    function topicSubtitle(topic) {
        if (topic?.is_group && !topic?.parent_topic_id) {
            const topicCount = topic.children_count ?? topic.children?.length ?? 0;
            const memberCount = topic.members_count ?? topic.members?.length ?? topic.member_ids?.length ?? 0;

            return `${topicCount} topics · ${memberCount} members`;
        }

        const messageCount = `${(topic.replies_count || 0) + 1} messages`;

        return messageCount;
    }

    function topicOnlineUrl(topicId) {
        return (config.routes?.topicOnlineTemplate || '/discussions/__TOPIC__/online')
            .replace('__TOPIC__', String(topicId));
    }

    function onlineUserLabel(users = [], context = 'participants') {
        if (!users.length) {
            return '';
        }

        const names = users.map((user) => user.name).filter(Boolean);
        const noun = context === 'members' ? 'member' : 'participant';

        if (names.length === 1) {
            return `${names[0]} online`;
        }

        if (names.length === 2) {
            return `${names[0]} and ${names[1]} online`;
        }

        if (names.length === 3) {
            return `${names[0]}, ${names[1]}, and ${names[2]} online`;
        }

        return `${names.length} ${noun}s online`;
    }

    function buildOnlineAvatars(users = []) {
        return users.slice(0, 4).map((user) => {
            const initials = escapeHtml(user.initials || avatarInitials(user.name || '?'));

            return `<span class="discussion-widget__online-avatar" title="${escapeHtml(user.name || 'Online user')}">${initials}</span>`;
        }).join('');
    }

    function dedupeOnlineUsers(users = []) {
        const seen = new Set();

        return users.filter((user) => {
            const id = Number(user?.id);

            if (!id || seen.has(id)) {
                return false;
            }

            seen.add(id);

            return true;
        });
    }

    function buildPresenceSidebarItem(user, isOnline = false, roleLabel = '') {
        const initials = escapeHtml(user.initials || avatarInitials(user.name || '?'));
        const statusText = isOnline
            ? (roleLabel ? `Online · ${roleLabel}` : 'Online')
            : (roleLabel || 'Offline');

        return `<div class="discussion-widget__presence-item ${isOnline ? 'is-online' : ''}">
            <span class="discussion-widget__presence-item-avatar">
                ${initials}
                ${isOnline ? '<span class="discussion-widget__online-dot" aria-hidden="true"></span>' : ''}
            </span>
            <div class="discussion-widget__presence-item-body">
                <strong>${escapeHtml(user.name || 'User')}</strong>
                <span>${escapeHtml(statusText)}</span>
            </div>
        </div>`;
    }

    function hidePresenceSidebar() {
        if (els.presenceSidebar) {
            els.presenceSidebar.hidden = true;
        }

        if (els.presenceSidebarInner) {
            els.presenceSidebarInner.innerHTML = '';
        }

        widget.classList.remove('discussion-widget--presence-open');
    }

    function renderPresenceSidebar({ onlineUsers = [], members = [], context = 'participants' } = {}) {
        if (!isPageMode || !els.presenceSidebar || !els.presenceSidebarInner) {
            return;
        }

        const uniqueOnline = dedupeOnlineUsers(onlineUsers);
        const hasMembers = members.length > 0;
        const hasOnline = uniqueOnline.length > 0;

        if (!hasMembers && !hasOnline) {
            hidePresenceSidebar();

            return;
        }

        widget.classList.add('discussion-widget--presence-open');
        els.presenceSidebar.hidden = false;

        let html = '';

        if (hasOnline) {
            html += `<section class="discussion-widget__presence-section">
                <h3 class="discussion-widget__presence-section-title">Online now</h3>
                <div class="discussion-widget__presence-list">
                    ${uniqueOnline.map((user) => buildPresenceSidebarItem(user, true)).join('')}
                </div>
            </section>`;
        } else if (hasMembers) {
            html += `<section class="discussion-widget__presence-section">
                <h3 class="discussion-widget__presence-section-title">Online now</h3>
                <p class="discussion-widget__presence-empty">No ${context === 'members' ? 'members' : 'participants'} online right now.</p>
            </section>`;
        }

        if (hasMembers) {
            html += `<section class="discussion-widget__presence-section">
                <h3 class="discussion-widget__presence-section-title">Members</h3>
                <div class="discussion-widget__presence-list">
                    ${members.map((member) => buildPresenceSidebarItem(
                        member,
                        Boolean(member.is_online),
                        member.is_owner ? 'Creator' : 'Member'
                    )).join('')}
                </div>
            </section>`;
        }

        els.presenceSidebarInner.innerHTML = html;
    }

    function renderOnlinePresence(onlineUsers = [], context = 'participants', members = []) {
        const uniqueOnline = dedupeOnlineUsers(onlineUsers);
        currentPresenceMembers = members;

        if (isPageMode) {
            if (els.online) {
                els.online.hidden = true;
                els.online.innerHTML = '';
            }

            renderPresenceSidebar({
                onlineUsers: uniqueOnline,
                members,
                context,
            });

            return;
        }

        hidePresenceSidebar();

        if (!els.online) {
            return;
        }

        if (!uniqueOnline.length) {
            els.online.hidden = true;
            els.online.innerHTML = '';

            return;
        }

        els.online.hidden = false;
        els.online.innerHTML = `<span class="discussion-widget__online-dot" aria-hidden="true"></span>
            <span class="discussion-widget__online-avatars">${buildOnlineAvatars(uniqueOnline)}</span>
            <span class="discussion-widget__online-text">${escapeHtml(onlineUserLabel(uniqueOnline, context))}</span>`;
    }

    function clearOnlinePresence() {
        onlineTopicId = null;
        currentPresenceMembers = [];

        if (onlinePollTimer) {
            window.clearInterval(onlinePollTimer);
            onlinePollTimer = null;
        }

        if (els.online) {
            els.online.hidden = true;
            els.online.innerHTML = '';
        }

        hidePresenceSidebar();
    }

    function startOnlinePolling(topicId, context = 'participants') {
        if (!topicId) {
            clearOnlinePresence();

            return;
        }

        onlineTopicId = Number(topicId);

        if (onlinePollTimer) {
            window.clearInterval(onlinePollTimer);
        }

        onlinePollTimer = window.setInterval(async () => {
            if (!onlineTopicId || Number(currentTopic?.id) !== onlineTopicId && Number(currentGroup?.id) !== onlineTopicId) {
                return;
            }

            try {
                const data = await requestJson(topicOnlineUrl(onlineTopicId));
                const members = data.members || currentPresenceMembers;
                renderOnlinePresence(data.online_users || [], data.context || context, members);

                if (currentGroup?.is_group && !currentGroup?.parent_topic_id && Array.isArray(data.members)) {
                    currentGroup.members = data.members;
                    renderGroupProfile(currentGroup, isPageMode ? [] : (data.online_users || []));
                    if (!els.membersModal?.hidden) {
                        renderMembersList(data.members, canManageMembersModal);
                    }
                }
            } catch (error) {
                // Ignore polling errors silently.
            }
        }, 45000);
    }

    function applyOnlinePresence(topic, data = {}) {
        const onlineUsers = dedupeOnlineUsers(data.online_users || topic.online_users || []);
        const members = data.members || topic.members || currentPresenceMembers || [];
        const context = topic?.is_group && !topic?.parent_topic_id || topic?.parent_topic_id
            ? 'members'
            : 'participants';

        renderOnlinePresence(onlineUsers, context, members);
        startOnlinePolling(topic.id, context);

        if (topic?.is_group && !topic?.parent_topic_id) {
            if (Array.isArray(data.members)) {
                topic.members = data.members;
            }

            renderGroupProfile(topic, isPageMode ? [] : onlineUsers);
        }
    }

    function buildGroupOnlineSection(onlineUsers = []) {
        if (!onlineUsers.length) {
            return '';
        }

        return `<div class="discussion-widget__group-online">
            <span class="discussion-widget__group-online-label">Online now</span>
            <div class="discussion-widget__group-online-list">
                ${onlineUsers.map((user) => `<span class="discussion-widget__group-online-chip">
                    <span class="discussion-widget__online-dot" aria-hidden="true"></span>
                    <span>${escapeHtml(user.name)}</span>
                </span>`).join('')}
            </div>
        </div>`;
    }

    function setHeaderTopicAvatar(topic) {
        if (!els.headerAvatar) {
            return;
        }

        if (topic?.is_group) {
            if (topic.group_image_url) {
                els.headerAvatar.className = 'discussion-widget__brand-mark discussion-avatar discussion-avatar--photo';
                els.headerAvatar.innerHTML = `<img src="${escapeHtml(topic.group_image_url)}" alt="">`;
            } else {
                els.headerAvatar.className = 'discussion-widget__brand-mark discussion-avatar discussion-avatar--icon discussion-avatar--group';
                els.headerAvatar.innerHTML = '<i class="fa-solid fa-users"></i>';
            }

            return;
        }

        if (topic && topic.is_group === false) {
            els.headerAvatar.className = 'discussion-widget__brand-mark discussion-avatar discussion-avatar--icon discussion-avatar--topic';
            els.headerAvatar.innerHTML = '<i class="fa-solid fa-hashtag"></i>';

            return;
        }

        setHeaderAvatar(topic?.author?.name || topic?.title || 'Member');
    }

    function updateMembersButton(topic = currentGroup || currentTopic) {
        if (!els.membersBtn) {
            return;
        }

        const group = topic?.is_group && !topic?.parent_topic_id ? topic : null;
        const show = Boolean(group?.can_manage_members);
        els.membersBtn.hidden = !show;
        els.membersBtn.dataset.topicId = group?.id ? String(group.id) : '';
    }

    function normalizePhoneDigits(value) {
        return window.soilnwaterDiscussionCompose?.normalizePhoneDigits?.(value) ?? null;
    }

    function selectionMapKey(entry) {
        return window.soilnwaterDiscussionCompose?.selectionMapKey?.(entry) ?? String(entry?.id || '');
    }

    function deleteSelectionEntry(selectionMap, key) {
        window.soilnwaterDiscussionCompose?.deleteSelectionEntry?.(selectionMap, key);
    }

    function buildMemberInvitePayload(memberSelection) {
        return window.soilnwaterDiscussionCompose?.buildMemberInvitePayload?.(memberSelection) || {
            member_ids: Array.from(memberSelection.keys()).filter((key) => !String(key).startsWith('phone:')).map(Number),
            phone_numbers: [],
        };
    }

    function renderMemberChips(containerId, selectionMap) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        container.innerHTML = Array.from(selectionMap.values()).map((entry) => {
            const key = selectionMapKey(entry);

            return `<span class="discussion-widget__member-chip" data-selection-key="${escapeHtml(key)}">
                ${escapeHtml(entry.name)}
                <button type="button" aria-label="Remove ${escapeHtml(entry.name)}">&times;</button>
            </span>`;
        }).join('');
    }

    function bindMemberChipRemoval(containerId, selectionMap) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        container.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            const chip = event.target.closest('.discussion-widget__member-chip');
            if (!button || !chip) {
                return;
            }

            deleteSelectionEntry(selectionMap, chip.dataset.selectionKey);
            renderMemberChips(containerId, selectionMap);
        });
    }

    async function searchUsers(query) {
        if (!config.routes?.usersSearch) {
            return [];
        }

        const url = new URL(config.routes.usersSearch, window.location.origin);
        url.searchParams.set('q', query);

        const data = await requestJson(url.toString(), { method: 'GET' });

        return data.users || [];
    }

    function renderMemberSearchResults(resultsEl, users, selectionMap, chipsContainerId, inputEl, query = '', options = {}) {
        if (!resultsEl) {
            return;
        }

        if (inputEl) {
            inputEl.setAttribute('aria-expanded', users.length ? 'true' : 'false');
        }

        if (!users.length) {
            resultsEl.hidden = false;
            const phone = normalizePhoneDigits(query);

            if (phone) {
                const excludePhones = options.getExcludePhones?.() || new Set();

                if (excludePhones.has(phone)) {
                    resultsEl.innerHTML = `<div class="discussion-widget__member-results-empty">An invitation is already pending for ${escapeHtml(phone)}.</div>`;

                    return;
                }

                const key = `phone:${phone}`;
                const selected = selectionMap.has(key);

                resultsEl.innerHTML = `<button type="button" class="discussion-widget__member-result ${selected ? 'is-selected' : ''}" data-phone-invite="${escapeHtml(phone)}" ${selected ? 'disabled' : ''} role="option">
                    <strong>Invite ${escapeHtml(phone)}</strong>
                    <span>Not registered — send SMS invitation</span>
                </button>`;

                resultsEl.querySelector('[data-phone-invite]:not([disabled])')?.addEventListener('click', (event) => {
                    const invitePhone = event.currentTarget.dataset.phoneInvite;
                    selectionMap.set(`phone:${invitePhone}`, {
                        phone: invitePhone,
                        name: invitePhone,
                    });
                    renderMemberChips(chipsContainerId, selectionMap);
                    resultsEl.hidden = true;
                    resultsEl.innerHTML = '';
                    if (inputEl) {
                        inputEl.value = '';
                        inputEl.setAttribute('aria-expanded', 'false');
                    }
                });

                return;
            }

            resultsEl.innerHTML = `<div class="discussion-widget__member-results-empty">${query ? 'No member found for that number.' : 'Enter a mobile number to find a member.'}</div>`;

            return;
        }

        resultsEl.hidden = false;
        resultsEl.innerHTML = users.map((user) => {
            const selected = selectionMap.has(Number(user.id));

            return `<button type="button" class="discussion-widget__member-result ${selected ? 'is-selected' : ''}" data-user-id="${user.id}" data-user-name="${escapeHtml(user.name)}" ${selected ? 'disabled' : ''} role="option">
                <strong>${escapeHtml(user.name)}</strong>
                <span>${escapeHtml(user.phone || user.email || '')}</span>
            </button>`;
        }).join('');

        resultsEl.querySelectorAll('.discussion-widget__member-result:not([disabled])').forEach((button) => {
            button.addEventListener('click', () => {
                selectionMap.set(Number(button.dataset.userId), {
                    id: Number(button.dataset.userId),
                    name: button.dataset.userName,
                });
                renderMemberChips(chipsContainerId, selectionMap);
                resultsEl.hidden = true;
                resultsEl.innerHTML = '';
                if (inputEl) {
                    inputEl.value = '';
                    inputEl.setAttribute('aria-expanded', 'false');
                }
            });
        });
    }

    function hideMemberSearchResults(resultsEl, inputEl) {
        if (!resultsEl) {
            return;
        }

        resultsEl.hidden = true;
        resultsEl.innerHTML = '';

        if (inputEl) {
            inputEl.setAttribute('aria-expanded', 'false');
        }
    }

    async function loadMemberSearchResults(input, resultsEl, selectionMap, chipsContainerId, options = {}) {
        const query = input.value.trim();
        const digits = query.replace(/\D+/g, '');
        const excludeIds = options.getExcludeIds?.() || new Set();

        if (digits.length < 8) {
            if (!digits.length) {
                hideMemberSearchResults(resultsEl, input);
                return;
            }

            renderMemberSearchResults(resultsEl, [], selectionMap, chipsContainerId, input, 'incomplete', options);
            if (resultsEl) {
                resultsEl.hidden = false;
                resultsEl.innerHTML = '<div class="discussion-widget__member-results-empty">Enter a complete mobile number to search.</div>';
            }
            return;
        }

        try {
            let users = await searchUsers(query);
            users = users.filter((user) => !excludeIds.has(Number(user.id)));
            renderMemberSearchResults(resultsEl, users, selectionMap, chipsContainerId, input, query, options);
        } catch (error) {
            notify('error', error.message);
        }
    }

    function bindMemberSearchInput(inputId, resultsId, selectionMap, chipsContainerId, options = {}) {
        const input = document.getElementById(inputId);
        const resultsEl = document.getElementById(resultsId);
        if (!input || !resultsEl) {
            return;
        }

        const openResults = () => {
            clearTimeout(memberSearchTimer);
            memberSearchTimer = window.setTimeout(() => {
                loadMemberSearchResults(input, resultsEl, selectionMap, chipsContainerId, options);
            }, options.debounceMs ?? 150);
        };

        input.addEventListener('focus', () => {
            if (input.value.replace(/\D+/g, '').length >= 8) {
                openResults();
            }
        });
        input.addEventListener('click', () => {
            if (input.value.replace(/\D+/g, '').length >= 8) {
                openResults();
            }
        });

        input.addEventListener('input', () => {
            clearTimeout(memberSearchTimer);
            memberSearchTimer = window.setTimeout(() => {
                loadMemberSearchResults(input, resultsEl, selectionMap, chipsContainerId, options);
            }, 250);
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hideMemberSearchResults(resultsEl, input);
            }
        });

        document.addEventListener('click', (event) => {
            const wrap = input.closest('.discussion-widget__member-search-wrap');
            if (wrap?.contains(event.target)) {
                return;
            }

            hideMemberSearchResults(resultsEl, input);
        });
    }

    function invitationAcceptUrl(invitationId) {
        return (config.routes?.invitationAcceptTemplate || '/discussions/invitations/__INVITATION__/accept')
            .replace('__INVITATION__', String(invitationId));
    }

    function invitationRejectUrl(invitationId) {
        return (config.routes?.invitationRejectTemplate || '/discussions/invitations/__INVITATION__/reject')
            .replace('__INVITATION__', String(invitationId));
    }

    function membersUrl(topicId) {
        return (config.routes?.topicMembersTemplate || '/discussions/__TOPIC__/members')
            .replace('__TOPIC__', String(topicId));
    }

    function membersStoreUrl(topicId) {
        return (config.routes?.topicMembersStoreTemplate || '/discussions/__TOPIC__/members')
            .replace('__TOPIC__', String(topicId));
    }

    function membersDestroyUrl(topicId, memberId) {
        return (config.routes?.topicMembersDestroyTemplate || '/discussions/__TOPIC__/members/__MEMBER__')
            .replace('__TOPIC__', String(topicId))
            .replace('__MEMBER__', String(memberId));
    }

    function groupImageUpdateUrl(topicId) {
        return (config.routes?.topicGroupImageUpdateTemplate || '/discussions/__TOPIC__/group-image')
            .replace('__TOPIC__', String(topicId));
    }

    function groupImageDestroyUrl(topicId) {
        return (config.routes?.topicGroupImageDestroyTemplate || '/discussions/__TOPIC__/group-image')
            .replace('__TOPIC__', String(topicId));
    }

    function groupSettingsUpdateUrl(topicId) {
        return (config.routes?.topicGroupSettingsUpdateTemplate || '/discussions/__TOPIC__/group-settings')
            .replace('__TOPIC__', String(topicId));
    }

    function groupDestroyUrl(topicId) {
        return (config.routes?.topicGroupDestroyTemplate || '/discussions/__TOPIC__/group')
            .replace('__TOPIC__', String(topicId));
    }

    function groupLeaveUrl(topicId) {
        return (config.routes?.topicLeaveTemplate || '/discussions/__TOPIC__/leave')
            .replace('__TOPIC__', String(topicId));
    }

    function removeTopicCard(topicId) {
        document.getElementById(`discussion-widget-topic-${topicId}`)?.remove();
        setTopicUnread(topicId, 0);
    }

    function handleGroupRemoved(groupId, message) {
        closeMembersModal();
        removeTopicCard(groupId);
        currentGroup = null;
        currentTopic = null;
        config.topicId = null;
        topicsLoaded = false;
        showTopics();
        notify('success', message);
    }

    function groupPhotoPreviewHtml(imageUrl = null) {
        if (imageUrl) {
            return `<span class="discussion-avatar discussion-avatar--photo discussion-avatar--lg"><img src="${escapeHtml(imageUrl)}" alt=""></span>`;
        }

        return '<span class="discussion-avatar discussion-avatar--icon discussion-avatar--group discussion-avatar--lg" aria-hidden="true"><i class="fa-solid fa-users"></i></span>';
    }

    function renderMembersPhotoPreview(imageUrl = null) {
        if (!els.membersPhotoPreview) {
            return;
        }

        els.membersPhotoPreview.innerHTML = groupPhotoPreviewHtml(imageUrl);

        if (els.membersPhotoRemoveBtn) {
            els.membersPhotoRemoveBtn.hidden = !imageUrl;
        }

        if (els.membersPhotoActionLabel) {
            els.membersPhotoActionLabel.textContent = imageUrl ? 'Change photo' : 'Add group photo';
        }
    }

    function syncTopicGroupSettings(topicPayload = {}) {
        const targetId = Number(topicPayload.id);
        const appliesToGroup = currentGroup && Number(currentGroup.id) === targetId;
        const appliesToTopic = currentTopic && Number(currentTopic.id) === targetId;

        if (appliesToGroup) {
            currentGroup = {
                ...currentGroup,
                ...topicPayload,
                group_image_url: topicPayload.group_image_url ?? currentGroup.group_image_url ?? null,
            };
            currentTopic = currentGroup;
            renderGroupProfile(currentGroup);
            setHeaderTitle(currentGroup.title, topicSubtitle(currentGroup));
            setHeaderTopicAvatar(currentGroup);
        } else if (appliesToTopic) {
            currentTopic = {
                ...currentTopic,
                ...topicPayload,
                group_image_url: topicPayload.group_image_url ?? currentTopic.group_image_url ?? null,
            };
        }

        const listTopic = appliesToGroup ? currentGroup : (appliesToTopic ? currentTopic : null);
        if (!listTopic) {
            return;
        }

        const card = document.getElementById(`discussion-widget-topic-${listTopic.id}`);
        if (card) {
            const avatar = card.querySelector('.discussion-avatar, .discussion-avatar--photo, .discussion-avatar--group');
            if (avatar) {
                avatar.outerHTML = topicListAvatarHtml(listTopic);
            }
        }
    }

    function renderGroupProfile(group, onlineUsers = group?.online_users || []) {
        if (!els.groupProfile || !group) {
            return;
        }

        const details = group.body
            ? escapeHtml(group.body)
            : '<span class="discussion-widget__group-profile-empty">No group details added yet.</span>';

        const onlineSection = buildGroupOnlineSection(onlineUsers);

        els.groupProfile.innerHTML = `<div class="discussion-widget__group-profile-inner">
            ${groupAvatarHtml(group, 'discussion-avatar--lg')}
            <div class="discussion-widget__group-profile-text">
                <strong>${escapeHtml(group.title)}</strong>
                <p>${details}</p>
            </div>
        </div>${onlineSection}`;
    }

    function renderMembersDetails(group, canManage = canManageMembersModal) {
        if (els.membersDetailsEdit) {
            els.membersDetailsEdit.hidden = !canManage;
        }

        if (canManage) {
            if (els.membersGroupTitle) {
                els.membersGroupTitle.value = group?.title || '';
            }
            if (els.membersGroupDetails) {
                els.membersGroupDetails.value = group?.body || '';
            }
            if (els.membersDetailsView) {
                els.membersDetailsView.hidden = true;
            }

            return;
        }

        if (els.membersDetailsView) {
            els.membersDetailsView.hidden = false;
            els.membersDetailsView.innerHTML = `<strong>${escapeHtml(group?.title || 'Group')}</strong>
                <p>${group?.body ? escapeHtml(group.body) : 'No group details added yet.'}</p>`;
        }
    }

    function renderMembersList(members = [], canManage = canManageMembersModal) {
        if (!els.membersList) {
            return;
        }

        modalGroupMemberIds.clear();
        members.forEach((member) => {
            if (member?.id) {
                modalGroupMemberIds.add(Number(member.id));
            }
        });

        if (!members.length) {
            els.membersList.innerHTML = '<p class="discussion-widget__members-empty">No members yet.</p>';

            return;
        }

        els.membersList.innerHTML = members.map((member) => {
            const icon = member.is_owner
                ? '<i class="fa-solid fa-crown" title="Group creator"></i>'
                : '<i class="fa-solid fa-user"></i>';
            const removeBtn = canManage && !member.is_owner
                ? `<button type="button"
                           class="discussion-widget__member-remove"
                           data-member-id="${member.id}"
                           aria-label="Remove ${escapeHtml(member.name)}">
                       Remove
                   </button>`
                : '';

            return `<div class="discussion-widget__member-item">
                <span class="discussion-widget__member-item-icon">${icon}</span>
                <div class="discussion-widget__member-item-body">
                    <strong>${escapeHtml(member.name)}</strong>
                    <span>${member.is_owner ? 'Creator' : 'Member'}${member.is_online ? ' · Online' : ''}</span>
                </div>
                ${member.is_online ? '<span class="discussion-widget__member-online-dot" title="Online now" aria-label="Online now"></span>' : ''}
                ${removeBtn}
            </div>`;
        }).join('');
    }

    function renderPendingInvitations(invitations = [], canManage = canManageMembersModal) {
        modalPendingInviteeIds.clear();
        modalPendingInviteePhones.clear();
        invitations.forEach((invitation) => {
            if (invitation?.invitee?.id) {
                modalPendingInviteeIds.add(Number(invitation.invitee.id));
            }

            if (invitation?.invitee?.phone) {
                modalPendingInviteePhones.add(String(invitation.invitee.phone));
            }
        });

        if (!els.membersPendingSection || !els.membersPendingList) {
            return;
        }

        if (!canManage || !invitations.length) {
            els.membersPendingSection.hidden = true;
            els.membersPendingList.innerHTML = '';
            return;
        }

        els.membersPendingSection.hidden = false;
        els.membersPendingList.innerHTML = invitations.map((invitation) => {
            const name = invitation.invitee?.name || 'Member';
            const phone = invitation.invitee?.phone || '';

            return `<div class="discussion-widget__member-item">
                <span class="discussion-widget__member-item-icon"><i class="fa-regular fa-clock"></i></span>
                <div class="discussion-widget__member-item-body">
                    <strong>${escapeHtml(name)}</strong>
                    <span>Invitation pending${phone ? ` · ${escapeHtml(phone)}` : ''}</span>
                </div>
            </div>`;
        }).join('');
    }

    async function openMembersModal() {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id || !els.membersModal) {
            return;
        }

        modalMemberSelection.clear();
        renderMemberChips('discussionWidgetMembersAddChips', modalMemberSelection);
        const membersAddSearch = document.getElementById('discussionWidgetMembersAddSearch');
        if (membersAddSearch) {
            membersAddSearch.value = '';
        }
        hideMemberSearchResults(
            document.getElementById('discussionWidgetMembersAddResults'),
            membersAddSearch
        );

        try {
            const data = await requestJson(membersUrl(group.id), { method: 'GET' });
            canManageMembersModal = Boolean(data.can_manage_members);
            canLeaveGroupModal = Boolean(data.can_leave_group);
            canDeleteGroupModal = Boolean(data.can_delete_group);
            const modalGroup = {
                ...group,
                body: currentGroup?.body ?? group.body,
            };
            const modalTitle = document.querySelector('.discussion-widget__members-head h3');
            if (modalTitle) {
                modalTitle.textContent = canManageMembersModal ? 'Group settings' : 'Group info';
            }
            renderMembersPhotoPreview(data.group_image_url || modalGroup.group_image_url || null);
            renderMembersDetails(modalGroup, canManageMembersModal);
            if (els.membersPhotoSection) {
                els.membersPhotoSection.hidden = !canManageMembersModal;
            }
            renderMembersList(data.members || [], canManageMembersModal);
            renderPendingInvitations(data.pending_invitations || [], canManageMembersModal);
            if (els.membersAddSection) {
                els.membersAddSection.hidden = !canManageMembersModal;
            }
            if (els.leaveGroupBtn) {
                els.leaveGroupBtn.hidden = !canLeaveGroupModal;
            }
            if (els.deleteGroupBtn) {
                els.deleteGroupBtn.hidden = !canDeleteGroupModal;
            }
            els.membersModal.hidden = false;
        } catch (error) {
            notify('error', error.message);
        }
    }

    function closeMembersModal() {
        modalMemberSelection.clear();
        modalGroupMemberIds.clear();
        modalPendingInviteeIds.clear();
        modalPendingInviteePhones.clear();
        renderMemberChips('discussionWidgetMembersAddChips', modalMemberSelection);
        if (els.membersPendingSection) {
            els.membersPendingSection.hidden = true;
        }
        hideMemberSearchResults(
            document.getElementById('discussionWidgetMembersAddResults'),
            document.getElementById('discussionWidgetMembersAddSearch')
        );
        if (els.membersModal) {
            els.membersModal.hidden = true;
        }
        if (els.leaveGroupBtn) {
            els.leaveGroupBtn.hidden = true;
        }
        if (els.deleteGroupBtn) {
            els.deleteGroupBtn.hidden = true;
        }
    }

    function syncComposeChatType() {
        // Legacy hook retained for compatibility.
    }

    function showComposeGroupSummary() {
        widgetGroupComposeController?.renderSummary?.();
    }

    function formatUnreadCount(count) {
        return count > 99 ? '99+' : String(count);
    }

    function updateFabBadge(count) {
        config.globalUnread = Math.max(0, count);
        const badges = [fabBadge].filter(Boolean);

        if (!badges.length) {
            return;
        }

        if (count > 0) {
            badges.forEach((badge) => {
                badge.textContent = formatUnreadCount(count);
                badge.hidden = false;
                badge.setAttribute('aria-label', `${count} unread community messages`);
            });
            chatTriggers().forEach((trigger) => trigger.classList.add('has-unread'));
        } else {
            badges.forEach((badge) => {
                badge.hidden = true;
                badge.textContent = '0';
            });
            chatTriggers().forEach((trigger) => trigger.classList.remove('has-unread'));
        }
    }

    function getTopicUnread(topicId) {
        return config.unreadTopics?.[topicId] || 0;
    }

    function setTopicUnread(topicId, count) {
        config.unreadTopics = config.unreadTopics || {};
        if (count > 0) {
            config.unreadTopics[topicId] = count;
        } else {
            delete config.unreadTopics[topicId];
        }

        updateTopicCardUnread(topicId, count);
    }

    function incrementTopicUnread(topicId, amount = 1) {
        const next = getTopicUnread(topicId) + amount;
        setTopicUnread(topicId, next);
        updateFabBadge((config.globalUnread || 0) + amount);
    }

    function updateTopicCardUnread(topicId, count) {
        const card = document.getElementById(`discussion-widget-topic-${topicId}`);
        if (!card) {
            return;
        }

        card.classList.toggle('discussion-widget-topic--unread', count > 0);

        let badge = card.querySelector('.discussion-widget-topic__unread');
        if (count > 0) {
            if (!badge) {
                const head = card.querySelector('.discussion-widget-topic__head');
                if (head) {
                    head.insertAdjacentHTML('beforeend', `<span class="discussion-widget-topic__unread">${formatUnreadCount(count)}</span>`);
                }
            } else {
                badge.textContent = formatUnreadCount(count);
            }
        } else if (badge) {
            badge.remove();
        }
    }

    async function loadUnreadSummary() {
        if (!config.routes?.unreadSummary) {
            return;
        }

        try {
            const data = await requestJson(config.routes.unreadSummary);
            config.unreadTopics = data.topics || {};
            updateFabBadge(data.global_unread || 0);
        } catch (error) {
            // ignore badge load failures
        }
    }

    function topicReadUrl(topicId) {
        return (config.routes?.topicReadTemplate || '/discussions/__TOPIC__/read')
            .replace('__TOPIC__', String(topicId));
    }

    async function markTopicRead(topicId) {
        const previous = getTopicUnread(topicId);
        if (previous > 0) {
            setTopicUnread(topicId, 0);
            updateFabBadge(Math.max(0, (config.globalUnread || 0) - previous));
        }

        try {
            const data = await requestJson(topicReadUrl(topicId), {
                method: 'POST',
                body: JSON.stringify({}),
            });
            updateFabBadge(data.global_unread || 0);
        } catch (error) {
            // ignore mark-read failures
        }
    }

    function attachmentHelpers() {
        return window.soilnwaterDiscussionAttachments || {};
    }

    function buildAttachmentsHtml(attachments) {
        const helpers = attachmentHelpers();

        if (typeof helpers.buildAttachmentsHtml === 'function') {
            return helpers.buildAttachmentsHtml(attachments, escapeHtml);
        }

        return '';
    }

    async function requestFormData(url, formData, method = 'POST') {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                || data.message
                || 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    function topicUrl(topicId) {
        return (config.routes?.topicShowTemplate || '/discussions/__TOPIC__')
            .replace('__TOPIC__', String(topicId));
    }

    function repliesUrl(topicId) {
        return (config.routes?.topicRepliesTemplate || '/discussions/__TOPIC__/replies')
            .replace('__TOPIC__', String(topicId));
    }

    function pinUrl(topicId) {
        return (config.routes?.topicPinTemplate || '/discussions/__TOPIC__/pin')
            .replace('__TOPIC__', String(topicId));
    }

    function topicReactUrl(topicId) {
        return (config.routes?.topicReactTemplate || '/discussions/__TOPIC__/react')
            .replace('__TOPIC__', String(topicId));
    }

    function replyReactUrl(replyId) {
        return (config.routes?.replyReactTemplate || '/discussions/replies/__REPLY__/react')
            .replace('__REPLY__', String(replyId));
    }

    function finishPanelChange(name) {
        activePanelName = name;
        updateHeaderForPanel(name);

        if (name !== 'thread') {
            replyEmojiPicker?.close?.();
        }

        if (isComposePanel(name) || name === 'groupPick') {
            clearOnlinePresence();
        }
    }

    function showPanelSingle(name) {
        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) {
                return;
            }

            const active = key === name;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;
        });

        finishPanelChange(name);
    }

    function showPanelDesktopPage(name) {
        panels.topics?.classList.add('is-active');
        if (panels.topics) {
            panels.topics.hidden = false;
        }

        const showThread = name === 'thread';
        const showCompose = name === 'compose';
        const showGroupCompose = name === 'groupCompose';
        const showGroupTopicCompose = name === 'groupTopicCompose';
        const showGroupPick = name === 'groupPick';
        const showGroupTopics = name === 'groupTopics';

        if (panels.thread) {
            panels.thread.classList.toggle('is-active', showThread);
            panels.thread.hidden = !showThread;
        }
        if (panels.groupTopics) {
            panels.groupTopics.classList.toggle('is-active', showGroupTopics);
            panels.groupTopics.hidden = !showGroupTopics;
        }
        if (panels.groupPick) {
            panels.groupPick.classList.toggle('is-active', showGroupPick);
            panels.groupPick.hidden = !showGroupPick;
        }
        if (panels.compose) {
            panels.compose.classList.toggle('is-active', showCompose);
            panels.compose.hidden = !showCompose;
        }
        if (panels.groupCompose) {
            panels.groupCompose.classList.toggle('is-active', showGroupCompose);
            panels.groupCompose.hidden = !showGroupCompose;
        }
        if (panels.groupTopicCompose) {
            panels.groupTopicCompose.classList.toggle('is-active', showGroupTopicCompose);
            panels.groupTopicCompose.hidden = !showGroupTopicCompose;
        }

        finishPanelChange(name);
    }

    function showPanel(name) {
        if (isPageMode && !isMobileViewport()) {
            showPanelDesktopPage(name);
            return;
        }

        showPanelSingle(name);
    }

    function updateHeaderForPanel(name) {
        const isTopics = name === 'topics';
        const isThread = name === 'thread';
        const isCompose = isComposePanel(name);
        const isGroupPick = name === 'groupPick';
        const isGroupTopics = name === 'groupTopics';
        const showListActions = isTopics;

        if (els.backBtn) {
            if (isPageMode && isMobileViewport()) {
                els.backBtn.hidden = name === 'topics';
            } else {
                els.backBtn.hidden = isPageMode
                    ? !(isCompose || isGroupPick || isGroupTopics || currentTopic)
                    : isTopics;
            }
        }
        if (els.newTopicBtn) {
            els.newTopicBtn.hidden = !showListActions;
        }
        if (els.newGroupBtn) {
            els.newGroupBtn.hidden = !showListActions;
        }
        if (els.pinBtn) {
            els.pinBtn.hidden = !isThread || !config.canPin;
        }
        if (els.closeBtn) {
            els.closeBtn.hidden = isPageMode;
        }
        if (els.sizeBtn) {
            els.sizeBtn.hidden = isPageMode || isMobileViewport();
        }
        if (els.fullPageBtn) {
            els.fullPageBtn.hidden = isPageMode;
        }
        if (els.membersBtn) {
            const group = isGroupTopics
                ? currentGroup
                : (isThread && currentTopic?.parent_topic_id ? null : currentGroup);
            els.membersBtn.hidden = !group?.can_manage_members;
        }
        if (els.headerAvatar && isTopics) {
            els.headerAvatar.innerHTML = '<i class="fa-solid fa-people-group"></i>';
        }

        const header = widget.querySelector('.discussion-widget__header');
        if (header) {
            header.classList.toggle('discussion-widget__header--conversation', isThread || isGroupTopics);
        }
    }

    function setHeaderTitle(title, subtitle) {
        if (els.title) {
            els.title.textContent = title;
        }
        if (els.subtitle) {
            els.subtitle.textContent = subtitle || '';
        }
    }

    function setHeaderAvatar(name) {
        if (!els.headerAvatar) {
            return;
        }
        els.headerAvatar.className = 'discussion-widget__brand-mark discussion-avatar discussion-avatar--sm';
        els.headerAvatar.textContent = avatarInitials(name);
    }

    function resetHeaderAvatar() {
        if (!els.headerAvatar) {
            return;
        }
        els.headerAvatar.className = 'discussion-widget__brand-mark';
        els.headerAvatar.innerHTML = '<i class="fa-solid fa-people-group"></i>';
    }

    function setOpen(open) {
        if (isPageMode) {
            widget.classList.add('is-open');
            widget.hidden = false;
            document.body.classList.add('discussion-widget-open');
            return;
        }

        fab?.classList.toggle('is-open', open);
        fab?.setAttribute('aria-expanded', open ? 'true' : 'false');
        widget.classList.toggle('is-open', open);

        if (open) {
            widget.hidden = false;
            document.body.classList.add('discussion-widget-open');
            dockFabToCorner();
            syncWidgetAnchor();
            window.requestAnimationFrame(() => {
                syncWidgetAnchor();
                window.requestAnimationFrame(syncWidgetAnchor);
            });
        } else {
            clearWidgetAnchorStyles();
            window.setTimeout(() => {
                if (!widget.classList.contains('is-open')) {
                    widget.hidden = true;
                }
            }, 220);
            document.body.classList.remove('discussion-widget-open');
            syncBannerChatPosition();
        }
    }

    function isOpen() {
        return isPageMode || widget.classList.contains('is-open');
    }

    function messengerUrl(topicId = null) {
        const base = config.routes?.messenger || '/discussions/messenger';
        if (!topicId) {
            return base;
        }

        return `${base.replace(/\/$/, '')}/${topicId}`;
    }

    function updateFullPageLink(topicId = null) {
        if (!els.fullPageBtn || isPageMode) {
            return;
        }

        els.fullPageBtn.href = messengerUrl(topicId || currentTopic?.id || null);
    }

    function updateMessengerUrl(topicId = null) {
        if (!isPageMode || !window.history?.replaceState) {
            return;
        }

        window.history.replaceState({}, '', messengerUrl(topicId));
    }

    function setComposerVisible(visible) {
        if (!els.replyForm) {
            return;
        }

        if (visible) {
            els.replyForm.hidden = false;
        } else if (isPageMode) {
            els.replyForm.hidden = true;
        }
    }

    function showEmptyThreadPlaceholder() {
        if (!els.messages) {
            return;
        }

        els.messages.innerHTML = `<div class="discussion-widget__empty" id="discussionWidgetEmptyThread">
            <div class="discussion-widget__empty-icon"><i class="fa-regular fa-comments"></i></div>
            <h4>Select a chat</h4>
            <p>Choose a conversation from the list or start a new one.</p>
        </div>`;

        setHeaderTitle('Chats', 'Select a conversation');
        resetHeaderAvatar();
        clearOnlinePresence();
        setComposerVisible(false);
    }

    function getWidgetSize() {
        return config.widgetSize || 'default';
    }

    function applyWidgetSize(size = getWidgetSize()) {
        const nextSize = WIDGET_SIZE_ORDER.includes(size) ? size : 'default';
        config.widgetSize = nextSize;

        WIDGET_SIZE_ORDER.forEach((option) => {
            widget.classList.toggle(`discussion-widget--size-${option}`, option === nextSize && option !== 'default');
        });

        if (els.sizeBtn) {
            els.sizeBtn.title = WIDGET_SIZE_LABELS[nextSize] || 'Change popup size';
            els.sizeBtn.setAttribute('aria-label', els.sizeBtn.title);
        }

        try {
            localStorage.setItem(WIDGET_SIZE_STORAGE_KEY, nextSize);
        } catch (error) {
            // ignore storage failures
        }

        syncWidgetAnchor();
    }

    function cycleWidgetSize() {
        const currentIndex = WIDGET_SIZE_ORDER.indexOf(getWidgetSize());
        const nextIndex = (currentIndex + 1) % WIDGET_SIZE_ORDER.length;
        applyWidgetSize(WIDGET_SIZE_ORDER[nextIndex]);
    }

    function restoreWidgetSize() {
        let saved = 'default';

        try {
            saved = localStorage.getItem(WIDGET_SIZE_STORAGE_KEY) || 'default';
        } catch (error) {
            saved = 'default';
        }

        applyWidgetSize(saved);
    }

    function formatPostedDate(item) {
        if (item?.created_at_date) {
            return item.created_at_date;
        }

        if (!item?.created_at) {
            return item?.created_at_human || '';
        }

        try {
            return new Date(item.created_at).toLocaleDateString('en-IN', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
            });
        } catch (error) {
            return item.created_at_human || '';
        }
    }

    function formatMessageTimestamp(item) {
        const date = formatPostedDate(item);
        const time = item?.created_at_time || '';

        if (date && time) {
            return `${date}, ${time}`;
        }

        return date || item?.created_at_human || 'just now';
    }

    function buildTopicCard(topic) {
        const authorName = topic.author?.name || 'Member';
        const excerpt = topic.body
            ? escapeHtml(String(topic.body).slice(0, 80)) + (String(topic.body).length > 80 ? '…' : '')
            : `${topic.replies_count || 0} ${(topic.replies_count || 0) === 1 ? 'reply' : 'replies'}`;
        const unread = topic.unread_count ?? getTopicUnread(topic.id);
        if (unread > 0) {
            setTopicUnread(topic.id, unread);
        }
        const unreadBadge = unread > 0
            ? `<span class="discussion-widget-topic__unread">${formatUnreadCount(unread)}</span>`
            : '';
        const pinnedClass = topic.is_pinned ? 'discussion-widget-topic--pinned' : '';
        const unreadClass = unread > 0 ? 'discussion-widget-topic--unread' : '';
        const postedDate = formatPostedDate(topic);
        const postedTime = topic.created_at_time || '';

        return `<button type="button" class="discussion-widget-topic ${pinnedClass} ${unreadClass}" data-topic-id="${topic.id}" data-search="${escapeHtml((topic.title + ' ' + (topic.body || '') + ' ' + authorName).toLowerCase())}" id="discussion-widget-topic-${topic.id}">
            ${topicListAvatarHtml(topic)}
            <div class="discussion-widget-topic__content">
                <div class="discussion-widget-topic__row">
                    <h3 class="discussion-widget-topic__title">${escapeHtml(topic.title)}</h3>
                    ${unreadBadge}
                </div>
                <div class="discussion-widget-topic__row">
                    <p class="discussion-widget-topic__excerpt">${excerpt}</p>
                </div>
                <div class="discussion-widget-topic__row discussion-widget-topic__meta">
                    <span class="discussion-widget-topic__author">${topic.is_group ? `<i class="fa-solid fa-users"></i> Group` : `<i class="fa-solid fa-hashtag"></i> Topic`} · ${escapeHtml(authorName)}</span>
                    <time class="discussion-widget-topic__date" datetime="${escapeHtml(topic.created_at || '')}">${escapeHtml(postedDate)}${postedTime ? ` · ${escapeHtml(postedTime)}` : ''}</time>
                </div>
            </div>
        </button>`;
    }

    function buildReactionsMenuHtml(reactableType, reactableId, reactUrl, counts = {}, userReactions = []) {
        const labels = config.reactionLabels || Object.keys(reactionIcons);
        const summary = labels
            .filter((label) => (counts[label] || 0) > 0)
            .map((label) => {
                const active = userReactions.includes(label);
                const count = counts[label] || 0;
                return `<span class="discussion-msg__reaction-chip ${active ? 'is-mine' : ''}" title="${escapeHtml(label)}">
                    <i class="fa-solid ${reactionIcons[label] || 'fa-face-smile'}"></i>
                    <span>${count}</span>
                </span>`;
            })
            .join('');

        const menuItems = labels.map((label) => {
            const active = userReactions.includes(label);
            const count = counts[label] || 0;
            return `<button type="button"
                        class="discussion-reaction-btn discussion-reaction-menu-item ${active ? 'is-active' : ''}"
                        data-reaction="${escapeHtml(label)}"
                        data-active="${active ? '1' : '0'}"
                        aria-pressed="${active ? 'true' : 'false'}">
                    <i class="fa-solid ${reactionIcons[label] || 'fa-face-smile'}"></i>
                    <span>${escapeHtml(label)}</span>
                    <span class="discussion-reaction-count">${count > 0 ? count : ''}</span>
                </button>`;
        }).join('');

        return `<div class="discussion-msg__reaction-summary${summary ? '' : ' is-empty'}">${summary}</div>
        <div class="discussion-msg__actions">
            <div class="discussion-msg__menu">
                <button type="button" class="discussion-msg__menu-btn" aria-label="Message actions" aria-expanded="false">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
                <div class="discussion-msg__menu-panel" hidden>
                    <p class="discussion-msg__menu-title">React to message</p>
                    <div class="discussion-reactions discussion-reactions--menu discussion-widget-reactions"
                         data-reactable-type="${escapeHtml(reactableType)}"
                         data-reactable-id="${escapeHtml(reactableId)}"
                         data-react-url="${escapeHtml(reactUrl)}">
                        ${menuItems}
                    </div>
                </div>
            </div>
        </div>`;
    }

    function buildTopicMessage(topic) {
        const authorName = topic.author?.name || 'Member';
        const separator = `<div class="discussion-msg__date-separator"><strong>${escapeHtml(topic.title)}</strong></div>`;
        const bodyBlock = topic.body
            ? `<p class="discussion-msg__body">${escapeHtml(topic.body)}</p>`
            : '';

        return `${separator}
        <article class="discussion-msg discussion-msg--in discussion-widget-msg" data-reactable-type="DiscussionTopic" data-reactable-id="${topic.id}">
            <span class="discussion-msg__sender">${escapeHtml(authorName)}</span>
            <div class="discussion-msg__bubble-wrap">
                <div class="discussion-msg__bubble">
                    ${bodyBlock}
                    ${buildAttachmentsHtml(topic.attachments || [])}
                    <span class="discussion-msg__time">${escapeHtml(formatMessageTimestamp(topic))}</span>
                </div>
                ${buildReactionsMenuHtml('DiscussionTopic', topic.id, topicReactUrl(topic.id), topic.reaction_counts || {}, topic.user_reactions || [])}
            </div>
        </article>`;
    }

    function buildReplyMessage(reply) {
        const mine = Number(reply.author?.id) === Number(config.currentUserId);
        const authorName = reply.author?.name || 'Member';
        const bodyBlock = reply.body
            ? `<p class="discussion-msg__body">${escapeHtml(reply.body || '')}</p>`
            : '';

        return `<article class="discussion-msg ${mine ? 'discussion-msg--mine' : 'discussion-msg--in'} discussion-widget-msg ${mine ? 'discussion-widget-msg--mine' : ''}"
                         id="discussion-widget-reply-${reply.id}"
                         data-reply-id="${reply.id}"
                         data-reactable-type="DiscussionReply"
                         data-reactable-id="${reply.id}">
            ${mine ? '' : `<span class="discussion-msg__sender">${escapeHtml(authorName)}</span>`}
            <div class="discussion-msg__bubble-wrap">
                <div class="discussion-msg__bubble">
                    ${bodyBlock}
                    ${buildAttachmentsHtml(reply.attachments || [])}
                    <span class="discussion-msg__time">${escapeHtml(formatMessageTimestamp(reply))}</span>
                </div>
                ${buildReactionsMenuHtml('DiscussionReply', reply.id, replyReactUrl(reply.id), reply.reaction_counts || {}, reply.user_reactions || [])}
            </div>
        </article>`;
    }

    function buildInvitationCard(invitation) {
        const groupTitle = invitation.group_title || 'Community group';
        const inviterName = invitation.inviter?.name || 'A community member';

        return `<div class="discussion-widget-topic discussion-widget-invite" data-invitation-id="${invitation.id}">
            <span class="discussion-avatar discussion-avatar--icon discussion-avatar--group" aria-hidden="true"><i class="fa-solid fa-envelope-open-text"></i></span>
            <div class="discussion-widget-topic__content">
                <div class="discussion-widget-topic__row">
                    <h3 class="discussion-widget-topic__title">${escapeHtml(groupTitle)}</h3>
                    <span class="discussion-widget-topic__unread">Invite</span>
                </div>
                <p class="discussion-widget-topic__excerpt">${escapeHtml(inviterName)} invited you to join this group.</p>
                <div class="discussion-widget-invite__actions">
                    <button type="button" class="discussion-widget-invite__btn discussion-widget-invite__btn--reject" data-invitation-action="reject">Reject</button>
                    <button type="button" class="discussion-widget-invite__btn discussion-widget-invite__btn--accept" data-invitation-action="accept">Approve</button>
                </div>
            </div>
        </div>`;
    }

    function renderTopics(topics, invitations = []) {
        if (!els.topicList) {
            return;
        }

        if (!topics.length && !invitations.length) {
            els.topicList.innerHTML = `<div class="discussion-widget__empty" id="discussionWidgetEmptyState">
                <div class="discussion-widget__empty-icon"><i class="fa-regular fa-comments"></i></div>
                <h4>No conversations yet</h4>
                <p>Start the first discussion with the community.</p>
            </div>`;
            return;
        }

        const invitationHtml = invitations.map(buildInvitationCard).join('');
        els.topicList.innerHTML = invitationHtml + topics.map(buildTopicCard).join('');

        const search = document.getElementById('discussionWidgetSearch');
        if (search) {
            search.placeholder = topics.length
                ? `Search ${topics.length} chats`
                : 'Search or start new chat';
        }
    }

    function prependTopicCard(topic) {
        if (!els.topicList || document.getElementById(`discussion-widget-topic-${topic.id}`)) {
            return;
        }

        const empty = document.getElementById('discussionWidgetEmptyState');
        if (empty) {
            empty.remove();
        }

        const loading = document.getElementById('discussionWidgetTopicsLoading');
        if (loading) {
            loading.remove();
        }

        els.topicList.insertAdjacentHTML('afterbegin', buildTopicCard(topic));
    }

    function appendReplyMessage(reply) {
        if (!els.messages || document.getElementById(`discussion-widget-reply-${reply.id}`)) {
            return;
        }

        if (!currentTopic || Number(reply.discussion_topic_id) !== Number(currentTopic.id)) {
            return;
        }

        const empty = document.getElementById('discussionWidgetEmptyReplies');
        if (empty) {
            empty.remove();
        }

        els.messages.insertAdjacentHTML('beforeend', buildReplyMessage(reply));
        els.messages.scrollTop = els.messages.scrollHeight;

        currentTopic.replies_count = (currentTopic.replies_count || 0) + 1;
        if (els.subtitle) {
            const count = currentTopic.replies_count || 0;
            els.subtitle.textContent = `${count} ${count === 1 ? 'reply' : 'replies'}`;
        }
    }

    function createPendingReplyId() {
        return `pending-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    }

    function appendPendingReplyMessage({ pendingId, body, files }) {
        if (!els.messages || !pendingId) {
            return null;
        }

        const helpers = attachmentHelpersRef();
        const attachmentsHtml = typeof helpers.buildPendingAttachmentsHtml === 'function'
            ? helpers.buildPendingAttachmentsHtml(files, { escapeHtml, progress: 0, phase: 'upload' })
            : '';
        const bodyBlock = body
            ? `<p class="discussion-msg__body">${escapeHtml(body)}</p>`
            : '';

        const empty = document.getElementById('discussionWidgetEmptyReplies');
        if (empty) {
            empty.remove();
        }

        els.messages.insertAdjacentHTML('beforeend', `<article class="discussion-msg discussion-msg--mine discussion-msg--pending discussion-widget-msg discussion-widget-msg--mine"
                         id="discussion-widget-reply-${pendingId}"
                         data-pending-reply="1">
            <div class="discussion-msg__bubble-wrap">
                <div class="discussion-msg__bubble">
                    ${bodyBlock}
                    ${attachmentsHtml}
                    <span class="discussion-msg__time"><i class="fa-regular fa-clock" aria-hidden="true"></i> Sending…</span>
                </div>
            </div>
        </article>`);
        els.messages.scrollTop = els.messages.scrollHeight;

        return document.getElementById(`discussion-widget-reply-${pendingId}`);
    }

    function updatePendingReplyProgress(pendingId, progress, phase) {
        const pendingEl = document.getElementById(`discussion-widget-reply-${pendingId}`);
        if (!pendingEl) {
            return;
        }

        attachmentHelpersRef().updatePendingAttachmentProgress?.(pendingEl, progress, phase);

        const timeEl = pendingEl.querySelector('.discussion-msg__time');
        if (timeEl) {
            if (phase === 'processing') {
                timeEl.innerHTML = '<i class="fa-regular fa-clock" aria-hidden="true"></i> Sending…';
            } else if (phase === 'done') {
                timeEl.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Sent';
            } else {
                timeEl.innerHTML = `<i class="fa-regular fa-clock" aria-hidden="true"></i> Uploading · ${Math.round(progress)}%`;
            }
        }
    }

    function removePendingReplyMessage(pendingId) {
        const pendingEl = document.getElementById(`discussion-widget-reply-${pendingId}`);
        if (!pendingEl) {
            return;
        }

        attachmentHelpersRef().revokePendingAttachmentUrls?.(pendingEl);
        pendingEl.remove();
    }

    function failPendingReplyMessage(pendingId, message) {
        const pendingEl = document.getElementById(`discussion-widget-reply-${pendingId}`);
        if (!pendingEl) {
            return;
        }

        pendingEl.classList.add('discussion-msg--failed');
        pendingEl.classList.remove('discussion-msg--pending');

        const overlay = pendingEl.querySelector('.discussion-msg__upload-overlay');
        if (overlay) {
            overlay.innerHTML = `<span class="discussion-msg__upload-failed"><i class="fa-solid fa-circle-exclamation"></i> Failed</span>`;
            overlay.hidden = false;
        }

        const timeEl = pendingEl.querySelector('.discussion-msg__time');
        if (timeEl) {
            timeEl.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> Not sent';
        }

        pendingEl.setAttribute('title', message || 'Could not send message');
    }

    function finalizePendingReplyMessage(pendingId, reply) {
        removePendingReplyMessage(pendingId);

        if (reply) {
            appendReplyMessage(reply);
        }
    }

    async function loadTopics(force = false) {
        if (topicsLoaded && !force) {
            return;
        }

        if (els.topicList) {
            els.topicList.innerHTML = `<div class="discussion-widget__loading" id="discussionWidgetTopicsLoading">
                <span class="discussion-widget__spinner" aria-hidden="true"></span>
                <span>Loading topics…</span>
            </div>`;
        }

        try {
            const data = await requestJson(config.routes.discussionsIndex || '/discussions');
            config.canPin = Boolean(data.can_pin);
            if (typeof data.global_unread === 'number') {
                updateFabBadge(data.global_unread);
            }
            renderTopics(data.topics || [], data.pending_invitations || []);
            topicsLoaded = true;
        } catch (error) {
            if (els.topicList) {
                els.topicList.innerHTML = `<div class="discussion-widget__empty"><p>${escapeHtml(error.message)}</p></div>`;
            }
            notify('error', error.message);
        }
    }

    function updatePinButton(isPinned) {
        if (!els.pinBtn) {
            return;
        }

        els.pinBtn.classList.toggle('is-pinned', isPinned);
        els.pinBtn.title = isPinned ? 'Unpin' : 'Pin topic';
    }

    function buildGroupTopicCard(topic) {
        const authorName = topic.author?.name || 'Member';
        const excerpt = topic.body
            ? escapeHtml(String(topic.body).slice(0, 80)) + (String(topic.body).length > 80 ? '…' : '')
            : `${topic.replies_count || 0} ${(topic.replies_count || 0) === 1 ? 'reply' : 'replies'}`;
        const unread = topic.unread_count ?? getTopicUnread(topic.id);
        if (unread > 0) {
            setTopicUnread(topic.id, unread);
        }
        const unreadBadge = unread > 0
            ? `<span class="discussion-widget-topic__unread">${formatUnreadCount(unread)}</span>`
            : '';
        const unreadClass = unread > 0 ? 'discussion-widget-topic--unread' : '';
        const postedDate = formatPostedDate(topic);
        const postedTime = topic.created_at_time || '';

        return `<button type="button" class="discussion-widget-topic ${unreadClass}" data-topic-id="${topic.id}" data-group-topic="1">
            <span class="discussion-avatar discussion-avatar--icon discussion-avatar--topic" aria-hidden="true"><i class="fa-solid fa-hashtag"></i></span>
            <div class="discussion-widget-topic__content">
                <div class="discussion-widget-topic__row">
                    <h3 class="discussion-widget-topic__title">${escapeHtml(topic.title)}</h3>
                    ${unreadBadge}
                </div>
                <div class="discussion-widget-topic__row">
                    <p class="discussion-widget-topic__excerpt">${excerpt}</p>
                </div>
                <div class="discussion-widget-topic__row discussion-widget-topic__meta">
                    <span class="discussion-widget-topic__author"><i class="fa-solid fa-hashtag"></i> Topic · ${escapeHtml(authorName)}</span>
                    <time class="discussion-widget-topic__date" datetime="${escapeHtml(topic.created_at || '')}">${escapeHtml(postedDate)}${postedTime ? ` · ${escapeHtml(postedTime)}` : ''}</time>
                </div>
            </div>
        </button>`;
    }

    function renderGroupTopicsList(children = []) {
        const listEl = els.groupTopicsList;
        const emptyEl = document.getElementById('discussionWidgetGroupTopicsEmpty');
        if (!listEl) {
            return;
        }

        if (!children.length) {
            listEl.innerHTML = '';
            if (emptyEl) {
                emptyEl.hidden = false;
                listEl.appendChild(emptyEl);
            }

            return;
        }

        if (emptyEl) {
            emptyEl.hidden = true;
        }

        listEl.innerHTML = children.map(buildGroupTopicCard).join('');
    }

    function openGroupTopics(group) {
        currentGroup = group;
        currentTopic = group;
        config.topicId = group.id;
        openRequestId += 1;

        showPanel('groupTopics');
        setHeaderTitle(group.title, topicSubtitle(group));
        setHeaderTopicAvatar(group);
        updateMembersButton(group);
        setComposerVisible(false);
        renderGroupTopicsList(group.children || []);
        applyOnlinePresence(group, { online_users: group.online_users || [], members: group.members || [] });
        updateFullPageLink(group.id);
        updateMessengerUrl(group.id);

        if (els.pinBtn) {
            els.pinBtn.hidden = true;
        }
    }

    function openThreadFromData(topic, data = {}) {
        currentTopic = topic;
        config.canPin = Boolean(data.can_pin);
        config.topicId = topic.id;

        if (topic.parent && topic.parent.id) {
            currentGroup = topic.parent;
        } else if (topic.parent_topic_id && currentGroup?.id !== Number(topic.parent_topic_id)) {
            currentGroup = { id: topic.parent_topic_id, title: 'Group' };
        }

        showPanel('thread');
        setHeaderTitle(topic.title, topicSubtitle(topic));
        if (topic.parent_topic_id) {
            if (els.headerAvatar) {
                els.headerAvatar.className = 'discussion-widget__brand-mark discussion-avatar discussion-avatar--icon discussion-avatar--topic';
                els.headerAvatar.innerHTML = '<i class="fa-solid fa-hashtag"></i>';
            }
        } else {
            setHeaderTopicAvatar(topic);
        }
        updateMembersButton(null);

        updatePinButton(Boolean(topic.is_pinned));
        if (els.pinBtn) {
            els.pinBtn.hidden = !config.canPin;
            els.pinBtn.dataset.url = pinUrl(topic.id);
        }

        const replies = topic.replies || [];
        let html = buildTopicMessage(topic);
        if (replies.length) {
            html += replies.map(buildReplyMessage).join('');
        } else {
            html += `<div class="discussion-widget__empty" id="discussionWidgetEmptyReplies">
                <div class="discussion-widget__empty-icon"><i class="fa-regular fa-comment-dots"></i></div>
                <h4>No replies yet</h4>
                <p>Be the first to respond.</p>
            </div>`;
        }

        if (els.messages) {
            els.messages.innerHTML = html;
            els.messages.scrollTop = els.messages.scrollHeight;
        }

        setComposerVisible(true);
        markTopicRead(topic.id);
        applyOnlinePresence(topic, {
            online_users: topic.online_users || [],
            members: topic.members || [],
        });
        updateFullPageLink(topic.id);
        updateMessengerUrl(topic.id);
    }

    async function reloadCurrentGroup() {
        if (!currentGroup?.id) {
            return;
        }

        try {
            const data = await requestJson(topicUrl(currentGroup.id));
            openGroupTopics(data.topic);
        } catch (error) {
            notify('error', error.message);
        }
    }

    async function openTopic(topicId) {
        const requestId = ++openRequestId;
        currentTopic = { id: topicId };
        config.topicId = topicId;

        showPanel('thread');

        setHeaderTitle('Loading…', 'Please wait');
        resetHeaderAvatar();
        setComposerVisible(false);
        if (els.messages) {
            els.messages.innerHTML = `<div class="discussion-widget__loading" id="discussionWidgetThreadLoading">
                <span class="discussion-widget__spinner" aria-hidden="true"></span>
                <span>Opening conversation…</span>
            </div>`;
        }
        if (els.pinBtn) {
            els.pinBtn.hidden = !config.canPin;
        }
        updateMembersButton(null);

        try {
            const data = await requestJson(topicUrl(topicId));
            if (requestId !== openRequestId) {
                return;
            }

            const topic = data.topic;

            if (topic.is_group && !topic.parent_topic_id) {
                openGroupTopics(topic);

                return;
            }

            openThreadFromData(topic, data);
        } catch (error) {
            if (requestId !== openRequestId) {
                return;
            }
            if (els.messages) {
                els.messages.innerHTML = `<div class="discussion-widget__empty"><p>${escapeHtml(error.message)}</p></div>`;
            }
            notify('error', error.message);
        }
    }

    function showTopics() {
        currentTopic = null;
        currentGroup = null;
        config.topicId = null;
        openRequestId += 1;
        clearOnlinePresence();

        const search = document.getElementById('discussionWidgetSearch');
        if (search) {
            search.value = '';
        }

        if (isPageMode) {
            if (isMobileViewport()) {
                setHeaderTitle('Chats', `${config.globalUnread || 0} unread`);
                resetHeaderAvatar();
                updateMembersButton(null);
                showPanel('topics');
            } else {
                showPanel('thread');
                showEmptyThreadPlaceholder();
            }

            loadTopics();
            updateMessengerUrl(null);
            updateFullPageLink(null);
            return;
        }

        setHeaderTitle('Chats', `${config.globalUnread || 0} unread`);
        resetHeaderAvatar();
        updateMembersButton(null);

        showPanel('topics');
        loadTopics();
        updateFullPageLink(null);
    }

    function showComposeTopic() {
        composeMode = 'topic';
        showPanel('compose');
        setHeaderTitle('New topic', 'Create a public discussion');
        resetHeaderAvatar();
        updateMembersButton(null);
        setComposerVisible(false);
        document.getElementById('discussionWidgetTopicTitle')?.focus();
    }

    function showGroupPick() {
        composeMode = 'group';
        showPanel('groupPick');
        setHeaderTitle('Add participants', 'Find a member by mobile number');
        resetHeaderAvatar();
        updateMembersButton(null);
        setComposerVisible(false);
        widgetGroupPicker?.reset?.();
        document.getElementById('discussionWidgetGroupSearch')?.focus();
    }

    function showComposeGroup() {
        composeMode = 'group';
        showComposeGroupSummary();
        showPanel('groupCompose');
        setHeaderTitle('New group', 'Add name, photo, and details');
        resetHeaderAvatar();
        updateMembersButton(null);
        setComposerVisible(false);
        document.getElementById('discussionWidgetGroupTitle')?.focus();
    }

    function showComposeGroupTopic() {
        if (!currentGroup?.id) {
            return;
        }

        if (els.groupTopicParentInput) {
            els.groupTopicParentInput.value = String(currentGroup.id);
        }

        composeMode = 'groupTopic';
        showPanel('groupTopicCompose');
        setHeaderTitle('New topic', `In ${currentGroup.title}`);
        resetHeaderAvatar();
        updateMembersButton(null);
        setComposerVisible(false);
        document.getElementById('discussionWidgetGroupTopicTitle')?.focus();
    }

    function showCompose() {
        showComposeTopic();
    }

    async function toggleOpen() {
        const next = !isOpen();
        setOpen(next);

        if (next) {
            showTopics();
        }
    }

    function autoResizeTextarea(textarea) {
        if (!textarea) {
            return;
        }

        textarea.style.height = 'auto';
        textarea.style.height = `${Math.min(textarea.scrollHeight, 120)}px`;
    }

    fab?.addEventListener('click', () => {
        toggleOpen();
    });

    initBannerChatPosition();
    resetFabState();
    window.addEventListener('pageshow', resetFabState);

    els.closeBtn?.addEventListener('click', () => setOpen(false));
    els.sizeBtn?.addEventListener('click', cycleWidgetSize);
    els.backBtn?.addEventListener('click', () => {
        if (panels.groupPick?.classList.contains('is-active')) {
            showTopics();
            widgetGroupPicker?.reset?.();
            return;
        }
        if (panels.groupTopicCompose?.classList.contains('is-active')) {
            reloadCurrentGroup();
            return;
        }
        if (panels.groupCompose?.classList.contains('is-active')) {
            showGroupPick();
            return;
        }
        if (panels.compose?.classList.contains('is-active')) {
            showTopics();
            return;
        }
        if (panels.thread?.classList.contains('is-active') && currentTopic?.parent_topic_id && currentGroup?.id) {
            reloadCurrentGroup();
            return;
        }
        if (panels.groupTopics?.classList.contains('is-active')) {
            showTopics();
            return;
        }
        showTopics();
    });
    els.newTopicBtn?.addEventListener('click', showComposeTopic);
    els.newGroupBtn?.addEventListener('click', showGroupPick);
    els.newGroupTopicBtn?.addEventListener('click', showComposeGroupTopic);
    els.groupInfoBtn?.addEventListener('click', openMembersModal);
    els.membersBtn?.addEventListener('click', openMembersModal);
    els.membersCloseBtn?.addEventListener('click', closeMembersModal);
    els.membersModal?.addEventListener('click', (event) => {
        if (event.target === els.membersModal) {
            closeMembersModal();
        }
    });
    els.membersAddBtn?.addEventListener('click', async () => {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id || modalMemberSelection.size === 0) {
            return;
        }

        els.membersAddBtn.disabled = true;

        try {
            const data = await requestJson(membersStoreUrl(group.id), {
                method: 'POST',
                body: JSON.stringify(buildMemberInvitePayload(modalMemberSelection)),
            });

            renderMembersList(data.members || [], canManageMembersModal);
            renderPendingInvitations(data.pending_invitations || [], canManageMembersModal);
            modalMemberSelection.clear();
            renderMemberChips('discussionWidgetMembersAddChips', modalMemberSelection);
            notify('success', data.message || 'Invitation sent.');
        } catch (error) {
            notify('error', error.message);
        } finally {
            els.membersAddBtn.disabled = false;
        }
    });

    els.membersList?.addEventListener('click', async (event) => {
        const removeBtn = event.target.closest('.discussion-widget__member-remove');
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!removeBtn || !group?.id) {
            return;
        }

        const memberId = Number(removeBtn.dataset.memberId);
        removeBtn.disabled = true;

        try {
            const data = await requestJson(membersDestroyUrl(group.id, memberId), {
                method: 'DELETE',
            });

            renderMembersList(data.members || [], canManageMembersModal);
            notify('success', data.message || 'Member removed.');
        } catch (error) {
            notify('error', error.message);
            removeBtn.disabled = false;
        }
    });

    els.membersPhotoInput?.addEventListener('change', async () => {
        const file = els.membersPhotoInput.files?.[0];
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!file || !group?.id) {
            return;
        }

        const formData = new FormData();
        formData.append('group_image', file);

        try {
            const data = await requestFormJson(groupImageUpdateUrl(group.id), formData);
            renderMembersPhotoPreview(data.group_image_url || null);
            syncTopicGroupSettings(data.topic || { id: group.id, group_image_url: data.group_image_url });
            notify('success', data.message || 'Group photo updated.');
        } catch (error) {
            notify('error', error.message);
        } finally {
            els.membersPhotoInput.value = '';
        }
    });

    els.membersPhotoRemoveBtn?.addEventListener('click', async () => {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id) {
            return;
        }

        els.membersPhotoRemoveBtn.disabled = true;

        try {
            const data = await requestJson(groupImageDestroyUrl(group.id), {
                method: 'DELETE',
            });

            renderMembersPhotoPreview(null);
            syncTopicGroupSettings(data.topic || { id: group.id, group_image_url: null });
            notify('success', data.message || 'Group photo removed.');
        } catch (error) {
            notify('error', error.message);
        } finally {
            els.membersPhotoRemoveBtn.disabled = false;
        }
    });

    els.membersSaveDetailsBtn?.addEventListener('click', async () => {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id || !canManageMembersModal) {
            return;
        }

        const title = els.membersGroupTitle?.value?.trim() || '';
        const body = els.membersGroupDetails?.value?.trim() || '';

        if (!title) {
            notify('error', 'Group name is required.');
            return;
        }

        els.membersSaveDetailsBtn.disabled = true;

        try {
            const data = await requestJson(groupSettingsUpdateUrl(group.id), {
                method: 'PATCH',
                body: JSON.stringify({
                    title,
                    body: body || null,
                }),
            });

            syncTopicGroupSettings(data.topic || { id: group.id, title, body });
            renderMembersDetails(data.topic || { title, body }, true);
            notify('success', data.message || 'Group settings updated.');
        } catch (error) {
            notify('error', error.message);
        } finally {
            els.membersSaveDetailsBtn.disabled = false;
        }
    });

    els.leaveGroupBtn?.addEventListener('click', async () => {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id || !canLeaveGroupModal) {
            return;
        }

        const confirmed = window.confirm('Leave this group? You will no longer see its topics or messages.');
        if (!confirmed) {
            return;
        }

        els.leaveGroupBtn.disabled = true;

        try {
            const data = await requestJson(groupLeaveUrl(group.id), {
                method: 'POST',
                body: JSON.stringify({}),
            });

            handleGroupRemoved(data.left_group_id || group.id, data.message || 'You left the group.');
        } catch (error) {
            notify('error', error.message);
            els.leaveGroupBtn.disabled = false;
        }
    });

    els.deleteGroupBtn?.addEventListener('click', async () => {
        const group = currentGroup || (currentTopic?.is_group && !currentTopic?.parent_topic_id ? currentTopic : null);
        if (!group?.id || !canDeleteGroupModal) {
            return;
        }

        const confirmed = await confirmDestructiveAction({
            title: 'Delete this group?',
            text: 'All topics, chats, and uploaded files in this group will be permanently removed. This cannot be undone.',
            confirmText: 'Delete group',
            cancelText: 'Cancel',
        });
        if (!confirmed) {
            return;
        }

        els.deleteGroupBtn.disabled = true;

        try {
            const data = await requestJson(groupDestroyUrl(group.id), {
                method: 'DELETE',
            });

            handleGroupRemoved(data.deleted_group_id || group.id, data.message || 'Group deleted.');
        } catch (error) {
            notify('error', error.message);
            els.deleteGroupBtn.disabled = false;
        }
    });

    bindMemberChipRemoval('discussionWidgetMembersAddChips', modalMemberSelection);
    bindMemberSearchInput(
        'discussionWidgetMembersAddSearch',
        'discussionWidgetMembersAddResults',
        modalMemberSelection,
        'discussionWidgetMembersAddChips',
        {
            getExcludeIds: () => new Set([...modalGroupMemberIds, ...modalPendingInviteeIds]),
            getExcludePhones: () => new Set(modalPendingInviteePhones),
        }
    );

    widgetGroupPicker = window.soilnwaterDiscussionCompose?.initGroupMemberPicker?.({
        prefix: 'discussionWidget',
        onNext() {
            showComposeGroup();
        },
    }) || null;

    widgetComposeController = window.soilnwaterDiscussionCompose?.initNewChatForm?.({
        form: els.newTopicForm,
        attachImageBtnId: 'discussionWidgetTopicAttachImageBtn',
        attachVideoBtnId: 'discussionWidgetTopicAttachVideoBtn',
        attachDocumentBtnId: 'discussionWidgetTopicAttachDocumentBtn',
        attachmentsInputId: 'discussionWidgetTopicAttachments',
        attachmentsPreviewId: 'discussionWidgetTopicPreview',
        onSuccess(data) {
            notify('success', data.message || 'Topic created.');
            topicsLoaded = false;

            if (data.topic?.id) {
                prependTopicCard(data.topic);
                openTopic(data.topic.id);
            } else {
                showTopics();
            }
        },
        onError(error) {
            notify('error', error.message);
        },
    });

    if (els.newGroupForm) {
        widgetGroupComposeController = window.soilnwaterDiscussionCompose?.initNewChatForm?.({
            form: els.newGroupForm,
            skipAttachments: true,
            memberSelection: widgetGroupPicker?.selectionMap || new Map(),
            pickerController: widgetGroupPicker,
            summaryElId: 'discussionWidgetGroupSelectedSummary',
            onSuccess(data) {
                notify('success', data.message || 'Group created.');
                topicsLoaded = false;
                widgetGroupPicker?.reset?.();

                if (data.topic?.id) {
                    prependTopicCard(data.topic);
                    openTopic(data.topic.id);
                } else {
                    showTopics();
                }
            },
            onError(error) {
                notify('error', error.message);
            },
        });
    }

    if (els.newGroupTopicForm) {
        window.soilnwaterDiscussionCompose?.initNewChatForm?.({
            form: els.newGroupTopicForm,
            attachImageBtnId: 'discussionWidgetGroupTopicAttachImageBtn',
            attachVideoBtnId: 'discussionWidgetGroupTopicAttachVideoBtn',
            attachDocumentBtnId: 'discussionWidgetGroupTopicAttachDocumentBtn',
            attachmentsInputId: 'discussionWidgetGroupTopicAttachments',
            attachmentsPreviewId: 'discussionWidgetGroupTopicAttachmentsPreview',
            onSuccess(data) {
                notify('success', data.message || 'Group topic created.');

                if (data.topic?.id && currentGroup?.id) {
                    reloadCurrentGroup().then(() => {
                        openTopic(data.topic.id);
                    });
                } else if (currentGroup?.id) {
                    reloadCurrentGroup();
                }
            },
            onError(error) {
                notify('error', error.message);
            },
        });
    }

    els.topicList?.addEventListener('click', async (event) => {
        const inviteAction = event.target.closest('[data-invitation-action]');
        const inviteCard = event.target.closest('[data-invitation-id]');

        if (inviteAction && inviteCard) {
            event.preventDefault();
            event.stopPropagation();
            const invitationId = inviteCard.dataset.invitationId;
            const action = inviteAction.dataset.invitationAction;
            const url = action === 'accept'
                ? invitationAcceptUrl(invitationId)
                : invitationRejectUrl(invitationId);
            inviteAction.disabled = true;

            try {
                const data = await requestJson(url, { method: 'POST', body: JSON.stringify({}) });
                notify('success', data.message || (action === 'accept' ? 'You joined the group.' : 'Invitation declined.'));
                inviteCard.remove();
                topicsLoaded = false;
                await loadTopics(true);

                if (action === 'accept' && data.invitation?.group_id) {
                    openTopic(data.invitation.group_id);
                }
            } catch (error) {
                notify('error', error.message);
                inviteAction.disabled = false;
            }
            return;
        }

        const card = event.target.closest('[data-topic-id]');
        if (!card) {
            return;
        }

        openTopic(card.dataset.topicId);
    });

    els.groupTopicsList?.addEventListener('click', (event) => {
        const card = event.target.closest('[data-group-topic]');
        if (!card) {
            return;
        }

        openTopic(card.dataset.topicId);
    });

    els.replyBody?.addEventListener('input', () => autoResizeTextarea(els.replyBody));

    let replySubmitting = false;

    els.replyForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (replySubmitting) {
            notify('error', 'Please wait for the current message to finish sending.');
            return;
        }

        if (!currentTopic?.id) {
            notify('error', 'Select a chat before sending a message.');
            return;
        }

        const body = els.replyBody?.value?.trim() || '';
        const hasFiles = replyAttachmentPool?.files?.length > 0;

        if (!body && !hasFiles) {
            notify('error', 'Please enter a message or attach a file.');
            return;
        }

        const submit = els.replyForm.querySelector('[type="submit"]');
        const progressEl = document.getElementById('discussionWidgetUploadProgress');
        const noticeEl = document.getElementById('discussionWidgetComposerNotice');
        const filesSnapshot = Array.from(replyAttachmentPool?.files || []);
        const hasUpload = filesSnapshot.length > 0;
        const pendingId = hasUpload ? createPendingReplyId() : null;

        replySubmitting = true;

        if (noticeEl) {
            noticeEl.hidden = true;
            noticeEl.textContent = '';
        }

        if (submit) {
            submit.disabled = true;
        }

        if (hasUpload && pendingId) {
            appendPendingReplyMessage({ pendingId, body, files: filesSnapshot });

            if (els.replyBody) {
                els.replyBody.value = '';
                autoResizeTextarea(els.replyBody);
            }
            replyEmojiPicker?.close?.();
            attachmentHelpersRef().clearAttachmentPool?.(
                replyAttachmentPool,
                document.getElementById('discussionWidgetReplyPreview')
            );
            attachmentHelpersRef().hideUploadProgress?.(progressEl);
        }

        try {
            const formData = new FormData();
            const token = csrfToken();
            if (token) {
                formData.append('_token', token);
            }
            if (body) {
                formData.append('body', body);
            }

            if (hasUpload) {
                filesSnapshot.forEach((file) => {
                    formData.append('attachments[]', file);
                });
            } else {
                attachmentHelpersRef().appendPoolToFormData?.(formData, replyAttachmentPool);
            }

            const data = await sendFormDataWithUpload(
                repliesUrl(currentTopic.id),
                formData,
                hasUpload ? null : progressEl,
                {
                    onProgress(percent, loaded, total, phase) {
                        if (pendingId) {
                            updatePendingReplyProgress(pendingId, percent, phase);
                        }
                    },
                }
            );

            if (!data.reply) {
                throw new Error('Message could not be sent. Please try again.');
            }

            if (pendingId) {
                finalizePendingReplyMessage(pendingId, data.reply);
            } else {
                appendReplyMessage(data.reply);

                if (els.replyBody) {
                    els.replyBody.value = '';
                    autoResizeTextarea(els.replyBody);
                }
                replyEmojiPicker?.close?.();
                attachmentHelpersRef().clearAttachmentPool?.(
                    replyAttachmentPool,
                    document.getElementById('discussionWidgetReplyPreview')
                );
            }
        } catch (error) {
            if (pendingId) {
                failPendingReplyMessage(pendingId, error.message);
            }
            notify('error', error.message);
        } finally {
            attachmentHelpersRef().hideUploadProgress?.(progressEl);
            replySubmitting = false;
            if (submit) {
                submit.disabled = false;
            }
        }
    });

    els.pinBtn?.addEventListener('click', async () => {
        if (!els.pinBtn.dataset.url) {
            return;
        }

        els.pinBtn.disabled = true;

        try {
            const data = await requestJson(els.pinBtn.dataset.url, {
                method: 'POST',
                body: JSON.stringify({}),
            });
            updatePinButton(Boolean(data.is_pinned));
            if (currentTopic) {
                currentTopic.is_pinned = Boolean(data.is_pinned);
            }
            notify('success', data.message || 'Updated.');
            topicsLoaded = false;
        } catch (error) {
            notify('error', error.message);
        } finally {
            els.pinBtn.disabled = false;
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen() && !isPageMode) {
            setOpen(false);
        }
    });

    // Bridge realtime helpers used by discussion.js / echo
    const previousUi = window.soilnwaterDiscussionUi || {};
    window.soilnwaterDiscussionUi = {
        ...previousUi,
        prependTopic(topic) {
            if (!canAccessTopic(topic)) {
                return;
            }

            if (topic.parent_topic_id) {
                if (currentGroup && Number(currentGroup.id) === Number(topic.parent_topic_id)) {
                    reloadCurrentGroup();
                }

                return;
            }

            previousUi.prependTopic?.(topic);
            prependTopicCard(topic);
            if (topic.author?.id !== config.currentUserId) {
                incrementTopicUnread(topic.id, 1);
            }
        },
        appendReply(reply) {
            previousUi.appendReply?.(reply);
            appendReplyMessage(reply);
        },
        reorderTopicPin(topicId, isPinned) {
            previousUi.reorderTopicPin?.(topicId, isPinned);
            const card = document.getElementById(`discussion-widget-topic-${topicId}`);
            if (card) {
                card.classList.toggle('discussion-widget-topic--pinned', isPinned);
            }
            if (currentTopic && Number(currentTopic.id) === Number(topicId)) {
                updatePinButton(isPinned);
            }
        },
        updateTopicPinButton(isPinned) {
            previousUi.updateTopicPinButton?.(isPinned);
            updatePinButton(isPinned);
        },
        updateReactionButtons(container, reaction, active, counts) {
            previousUi.updateReactionButtons?.(container, reaction, active, counts);

            if (!container || !container.classList.contains('discussion-widget-reactions')) {
                return;
            }

            container.querySelectorAll('.discussion-reaction-btn').forEach((button) => {
                const label = button.dataset.reaction;
                const isActive = label === reaction ? active : button.dataset.active === '1';
                button.dataset.active = isActive ? '1' : '0';
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');

                const countEl = button.querySelector('.discussion-reaction-count');
                if (countEl) {
                    const count = counts[label] || 0;
                    countEl.textContent = count > 0 ? String(count) : '';
                }
            });

            if (typeof previousUi.updateReactionSummary === 'function') {
                // handled in discussion.js updateReactionButtons
            } else {
                const summaryEl = container.closest('.discussion-msg__footer')?.querySelector('.discussion-msg__reaction-summary');
                if (summaryEl) {
                    const labels = config.reactionLabels || Object.keys(reactionIcons);
                    const chips = labels
                        .filter((label) => (counts[label] || 0) > 0)
                        .map((label) => {
                            const btn = container.querySelector(`[data-reaction="${label}"]`);
                            const isActiveChip = btn?.dataset.active === '1';
                            const count = counts[label] || 0;
                            return `<span class="discussion-msg__reaction-chip ${isActiveChip ? 'is-mine' : ''}" title="${escapeHtml(label)}">
                                <i class="fa-solid ${reactionIcons[label] || 'fa-face-smile'}"></i>
                                <span>${count}</span>
                            </span>`;
                        })
                        .join('');
                    summaryEl.innerHTML = chips;
                    summaryEl.classList.toggle('is-empty', chips === '');
                }
            }
        },
        findReactionContainer(reactableType, reactableId) {
            return document.querySelector(
                `.discussion-reactions[data-reactable-type="${reactableType}"][data-reactable-id="${reactableId}"]`
            ) || previousUi.findReactionContainer?.(reactableType, reactableId);
        },
        incrementTopicUnread(topicId, amount = 1) {
            incrementTopicUnread(topicId, amount);
        },
        markTopicRead(topicId) {
            markTopicRead(topicId);
        },
        prependTopicWithUnread(topic) {
            if (!canAccessTopic(topic)) {
                return;
            }

            if (topic.parent_topic_id) {
                const groupId = Number(topic.parent_topic_id);
                incrementTopicUnread(groupId, 1);
                if (currentGroup && Number(currentGroup.id) === groupId) {
                    reloadCurrentGroup();
                }

                return;
            }

            previousUi.prependTopic?.(topic);
            prependTopicCard(topic);
            if (topic.author?.id !== config.currentUserId) {
                incrementTopicUnread(topic.id, 1);
            }
        },
        canAccessTopic,
    };

    document.getElementById('discussionWidgetSearch')?.addEventListener('input', (event) => {
        const query = String(event.target.value || '').trim().toLowerCase();
        els.topicList?.querySelectorAll('.discussion-widget-topic').forEach((card) => {
            const haystack = card.dataset.search || card.textContent?.toLowerCase() || '';
            card.hidden = query !== '' && !haystack.includes(query);
        });
    });

    replyAttachmentPool = attachmentHelpersRef().bindAttachmentPicker?.({
        input: document.getElementById('discussionWidgetReplyAttachments'),
        previewEl: document.getElementById('discussionWidgetReplyPreview'),
        imageButton: document.getElementById('discussionWidgetReplyAttachImageBtn'),
        videoButton: document.getElementById('discussionWidgetReplyAttachVideoBtn'),
        documentButton: document.getElementById('discussionWidgetReplyAttachDocumentBtn'),
        onError(message) {
            notify('error', message);
        },
    });

    replyEmojiPicker = window.soilnwaterDiscussionEmoji?.initPicker?.({
        trigger: document.getElementById('discussionWidgetReplyEmojiBtn'),
        textarea: els.replyBody,
        panel: document.getElementById('discussionWidgetReplyEmojiPicker'),
        anchor: els.replyForm,
    }) || null;
    loadUnreadSummary();
    restoreWidgetSize();
    updateFullPageLink(null);

    function syncPageLayoutOnResize() {
        if (!isPageMode) {
            return;
        }

        if (isMobileViewport()) {
            if (!currentTopic && !currentGroup && (activePanelName === 'thread' || activePanelName === 'topics')) {
                showTopics();
                return;
            }

            showPanel(activePanelName);
            return;
        }

        if (!currentTopic && !currentGroup) {
            showTopics();
            return;
        }

        showPanel(activePanelName);
    }

    function initPageMode() {
        if (!isPageMode) {
            return;
        }

        document.body.classList.add('discussion-messenger-page');
        setOpen(true);
        showTopics();

        let pageResizeTimer = 0;
        window.addEventListener('resize', () => {
            window.clearTimeout(pageResizeTimer);
            pageResizeTimer = window.setTimeout(syncPageLayoutOnResize, 120);
        });

        if (config.initialTopicId) {
            openTopic(config.initialTopicId);
            return;
        }

        if (config.initialCompose) {
            showCompose();
        }
    }

    window.soilnwaterDiscussionWidget = {
        open() {
            if (!isOpen()) {
                setOpen(true);
                showTopics();
            }
        },
        close() {
            if (!isPageMode) {
                setOpen(false);
            }
        },
        openTopic(topicId) {
            if (!isOpen()) {
                setOpen(true);
            }
            openTopic(topicId);
        },
        showCompose() {
            if (!isOpen()) {
                setOpen(true);
            }
            showComposeTopic();
        },
        showGroupPick() {
            if (!isOpen()) {
                setOpen(true);
            }
            showGroupPick();
        },
        initPageMode,
    };

    if (isPageMode) {
        initPageMode();
    }
})();

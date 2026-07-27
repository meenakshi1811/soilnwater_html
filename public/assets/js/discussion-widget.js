(function () {
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
        thread: document.getElementById('discussionWidgetThread'),
        compose: document.getElementById('discussionWidgetCompose'),
    };

    const els = {
        title: document.getElementById('discussionWidgetTitle'),
        subtitle: document.getElementById('discussionWidgetSubtitle'),
        topicList: document.getElementById('discussionWidgetTopicList'),
        messages: document.getElementById('discussionWidgetMessages'),
        replyForm: document.getElementById('discussionWidgetReplyForm'),
        replyBody: document.getElementById('discussionWidgetReplyBody'),
        newTopicForm: document.getElementById('discussionWidgetNewTopicForm'),
        pinBtn: document.getElementById('discussionWidgetPinBtn'),
        newTopicBtn: document.getElementById('discussionWidgetNewTopicBtn'),
        closeBtn: document.getElementById('discussionWidgetCloseBtn'),
        backBtn: document.getElementById('discussionWidgetBackBtn'),
        headerAvatar: document.getElementById('discussionWidgetHeaderAvatar'),
        sizeBtn: document.getElementById('discussionWidgetSizeBtn'),
        fullPageBtn: document.getElementById('discussionWidgetFullPageBtn'),
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

    let topicsLoaded = false;
    let currentTopic = null;
    let openRequestId = 0;

    function csrfToken() {
        return config.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }

    function notify(type, message) {
        try {
            if (window.toastr && typeof window.toastr[type] === 'function') {
                window.toastr[type](message);
                return;
            }
        } catch (error) {
            // fall through
        }

        if (type === 'error') {
            console.error(message);
        }
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
            const message = data.message
                || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
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

    function formatUnreadCount(count) {
        return count > 99 ? '99+' : String(count);
    }

    function updateFabBadge(count) {
        config.globalUnread = Math.max(0, count);
        if (!fabBadge) {
            return;
        }

        if (count > 0) {
            fabBadge.textContent = formatUnreadCount(count);
            fabBadge.hidden = false;
            fabBadge.setAttribute('aria-label', `${count} unread messages`);
            fab?.classList.add('has-unread');
        } else {
            fabBadge.hidden = true;
            fabBadge.textContent = '0';
            fab?.classList.remove('has-unread');
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

    function buildAttachmentsHtml(attachments) {
        if (!attachments?.length) {
            return '';
        }

        return `<div class="discussion-attachments">${attachments.map((attachment) => {
            if (attachment.kind === 'video') {
                return `<video class="discussion-attachments__video" controls preload="metadata" src="${escapeHtml(attachment.url || '')}"></video>`;
            }

            return `<a class="discussion-attachments__image-link" href="${escapeHtml(attachment.url || '#')}" target="_blank" rel="noopener">
                <img class="discussion-attachments__image" src="${escapeHtml(attachment.url || '')}" alt="${escapeHtml(attachment.name || 'Attachment')}" loading="lazy">
            </a>`;
        }).join('')}</div>`;
    }

    function bindMediaPreview(input, previewEl) {
        if (!input || !previewEl) {
            return;
        }

        input.addEventListener('change', () => {
            previewEl.innerHTML = '';
            previewEl.hidden = true;

            Array.from(input.files || []).forEach((file, index) => {
                previewEl.hidden = false;
                const item = document.createElement('div');
                item.className = 'discussion-media-preview__item';

                if (file.type.startsWith('video/')) {
                    const video = document.createElement('video');
                    video.src = URL.createObjectURL(file);
                    video.muted = true;
                    item.appendChild(video);
                } else {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    item.appendChild(img);
                }

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'discussion-media-preview__remove';
                remove.innerHTML = '&times;';
                remove.addEventListener('click', () => {
                    const transfer = new DataTransfer();
                    Array.from(input.files || []).forEach((existing, existingIndex) => {
                        if (existingIndex !== index) {
                            transfer.items.add(existing);
                        }
                    });
                    input.files = transfer.files;
                    input.dispatchEvent(new Event('change'));
                });
                item.appendChild(remove);
                previewEl.appendChild(item);
            });
        });
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
            const message = data.message
                || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
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

    function showPanel(name) {
        if (isPageMode) {
            panels.topics?.classList.add('is-active');
            if (panels.topics) {
                panels.topics.hidden = false;
            }

            const showThread = name === 'thread';
            const showCompose = name === 'compose';

            if (panels.thread) {
                panels.thread.classList.toggle('is-active', showThread);
                panels.thread.hidden = !showThread;
            }
            if (panels.compose) {
                panels.compose.classList.toggle('is-active', showCompose);
                panels.compose.hidden = !showCompose;
            }

            updateHeaderForPanel(name);
            return;
        }

        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) {
                return;
            }

            const active = key === name;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;
        });

        updateHeaderForPanel(name);
    }

    function updateHeaderForPanel(name) {
        const isTopics = name === 'topics';
        const isThread = name === 'thread';
        const isCompose = name === 'compose';

        if (els.backBtn) {
            els.backBtn.hidden = isPageMode ? isCompose : isTopics;
        }
        if (els.newTopicBtn) {
            els.newTopicBtn.hidden = isPageMode ? isCompose : !isTopics;
        }
        if (els.pinBtn) {
            els.pinBtn.hidden = !isThread || !config.canPin;
        }
        if (els.closeBtn) {
            els.closeBtn.hidden = isPageMode;
        }
        if (els.sizeBtn) {
            els.sizeBtn.hidden = isPageMode;
        }
        if (els.fullPageBtn) {
            els.fullPageBtn.hidden = isPageMode;
        }
        if (els.headerAvatar && isTopics) {
            els.headerAvatar.innerHTML = '<i class="fa-solid fa-comments"></i>';
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
        els.headerAvatar.innerHTML = '<i class="fa-solid fa-comments"></i>';
    }

    function setOpen(open) {
        if (isPageMode) {
            widget.classList.add('is-open');
            widget.hidden = false;
            document.body.classList.add('discussion-widget-open');
            return;
        }

        fab.classList.toggle('is-open', open);
        fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        widget.classList.toggle('is-open', open);

        if (open) {
            widget.hidden = false;
            document.body.classList.add('discussion-widget-open');
        } else {
            window.setTimeout(() => {
                if (!widget.classList.contains('is-open')) {
                    widget.hidden = true;
                }
            }, 220);
            document.body.classList.remove('discussion-widget-open');
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
            ${avatarHtml(authorName)}
            <div class="discussion-widget-topic__content">
                <div class="discussion-widget-topic__row">
                    <h3 class="discussion-widget-topic__title">${escapeHtml(topic.title)}</h3>
                    ${unreadBadge}
                </div>
                <div class="discussion-widget-topic__row">
                    <p class="discussion-widget-topic__excerpt">${excerpt}</p>
                </div>
                <div class="discussion-widget-topic__row discussion-widget-topic__meta">
                    <span class="discussion-widget-topic__author">${escapeHtml(authorName)}</span>
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

    function renderTopics(topics) {
        if (!els.topicList) {
            return;
        }

        if (!topics.length) {
            els.topicList.innerHTML = `<div class="discussion-widget__empty" id="discussionWidgetEmptyState">
                <div class="discussion-widget__empty-icon"><i class="fa-regular fa-comments"></i></div>
                <h4>No conversations yet</h4>
                <p>Start the first discussion with the community.</p>
            </div>`;
            return;
        }

        els.topicList.innerHTML = topics.map(buildTopicCard).join('');

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
            renderTopics(data.topics || []);
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

        try {
            const data = await requestJson(topicUrl(topicId));
            if (requestId !== openRequestId) {
                return;
            }

            const topic = data.topic;
            currentTopic = topic;
            config.canPin = Boolean(data.can_pin);
            config.topicId = topic.id;

            setHeaderTitle(topic.title, `${(topic.replies_count || 0) + 1} messages`);
            setHeaderAvatar(topic.author?.name || topic.title);

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
            updateFullPageLink(topic.id);
            updateMessengerUrl(topic.id);
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
        config.topicId = null;
        openRequestId += 1;

        const search = document.getElementById('discussionWidgetSearch');
        if (search) {
            search.value = '';
        }

        if (isPageMode) {
            showPanel('thread');
            showEmptyThreadPlaceholder();
            loadTopics();
            updateMessengerUrl(null);
            updateFullPageLink(null);
            return;
        }

        setHeaderTitle('Chats', `${config.globalUnread || 0} unread`);
        resetHeaderAvatar();

        showPanel('topics');
        loadTopics();
        updateFullPageLink(null);
    }

    function showCompose() {
        showPanel('compose');
        setHeaderTitle('New chat', 'Create a group conversation');
        resetHeaderAvatar();
        setComposerVisible(false);
        document.getElementById('discussionWidgetTopicTitle')?.focus();
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

    els.closeBtn?.addEventListener('click', () => setOpen(false));
    els.sizeBtn?.addEventListener('click', cycleWidgetSize);
    els.backBtn?.addEventListener('click', () => {
        if (panels.compose?.classList.contains('is-active')) {
            showTopics();
            return;
        }
        showTopics();
    });
    els.newTopicBtn?.addEventListener('click', showCompose);

    els.topicList?.addEventListener('click', (event) => {
        const card = event.target.closest('[data-topic-id]');
        if (!card) {
            return;
        }

        openTopic(card.dataset.topicId);
    });

    els.replyBody?.addEventListener('input', () => autoResizeTextarea(els.replyBody));

    els.replyForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!currentTopic?.id) {
            return;
        }

        const body = els.replyBody?.value?.trim() || '';
        const fileInput = document.getElementById('discussionWidgetReplyAttachments');
        const hasFiles = fileInput?.files?.length > 0;

        if (!body && !hasFiles) {
            return;
        }

        const submit = els.replyForm.querySelector('[type="submit"]');
        if (submit) {
            submit.disabled = true;
        }

        try {
            const formData = new FormData();
            if (body) {
                formData.append('body', body);
            }
            Array.from(fileInput?.files || []).forEach((file) => {
                formData.append('attachments[]', file);
            });

            const data = await requestFormData(repliesUrl(currentTopic.id), formData);

            if (data.reply) {
                appendReplyMessage(data.reply);
            }

            if (els.replyBody) {
                els.replyBody.value = '';
                autoResizeTextarea(els.replyBody);
            }
            if (fileInput) {
                fileInput.value = '';
            }
            const preview = document.getElementById('discussionWidgetReplyPreview');
            if (preview) {
                preview.innerHTML = '';
                preview.hidden = true;
            }

            notify('success', data.message || 'Reply posted.');
        } catch (error) {
            notify('error', error.message);
        } finally {
            if (submit) {
                submit.disabled = false;
            }
        }
    });

    els.newTopicForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submit = els.newTopicForm.querySelector('[type="submit"]');
        const title = els.newTopicForm.querySelector('[name="title"]')?.value?.trim() || '';
        const body = els.newTopicForm.querySelector('[name="body"]')?.value?.trim() || '';
        const fileInput = document.getElementById('discussionWidgetTopicAttachments');

        if (submit) {
            submit.disabled = true;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            if (body) {
                formData.append('body', body);
            }
            Array.from(fileInput?.files || []).forEach((file) => {
                formData.append('attachments[]', file);
            });

            const data = await requestFormData(els.newTopicForm.dataset.url || config.routes.discussionsStore, formData);

            notify('success', data.message || 'Topic created.');
            els.newTopicForm.reset();
            if (fileInput) {
                fileInput.value = '';
            }
            const preview = document.getElementById('discussionWidgetTopicPreview');
            if (preview) {
                preview.innerHTML = '';
                preview.hidden = true;
            }
            topicsLoaded = false;

            if (data.topic?.id) {
                prependTopicCard(data.topic);
                openTopic(data.topic.id);
            } else {
                showTopics();
            }
        } catch (error) {
            notify('error', error.message);
        } finally {
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
            previousUi.prependTopic?.(topic);
            prependTopicCard(topic);
            if (topic.author?.id !== config.currentUserId) {
                incrementTopicUnread(topic.id, 1);
            }
        },
    };

    document.getElementById('discussionWidgetSearch')?.addEventListener('input', (event) => {
        const query = String(event.target.value || '').trim().toLowerCase();
        els.topicList?.querySelectorAll('.discussion-widget-topic').forEach((card) => {
            const haystack = card.dataset.search || card.textContent?.toLowerCase() || '';
            card.hidden = query !== '' && !haystack.includes(query);
        });
    });

    bindMediaPreview(document.getElementById('discussionWidgetReplyAttachments'), document.getElementById('discussionWidgetReplyPreview'));
    bindMediaPreview(document.getElementById('discussionWidgetTopicAttachments'), document.getElementById('discussionWidgetTopicPreview'));
    loadUnreadSummary();
    restoreWidgetSize();
    updateFullPageLink(null);

    function initPageMode() {
        if (!isPageMode) {
            return;
        }

        document.body.classList.add('discussion-messenger-page');
        setOpen(true);
        showTopics();

        if (config.initialTopicId) {
            openTopic(config.initialTopicId);
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
            showCompose();
        },
        initPageMode,
    };

    if (isPageMode) {
        initPageMode();
    }
})();

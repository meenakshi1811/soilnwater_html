(function () {
    const config = window.soilnwaterDiscussion || {};
    const ui = () => window.soilnwaterDiscussionUi || {};

    const fab = document.getElementById('discussionFab');
    const widget = document.getElementById('discussionWidget');

    if (!fab || !widget) {
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
        composeBackBtn: document.getElementById('discussionWidgetComposeBackBtn'),
    };

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
        } else {
            fabBadge.hidden = true;
            fabBadge.textContent = '0';
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
        Object.entries(panels).forEach(([key, panel]) => {
            if (!panel) {
                return;
            }

            const active = key === name;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;
        });

        if (els.newTopicBtn) {
            els.newTopicBtn.hidden = name !== 'topics';
        }
    }

    function setOpen(open) {
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
        return widget.classList.contains('is-open');
    }

    function buildTopicCard(topic) {
        const excerpt = topic.body
            ? `<p class="discussion-widget-topic__excerpt">${escapeHtml(String(topic.body).slice(0, 120))}${String(topic.body).length > 120 ? '…' : ''}</p>`
            : '';
        const badge = topic.is_pinned
            ? '<span class="discussion-widget-topic__badge"><i class="fa-solid fa-thumbtack"></i> Pinned</span>'
            : '';
        const replies = topic.replies_count || 0;
        const unread = topic.unread_count ?? getTopicUnread(topic.id);
        if (unread > 0) {
            setTopicUnread(topic.id, unread);
        }
        const unreadBadge = unread > 0
            ? `<span class="discussion-widget-topic__unread">${formatUnreadCount(unread)}</span>`
            : '';

        return `<button type="button" class="discussion-widget-topic ${topic.is_pinned ? 'discussion-widget-topic--pinned' : ''} ${unread > 0 ? 'discussion-widget-topic--unread' : ''}" data-topic-id="${topic.id}" id="discussion-widget-topic-${topic.id}">
            ${badge}
            <div class="discussion-widget-topic__head">
                <h3 class="discussion-widget-topic__title">${escapeHtml(topic.title)}</h3>
                ${unreadBadge}
            </div>
            ${excerpt}
            <div class="discussion-widget-topic__meta">
                <span><i class="fa-solid fa-user me-1"></i>${escapeHtml(topic.author?.name || 'Member')}</span>
                <span><i class="fa-regular fa-clock me-1"></i>${escapeHtml(topic.created_at_human || 'just now')}</span>
                <span><i class="fa-solid fa-reply me-1"></i>${replies} ${replies === 1 ? 'reply' : 'replies'}</span>
            </div>
        </button>`;
    }

    function buildReactionsHtml(reactableType, reactableId, reactUrl, counts = {}, userReactions = []) {
        const labels = config.reactionLabels || Object.keys(reactionIcons);

        return `<div class="discussion-widget-reactions discussion-reactions"
                     data-reactable-type="${escapeHtml(reactableType)}"
                     data-reactable-id="${escapeHtml(reactableId)}"
                     data-react-url="${escapeHtml(reactUrl)}">
            ${labels.map((label) => {
                const active = userReactions.includes(label);
                const count = counts[label] || 0;
                return `<button type="button"
                            class="discussion-widget-reaction discussion-reaction-btn ${active ? 'is-active' : ''}"
                            data-reaction="${escapeHtml(label)}"
                            data-active="${active ? '1' : '0'}"
                            aria-pressed="${active ? 'true' : 'false'}"
                            title="${escapeHtml(label)}"
                            aria-label="${escapeHtml(label)}${count > 0 ? ` (${count})` : ''}">
                        <i class="fa-solid ${reactionIcons[label] || 'fa-face-smile'}"></i>
                        <span class="discussion-reaction-count">${count > 0 ? count : ''}</span>
                    </button>`;
            }).join('')}
        </div>`;
    }

    function buildTopicMessage(topic) {
        return `<article class="discussion-widget-msg discussion-widget-msg--topic" data-reactable-type="DiscussionTopic" data-reactable-id="${topic.id}">
            <div class="discussion-widget-msg__meta">
                <span class="discussion-widget-msg__author">${escapeHtml(topic.author?.name || 'Member')}</span>
                <span>${escapeHtml(topic.created_at_human || 'just now')}</span>
            </div>
            <h3 class="discussion-widget-msg__title">${escapeHtml(topic.title)}</h3>
            ${topic.body ? `<p class="discussion-widget-msg__body">${escapeHtml(topic.body)}</p>` : ''}
            ${buildAttachmentsHtml(topic.attachments || [])}
            ${buildReactionsHtml('DiscussionTopic', topic.id, topicReactUrl(topic.id), topic.reaction_counts || {}, topic.user_reactions || [])}
        </article>`;
    }

    function buildReplyMessage(reply) {
        const mine = Number(reply.author?.id) === Number(config.currentUserId);
        return `<article class="discussion-widget-msg ${mine ? 'discussion-widget-msg--mine' : ''}"
                         id="discussion-widget-reply-${reply.id}"
                         data-reply-id="${reply.id}"
                         data-reactable-type="DiscussionReply"
                         data-reactable-id="${reply.id}">
            <div class="discussion-widget-msg__meta">
                <span class="discussion-widget-msg__author">${escapeHtml(reply.author?.name || 'Member')}</span>
                <span>${escapeHtml(reply.created_at_human || 'just now')}</span>
            </div>
            ${reply.body ? `<p class="discussion-widget-msg__body">${escapeHtml(reply.body || '')}</p>` : ''}
            ${buildAttachmentsHtml(reply.attachments || [])}
            ${buildReactionsHtml('DiscussionReply', reply.id, replyReactUrl(reply.id), reply.reaction_counts || {}, reply.user_reactions || [])}
        </article>`;
    }

    function renderTopics(topics) {
        if (!els.topicList) {
            return;
        }

        if (!topics.length) {
            els.topicList.innerHTML = `<div class="discussion-widget__empty" id="discussionWidgetEmptyState">
                <i class="fa-regular fa-comments fa-2x"></i>
                <p>No topics yet. Start the first conversation.</p>
            </div>`;
            return;
        }

        els.topicList.innerHTML = topics.map(buildTopicCard).join('');
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
        els.pinBtn.innerHTML = `<i class="fa-solid fa-thumbtack"></i><span>${isPinned ? 'Unpin' : 'Pin'}</span>`;
    }

    async function openTopic(topicId) {
        const requestId = ++openRequestId;
        currentTopic = { id: topicId };
        config.topicId = topicId;

        showPanel('thread');

        if (els.title) {
            els.title.textContent = 'Conversation';
        }
        if (els.subtitle) {
            els.subtitle.textContent = 'Loading…';
        }
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

            if (els.title) {
                els.title.textContent = topic.title;
            }
            if (els.subtitle) {
                const count = topic.replies_count || 0;
                els.subtitle.textContent = `${count} ${count === 1 ? 'reply' : 'replies'}`;
            }

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
                html += `<div class="discussion-widget__empty" id="discussionWidgetEmptyReplies"><p>No replies yet. Start the conversation.</p></div>`;
            }

            if (els.messages) {
                els.messages.innerHTML = html;
                els.messages.scrollTop = els.messages.scrollHeight;
            }

            markTopicRead(topic.id);
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

        if (els.title) {
            els.title.textContent = 'Community Chat';
        }
        if (els.subtitle) {
            els.subtitle.textContent = 'Discussions with fellow members';
        }

        showPanel('topics');
        loadTopics();
    }

    function showCompose() {
        showPanel('compose');
        if (els.title) {
            els.title.textContent = 'New topic';
        }
        if (els.subtitle) {
            els.subtitle.textContent = 'Share something with the community';
        }
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

    fab.addEventListener('click', () => {
        toggleOpen();
    });

    els.closeBtn?.addEventListener('click', () => setOpen(false));
    els.backBtn?.addEventListener('click', showTopics);
    els.composeBackBtn?.addEventListener('click', showTopics);
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
        if (event.key === 'Escape' && isOpen()) {
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

    bindMediaPreview(document.getElementById('discussionWidgetReplyAttachments'), document.getElementById('discussionWidgetReplyPreview'));
    bindMediaPreview(document.getElementById('discussionWidgetTopicAttachments'), document.getElementById('discussionWidgetTopicPreview'));
    loadUnreadSummary();

    window.soilnwaterDiscussionWidget = {
        open() {
            if (!isOpen()) {
                setOpen(true);
                showTopics();
            }
        },
        close() {
            setOpen(false);
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
    };
})();

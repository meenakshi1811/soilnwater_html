(function () {
    const config = window.soilnwaterDiscussion || {};

    function csrfToken() {
        return config.csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }

    function notify(type, message) {
        const toastType = type === 'error' ? 'error' : type;

        try {
            if (window.toastr && window.jQuery && typeof window.toastr[toastType] === 'function') {
                window.toastr[toastType](message);
                return;
            }
        } catch (error) {
            console.warn('Toastr unavailable.', error);
        }

        console.warn(message);
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message || data.errors
                ? Object.values(data.errors || {}).flat().join(' ')
                : 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    async function postFormData(url, formData) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
            credentials: 'same-origin',
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message || data.errors
                ? Object.values(data.errors || {}).flat().join(' ')
                : 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function nl2br(value) {
        return escapeHtml(value).replace(/\n/g, '<br>');
    }

    function formatUnreadCount(count) {
        return count > 99 ? '99+' : String(count);
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

    function updateReactionSummary(container, counts) {
        const reactableType = container?.dataset.reactableType;
        const reactableId = container?.dataset.reactableId;
        let summaryEl = container?.closest('.discussion-msg__bubble-wrap')
            ?.querySelector('.discussion-msg__reaction-summary');

        if (!summaryEl && reactableType && reactableId) {
            summaryEl = document.querySelector(
                `[data-reactable-type="${CSS.escape(reactableType)}"][data-reactable-id="${CSS.escape(String(reactableId))}"] .discussion-msg__reaction-summary`
            );
        }

        if (!summaryEl) {
            return;
        }

        const labels = config.reactionLabels || ['Like', 'Love', 'Insightful', 'Agree'];
        const icons = config.reactionIcons || {
            Like: 'fa-thumbs-up',
            Love: 'fa-heart',
            Insightful: 'fa-lightbulb',
            Agree: 'fa-check',
        };

        const chips = labels
            .filter((label) => (counts[label] || 0) > 0)
            .map((label) => {
                const button = container.querySelector(`[data-reaction="${label}"]`);
                const active = button?.dataset.active === '1';
                const count = counts[label] || 0;
                return `<span class="discussion-msg__reaction-chip ${active ? 'is-mine' : ''}" title="${escapeHtml(label)}">
                    <i class="fa-solid ${icons[label] || 'fa-face-smile'}"></i>
                    <span>${count}</span>
                </span>`;
            })
            .join('');

        summaryEl.innerHTML = chips;
        summaryEl.classList.toggle('is-empty', chips === '');
    }

    function updateReactionButtons(container, reaction, active, counts) {
        if (!container) {
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

        updateReactionSummary(container, counts);
    }

    function resetMessageMenuPanel(panel) {
        if (!panel) {
            return;
        }

        panel.classList.remove('is-floating');
        panel.style.top = '';
        panel.style.left = '';

        const host = panel._discussionMenuHost;
        if (host && panel.parentElement === document.body) {
            host.appendChild(panel);
        }

        panel.hidden = true;
    }

    function positionMessageMenuPanel(menuBtn, panel) {
        const isMine = Boolean(menuBtn.closest('.discussion-msg--mine, .discussion-widget-msg--mine'));
        const menu = menuBtn.closest('.discussion-msg__menu');
        if (menu) {
            panel._discussionMenuHost = menu;
        }

        const padding = 8;
        const gap = 4;

        panel.classList.add('is-floating');
        panel.hidden = false;

        if (panel.parentElement !== document.body) {
            document.body.appendChild(panel);
        }

        const btnRect = menuBtn.getBoundingClientRect();
        const panelRect = panel.getBoundingClientRect();

        let left = isMine ? btnRect.right - panelRect.width : btnRect.left;
        let top = btnRect.bottom + gap;

        left = Math.max(padding, Math.min(left, window.innerWidth - panelRect.width - padding));

        if (top + panelRect.height > window.innerHeight - padding) {
            top = btnRect.top - panelRect.height - gap;
        }

        top = Math.max(padding, top);

        panel.style.left = `${Math.round(left)}px`;
        panel.style.top = `${Math.round(top)}px`;
    }

    function closeAllMessageMenus() {
        document.querySelectorAll('.discussion-msg__menu-panel').forEach((panel) => {
            resetMessageMenuPanel(panel);
        });
        document.querySelectorAll('.discussion-msg__menu-btn').forEach((btn) => {
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    function findReactionContainer(reactableType, reactableId) {
        return document.querySelector(
            `.discussion-reactions[data-reactable-type="${reactableType}"][data-reactable-id="${reactableId}"]`
        );
    }

    function replyReactUrl(replyId) {
        if (config.routes?.replyReactTemplate) {
            return config.routes.replyReactTemplate.replace('__REPLY__', String(replyId));
        }

        return `/discussions/replies/${replyId}/react`;
    }

    function avatarInitials(name) {
        const parts = String(name || 'M').trim().split(/\s+/).filter(Boolean);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }

        return (parts[0]?.[0] || 'M').toUpperCase();
    }

    function avatarHtml(name, extraClass = 'discussion-avatar--sm') {
        return `<span class="discussion-avatar ${extraClass}" aria-hidden="true">${escapeHtml(avatarInitials(name))}</span>`;
    }

    function buildReactionsMenuHtml(reactableType, reactableId, reactUrl, counts = {}, userReactions = []) {
        const reactionLabels = config.reactionLabels || ['Like', 'Love', 'Insightful', 'Agree'];
        const icons = config.reactionIcons || {
            Like: 'fa-thumbs-up',
            Love: 'fa-heart',
            Insightful: 'fa-lightbulb',
            Agree: 'fa-check',
        };

        const summary = reactionLabels
            .filter((label) => (counts[label] || 0) > 0)
            .map((label) => {
                const active = userReactions.includes(label);
                const count = counts[label] || 0;
                return `<span class="discussion-msg__reaction-chip ${active ? 'is-mine' : ''}" title="${escapeHtml(label)}">
                    <i class="fa-solid ${icons[label] || 'fa-face-smile'}"></i>
                    <span>${count}</span>
                </span>`;
            })
            .join('');

        const menuItems = reactionLabels.map((label) => {
            const active = userReactions.includes(label);
            const count = counts[label] || 0;
            const icon = icons[label] || 'fa-face-smile';
            return `<button type="button"
                        class="discussion-reaction-btn discussion-reaction-menu-item ${active ? 'is-active' : ''}"
                        data-reaction="${escapeHtml(label)}"
                        data-active="${active ? '1' : '0'}"
                        aria-pressed="${active ? 'true' : 'false'}">
                    <i class="fa-solid ${icon}"></i>
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
                    <div class="discussion-reactions discussion-reactions--menu"
                         data-reactable-type="${escapeHtml(reactableType)}"
                         data-reactable-id="${escapeHtml(reactableId)}"
                         data-react-url="${escapeHtml(reactUrl)}">
                        ${menuItems}
                    </div>
                </div>
            </div>
        </div>`;
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

    function buildReplyHtml(reply) {
        const bodyHtml = reply.body
            ? `<p class="discussion-reply-body">${nl2br(reply.body || '')}</p>`
            : '';
        const authorName = reply.author?.name || 'Community member';

        return `<div class="discussion-reply" id="discussion-reply-${reply.id}" data-reply-id="${reply.id}" data-reactable-type="DiscussionReply" data-reactable-id="${reply.id}">
            ${avatarHtml(authorName)}
            <div class="discussion-reply__content">
                <div class="discussion-reply__header">
                    <span class="discussion-reply__author">${escapeHtml(authorName)}</span>
                    <small class="discussion-reply-time">${escapeHtml(formatMessageTimestamp(reply))}</small>
                </div>
                <div class="discussion-msg__bubble-wrap">
                    <div class="discussion-msg__bubble">
                        ${bodyHtml}
                        ${buildAttachmentsHtml(reply.attachments || [])}
                    </div>
                    ${buildReactionsMenuHtml('DiscussionReply', reply.id, replyReactUrl(reply.id), reply.reaction_counts || {}, reply.user_reactions || [])}
                </div>
            </div>
        </div>`;
    }

    function buildTopicCardHtml(topic) {
        const authorName = topic.author?.name || 'Community member';
        const pinnedClass = topic.is_pinned ? 'discussion-topic-card--pinned' : '';
        const unread = topic.unread_count || 0;
        const unreadClass = unread > 0 ? 'discussion-topic-card--unread' : '';
        const unreadBadge = unread > 0
            ? `<span class="discussion-topic-unread-badge">${formatUnreadCount(unread)}</span>`
            : '';
        const excerpt = topic.body
            ? `<p class="discussion-topic-excerpt">${escapeHtml(topic.body).slice(0, 120)}</p>`
            : '';
        const replyLabel = topic.replies_count === 1 ? 'reply' : 'replies';

        return `<article class="discussion-topic-card ${pinnedClass} ${unreadClass}" data-topic-id="${topic.id}" id="discussion-topic-${topic.id}">
            ${avatarHtml(authorName)}
            <div class="discussion-topic-card__body">
                <div class="discussion-topic-card__top">
                    <a href="${escapeHtml(topic.url)}" class="discussion-topic-link">${escapeHtml(topic.title)}</a>
                    ${unreadBadge}
                </div>
                ${excerpt}
                <div class="discussion-topic-meta">
                    <span>${escapeHtml(authorName)}</span>
                    <span>${topic.replies_count || 0} ${replyLabel}</span>
                    <time class="discussion-widget-topic__date">${escapeHtml(formatMessageTimestamp(topic))}</time>
                </div>
            </div>
            <div class="d-flex flex-column align-items-end gap-2">
                ${unreadBadge}
                <a href="${escapeHtml(topic.url)}" class="discussion-btn discussion-btn--outline discussion-btn--sm">Open</a>
            </div>
        </article>`;
    }

    function prependTopic(topic) {
        const list = document.getElementById('discussionTopicList');
        if (!list || document.getElementById(`discussion-topic-${topic.id}`)) {
            return;
        }

        const empty = document.getElementById('discussionEmptyState');
        if (empty) {
            empty.remove();
        }

        list.insertAdjacentHTML('afterbegin', buildTopicCardHtml(topic));
    }

    function appendReply(reply) {
        if (document.getElementById(`discussion-reply-${reply.id}`)) {
            return;
        }

        const list = document.getElementById('discussionReplyList');
        if (!list) {
            return;
        }

        const empty = document.getElementById('discussionEmptyReplies');
        if (empty) {
            empty.remove();
        }

        list.insertAdjacentHTML('beforeend', buildReplyHtml(reply));

        const countEl = document.getElementById('discussionReplyCount');
        if (countEl) {
            countEl.textContent = String((parseInt(countEl.textContent, 10) || 0) + 1);
        }
    }

    function reorderTopicPin(topicId, isPinned) {
        const card = document.getElementById(`discussion-topic-${topicId}`);
        if (!card) {
            return;
        }

        card.classList.toggle('discussion-topic-card--pinned', isPinned);
        const badge = card.querySelector('.discussion-pin-badge');
        if (isPinned && !badge) {
            const body = card.querySelector('.discussion-topic-card__body');
            if (body) {
                body.insertAdjacentHTML('afterbegin', '<span class="discussion-pin-badge"><i class="fa-solid fa-thumbtack"></i> Pinned</span>');
            }
        } else if (!isPinned && badge) {
            badge.remove();
        }

        const list = document.getElementById('discussionTopicList');
        if (list && isPinned) {
            list.prepend(card);
        }
    }

    function updateTopicPinButton(isPinned) {
        const button = document.querySelector('.discussion-pin-btn');
        if (!button) {
            return;
        }

        button.dataset.pinned = isPinned ? '1' : '0';
        button.classList.toggle('is-pinned', isPinned);
        button.innerHTML = `<i class="fa-solid fa-thumbtack"></i>${isPinned ? 'Unpin' : 'Pin'}`;

        const titleBadge = document.querySelector('.discussion-topic-title .discussion-pin-badge');
        if (isPinned && !titleBadge) {
            const title = document.getElementById('discussionTopicTitle');
            if (title) {
                title.insertAdjacentHTML('beforebegin', '<span class="discussion-pin-badge me-2"><i class="fa-solid fa-thumbtack"></i> Pinned</span>');
            }
        } else if (!isPinned && titleBadge) {
            titleBadge.remove();
        }
    }

    async function handleReactionClick(event) {
        const button = event.target.closest('.discussion-reaction-btn');
        if (!button) {
            return;
        }

        const container = button.closest('.discussion-reactions');
        if (!container) {
            return;
        }

        const url = container.dataset.reactUrl;
        if (!url) {
            return;
        }

        button.disabled = true;

        try {
            const data = await postJson(url, { reaction: button.dataset.reaction });
            updateReactionButtons(container, data.reaction, data.active, data.counts || {});
            closeAllMessageMenus();
        } catch (error) {
            notify('error', error.message);
        } finally {
            button.disabled = false;
        }
    }

    function bindMessageMenuDelegation(root) {
        root.addEventListener('click', (event) => {
            const menuBtn = event.target.closest('.discussion-msg__menu-btn');
            if (menuBtn) {
                event.preventDefault();
                event.stopPropagation();
                const menu = menuBtn.closest('.discussion-msg__menu');
                const willOpen = menuBtn.getAttribute('aria-expanded') !== 'true';
                closeAllMessageMenus();
                const panel = menu?.querySelector('.discussion-msg__menu-panel');
                if (willOpen && panel) {
                    positionMessageMenuPanel(menuBtn, panel);
                    menuBtn.setAttribute('aria-expanded', 'true');
                }
                return;
            }

            if (!event.target.closest('.discussion-msg__menu')
                && !event.target.closest('.discussion-msg__menu-panel')) {
                closeAllMessageMenus();
            }
        });
    }

    function bindReactionDelegation(root) {
        root.addEventListener('click', (event) => {
            if (event.target.closest('.discussion-reaction-btn')) {
                event.preventDefault();
                handleReactionClick(event);
            }
        });
    }

    function initNewTopicForm() {
        const form = document.getElementById('newTopicForm');
        if (!form) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submit = form.querySelector('[type="submit"]');
            if (submit) {
                submit.disabled = true;
            }

            try {
                const data = await postJson(form.dataset.url, {
                    title: form.querySelector('[name="title"]')?.value || '',
                    body: form.querySelector('[name="body"]')?.value || '',
                });

                notify('success', data.message);

                form.reset();
                const modalEl = document.getElementById('newTopicModal');
                if (modalEl && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }

                if (data.topic && window.soilnwaterDiscussionWidget?.openTopic) {
                    window.soilnwaterDiscussionUi?.prependTopic?.(data.topic);
                    window.soilnwaterDiscussionWidget.openTopic(data.topic.id);
                    return;
                }

                if (data.topic?.url) {
                    window.location.href = data.topic.url;
                }
            } catch (error) {
                notify('error', error.message);
            } finally {
                if (submit) {
                    submit.disabled = false;
                }
            }
        });
    }

    function initReplyForm() {
        const form = document.getElementById('discussionReplyForm');
        if (!form) {
            return;
        }

        bindMediaPreview(document.getElementById('replyAttachments'), document.getElementById('replyAttachmentsPreview'));

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submit = form.querySelector('[type="submit"]');
            const textarea = form.querySelector('[name="body"]');
            const fileInput = document.getElementById('replyAttachments');
            const body = textarea?.value?.trim() || '';
            const hasFiles = fileInput?.files?.length > 0;

            if (!body && !hasFiles) {
                notify('error', 'Please enter a message or attach a file.');
                return;
            }

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

                const data = await postFormData(form.dataset.url, formData);

                if (data.reply) {
                    appendReply(data.reply);
                }

                if (textarea) {
                    textarea.value = '';
                }
                if (fileInput) {
                    fileInput.value = '';
                }
                const preview = document.getElementById('replyAttachmentsPreview');
                if (preview) {
                    preview.innerHTML = '';
                    preview.hidden = true;
                }

                notify('success', data.message);
            } catch (error) {
                notify('error', error.message);
            } finally {
                if (submit) {
                    submit.disabled = false;
                }
            }
        });
    }

    function initPinButton() {
        const button = document.querySelector('.discussion-pin-btn');
        if (!button) {
            return;
        }

        button.addEventListener('click', async () => {
            button.disabled = true;

            try {
                const data = await postJson(button.dataset.url, {});
                updateTopicPinButton(data.is_pinned);
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message);
            } finally {
                button.disabled = false;
            }
        });
    }

    window.soilnwaterDiscussionUi = {
        prependTopic,
        appendReply,
        reorderTopicPin,
        updateTopicPinButton,
        updateReactionButtons,
        findReactionContainer,
        closeAllMessageMenus,
    };

    document.addEventListener('DOMContentLoaded', () => {
        bindReactionDelegation(document);
        bindMessageMenuDelegation(document);
        document.querySelectorAll('.discussion-widget__scroll').forEach((el) => {
            el.addEventListener('scroll', closeAllMessageMenus, { passive: true });
        });
        window.addEventListener('resize', closeAllMessageMenus);
        initNewTopicForm();
        initReplyForm();
        initPinButton();
    });
})();

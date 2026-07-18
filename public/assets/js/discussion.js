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

    function updateReactionButtons(container, reaction, active, counts) {
        if (!container) {
            return;
        }

        container.querySelectorAll('.discussion-reaction-btn').forEach((button) => {
            const label = button.dataset.reaction;
            const isActive = label === reaction ? active : button.dataset.active === '1';
            button.dataset.active = isActive ? '1' : '0';
            button.classList.toggle('btn-success', isActive);
            button.classList.toggle('btn-outline-secondary', !isActive);
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');

            const countEl = button.querySelector('.discussion-reaction-count');
            if (countEl) {
                const count = counts[label] || 0;
                countEl.textContent = count > 0 ? String(count) : '';
            }
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

    function buildReplyHtml(reply) {
        const reactionLabels = config.reactionLabels || ['Like', 'Love', 'Insightful', 'Agree'];
        const icons = {
            Like: 'fa-thumbs-up',
            Love: 'fa-heart',
            Insightful: 'fa-lightbulb',
            Agree: 'fa-check',
        };

        const buttons = reactionLabels.map((label) => {
            const icon = icons[label] || 'fa-face-smile';
            return `<button type="button" class="btn btn-sm discussion-reaction-btn btn-outline-secondary" data-reaction="${escapeHtml(label)}" data-active="0" aria-pressed="false">
                <i class="fa-solid ${icon} me-1"></i>
                <span class="discussion-reaction-label">${escapeHtml(label)}</span>
                <span class="discussion-reaction-count"></span>
            </button>`;
        }).join('');

        return `<div class="discussion-reply border rounded-3 p-3 mb-3" id="discussion-reply-${reply.id}" data-reply-id="${reply.id}" data-reactable-type="DiscussionReply" data-reactable-id="${reply.id}">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                <div>
                    <strong>${escapeHtml(reply.author?.name || 'Community member')}</strong>
                    <small class="text-muted d-block discussion-reply-time">${escapeHtml(reply.created_at_human || 'just now')}</small>
                </div>
            </div>
            <p class="mb-2 discussion-reply-body">${nl2br(reply.body || '')}</p>
            <div class="discussion-reactions d-flex flex-wrap gap-2" data-reactable-type="DiscussionReply" data-reactable-id="${reply.id}" data-react-url="${escapeHtml(replyReactUrl(reply.id))}">
                ${buttons}
            </div>
        </div>`;
    }

    function buildTopicCardHtml(topic) {
        const pinnedBadge = topic.is_pinned
            ? '<span class="badge bg-warning text-dark mb-2 discussion-pin-badge"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>'
            : '';
        const excerpt = topic.body
            ? `<p class="text-muted small mb-2 discussion-topic-excerpt">${escapeHtml(topic.body).slice(0, 140)}</p>`
            : '';
        const replyLabel = topic.replies_count === 1 ? 'reply' : 'replies';

        return `<div class="discussion-topic-card border rounded-3 p-3 mb-3 ${topic.is_pinned ? 'discussion-topic-card--pinned' : ''}" data-topic-id="${topic.id}" id="discussion-topic-${topic.id}">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div class="flex-grow-1">
                    ${pinnedBadge}
                    <h3 class="h6 mb-1">
                        <a href="${escapeHtml(topic.url)}" class="text-decoration-none discussion-topic-link">${escapeHtml(topic.title)}</a>
                    </h3>
                    ${excerpt}
                    <div class="d-flex flex-wrap gap-3 small text-muted">
                        <span><i class="fa-solid fa-user me-1"></i>${escapeHtml(topic.author?.name || 'Community member')}</span>
                        <span><i class="fa-regular fa-clock me-1"></i>${escapeHtml(topic.created_at_human || 'just now')}</span>
                        <span><i class="fa-solid fa-reply me-1"></i>${topic.replies_count || 0} ${replyLabel}</span>
                    </div>
                </div>
                <a href="${escapeHtml(topic.url)}" class="btn btn-sm btn-outline-success">Open</a>
            </div>
        </div>`;
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
            const titleWrap = card.querySelector('.flex-grow-1');
            if (titleWrap) {
                titleWrap.insertAdjacentHTML('afterbegin', '<span class="badge bg-warning text-dark mb-2 discussion-pin-badge"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>');
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
        button.classList.toggle('btn-warning', isPinned);
        button.classList.toggle('btn-outline-warning', !isPinned);
        button.innerHTML = `<i class="fa-solid fa-thumbtack me-1"></i>${isPinned ? 'Unpin' : 'Pin'}`;

        const titleBadge = document.querySelector('.discussion-topic-title .discussion-pin-badge');
        if (isPinned && !titleBadge) {
            const title = document.getElementById('discussionTopicTitle');
            if (title) {
                title.insertAdjacentHTML('beforebegin', '<span class="badge bg-warning text-dark me-2 discussion-pin-badge"><i class="fa-solid fa-thumbtack me-1"></i>Pinned</span>');
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
            notify('success', data.message);
        } catch (error) {
            notify('error', error.message);
        } finally {
            button.disabled = false;
        }
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

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submit = form.querySelector('[type="submit"]');
            const textarea = form.querySelector('[name="body"]');
            if (submit) {
                submit.disabled = true;
            }

            try {
                const data = await postJson(form.dataset.url, {
                    body: textarea?.value || '',
                });

                if (data.reply) {
                    appendReply(data.reply);
                }

                if (textarea) {
                    textarea.value = '';
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
    };

    document.addEventListener('DOMContentLoaded', () => {
        bindReactionDelegation(document);
        initNewTopicForm();
        initReplyForm();
        initPinButton();
    });
})();

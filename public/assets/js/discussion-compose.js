(function () {
    const config = () => window.soilnwaterDiscussion || {};

    function csrfToken() {
        return config().csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
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

    function renderMemberChips(containerId, selectionMap) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        container.innerHTML = Array.from(selectionMap.values()).map((user) => {
            return `<span class="discussion-widget__member-chip" data-user-id="${user.id}">
                ${escapeHtml(user.name)}
                <button type="button" aria-label="Remove ${escapeHtml(user.name)}">&times;</button>
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

            selectionMap.delete(Number(chip.dataset.userId));
            renderMemberChips(containerId, selectionMap);
        });
    }

    async function searchUsers(query) {
        const usersSearch = config().routes?.usersSearch;
        if (!usersSearch) {
            return [];
        }

        const url = new URL(usersSearch, window.location.origin);
        url.searchParams.set('q', query);

        const data = await requestJson(url.toString(), { method: 'GET' });

        return data.users || [];
    }

    function renderMemberSearchResults(resultsEl, users, selectionMap, chipsContainerId) {
        if (!resultsEl) {
            return;
        }

        if (!users.length) {
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';

            return;
        }

        resultsEl.hidden = false;
        resultsEl.innerHTML = users.map((user) => {
            const selected = selectionMap.has(Number(user.id));

            return `<button type="button" class="discussion-widget__member-result ${selected ? 'is-selected' : ''}" data-user-id="${user.id}" data-user-name="${escapeHtml(user.name)}" ${selected ? 'disabled' : ''}>
                <strong>${escapeHtml(user.name)}</strong>
                <span>${escapeHtml(user.email || '')}</span>
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
                const input = resultsEl.previousElementSibling;
                if (input) {
                    input.value = '';
                }
            });
        });
    }

    function bindMemberSearchInput(inputId, resultsId, selectionMap, chipsContainerId) {
        const input = document.getElementById(inputId);
        const resultsEl = document.getElementById(resultsId);
        if (!input || !resultsEl) {
            return;
        }

        let timer = null;

        input.addEventListener('input', () => {
            clearTimeout(timer);
            const query = input.value.trim();

            if (query.length < 2) {
                resultsEl.hidden = true;
                resultsEl.innerHTML = '';

                return;
            }

            timer = window.setTimeout(async () => {
                try {
                    const users = await searchUsers(query);
                    renderMemberSearchResults(resultsEl, users, selectionMap, chipsContainerId);
                } catch (error) {
                    console.error(error);
                }
            }, 250);
        });
    }

    function syncChatType(form, membersFieldId) {
        if (!form) {
            return;
        }

        const isGroup = form.querySelector('input[name="is_group"]:checked')?.value === '1';
        const field = document.getElementById(membersFieldId);

        if (field) {
            field.hidden = !isGroup;
        }
    }

    function buildFormData(form, memberSelection, attachmentPool) {
        const formData = new FormData();
        const title = form.querySelector('[name="title"]')?.value?.trim() || '';
        const body = form.querySelector('[name="body"]')?.value?.trim() || '';
        const isGroup = form.querySelector('input[name="is_group"]:checked')?.value === '1';

        formData.append('title', title);
        formData.append('is_group', isGroup ? '1' : '0');

        if (body) {
            formData.append('body', body);
        }

        Array.from(memberSelection.values()).forEach((member) => {
            formData.append('member_ids[]', String(member.id));
        });

        window.soilnwaterDiscussionAttachments?.appendPoolToFormData?.(formData, attachmentPool);

        return formData;
    }

    function resetForm(form, memberSelection, attachmentPool, membersFieldId, memberChipsId, attachmentsPreviewId) {
        form?.reset();
        memberSelection.clear();
        renderMemberChips(memberChipsId, memberSelection);
        syncChatType(form, membersFieldId);
        window.soilnwaterDiscussionAttachments?.clearAttachmentPool?.(
            attachmentPool,
            document.getElementById(attachmentsPreviewId)
        );
    }

    function initNewChatForm(options) {
        const form = typeof options.form === 'string'
            ? document.getElementById(options.form)
            : options.form;

        if (!form) {
            return null;
        }

        const memberSelection = new Map();
        const attachmentHelpers = window.soilnwaterDiscussionAttachments || {};
        const attachmentPool = attachmentHelpers.bindAttachmentPicker?.({
            input: document.getElementById(options.attachmentsInputId),
            previewEl: document.getElementById(options.attachmentsPreviewId),
            imageButton: document.getElementById(options.attachImageBtnId),
            videoButton: document.getElementById(options.attachVideoBtnId),
            documentButton: document.getElementById(options.attachDocumentBtnId),
        });

        form.querySelectorAll('input[name="is_group"]').forEach((input) => {
            input.addEventListener('change', () => {
                syncChatType(form, options.membersFieldId);
                if (form.querySelector('input[name="is_group"]:checked')?.value !== '1') {
                    memberSelection.clear();
                    renderMemberChips(options.memberChipsId, memberSelection);
                }
            });
        });

        bindMemberChipRemoval(options.memberChipsId, memberSelection);
        bindMemberSearchInput(
            options.memberSearchId,
            options.memberResultsId,
            memberSelection,
            options.memberChipsId
        );
        syncChatType(form, options.membersFieldId);

        if (options.handleSubmit === false) {
            return {
                form,
                memberSelection,
                attachmentPool,
                syncChatType: () => syncChatType(form, options.membersFieldId),
                buildFormData: () => buildFormData(form, memberSelection, attachmentPool),
                reset: () => resetForm(
                    form,
                    memberSelection,
                    attachmentPool,
                    options.membersFieldId,
                    options.memberChipsId,
                    options.attachmentsPreviewId
                ),
            };
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const submit = form.querySelector('[type="submit"]');
            if (submit) {
                submit.disabled = true;
            }

            try {
                const formData = buildFormData(form, memberSelection, attachmentPool);
                const response = await fetch(form.dataset.url || config().routes?.discussionsStore, {
                    method: 'POST',
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
                    throw new Error(
                        data.message
                        || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                        || 'Something went wrong.'
                    );
                }

                resetForm(
                    form,
                    memberSelection,
                    attachmentPool,
                    options.membersFieldId,
                    options.memberChipsId,
                    options.attachmentsPreviewId
                );

                if (typeof options.onSuccess === 'function') {
                    options.onSuccess(data);
                }
            } catch (error) {
                if (typeof options.onError === 'function') {
                    options.onError(error);
                } else {
                    console.error(error.message);
                }
            } finally {
                if (submit) {
                    submit.disabled = false;
                }
            }
        });

        return {
            form,
            memberSelection,
            attachmentPool,
            syncChatType: () => syncChatType(form, options.membersFieldId),
            buildFormData: () => buildFormData(form, memberSelection, attachmentPool),
            reset: () => resetForm(
                form,
                memberSelection,
                attachmentPool,
                options.membersFieldId,
                options.memberChipsId,
                options.attachmentsPreviewId
            ),
        };
    }

    window.soilnwaterDiscussionCompose = {
        initNewChatForm,
        syncChatType,
        renderMemberChips,
    };
})();

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

    function normalizePhoneDigits(value) {
        const digits = String(value ?? '').replace(/\D+/g, '');

        if (digits.length < 10) {
            return null;
        }

        return digits.length > 10 ? digits.slice(-10) : digits;
    }

    function selectionMapKey(entry) {
        if (entry?.id) {
            return String(entry.id);
        }

        return `phone:${entry.phone}`;
    }

    function deleteSelectionEntry(selectionMap, key) {
        if (String(key).startsWith('phone:')) {
            selectionMap.delete(String(key));
            return;
        }

        selectionMap.delete(Number(key));
    }

    function appendMemberSelectionsToFormData(formData, memberSelection) {
        Array.from(memberSelection.values()).forEach((member) => {
            if (member.id) {
                formData.append('member_ids[]', String(member.id));
            } else if (member.phone) {
                formData.append('phone_numbers[]', member.phone);
            }
        });
    }

    function buildMemberInvitePayload(memberSelection) {
        const payload = {
            member_ids: [],
            phone_numbers: [],
        };

        memberSelection.forEach((member) => {
            if (member.id) {
                payload.member_ids.push(member.id);
            } else if (member.phone) {
                payload.phone_numbers.push(member.phone);
            }
        });

        return payload;
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
            const message = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                || data.message
                || 'Something went wrong.';
            throw new Error(message);
        }

        return data;
    }

    async function fetchUsers(query = '') {
        const usersSearch = config().routes?.usersSearch;
        if (!usersSearch) {
            return [];
        }

        const url = new URL(usersSearch, window.location.origin);
        url.searchParams.set('q', query);

        const data = await requestJson(url.toString(), { method: 'GET' });

        return data.users || [];
    }

    function renderSelectedStrip(selectedEl, selectionMap) {
        if (!selectedEl) {
            return;
        }

        const members = Array.from(selectionMap.values());

        if (!members.length) {
            selectedEl.hidden = true;
            selectedEl.innerHTML = '';

            return;
        }

        selectedEl.hidden = false;
        selectedEl.innerHTML = members.map((user) => {
            const key = selectionMapKey(user);

            return `<button type="button" class="discussion-group-pick__selected-item" data-selection-key="${escapeHtml(key)}" title="Remove ${escapeHtml(user.name)}">
                <span class="discussion-avatar discussion-avatar--sm">${escapeHtml(user.initials || user.name?.[0] || 'U')}</span>
                <span class="discussion-group-pick__selected-name">${escapeHtml(user.name)}</span>
                <span class="discussion-group-pick__selected-remove" aria-hidden="true">&times;</span>
            </button>`;
        }).join('');
    }

    function renderGroupSummary(summaryEl, selectionMap) {
        if (!summaryEl) {
            return;
        }

        const members = Array.from(selectionMap.values());
        summaryEl.innerHTML = members.length
            ? `<div class="discussion-group-pick__summary-label">Participants (${members.length})</div>
               <div class="discussion-group-pick__summary-list">${members.map((user) => {
                return `<span class="discussion-group-pick__summary-chip">
                    <span class="discussion-avatar discussion-avatar--sm">${escapeHtml(user.initials || 'U')}</span>
                    ${escapeHtml(user.name)}
                </span>`;
            }).join('')}</div>`
            : '';
    }

    function renderGroupPickList(listEl, users, selectionMap, query = '') {
        if (!listEl) {
            return;
        }

        if (!users.length) {
            const phone = normalizePhoneDigits(query);

            if (phone) {
                const selected = selectionMap.has(`phone:${phone}`);

                listEl.innerHTML = `<button type="button"
                        class="discussion-group-pick__contact ${selected ? 'is-selected' : ''}"
                        data-phone-invite="${escapeHtml(phone)}"
                        role="option"
                        aria-selected="${selected ? 'true' : 'false'}">
                    <span class="discussion-avatar">☎</span>
                    <span class="discussion-group-pick__contact-body">
                        <strong>Invite ${escapeHtml(phone)}</strong>
                        <span>Not registered — send SMS invitation</span>
                    </span>
                    <span class="discussion-group-pick__check" aria-hidden="true">
                        <i class="fa-solid ${selected ? 'fa-circle-check' : 'fa-circle'}"></i>
                    </span>
                </button>`;

                return;
            }

            listEl.innerHTML = `<div class="discussion-group-pick__empty">${query
                ? 'No member found for that number.'
                : 'Enter a mobile number to find a member.'}</div>`;

            return;
        }

        listEl.innerHTML = users.map((user) => {
            const selected = selectionMap.has(Number(user.id));
            const initials = escapeHtml(user.initials || user.name?.[0] || 'U');

            return `<button type="button"
                        class="discussion-group-pick__contact ${selected ? 'is-selected' : ''}"
                        data-user-id="${user.id}"
                        role="option"
                        aria-selected="${selected ? 'true' : 'false'}">
                <span class="discussion-avatar">${initials}</span>
                <span class="discussion-group-pick__contact-body">
                    <strong>${escapeHtml(user.name)}</strong>
                    <span>${escapeHtml(user.phone || user.email || '')}</span>
                </span>
                <span class="discussion-group-pick__check" aria-hidden="true">
                    <i class="fa-solid ${selected ? 'fa-circle-check' : 'fa-circle'}"></i>
                </span>
            </button>`;
        }).join('');
    }

    function updateNextButton(nextBtn, selectionMap, footerEl, countEl) {
        const count = selectionMap.size;

        if (footerEl) {
            footerEl.hidden = count === 0;
        }

        if (countEl) {
            countEl.textContent = count === 1
                ? '1 participant selected'
                : `${count} participants selected`;
        }

        if (!nextBtn) {
            return;
        }

        nextBtn.setAttribute('aria-label', count > 0 ? `Continue with ${count} participants` : 'Continue');
    }

    function initGroupMemberPicker(options) {
        const prefix = options.prefix || 'discussionWidget';
        const selectionMap = options.selectionMap || new Map();
        const selectedEl = document.getElementById(`${prefix}GroupSelected`);
        const searchInput = document.getElementById(`${prefix}GroupSearch`);
        const listEl = document.getElementById(`${prefix}GroupList`);
        const nextBtn = document.getElementById(`${prefix}GroupNext`);
        const footerEl = document.getElementById(`${prefix}GroupFooter`);
        const countEl = document.getElementById(`${prefix}GroupCount`);
        const loadingEl = document.getElementById(`${prefix}GroupLoading`);
        let usersCache = [];
        let searchTimer = null;

        function refreshUi(query = '') {
            renderSelectedStrip(selectedEl, selectionMap);
            renderGroupPickList(listEl, usersCache, selectionMap, query);
            updateNextButton(nextBtn, selectionMap, footerEl, countEl);

            if (typeof options.onSelectionChange === 'function') {
                options.onSelectionChange(selectionMap);
            }
        }

        async function loadUsers(query = '') {
            const digits = String(query).replace(/\D+/g, '');

            if (digits.length < 8) {
                usersCache = [];
                if (loadingEl) {
                    loadingEl.hidden = true;
                }
                refreshUi(digits.length ? 'incomplete' : '');
                if (listEl && digits.length) {
                    listEl.innerHTML = '<div class="discussion-group-pick__empty">Enter a complete mobile number to search.</div>';
                }
                return;
            }

            if (loadingEl) {
                loadingEl.hidden = false;
            }

            try {
                usersCache = await fetchUsers(query);
                refreshUi(query);
            } catch (error) {
                if (listEl) {
                    listEl.innerHTML = `<div class="discussion-group-pick__empty">${escapeHtml(error.message)}</div>`;
                }
            } finally {
                if (loadingEl) {
                    loadingEl.hidden = true;
                }
            }
        }

        selectedEl?.addEventListener('click', (event) => {
            const item = event.target.closest('.discussion-group-pick__selected-item');
            if (!item) {
                return;
            }

            deleteSelectionEntry(selectionMap, item.dataset.selectionKey);
            refreshUi();
        });

        listEl?.addEventListener('click', (event) => {
            const phoneInvite = event.target.closest('[data-phone-invite]');
            if (phoneInvite) {
                const phone = phoneInvite.dataset.phoneInvite;
                const key = `phone:${phone}`;

                if (selectionMap.has(key)) {
                    selectionMap.delete(key);
                } else {
                    selectionMap.set(key, {
                        phone,
                        name: phone,
                        initials: '☎',
                    });
                }

                refreshUi(searchInput?.value?.trim() || '');

                return;
            }

            const contact = event.target.closest('.discussion-group-pick__contact');
            if (!contact) {
                return;
            }

            const userId = Number(contact.dataset.userId);
            const user = usersCache.find((entry) => Number(entry.id) === userId);

            if (!user) {
                return;
            }

            if (selectionMap.has(userId)) {
                selectionMap.delete(userId);
            } else {
                selectionMap.set(userId, user);
            }

            refreshUi();
        });

        searchInput?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => {
                loadUsers(searchInput.value.trim());
            }, 250);
        });

        nextBtn?.addEventListener('click', () => {
            if (selectionMap.size === 0) {
                return;
            }

            if (typeof options.onNext === 'function') {
                options.onNext(selectionMap);
            }
        });

        return {
            selectionMap,
            loadUsers,
            refreshUi,
            renderSummary(targetEl) {
                renderGroupSummary(targetEl, selectionMap);
            },
            reset() {
                selectionMap.clear();
                usersCache = [];
                if (searchInput) {
                    searchInput.value = '';
                }
                refreshUi();
            },
        };
    }

    function buildFormData(form, memberSelection, attachmentPool) {
        const formData = new FormData();
        const title = form.querySelector('[name="title"]')?.value?.trim() || '';
        const body = form.querySelector('[name="body"]')?.value?.trim() || '';
        const isGroup = form.querySelector('[name="is_group"]')?.value === '1'
            || form.dataset.composeMode === 'group';
        const parentTopicId = form.querySelector('[name="parent_topic_id"]')?.value?.trim() || '';

        formData.append('title', title);
        formData.append('is_group', parentTopicId ? '0' : (isGroup ? '1' : '0'));

        if (parentTopicId) {
            formData.append('parent_topic_id', parentTopicId);
        }

        if (body) {
            formData.append('body', body);
        }

        const groupImageInput = form.querySelector('[name="group_image"]');
        if (groupImageInput?.files?.[0]) {
            formData.append('group_image', groupImageInput.files[0]);
        }

        Array.from(memberSelection.values()).forEach((member) => {
            if (member.id) {
                formData.append('member_ids[]', String(member.id));
            } else if (member.phone) {
                formData.append('phone_numbers[]', member.phone);
            }
        });

        window.soilnwaterDiscussionAttachments?.appendPoolToFormData?.(formData, attachmentPool);

        return formData;
    }

    function resetGroupImageField(form) {
        const input = form?.querySelector('[name="group_image"]');
        const preview = form?.querySelector('.discussion-widget__group-image-preview');
        const clearBtn = form?.querySelector('.discussion-widget__group-image-clear');

        if (input) {
            input.value = '';
        }

        if (preview) {
            preview.innerHTML = '<span class="discussion-avatar discussion-avatar--icon discussion-avatar--group discussion-avatar--lg" aria-hidden="true"><i class="fa-solid fa-users"></i></span>';
        }

        if (clearBtn) {
            clearBtn.hidden = true;
        }
    }

    function bindGroupImageField(form) {
        const input = form?.querySelector('[name="group_image"]');
        const preview = form?.querySelector('.discussion-widget__group-image-preview');
        const clearBtn = form?.querySelector('.discussion-widget__group-image-clear');

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                return;
            }

            preview.innerHTML = `<span class="discussion-avatar discussion-avatar--photo discussion-avatar--lg"><img src="${URL.createObjectURL(file)}" alt=""></span>`;
            if (clearBtn) {
                clearBtn.hidden = false;
            }
        });

        clearBtn?.addEventListener('click', () => {
            resetGroupImageField(form);
        });
    }

    function resetForm(form, memberSelection, attachmentPool, attachmentsPreviewId, summaryEl, pickerController) {
        form?.reset();
        memberSelection?.clear?.();
        pickerController?.reset?.();
        resetGroupImageField(form);
        if (summaryEl) {
            summaryEl.innerHTML = '';
        }
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

        const memberSelection = options.memberSelection || new Map();
        const summaryEl = options.summaryElId ? document.getElementById(options.summaryElId) : null;
        const attachmentHelpers = window.soilnwaterDiscussionAttachments || {};
        const attachmentPool = options.skipAttachments
            ? null
            : attachmentHelpers.bindAttachmentPicker?.({
                input: document.getElementById(options.attachmentsInputId),
                previewEl: document.getElementById(options.attachmentsPreviewId),
                imageButton: document.getElementById(options.attachImageBtnId),
                videoButton: document.getElementById(options.attachVideoBtnId),
                documentButton: document.getElementById(options.attachDocumentBtnId),
            });

        const renderSummary = () => {
            if (summaryEl) {
                renderGroupSummary(summaryEl, memberSelection);
            }
        };

        renderSummary();

        bindGroupImageField(form);

        if (options.handleSubmit === false) {
            return {
                form,
                memberSelection,
                attachmentPool,
                renderSummary,
                buildFormData: () => buildFormData(form, memberSelection, attachmentPool),
                reset: () => resetForm(
                    form,
                    memberSelection,
                    attachmentPool,
                    options.attachmentsPreviewId,
                    summaryEl,
                    options.pickerController
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
                        (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                        || data.message
                        || 'Something went wrong.'
                    );
                }

                resetForm(
                    form,
                    memberSelection,
                    attachmentPool,
                    options.attachmentsPreviewId,
                    summaryEl,
                    options.pickerController
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
            renderSummary,
            buildFormData: () => buildFormData(form, memberSelection, attachmentPool),
            reset: () => resetForm(
                form,
                memberSelection,
                attachmentPool,
                options.attachmentsPreviewId,
                summaryEl,
                options.pickerController
            ),
        };
    }

    window.soilnwaterDiscussionCompose = {
        initNewChatForm,
        initGroupMemberPicker,
        renderGroupSummary,
        fetchUsers,
        normalizePhoneDigits,
        buildMemberInvitePayload,
        selectionMapKey,
        deleteSelectionEntry,
    };
})();

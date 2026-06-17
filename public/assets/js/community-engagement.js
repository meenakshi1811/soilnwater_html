(function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Request failed.');
        }

        return data;
    }

    document.addEventListener('click', async function (event) {
        const saveButton = event.target.closest('.js-community-save-post');
        if (saveButton) {
            event.preventDefault();
            saveButton.disabled = true;

            try {
                const response = await fetch(saveButton.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: new URLSearchParams({ _token: csrfToken() }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to save post.');
                }

                saveButton.classList.toggle('is-saved', Boolean(data.saved));
                saveButton.innerHTML = data.saved
                    ? '<i class="fa-solid fa-bookmark me-1"></i>Saved'
                    : '<i class="fa-regular fa-bookmark me-1"></i>Save';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to save post.');
            } finally {
                saveButton.disabled = false;
            }

            return;
        }

        const categoryButton = event.target.closest('.js-community-subscribe-category');
        if (categoryButton) {
            event.preventDefault();
            categoryButton.disabled = true;

            try {
                const data = await postJson(categoryButton.dataset.url, {
                    content_type: categoryButton.dataset.contentType,
                    category: categoryButton.dataset.category,
                });

                categoryButton.classList.toggle('is-subscribed', Boolean(data.subscribed));
                categoryButton.textContent = data.subscribed ? 'Subscribed to category' : 'Subscribe to category';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to update subscription.');
            } finally {
                categoryButton.disabled = false;
            }

            return;
        }

        const topicButton = event.target.closest('.js-community-follow-topic');
        if (topicButton) {
            event.preventDefault();
            topicButton.disabled = true;

            try {
                const data = await postJson(topicButton.dataset.url, {
                    topic: topicButton.dataset.topic,
                });

                topicButton.classList.toggle('is-following', Boolean(data.following));
                topicButton.textContent = data.following ? 'Following' : 'Follow topic';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to update topic follow.');
            } finally {
                topicButton.disabled = false;
            }
        }
    });

    function updateReportEngagementStats(engagement) {
        if (!engagement) {
            return;
        }

        const map = {
            supports: engagement.supports_count,
            agreements: engagement.agreements_count,
            follows: engagement.follows_count,
        };

        Object.entries(map).forEach(([key, value]) => {
            const el = document.querySelector(`[data-report-stat="${key}"]`);
            if (el) {
                el.textContent = Number(value || 0).toLocaleString();
            }
        });
    }

    function updateReportTrustScore(score) {
        if (score === null || score === undefined) {
            return;
        }

        document.querySelectorAll('.report-trust-score__value').forEach((el) => {
            el.textContent = `${score}%`;
        });

        document.querySelectorAll('.community-post-banner-tag').forEach((badge) => {
            if (badge.textContent.trim().startsWith('Trust Score:')) {
                badge.textContent = `Trust Score: ${score}%`;
            }
        });
    }

    function setReportToggleState(button, active, action) {
        const labels = {
            support: active ? 'Supported' : 'Support report',
            agree: active ? 'Agreed' : 'I agree',
            follow: active ? 'Following' : 'Follow issue',
        };

        button.classList.toggle('btn-success', active);
        button.classList.toggle('btn-outline-success', !active);
        button.dataset.active = active ? '1' : '0';

        const label = button.querySelector('.js-report-action-label');
        if (label) {
            label.textContent = labels[action] || label.textContent;
        }
    }

    function appendParticipationTextEntry(list, entry) {
        if (!list) {
            return;
        }

        list.querySelectorAll('.js-participation-empty').forEach((el) => el.remove());

        const item = document.createElement('div');
        item.className = 'border rounded-3 p-3 bg-white';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                <strong class="small">${entry.contributor || 'Community member'}</strong>
                <small class="text-muted">${entry.created_at || 'Just now'}</small>
            </div>
            <p class="small mb-0"></p>
        `;
        item.querySelector('p').textContent = entry.body || '';
        list.prepend(item);
    }

    document.addEventListener('click', async function (event) {
        const toggleButton = event.target.closest('.js-report-engagement-toggle');
        if (toggleButton) {
            event.preventDefault();
            toggleButton.disabled = true;

            try {
                const response = await fetch(toggleButton.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: new URLSearchParams({ _token: csrfToken() }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to update report engagement.');
                }

                const action = toggleButton.dataset.action;
                const active = Boolean(data.supported ?? data.agreed ?? data.following);
                setReportToggleState(toggleButton, active, action);
                updateReportEngagementStats(data.engagement);
                updateReportTrustScore(data.report_trust_score);
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to update report engagement.');
            } finally {
                toggleButton.disabled = false;
            }

            return;
        }
    });

    document.addEventListener('submit', async function (event) {
        const textForm = event.target.closest('.js-participation-text-form');
        if (textForm) {
            event.preventDefault();

            const submitButton = textForm.querySelector('[type="submit"]');
            const bodyField = textForm.querySelector('[name="body"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

                const bodyText = bodyField?.value || '';

                try {
                    const response = await fetch(textForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ body: bodyField?.value || '' }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to submit participation.');
                }

                textForm.reset();

                const isSuggestion = textForm.action.includes('/participation/suggestion');
                const list = document.getElementById(isSuggestion ? 'participationSuggestionsList' : 'participationFeedbackList');
                appendParticipationTextEntry(list, {
                    contributor: 'You',
                    created_at: 'Just now',
                    body: bodyText,
                });

                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to submit participation.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }

            return;
        }

        const evidenceForm = event.target.closest('.js-participation-evidence-form');
        if (evidenceForm) {
            event.preventDefault();

            const submitButton = evidenceForm.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(evidenceForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: new FormData(evidenceForm),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to upload evidence.');
                }

                evidenceForm.reset();
                updateReportTrustScore(data.report_trust_score);

                const emptyMessage = document.getElementById('participationEvidenceEmpty');
                if (emptyMessage) {
                    emptyMessage.remove();
                }

                const section = document.getElementById('communityParticipationEvidenceSection');
                const list = document.getElementById('communityParticipationEvidenceList');
                if (section) {
                    section.style.display = '';
                }

                (data.evidence || []).forEach((item) => {
                    if (!list || list.querySelector(`[data-evidence-id="${item.id}"]`)) {
                        return;
                    }

                    const col = document.createElement('div');
                    col.className = 'col-md-6';
                    col.dataset.evidenceId = String(item.id);
                    col.innerHTML = `
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <strong class="small">${item.contributor || 'Community member'}</strong>
                                <small class="text-muted">${item.created_at || 'Just now'}</small>
                            </div>
                            <a href="${item.url}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary mb-2">
                                <i class="fa-solid fa-paperclip me-1"></i>${item.name}
                            </a>
                            ${item.note ? `<p class="small text-muted mb-0">${item.note}</p>` : ''}
                        </div>
                    `;
                    list?.prepend(col);
                });

                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to upload evidence.');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }

            return;
        }

        const form = event.target.closest('#communityPostReportForm');
        if (!form) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: new FormData(form),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to submit report.');
            }

            form.reset();
            const modalElement = form.closest('.modal');
            if (modalElement && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }

            notify('success', data.message || 'Post reported successfully.');
        } catch (error) {
            notify('error', error.message || 'Unable to submit report.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
})();

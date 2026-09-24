document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('schJobModal');
    if (!modalEl || !window.bootstrap) {
        return;
    }

    var applyForm = document.getElementById('schJobApplyForm');
    var titleEl = modalEl.querySelector('#schJobModalLabel');
    var metaEl = modalEl.querySelector('#schJobModalMeta');
    var chipsEl = modalEl.querySelector('#schJobModalChips');
    var descEl = modalEl.querySelector('#schJobModalDescription');
    var reqWrap = modalEl.querySelector('#schJobModalRequirementsWrap');
    var reqEl = modalEl.querySelector('#schJobModalRequirements');
    var appliedNote = modalEl.querySelector('#schJobAppliedNote');
    var applyBtn = modalEl.querySelector('.js-sch-job-apply-btn');
    var applyText = modalEl.querySelector('.js-job-apply-text');
    var applySending = modalEl.querySelector('.js-job-apply-sending');
    var coverField = modalEl.querySelector('#sch_job_cover_message');

    var pageRoot = document.getElementById('schoolProfilePage') || document.getElementById('schoolSectionPage');
    var loginUrl = pageRoot?.dataset.loginUrl || '/login';
    var currentApplyUrl = '';

    function notify(type, message) {
        if (typeof window.schNotify === 'function') {
            window.schNotify(type, message);
            return;
        }

        if (window.toastr && typeof window.toastr[type === 'success' ? 'success' : 'error'] === 'function') {
            window.toastr[type === 'success' ? 'success' : 'error'](message);
        }
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function formatMultiline(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    function setApplyUi(applied) {
        if (!applyForm || !applyBtn) {
            return;
        }

        var isApplied = applied === true || applied === '1' || applied === 1;
        applyBtn.disabled = isApplied;
        applyBtn.classList.toggle('d-none', isApplied);
        if (appliedNote) {
            appliedNote.classList.toggle('d-none', !isApplied);
        }
        if (coverField) {
            coverField.disabled = isApplied;
        }
    }

    function openJobModal(trigger) {
        var raw = trigger.getAttribute('data-job');
        if (!raw) {
            return;
        }

        var job;
        try {
            job = JSON.parse(raw);
        } catch (error) {
            return;
        }

        currentApplyUrl = trigger.getAttribute('data-apply-url') || '';

        if (titleEl) {
            titleEl.textContent = job.title || 'Job details';
        }

        if (metaEl) {
            var metaParts = [];
            if (job.department) {
                metaParts.push(job.department);
            }
            if (job.published_at) {
                metaParts.push('Posted ' + job.published_at);
            }
            if (job.application_deadline) {
                metaParts.push('Apply by ' + job.application_deadline);
            }
            metaEl.textContent = metaParts.join(' · ');
        }

        if (chipsEl) {
            var chips = [];
            if (job.employment_type_label) {
                chips.push('<span class="sch-job-chip">' + escapeHtml(job.employment_type_label) + '</span>');
            }
            if (job.location) {
                chips.push('<span class="sch-job-chip"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> ' + escapeHtml(job.location) + '</span>');
            }
            if (job.experience_label) {
                chips.push('<span class="sch-job-chip"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> ' + escapeHtml(job.experience_label) + '</span>');
            }
            if (job.salary_label) {
                chips.push('<span class="sch-job-chip"><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> ' + escapeHtml(job.salary_label) + '</span>');
            }
            chipsEl.innerHTML = chips.join('');
        }

        if (descEl) {
            descEl.innerHTML = formatMultiline(job.description || '');
        }

        if (reqWrap && reqEl) {
            if (job.requirements) {
                reqEl.innerHTML = formatMultiline(job.requirements);
                reqWrap.classList.remove('d-none');
            } else {
                reqEl.innerHTML = '';
                reqWrap.classList.add('d-none');
            }
        }

        setApplyUi(trigger.getAttribute('data-applied'));

        if (applyForm) {
            applyForm.reset();
        }

        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('.js-sch-job-open');
        if (!trigger) {
            return;
        }
        event.preventDefault();
        openJobModal(trigger);
    });

    if (applyForm) {
        applyForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!currentApplyUrl) {
                notify('error', 'Unable to submit application.');
                return;
            }

            if (pageRoot && pageRoot.dataset.isAuth !== '1') {
                window.location.href = loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'redirect=' + encodeURIComponent(window.location.href);
                return;
            }

            if (applyBtn) {
                applyBtn.disabled = true;
            }
            if (applyText) {
                applyText.classList.add('d-none');
            }
            if (applySending) {
                applySending.classList.remove('d-none');
            }

            try {
                var response = await fetch(currentApplyUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        cover_message: coverField ? coverField.value.trim() : '',
                    }),
                });

                var payload = await response.json().catch(function () {
                    return {};
                });

                if (response.status === 401) {
                    window.location.href = loginUrl;
                    return;
                }

                if (!response.ok || payload.ok === false) {
                    throw new Error(payload.message || 'Unable to submit application.');
                }

                notify('success', payload.message || 'Application submitted successfully.');

                document.querySelectorAll('.js-sch-job-open[data-apply-url="' + currentApplyUrl + '"]').forEach(function (card) {
                    card.setAttribute('data-applied', '1');
                    var badge = card.querySelector('.sch-job-card__applied');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'sch-job-card__applied';
                        badge.innerHTML = '<i class="fa-solid fa-circle-check" aria-hidden="true"></i> Applied';
                        card.insertBefore(badge, card.querySelector('.sch-job-card__title'));
                    }
                });

                setApplyUi(true);
            } catch (error) {
                notify('error', error.message || 'Unable to submit application.');
                if (applyBtn) {
                    applyBtn.disabled = false;
                }
            } finally {
                if (applyText) {
                    applyText.classList.remove('d-none');
                }
                if (applySending) {
                    applySending.classList.add('d-none');
                }
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('smNotesPage');
    if (!page) {
        return;
    }

    const notesUrl = page.dataset.notesUrl || window.location.pathname;
    const loginUrl = page.dataset.loginUrl || '/login';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const filterForm = document.getElementById('smNotesFilterForm');
    const resultsEl = document.getElementById('smNotesResults');
    const tabsEl = document.getElementById('smNotesCategoryTabs');
    const sortSelect = document.querySelector('.js-sm-notes-sort');
    const viewInput = document.getElementById('sm-filter-view');
    const sortInput = document.getElementById('sm-filter-sort');
    const categoryInput = document.getElementById('sm-filter-category');
    const subjectSelect = document.getElementById('sm-subject');
    const classSelect = document.getElementById('sm-class-course');
    const boardSelect = document.getElementById('sm-board');
    const topicSelect = document.getElementById('sm-topic');

    let activeRequest = null;

    function notify(type, message) {
        if (!message) {
            return;
        }

        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
            };
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    function buildParams(pageNumber) {
        const params = new URLSearchParams();

        if (!filterForm) {
            return params;
        }

        const formData = new FormData(filterForm);

        formData.forEach(function (value, key) {
            if (value === '' && key !== 'category') {
                return;
            }
            params.append(key, value);
        });

        if (sortSelect && sortInput) {
            sortInput.value = sortSelect.value;
            params.set('sort', sortSelect.value);
        }

        if (pageNumber && pageNumber > 1) {
            params.set('page', String(pageNumber));
        } else {
            params.delete('page');
        }

        return params;
    }

    function setLoading(isLoading) {
        if (!resultsEl) {
            return;
        }

        resultsEl.classList.toggle('is-loading', isLoading);
    }

    function syncViewToggle(viewMode) {
        document.querySelectorAll('.js-sm-notes-view').forEach(function (button) {
            button.classList.toggle('is-active', button.dataset.view === viewMode);
        });

        if (viewInput) {
            viewInput.value = viewMode;
        }
    }

    function syncFilterFormFromParams(params) {
        if (!filterForm) {
            return;
        }

        filterForm.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = false;
        });

        params.forEach(function (value, key) {
            if (key === 'material_types[]' || key === 'file_types[]') {
                const checkbox = filterForm.querySelector('input[name="' + key + '"][value="' + CSS.escape(value) + '"]');
                if (checkbox) {
                    checkbox.checked = true;
                }
                return;
            }

            const field = filterForm.elements.namedItem(key);
            if (field && 'value' in field && field.type !== 'checkbox') {
                field.value = value;
            }
        });

        if (categoryInput) {
            categoryInput.value = params.get('category') || '';
        }

        if (sortSelect) {
            sortSelect.value = params.get('sort') || 'recent';
        }

        if (sortInput) {
            sortInput.value = params.get('sort') || 'recent';
        }

        if (viewInput) {
            viewInput.value = params.get('view') || 'list';
            syncViewToggle(viewInput.value);
        }
    }

    async function loadNotes(options) {
        const opts = options || {};
        const pageNumber = opts.page || 1;
        const pushState = opts.pushState !== false;
        const successMessage = opts.successMessage || '';
        const params = opts.params instanceof URLSearchParams ? opts.params : buildParams(pageNumber);

        if (activeRequest) {
            activeRequest.abort();
        }

        const controller = new AbortController();
        activeRequest = controller;

        setLoading(true);

        try {
            const response = await fetch(notesUrl + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: controller.signal,
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Unable to load notes.');
            }

            if (resultsEl && data.results_html) {
                resultsEl.innerHTML = data.results_html;
            }

            if (tabsEl && data.category_tabs_html) {
                tabsEl.innerHTML = data.category_tabs_html;
            }

            if (pushState && data.url) {
                window.history.pushState({ smNotes: true }, '', data.url);
            }

            syncFilterFormFromParams(params);

            if (data.message) {
                notify('success', data.message);
            } else if (successMessage) {
                notify('success', successMessage);
            }

            bindDynamicHandlers();

            if (opts.scroll !== false && resultsEl) {
                resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            notify('error', error.message || 'Unable to load notes.');
        } finally {
            if (activeRequest === controller) {
                activeRequest = null;
            }
            setLoading(false);
        }
    }

    function updateBookmarkButton(button, saved) {
        button.classList.toggle('is-saved', saved);

        const icon = button.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-solid', saved);
            icon.classList.toggle('fa-regular', !saved);
        }
    }

    async function toggleBookmark(button) {
        const url = button.dataset.url;
        if (!url) {
            return;
        }

        if (page.dataset.isAuth !== '1') {
            notify('warning', 'Please login to save notes.');
            return;
        }

        button.disabled = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (response.status === 401) {
                notify('warning', 'Please login to save notes.');
                return;
            }

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Unable to update save status.');
            }

            const saved = Boolean(data.saved ?? data.bookmarked);
            updateBookmarkButton(button, saved);
            notify('success', data.message || (saved ? 'Saved successfully.' : 'Removed from saved.'));
        } catch (error) {
            notify('error', error.message || 'Unable to update save status.');
        } finally {
            button.disabled = false;
        }
    }

    async function submitFollowForm(form) {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (response.status === 401) {
                notify('warning', 'Please login to follow educators.');
                return;
            }

            if (!response.ok || data.ok === false) {
                throw new Error(data.message || 'Unable to follow educator.');
            }

            notify('success', data.message || 'Follow updated.');
        } catch (error) {
            notify('error', error.message || 'Unable to follow educator.');
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    }

    function bindDynamicHandlers() {
        document.querySelectorAll('.js-sm-bookmark').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }

            button.dataset.bound = '1';
            button.addEventListener('click', function (event) {
                event.preventDefault();
                toggleBookmark(button);
            });
        });

        document.querySelectorAll('.js-sm-educator-follow-form').forEach(function (form) {
            if (form.dataset.bound === '1') {
                return;
            }

            form.dataset.bound = '1';
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitFollowForm(form);
            });
        });
    }

    function applyFilterLink(link) {
        if (link.dataset.filterSubject !== undefined && subjectSelect) {
            subjectSelect.value = link.dataset.filterSubject || '';
        }

        if (link.dataset.filterClass !== undefined && classSelect) {
            classSelect.value = link.dataset.filterClass || '';
        }

        if (link.dataset.filterBoard !== undefined && boardSelect) {
            boardSelect.value = link.dataset.filterBoard || '';
        }

        if (link.dataset.filterTopic !== undefined && topicSelect) {
            topicSelect.value = link.dataset.filterTopic || '';
        }

        loadNotes({ page: 1, scroll: false });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            loadNotes({ page: 1 });
        });

        filterForm.querySelectorAll('select.form-select').forEach(function (select) {
            select.addEventListener('change', function () {
                loadNotes({ page: 1, scroll: false });
            });
        });

        filterForm.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                loadNotes({ page: 1, scroll: false });
            });
        });
    }

    document.querySelector('.js-sm-notes-reset')?.addEventListener('click', function (event) {
        event.preventDefault();

        if (!filterForm) {
            window.location.href = notesUrl;
            return;
        }

        filterForm.reset();
        if (viewInput) {
            viewInput.value = 'list';
        }
        if (sortInput) {
            sortInput.value = 'recent';
        }
        if (sortSelect) {
            sortSelect.value = 'recent';
        }
        if (categoryInput) {
            categoryInput.value = '';
        }

        syncViewToggle('list');
        loadNotes({ page: 1 });
    });

    sortSelect?.addEventListener('change', function () {
        if (sortInput) {
            sortInput.value = sortSelect.value;
        }
        loadNotes({ page: 1, scroll: false });
    });

    document.querySelectorAll('.js-sm-notes-view').forEach(function (button) {
        button.addEventListener('click', function () {
            const viewMode = button.dataset.view || 'list';
            syncViewToggle(viewMode);
            loadNotes({ page: 1, scroll: false });
        });
    });

    tabsEl?.addEventListener('click', function (event) {
        const tab = event.target.closest('.js-sm-notes-tab');
        if (!tab) {
            return;
        }

        event.preventDefault();

        if (categoryInput) {
            categoryInput.value = tab.dataset.category || '';
        }

        loadNotes({ page: 1 });
    });

    page.addEventListener('click', function (event) {
        const viewAllLink = event.target.closest('.js-sm-notes-view-all');
        if (viewAllLink) {
            event.preventDefault();

            if (subjectSelect) {
                subjectSelect.value = '';
            }

            loadNotes({ page: 1, scroll: false });
            return;
        }

        const filterLink = event.target.closest('.js-sm-notes-filter-link');
        if (filterLink) {
            event.preventDefault();
            applyFilterLink(filterLink);
            return;
        }

        const pageLink = event.target.closest('#smNotesResults .pagination a.page-link');
        if (!pageLink) {
            return;
        }

        event.preventDefault();

        const url = new URL(pageLink.href, window.location.origin);
        const pageNumber = parseInt(url.searchParams.get('page') || '1', 10);
        const params = new URLSearchParams(url.search);

        loadNotes({ page: pageNumber, params: params });
    });

    window.addEventListener('popstate', function (event) {
        if (!event.state || !event.state.smNotes) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        syncFilterFormFromParams(params);
        loadNotes({
            page: parseInt(params.get('page') || '1', 10),
            params: params,
            pushState: false,
            scroll: false,
        });
    });

    bindDynamicHandlers();
});

document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('eduCoursesPage');
    if (!page) {
        return;
    }

    const coursesUrl = page.dataset.coursesUrl || window.location.pathname;
    const loginUrl = page.dataset.loginUrl || '/login';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const filterForm = document.getElementById('eduCoursesFilterForm');
    const resultsEl = document.getElementById('eduCoursesResults');
    const summaryEl = document.getElementById('eduCoursesSummary');
    const sortSelect = document.querySelector('.js-edu-courses-sort');
    const sortInput = document.getElementById('eduCoursesSort');

    let activeRequest = null;
    let searchTimer = null;

    function notify(type, message) {
        if (!message) {
            return;
        }

        const toastType = type === 'danger' ? 'error' : type;

        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
            };
            window.toastr[toastType](message);
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
            if (value !== '') {
                params.append(key, value);
            }
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
        if (resultsEl) {
            resultsEl.classList.toggle('is-loading', isLoading);
        }
    }

    function bindDynamicHandlers() {
        document.querySelectorAll('.js-edu-course-bookmark').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }

            button.dataset.bound = '1';
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const url = button.dataset.url;
                if (!url) {
                    return;
                }

                button.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                })
                    .then(function (response) {
                        if (response.status === 401 || response.status === 419) {
                            window.location.href = loginUrl;
                            return null;
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        if (!data) {
                            return;
                        }

                        const saved = Boolean(data.saved ?? data.bookmarked);
                        button.dataset.bookmarked = saved ? '1' : '0';
                        button.classList.toggle('is-active', saved);
                        button.setAttribute('aria-pressed', saved ? 'true' : 'false');
                        button.innerHTML = saved
                            ? '<i class="fa-solid fa-bookmark" aria-hidden="true"></i> Saved'
                            : '<i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save';
                        notify('success', data.message || (saved ? 'Course saved.' : 'Course removed from saved.'));
                    })
                    .catch(function () {
                        notify('danger', 'Unable to update bookmark.');
                    })
                    .finally(function () {
                        button.disabled = false;
                    });
            });
        });
    }

    function loadCourses(pageNumber, pushState) {
        const params = buildParams(pageNumber);
        const requestUrl = coursesUrl + (params.toString() ? '?' + params.toString() : '');

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        setLoading(true);

        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            signal: activeRequest.signal,
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (!data.ok) {
                    throw new Error('Request failed');
                }

                if (resultsEl && data.results_html) {
                    resultsEl.innerHTML = data.results_html;
                }

                if (summaryEl && data.summary_html) {
                    summaryEl.innerHTML = data.summary_html;
                }

                bindDynamicHandlers();

                if (pushState !== false && data.url) {
                    window.history.replaceState({}, '', data.url);
                }
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    notify('danger', 'Unable to load courses.');
                }
            })
            .finally(function () {
                setLoading(false);
                activeRequest = null;
            });
    }

    filterForm?.addEventListener('change', function () {
        loadCourses(1);
    });

    filterForm?.querySelector('#eduCoursesSearch')?.addEventListener('input', function () {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function () {
            loadCourses(1);
        }, 350);
    });

    sortSelect?.addEventListener('change', function () {
        if (sortInput) {
            sortInput.value = sortSelect.value;
        }
        loadCourses(1);
    });

    document.querySelector('.js-edu-courses-reset')?.addEventListener('click', function () {
        if (!filterForm) {
            return;
        }

        filterForm.reset();
        if (sortInput) {
            sortInput.value = 'recent';
        }
        if (sortSelect) {
            sortSelect.value = 'recent';
        }
        loadCourses(1);
    });

    document.addEventListener('click', function (event) {
        const pageLink = event.target.closest('.js-edu-courses-page');
        if (!pageLink || !resultsEl?.contains(pageLink)) {
            return;
        }

        event.preventDefault();
        const pageNumber = Number(pageLink.dataset.page || 1);
        loadCourses(pageNumber);
        resultsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    bindDynamicHandlers();
});

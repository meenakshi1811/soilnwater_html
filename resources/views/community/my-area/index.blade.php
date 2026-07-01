@extends('frontend.layouts.app')

@section('meta_title', 'My Area | SoilnWater Community')
@section('meta_description', 'Report issues, suggest improvements, recognize heroes, share achievements, raise awareness, and track resolutions in your local area.')
@section('meta_url', route('community.my-area.index'))
@section('meta_canonical', route('community.my-area.index'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/community-hub-listing.css') }}?v={{ filemtime(public_path('assets/css/community-hub-listing.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/community-my-area.css') }}?v={{ filemtime(public_path('assets/css/community-my-area.css')) }}">
@endpush

@section('content')
<div class="my-area-hub">
    <section class="my-area-hero">
        <div class="my-area-hero__inner">
            <p class="my-area-hero__kicker">SoilnWater Civic Hub</p>
            <h1 class="my-area-hero__title">My Area</h1>
            <p class="my-area-hero__text">
                Your dedicated local community section — connect directly with neighbours and authorities through
                location-based feeds, area discussions, community voting, issue tracking, and resolution monitoring.
            </p>
            <div class="my-area-hero__actions">
                @auth
                    <a href="{{ route('community.posts.create', ['type' => 'my-area']) }}" class="btn btn-light btn-lg">Share with your area</a>
                @else
                    <a href="{{ route('login', ['redirect' => route('community.my-area.index')]) }}" class="btn btn-light btn-lg">Login to participate</a>
                @endauth
                <span class="my-area-hero__stat" id="myAreaTotalStat">
                    <i class="fa-solid fa-layer-group me-1"></i>{{ number_format($posts->total()) }} local {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}
                </span>
            </div>
            <div class="my-area-feature-grid">
                @foreach($activityTypes as $activity)
                    <div class="my-area-feature"><i class="fa-solid fa-location-dot me-2"></i>{{ $activity }}</div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="my-area-shell">
        <nav class="my-area-breadcrumb mb-3" aria-label="Breadcrumb">
            <a href="{{ route('community.index') }}"><i class="fa-solid fa-arrow-left me-1"></i>Community Hub</a>
            <span class="text-muted mx-2">/</span>
            <span class="text-muted">My Area</span>
        </nav>

        <div class="my-area-toolbar">
            <div class="my-area-toolbar__head">
                <h2 class="my-area-toolbar__title">Browse local posts</h2>
                <p class="my-area-toolbar__hint">Filter by activity, topic, status, or location — results update instantly.</p>
            </div>

            <div class="my-area-filter-scroll" id="myAreaActivityPills" role="group" aria-label="Activity filters">
                <button type="button" class="my-area-filter-pill {{ blank($filters['activity']) ? 'is-active' : '' }}" data-activity="">All activities</button>
                @foreach($activityTypes as $activity)
                    <button type="button" class="my-area-filter-pill {{ $filters['activity'] === $activity ? 'is-active' : '' }}" data-activity="{{ $activity }}">{{ $activity }}</button>
                @endforeach
            </div>

            <form id="myAreaFiltersForm" class="my-area-filters" autocomplete="off">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small" for="myAreaFilterActivity">Activity</label>
                        <select name="activity" id="myAreaFilterActivity" class="form-select form-select-sm">
                            <option value="">All activities</option>
                            @foreach($activityTypes as $activity)
                                <option value="{{ $activity }}" @selected($filters['activity'] === $activity)>{{ $activity }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small" for="myAreaFilterTopic">Topic</label>
                        <select name="topic" id="myAreaFilterTopic" class="form-select form-select-sm">
                            <option value="">All topics</option>
                            @foreach($topicCategories as $topic)
                                <option value="{{ $topic }}" @selected($filters['topic'] === $topic)>{{ $topic }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small" for="myAreaFilterStatus">Status</label>
                        <select name="status" id="myAreaFilterStatus" class="form-select form-select-sm">
                            <option value="">Any status</option>
                            @foreach($statusSteps as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small" for="myAreaFilterState">State</label>
                        <input type="text" name="state" id="myAreaFilterState" class="form-control form-control-sm" value="{{ $filters['state'] }}" placeholder="e.g. Uttarakhand">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small" for="myAreaFilterDistrict">District</label>
                        <input type="text" name="district" id="myAreaFilterDistrict" class="form-control form-control-sm" value="{{ $filters['district'] }}" placeholder="e.g. Dehradun">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small" for="myAreaFilterCity">City</label>
                        <input type="text" name="city" id="myAreaFilterCity" class="form-control form-control-sm" value="{{ $filters['city'] }}" placeholder="e.g. Dehradun">
                    </div>
                    <div class="col-md-4">
                        <div class="my-area-filters__actions">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="myAreaClearFilters">Clear filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <section class="my-area-posts-section" aria-live="polite" aria-busy="false" id="myAreaPostsSection">
            <div
                id="myAreaPostsGrid"
                class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3 g-lg-4"
                data-feed-url="{{ route('community.my-area.index') }}"
                data-next-page-url="{{ $posts->nextPageUrl() }}"
            >
                @include('community.partials.post-cards', [
                    'posts' => $posts,
                    'engagement' => $engagement,
                    'emptyMessage' => 'No My Area posts match your filters yet. Try adjusting filters or be the first to share with your neighbours.',
                ])
            </div>

            <div class="community-pagination-wrap" id="myAreaPaginationState">
                @if ($posts->total() > 0)
                    <p class="community-pagination-summary" id="myAreaSummaryText">
                        Showing 1 to {{ $posts->lastItem() }} of {{ $posts->total() }} results
                    </p>
                @else
                    <p class="community-pagination-summary d-none" id="myAreaSummaryText">Showing 0 results</p>
                @endif
                <p class="community-pagination-loading d-none" id="myAreaLoadingText">Loading posts…</p>
            </div>

            <div id="myAreaScrollSentinel" class="community-scroll-sentinel" aria-hidden="true"></div>
        </section>
    </div>
</div>

@include('community.partials.share-modal')
@include('community.partials.toastr-assets')
@endsection

@push('scripts')
<script>
    (function () {
        const feedUrl = @json(route('community.my-area.index'));
        const filterForm = document.getElementById('myAreaFiltersForm');
        const postsGrid = document.getElementById('myAreaPostsGrid');
        const postsSection = document.getElementById('myAreaPostsSection');
        const loadingText = document.getElementById('myAreaLoadingText');
        const summaryText = document.getElementById('myAreaSummaryText');
        const totalStat = document.getElementById('myAreaTotalStat');
        const scrollSentinel = document.getElementById('myAreaScrollSentinel');
        const activityPills = document.getElementById('myAreaActivityPills');
        const activitySelect = document.getElementById('myAreaFilterActivity');
        const clearFiltersButton = document.getElementById('myAreaClearFilters');
        let nextPageUrl = postsGrid ? (postsGrid.dataset.nextPageUrl || '') : '';
        let isLoading = false;
        let filterDebounceTimer = null;

        function buildFeedUrl(page) {
            const params = new URLSearchParams();

            if (!filterForm) {
                return feedUrl;
            }

            new FormData(filterForm).forEach(function (value, key) {
                if (String(value).trim() !== '') {
                    params.set(key, String(value).trim());
                }
            });

            if (page) {
                params.set('page', String(page));
            }

            const query = params.toString();

            return query ? `${feedUrl}?${query}` : feedUrl;
        }

        function setLoadingState(show) {
            if (loadingText) {
                loadingText.classList.toggle('d-none', !show);
            }

            if (postsSection) {
                postsSection.setAttribute('aria-busy', show ? 'true' : 'false');
            }
        }

        function syncActivityPills(activity) {
            if (!activityPills) {
                return;
            }

            activityPills.querySelectorAll('[data-activity]').forEach(function (pill) {
                pill.classList.toggle('is-active', pill.dataset.activity === activity);
            });
        }

        function updateSummary(payload) {
            if (summaryText) {
                if ((payload.total || 0) > 0) {
                    summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
                    summaryText.classList.remove('d-none');
                } else {
                    summaryText.textContent = 'Showing 0 results';
                    summaryText.classList.remove('d-none');
                }
            }

            if (totalStat) {
                const label = payload.total === 1 ? 'post' : 'posts';
                totalStat.innerHTML = `<i class="fa-solid fa-layer-group me-1"></i>${new Intl.NumberFormat().format(payload.total || 0)} local ${label}`;
            }
        }

        function updateHistory(url) {
            if (!window.history || !window.history.replaceState) {
                return;
            }

            window.history.replaceState({}, '', url);
        }

        async function loadMyAreaFeed(url, options) {
            const replace = options && options.replace === true;

            if (!postsGrid || isLoading) {
                return;
            }

            isLoading = true;
            setLoadingState(true);

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load My Area posts');
                }

                const payload = await response.json();

                if (replace) {
                    postsGrid.innerHTML = payload.html || '';
                    updateHistory(url);
                } else {
                    const emptyState = postsGrid.querySelector('.community-empty-state');

                    if (emptyState) {
                        emptyState.closest('.col-12')?.remove();
                    }

                    if (payload.html) {
                        postsGrid.insertAdjacentHTML('beforeend', payload.html);
                    }
                }

                nextPageUrl = payload.next_page_url || '';
                postsGrid.dataset.nextPageUrl = nextPageUrl;
                updateSummary(payload);
            } catch (error) {
                console.error(error);
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        function reloadFromFilters() {
            syncActivityPills(activitySelect ? activitySelect.value : '');
            loadMyAreaFeed(buildFeedUrl(1), { replace: true });
        }

        if (filterForm) {
            filterForm.addEventListener('change', reloadFromFilters);

            filterForm.querySelectorAll('input[type="text"]').forEach(function (input) {
                input.addEventListener('input', function () {
                    window.clearTimeout(filterDebounceTimer);
                    filterDebounceTimer = window.setTimeout(reloadFromFilters, 400);
                });
            });
        }

        if (activityPills) {
            activityPills.addEventListener('click', function (event) {
                const pill = event.target.closest('[data-activity]');

                if (!pill || !activitySelect) {
                    return;
                }

                activitySelect.value = pill.dataset.activity || '';
                reloadFromFilters();
            });
        }

        if (clearFiltersButton && filterForm) {
            clearFiltersButton.addEventListener('click', function () {
                filterForm.reset();
                reloadFromFilters();
            });
        }

        async function loadNextMyAreaPage() {
            if (!nextPageUrl) {
                return;
            }

            await loadMyAreaFeed(nextPageUrl, { replace: false });
        }

        if (scrollSentinel && postsGrid && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadNextMyAreaPage();
                    }
                });
            }, {
                rootMargin: '300px 0px',
            });

            observer.observe(scrollSentinel);
        } else {
            window.addEventListener('scroll', function () {
                if (!nextPageUrl || isLoading || !scrollSentinel) {
                    return;
                }

                const sentinelTop = scrollSentinel.getBoundingClientRect().top;

                if (sentinelTop <= window.innerHeight + 300) {
                    loadNextMyAreaPage();
                }
            }, { passive: true });
        }
    })();
</script>
<script src="{{ asset('assets/js/community-engagement.js') }}?v={{ filemtime(public_path('assets/js/community-engagement.js')) }}"></script>
@endpush

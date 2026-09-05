document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('educatorsPageRoot');
  if (!pageRoot) return;

  const educatorsGrid = document.getElementById('educatorsGrid');
  const featuredSection = document.getElementById('educatorsFeaturedSection');
  const featuredTrack = document.getElementById('educatorsFeaturedTrack');
  const searchFilter = document.getElementById('educatorsMarketFilterSearch');
  const subjectFilter = document.getElementById('educatorsMarketFilterSubject');
  const cityFilter = document.getElementById('educatorsMarketFilterCity');
  const radiusFilter = document.getElementById('educatorsMarketFilterRadius');
  const verifiedFilter = document.getElementById('educatorsMarketFilterVerified');
  const tuitionsFilter = document.getElementById('educatorsMarketFilterTuitions');
  const availableFilter = document.getElementById('educatorsMarketFilterAvailable');
  const locationFilter = document.getElementById('educatorsMarketFilterLocation');
  const sortFilter = document.getElementById('educatorsMarketSort');
  const ratingFilters = pageRoot.querySelectorAll('.educators-market-filter-rating');
  const applyFiltersBtn = document.getElementById('educatorsMarketApplyFilters');
  const resetFiltersBtn = document.getElementById('educatorsMarketResetFilters');
  const loadingText = document.getElementById('educatorsLoadingText');
  const summaryText = document.getElementById('educatorsSummaryText');
  const scrollSentinel = document.getElementById('educatorsScrollSentinel');
  const viewAllLink = document.getElementById('educatorsViewAllLink');
  const subjectChips = pageRoot.querySelectorAll('[data-educators-subject]');

  const indexUrl = pageRoot.dataset.indexUrl || window.location.pathname;
  const listingsUrl = pageRoot.dataset.listingsUrl || '';
  const hasLocation = pageRoot.dataset.hasLocation === '1';
  const isPreviewListing = pageRoot.dataset.previewListing === '1';

  let nextPageUrl = educatorsGrid?.dataset.nextPageUrl || '';
  let isLoading = false;
  let debounceTimer;

  function setLoadingState(show) {
    if (!loadingText) return;
    loadingText.classList.toggle('d-none', !show);
  }

  function buildEducatorsUrl(pageUrl) {
    const url = new URL(pageUrl || indexUrl, window.location.origin);
    const params = url.searchParams;

    const searchValue = searchFilter ? searchFilter.value.trim() : '';
    if (searchValue) params.set('search', searchValue); else params.delete('search');
    params.delete('q');

    if (subjectFilter && subjectFilter.value) params.set('subject', subjectFilter.value); else params.delete('subject');
    if (cityFilter && cityFilter.value) params.set('city', cityFilter.value); else params.delete('city');
    if (verifiedFilter && verifiedFilter.checked) params.set('verified', '1'); else params.delete('verified');
    if (tuitionsFilter && tuitionsFilter.checked) params.set('takes_tuitions', '1'); else params.delete('takes_tuitions');
    if (availableFilter && availableFilter.checked) params.set('available_now', '1'); else params.delete('available_now');

    const selectedRating = Array.from(ratingFilters).find(function (input) { return input.checked; });
    if (selectedRating) params.set('min_rating', selectedRating.value); else params.delete('min_rating');

    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value); else params.delete('radius');
    if (sortFilter && sortFilter.value && sortFilter.value !== 'recent') params.set('sort', sortFilter.value); else params.delete('sort');

    url.search = params.toString();
    return url.toString();
  }

  function syncBrowserUrl() {
    window.history.replaceState({}, '', buildEducatorsUrl(indexUrl));
  }

  function updateSummary(payload) {
    if (!summaryText) return;

    if (payload.total > 0) {
      if (isPreviewListing) {
        summaryText.textContent = 'Showing ' + payload.loaded_to + ' of ' + payload.total + ' educators';
      } else {
        summaryText.textContent = 'Showing 1 to ' + payload.loaded_to + ' of ' + payload.total + ' results';
      }
      summaryText.classList.remove('d-none');
    } else {
      summaryText.textContent = '';
      summaryText.classList.add('d-none');
    }
  }

  function syncViewAllLink() {
    if (!isPreviewListing || !viewAllLink || !listingsUrl) return;
    viewAllLink.href = buildEducatorsUrl(listingsUrl);
  }

  function updateFeaturedSection(payload) {
    if (!featuredTrack || !featuredSection) return;

    if (typeof payload.featured_html === 'string') {
      featuredTrack.innerHTML = payload.featured_html;
    }

    const hasFeatured = Number(payload.featured_total || 0) > 0;
    featuredSection.classList.toggle('d-none', !hasFeatured);
  }

  async function reloadEducatorsFromStart(options) {
    const settings = options || {};
    if (!educatorsGrid || isLoading) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildEducatorsUrl(indexUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to load educators');

      const payload = await response.json();
      educatorsGrid.innerHTML = payload.html || '';
      nextPageUrl = isPreviewListing ? '' : (payload.next_page_url || '');
      educatorsGrid.dataset.nextPageUrl = nextPageUrl;
      updateSummary(payload);
      updateFeaturedSection(payload);
      syncViewAllLink();

      if (settings.syncUrl !== false) {
        syncBrowserUrl();
      }
    } catch (error) {
      console.error(error);
    } finally {
      isLoading = false;
      setLoadingState(false);
    }
  }

  async function loadNextEducatorsPage() {
    if (!nextPageUrl || isLoading || !educatorsGrid) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildEducatorsUrl(nextPageUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to load more educators');

      const payload = await response.json();
      const emptyState = educatorsGrid.querySelector('.vendors-empty-state');
      if (emptyState) emptyState.remove();

      if (payload.html) {
        educatorsGrid.insertAdjacentHTML('beforeend', payload.html);
      }

      nextPageUrl = payload.next_page_url || '';
      educatorsGrid.dataset.nextPageUrl = nextPageUrl;
      updateSummary(payload);
    } catch (error) {
      console.error(error);
    } finally {
      isLoading = false;
      setLoadingState(false);
    }
  }

  function scheduleReload(delay) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
      reloadEducatorsFromStart();
    }, delay || 0);
  }

  function resetFilters() {
    if (searchFilter) searchFilter.value = '';
    if (subjectFilter) subjectFilter.value = '';
    if (cityFilter) cityFilter.value = '';
    if (radiusFilter) radiusFilter.value = '';
    if (verifiedFilter) verifiedFilter.checked = false;
    if (tuitionsFilter) tuitionsFilter.checked = false;
    if (availableFilter) availableFilter.checked = false;
    ratingFilters.forEach(function (input) { input.checked = false; });
    if (sortFilter) sortFilter.value = 'recent';
    reloadEducatorsFromStart();
  }

  const headerLocationInput = document.getElementById('headerCurrentLocation');
  if (locationFilter && headerLocationInput && headerLocationInput.value.trim()) {
    locationFilter.value = headerLocationInput.value.trim();
  }

  ratingFilters.forEach(function (input) {
    input.addEventListener('change', function () {
      if (input.checked) {
        ratingFilters.forEach(function (other) {
          if (other !== input) other.checked = false;
        });
      }
      scheduleReload(0);
    });
  });

  [subjectFilter, cityFilter, radiusFilter, verifiedFilter, tuitionsFilter, availableFilter, sortFilter].forEach(function (el) {
    if (!el) return;
    el.addEventListener('change', function () {
      scheduleReload(0);
    });
  });

  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function () {
      reloadEducatorsFromStart();
    });
  }

  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', resetFilters);
  }

  if (searchFilter) {
    searchFilter.addEventListener('input', function () {
      scheduleReload(350);
    });
  }

  subjectChips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      if (!subjectFilter) return;
      subjectFilter.value = chip.dataset.educatorsSubject || '';
      scheduleReload(0);
      document.getElementById('educatorsAllSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  if (!isPreviewListing && scrollSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          loadNextEducatorsPage();
        }
      });
    }, { rootMargin: '300px 0px' }).observe(scrollSentinel);
  } else if (!isPreviewListing) {
    window.addEventListener('scroll', function () {
      if (!nextPageUrl || isLoading || !scrollSentinel) return;
      if (scrollSentinel.getBoundingClientRect().top <= window.innerHeight + 300) {
        loadNextEducatorsPage();
      }
    }, { passive: true });
  }

  syncViewAllLink();
});

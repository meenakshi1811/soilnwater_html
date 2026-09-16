document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('institutesPageRoot');
  if (!pageRoot) return;

  const institutesGrid = document.getElementById('institutesGrid');
  const searchFilter = document.getElementById('institutesMarketFilterSearch');
  const typeFilter = document.getElementById('institutesMarketFilterType');
  const cityFilter = document.getElementById('institutesMarketFilterCity');
  const boardFilter = document.getElementById('institutesMarketFilterBoard');
  const radiusFilter = document.getElementById('institutesMarketFilterRadius');
  const verifiedFilter = document.getElementById('institutesMarketFilterVerified');
  const locationFilter = document.getElementById('institutesMarketFilterLocation');
  const sortFilter = document.getElementById('institutesMarketSort');
  const applyFiltersBtn = document.getElementById('institutesMarketApplyFilters');
  const resetFiltersBtn = document.getElementById('institutesMarketResetFilters');
  const loadingText = document.getElementById('institutesLoadingText');
  const summaryText = document.getElementById('institutesSummaryText');
  const scrollSentinel = document.getElementById('institutesScrollSentinel');
  const viewAllLink = document.getElementById('institutesViewAllLink');

  const indexUrl = pageRoot.dataset.indexUrl || window.location.pathname;
  const listingsUrl = pageRoot.dataset.listingsUrl || '';
  const hasLocation = pageRoot.dataset.hasLocation === '1';
  const isPreviewListing = pageRoot.dataset.previewListing === '1';

  let nextPageUrl = institutesGrid?.dataset.nextPageUrl || '';
  let isLoading = false;
  let debounceTimer;

  function setLoadingState(show) {
    if (!loadingText) return;
    loadingText.classList.toggle('d-none', !show);
  }

  function buildInstitutesUrl(pageUrl) {
    const url = new URL(pageUrl || indexUrl, window.location.origin);
    const params = url.searchParams;

    const searchValue = searchFilter ? searchFilter.value.trim() : '';
    if (searchValue) params.set('search', searchValue); else params.delete('search');
    params.delete('q');

    if (typeFilter && typeFilter.value) params.set('type', typeFilter.value); else params.delete('type');
    if (cityFilter && cityFilter.value) params.set('city', cityFilter.value); else params.delete('city');
    if (boardFilter && boardFilter.value) params.set('board', boardFilter.value); else params.delete('board');
    if (verifiedFilter && verifiedFilter.checked) params.set('verified', '1'); else params.delete('verified');
    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value); else params.delete('radius');
    if (sortFilter && sortFilter.value && sortFilter.value !== 'recent') params.set('sort', sortFilter.value); else params.delete('sort');

    url.search = params.toString();
    return url.toString();
  }

  function syncBrowserUrl() {
    window.history.replaceState({}, '', buildInstitutesUrl(indexUrl));
  }

  function updateSummary(payload) {
    if (!summaryText) return;

    if (payload.total > 0) {
      if (isPreviewListing) {
        summaryText.textContent = 'Showing ' + payload.loaded_to + ' of ' + payload.total + ' institutes';
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
    viewAllLink.href = buildInstitutesUrl(listingsUrl);
  }

  async function reloadInstitutesFromStart(options) {
    const settings = options || {};
    if (!institutesGrid || isLoading) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildInstitutesUrl(indexUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to load institutes');

      const payload = await response.json();
      institutesGrid.innerHTML = payload.html || '';
      nextPageUrl = isPreviewListing ? '' : (payload.next_page_url || '');
      institutesGrid.dataset.nextPageUrl = nextPageUrl;
      updateSummary(payload);
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

  async function loadNextInstitutesPage() {
    if (!nextPageUrl || isLoading || !institutesGrid) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildInstitutesUrl(nextPageUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) throw new Error('Failed to load more institutes');

      const payload = await response.json();
      const emptyState = institutesGrid.querySelector('.vendors-empty-state');
      if (emptyState) emptyState.remove();

      if (payload.html) {
        institutesGrid.insertAdjacentHTML('beforeend', payload.html);
      }

      nextPageUrl = payload.next_page_url || '';
      institutesGrid.dataset.nextPageUrl = nextPageUrl;
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
      reloadInstitutesFromStart();
    }, delay || 0);
  }

  function resetFilters() {
    if (searchFilter) searchFilter.value = '';
    if (typeFilter) typeFilter.value = '';
    if (cityFilter) cityFilter.value = '';
    if (boardFilter) boardFilter.value = '';
    if (radiusFilter) radiusFilter.value = '';
    if (verifiedFilter) verifiedFilter.checked = false;
    if (sortFilter) sortFilter.value = 'recent';
    reloadInstitutesFromStart();
  }

  const headerLocationInput = document.getElementById('headerCurrentLocation');
  if (locationFilter && headerLocationInput && headerLocationInput.value.trim()) {
    locationFilter.value = headerLocationInput.value.trim();
  }

  [typeFilter, cityFilter, boardFilter, radiusFilter, verifiedFilter, sortFilter].forEach(function (el) {
    if (!el) return;
    el.addEventListener('change', function () {
      scheduleReload(0);
    });
  });

  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function () {
      reloadInstitutesFromStart();
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

  if (!isPreviewListing && scrollSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          loadNextInstitutesPage();
        }
      });
    }, { rootMargin: '300px 0px' }).observe(scrollSentinel);
  } else if (!isPreviewListing) {
    window.addEventListener('scroll', function () {
      if (!nextPageUrl || isLoading || !scrollSentinel) return;
      if (scrollSentinel.getBoundingClientRect().top <= window.innerHeight + 300) {
        loadNextInstitutesPage();
      }
    }, { passive: true });
  }

  syncViewAllLink();
});

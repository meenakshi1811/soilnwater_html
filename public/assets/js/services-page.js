document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('servicesPageRoot');
  if (!pageRoot) return;

  const filterBar = document.getElementById('servicesFilterBar');
  const servicesGrid = document.getElementById('servicesGrid');
  const premiumSection = document.getElementById('servicesPremiumSection');
  const premiumTrack = document.getElementById('servicesPremiumTrack');
  const searchFilter = document.getElementById('servicesMarketFilterSearch');
  const categoryFilter = document.getElementById('servicesMarketFilterCategory');
  const subcategoryFilter = document.getElementById('servicesMarketFilterSubcategory');
  const radiusFilter = document.getElementById('servicesMarketFilterRadius');
  const premiumFilter = document.getElementById('servicesMarketFilterPremium');
  const verifiedFilter = document.getElementById('servicesMarketFilterVerified');
  const paymentFilter = document.getElementById('servicesMarketFilterPayment');
  const locationFilter = document.getElementById('servicesMarketFilterLocation');
  const ratingFilters = pageRoot.querySelectorAll('.services-market-filter-rating');
  const applyFiltersBtn = document.getElementById('servicesMarketApplyFilters');
  const resetFiltersBtn = document.getElementById('servicesMarketResetFilters');
  const sortFilter = document.getElementById('servicesMarketSort');
  const tabButtons = pageRoot.querySelectorAll('[data-services-tab]');
  const viewButtons = pageRoot.querySelectorAll('[data-services-view]');
  const loadingText = document.getElementById('servicesLoadingText');
  const summaryText = document.getElementById('servicesSummaryText');
  const scrollSentinel = document.getElementById('servicesScrollSentinel');
  const viewAllLink = document.getElementById('servicesViewAllLink');
  const categoryCards = pageRoot.querySelectorAll('[data-services-category-id]');

  const categories = JSON.parse(filterBar?.dataset.categories || '[]');
  const indexUrl = pageRoot.dataset.indexUrl || window.location.pathname;
  const listingsUrl = pageRoot.dataset.listingsUrl || '';
  const premiumUrl = pageRoot.dataset.premiumUrl || '/services/premium';
  const hasLocation = pageRoot.dataset.hasLocation === '1';
  const isPreviewListing = pageRoot.dataset.previewListing === '1';
  const urlParams = new URLSearchParams(window.location.search);

  let activeTab = urlParams.get('tab') || pageRoot.dataset.activeTab || 'all';
  let activeView = urlParams.get('view') || pageRoot.dataset.activeView || 'grid';
  let nextPageUrl = servicesGrid?.dataset.nextPageUrl || '';
  let isLoading = false;
  let debounceTimer;

  function setLoadingState(show) {
    if (!loadingText) return;
    loadingText.classList.toggle('d-none', !show);
  }

  function populateSubcategories() {
    if (!categoryFilter || !subcategoryFilter) return;

    subcategoryFilter.innerHTML = '<option value="">All subcategories</option>';
    const selectedCategory = categories.find(function (category) {
      return String(category.id) === String(categoryFilter.value);
    });

    if (!selectedCategory || !selectedCategory.children || !selectedCategory.children.length) {
      subcategoryFilter.disabled = true;
      subcategoryFilter.value = '';
      return;
    }

    selectedCategory.children.forEach(function (child) {
      const option = document.createElement('option');
      option.value = child.id;
      option.textContent = child.name;
      if (String(subcategoryFilter.dataset.selected || '') === String(child.id)) {
        option.selected = true;
      }
      subcategoryFilter.appendChild(option);
    });

    subcategoryFilter.disabled = false;
  }

  function buildPremiumServicesUrl() {
    const url = new URL(premiumUrl, window.location.origin);
    const params = url.searchParams;

    const searchValue = searchFilter ? searchFilter.value.trim() : '';
    if (searchValue) params.set('search', searchValue);

    if (categoryFilter && categoryFilter.value) params.set('category_id', categoryFilter.value);
    if (subcategoryFilter && subcategoryFilter.value) params.set('subcategory_id', subcategoryFilter.value);
    if (paymentFilter && paymentFilter.value) params.set('payment', paymentFilter.value);

    const selectedRating = Array.from(ratingFilters).find(function (input) { return input.checked; });
    if (selectedRating) params.set('min_rating', selectedRating.value);

    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value);

    url.search = params.toString();
    return url.toString();
  }

  function buildServicesUrl(pageUrl) {
    const url = new URL(pageUrl || indexUrl, window.location.origin);
    const params = url.searchParams;

    const searchValue = searchFilter ? searchFilter.value.trim() : '';
    if (searchValue) params.set('search', searchValue); else params.delete('search');

    if (categoryFilter && categoryFilter.value) params.set('category_id', categoryFilter.value); else params.delete('category_id');
    if (subcategoryFilter && subcategoryFilter.value) params.set('subcategory_id', subcategoryFilter.value); else params.delete('subcategory_id');
    if (premiumFilter && premiumFilter.checked) params.set('premium', '1'); else params.delete('premium');
    if (verifiedFilter && verifiedFilter.checked) params.set('verified', '1'); else params.delete('verified');
    if (paymentFilter && paymentFilter.value) params.set('payment', paymentFilter.value); else params.delete('payment');

    const selectedRating = Array.from(ratingFilters).find(function (input) { return input.checked; });
    if (selectedRating) params.set('min_rating', selectedRating.value); else params.delete('min_rating');

    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value); else params.delete('radius');
    if (sortFilter && sortFilter.value) params.set('sort', sortFilter.value); else params.delete('sort');
    if (activeTab && activeTab !== 'all') params.set('tab', activeTab); else params.delete('tab');
    if (activeView === 'list') params.set('view', 'list'); else params.delete('view');

    url.search = params.toString();
    return url.toString();
  }

  function syncViewClasses() {
    if (!servicesGrid) return;
    servicesGrid.classList.toggle('is-list-view', activeView === 'list');
    viewButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.dataset.servicesView === activeView);
    });
  }

  function syncTabClasses() {
    tabButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.dataset.servicesTab === activeTab);
    });
  }

  function syncBrowserUrl() {
    const nextUrl = buildServicesUrl(indexUrl);
    window.history.replaceState({}, '', nextUrl);
  }

  function updateSummary(payload) {
    if (!summaryText) return;

    if (payload.total > 0) {
      if (isPreviewListing) {
        summaryText.textContent = 'Showing ' + payload.loaded_to + ' of ' + payload.total + ' services';
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
    viewAllLink.href = buildServicesUrl(listingsUrl);
  }

  function updatePremiumSection(payload) {
    if (!premiumTrack || !premiumSection) return;

    if (typeof payload.premium_html === 'string') {
      premiumTrack.innerHTML = payload.premium_html;
    }

    const hasPremium = Number(payload.premium_total || 0) > 0;
    premiumSection.classList.toggle('d-none', !hasPremium);
  }

  async function reloadServicesFromStart(options) {
    const settings = options || {};

    if (!servicesGrid || isLoading) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildServicesUrl(indexUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to load services');
      }

      const payload = await response.json();
      servicesGrid.innerHTML = payload.html || '';
      nextPageUrl = isPreviewListing ? '' : (payload.next_page_url || '');
      servicesGrid.dataset.nextPageUrl = nextPageUrl;
      syncViewClasses();
      updateSummary(payload);
      updatePremiumSection(payload);
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

  async function loadNextServicesPage() {
    if (!nextPageUrl || isLoading || !servicesGrid) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildServicesUrl(nextPageUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to load more services');
      }

      const payload = await response.json();
      const emptyState = servicesGrid.querySelector('.vendors-empty-state');

      if (emptyState) {
        emptyState.remove();
      }

      if (payload.html) {
        servicesGrid.insertAdjacentHTML('beforeend', payload.html);
      }

      nextPageUrl = payload.next_page_url || '';
      servicesGrid.dataset.nextPageUrl = nextPageUrl;
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
      reloadServicesFromStart();
    }, delay || 0);
  }

  function resetFilters() {
    if (searchFilter) searchFilter.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (subcategoryFilter) {
      subcategoryFilter.innerHTML = '<option value="">All subcategories</option>';
      subcategoryFilter.disabled = true;
      subcategoryFilter.dataset.selected = '';
    }
    if (radiusFilter) radiusFilter.value = '';
    if (premiumFilter) premiumFilter.checked = false;
    if (verifiedFilter) verifiedFilter.checked = false;
    if (paymentFilter) paymentFilter.value = '';
    ratingFilters.forEach(function (input) { input.checked = false; });
    if (sortFilter) sortFilter.value = 'recent';
    activeTab = 'all';
    syncTabClasses();
    reloadServicesFromStart();
  }

  if (subcategoryFilter) {
    subcategoryFilter.dataset.selected = new URLSearchParams(window.location.search).get('subcategory_id') || '';
  }

  populateSubcategories();
  syncViewClasses();
  syncTabClasses();

  if (window.location.hash === '#servicesAllSection') {
    document.getElementById('servicesAllSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

  if (categoryFilter) {
    categoryFilter.addEventListener('change', function () {
      if (subcategoryFilter) {
        subcategoryFilter.dataset.selected = '';
      }
      populateSubcategories();
      scheduleReload(0);
    });
  }

  if (subcategoryFilter) {
    subcategoryFilter.addEventListener('change', function () {
      scheduleReload(0);
    });
  }

  if (premiumFilter) {
    premiumFilter.addEventListener('change', function () {
      if (premiumFilter.checked) {
        window.location.href = buildPremiumServicesUrl();
        return;
      }
      scheduleReload(0);
    });
  }

  if (verifiedFilter) {
    verifiedFilter.addEventListener('change', function () {
      scheduleReload(0);
    });
  }

  if (paymentFilter) {
    paymentFilter.addEventListener('change', function () {
      scheduleReload(0);
    });
  }

  if (radiusFilter) {
    radiusFilter.addEventListener('change', function () {
      scheduleReload(0);
    });
  }

  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', function () {
      reloadServicesFromStart();
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

  if (sortFilter) {
    sortFilter.addEventListener('change', function () {
      scheduleReload(0);
    });
  }

  tabButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      if (isLoading) return;
      activeTab = button.dataset.servicesTab || 'all';
      syncTabClasses();
      reloadServicesFromStart();
    });
  });

  viewButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      activeView = button.dataset.servicesView || 'grid';
      syncViewClasses();
      scheduleReload(0);
    });
  });

  categoryCards.forEach(function (card) {
    card.addEventListener('click', function (event) {
      event.preventDefault();
      if (!categoryFilter) return;
      categoryFilter.value = card.dataset.servicesCategoryId || '';
      if (subcategoryFilter) {
        subcategoryFilter.dataset.selected = '';
      }
      populateSubcategories();
      scheduleReload(0);
      document.getElementById('servicesAllSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  if (!isPreviewListing && scrollSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          loadNextServicesPage();
        }
      });
    }, { rootMargin: '300px 0px' }).observe(scrollSentinel);
  } else if (!isPreviewListing) {
    window.addEventListener('scroll', function () {
      if (!nextPageUrl || isLoading || !scrollSentinel) return;
      const sentinelTop = scrollSentinel.getBoundingClientRect().top;
      if (sentinelTop <= window.innerHeight + 300) {
        loadNextServicesPage();
      }
    }, { passive: true });
  }

  syncViewAllLink();
});

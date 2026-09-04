document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('premiumVendorsPageRoot');
  if (!pageRoot) return;

  const filterBar = document.getElementById('premiumVendorsFilterBar');
  const vendorsGrid = document.getElementById('premiumVendorsGrid');
  const searchFilter = document.getElementById('premiumVendorsFilterSearch');
  const categoryFilter = document.getElementById('premiumVendorsFilterCategory');
  const subcategoryFilter = document.getElementById('premiumVendorsFilterSubcategory');
  const radiusFilter = document.getElementById('premiumVendorsFilterRadius');
  const paymentFilter = document.getElementById('premiumVendorsFilterPayment');
  const locationFilter = document.getElementById('premiumVendorsFilterLocation');
  const ratingFilters = pageRoot.querySelectorAll('.premium-vendors-filter-rating');
  const applyFiltersBtn = document.getElementById('premiumVendorsApplyFilters');
  const resetFiltersBtn = document.getElementById('premiumVendorsResetFilters');
  const loadingText = document.getElementById('premiumVendorsLoadingText');
  const summaryText = document.getElementById('premiumVendorsSummaryText');
  const scrollSentinel = document.getElementById('premiumVendorsScrollSentinel');

  const categories = JSON.parse(filterBar?.dataset.categories || '[]');
  const indexUrl = pageRoot.dataset.indexUrl || window.location.pathname;
  const hasLocation = pageRoot.dataset.hasLocation === '1';

  let nextPageUrl = vendorsGrid?.dataset.nextPageUrl || '';
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

  function buildVendorsUrl(pageUrl) {
    const url = new URL(pageUrl || indexUrl, window.location.origin);
    const params = url.searchParams;

    const searchValue = searchFilter ? searchFilter.value.trim() : '';
    if (searchValue) params.set('search', searchValue); else params.delete('search');

    if (categoryFilter && categoryFilter.value) params.set('category_id', categoryFilter.value); else params.delete('category_id');
    if (subcategoryFilter && subcategoryFilter.value) params.set('subcategory_id', subcategoryFilter.value); else params.delete('subcategory_id');
    if (paymentFilter && paymentFilter.value) params.set('payment', paymentFilter.value); else params.delete('payment');

    const selectedRating = Array.from(ratingFilters).find(function (input) { return input.checked; });
    if (selectedRating) params.set('min_rating', selectedRating.value); else params.delete('min_rating');

    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value); else params.delete('radius');

    url.search = params.toString();
    return url.toString();
  }

  function syncBrowserUrl() {
    window.history.replaceState({}, '', buildVendorsUrl(indexUrl));
  }

  function updateSummary(payload) {
    if (!summaryText) return;

    if (payload.total > 0) {
      summaryText.textContent = 'Showing 1 to ' + payload.loaded_to + ' of ' + payload.total + ' premium vendors';
      summaryText.classList.remove('d-none');
    } else {
      summaryText.textContent = '';
      summaryText.classList.add('d-none');
    }
  }

  async function reloadVendorsFromStart(options) {
    const settings = options || {};

    if (!vendorsGrid || isLoading) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildVendorsUrl(indexUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to load premium vendors');
      }

      const payload = await response.json();
      vendorsGrid.innerHTML = payload.html || '';
      nextPageUrl = payload.next_page_url || '';
      vendorsGrid.dataset.nextPageUrl = nextPageUrl;
      updateSummary(payload);

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

  async function loadNextVendorsPage() {
    if (!nextPageUrl || isLoading || !vendorsGrid) return;

    isLoading = true;
    setLoadingState(true);

    try {
      const response = await fetch(buildVendorsUrl(nextPageUrl), {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error('Failed to load more premium vendors');
      }

      const payload = await response.json();
      const emptyState = vendorsGrid.querySelector('.vendors-empty-inline');

      if (emptyState) {
        emptyState.remove();
      }

      if (payload.html) {
        vendorsGrid.insertAdjacentHTML('beforeend', payload.html);
      }

      nextPageUrl = payload.next_page_url || '';
      vendorsGrid.dataset.nextPageUrl = nextPageUrl;
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
      reloadVendorsFromStart();
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
    if (paymentFilter) paymentFilter.value = '';
    ratingFilters.forEach(function (input) { input.checked = false; });
    reloadVendorsFromStart();
  }

  if (subcategoryFilter) {
    subcategoryFilter.dataset.selected = new URLSearchParams(window.location.search).get('subcategory_id') || '';
  }

  populateSubcategories();

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
      reloadVendorsFromStart();
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

  if (scrollSentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          loadNextVendorsPage();
        }
      });
    }, { rootMargin: '300px 0px' }).observe(scrollSentinel);
  } else {
    window.addEventListener('scroll', function () {
      if (!nextPageUrl || isLoading || !scrollSentinel) return;
      const sentinelTop = scrollSentinel.getBoundingClientRect().top;
      if (sentinelTop <= window.innerHeight + 300) {
        loadNextVendorsPage();
      }
    }, { passive: true });
  }
});

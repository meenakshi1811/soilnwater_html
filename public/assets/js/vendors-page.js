document.addEventListener('DOMContentLoaded', function () {
  const pageRoot = document.getElementById('vendorsPageRoot');
  if (!pageRoot) return;

  const filterBar = document.getElementById('vendorsFilterBar');
  const vendorsGrid = document.getElementById('vendorsGrid');
  const searchFilter = document.getElementById('vendorsMarketFilterSearch');
  const categoryFilter = document.getElementById('vendorsMarketFilterCategory');
  const subcategoryFilter = document.getElementById('vendorsMarketFilterSubcategory');
  const radiusFilter = document.getElementById('vendorsMarketFilterRadius');
  const premiumFilter = document.getElementById('vendorsMarketFilterPremium');
  const verifiedFilter = document.getElementById('vendorsMarketFilterVerified');
  const paymentFilter = document.getElementById('vendorsMarketFilterPayment');
  const applyFiltersBtn = document.getElementById('vendorsMarketApplyFilters');
  const resetFiltersBtn = document.getElementById('vendorsMarketResetFilters');
  const sortFilter = document.getElementById('vendorsMarketSort');
  const tabButtons = pageRoot.querySelectorAll('[data-vendors-tab]');
  const viewButtons = pageRoot.querySelectorAll('[data-vendors-view]');
  const loadingText = document.getElementById('vendorsLoadingText');
  const summaryText = document.getElementById('vendorsSummaryText');
  const scrollSentinel = document.getElementById('vendorsScrollSentinel');
  const viewPremiumLink = document.getElementById('vendorsViewPremiumLink');
  const categoryCards = pageRoot.querySelectorAll('[data-vendors-category-id]');

  const categories = JSON.parse(filterBar?.dataset.categories || '[]');
  const indexUrl = pageRoot.dataset.indexUrl || window.location.pathname;
  const hasLocation = pageRoot.dataset.hasLocation === '1';

  let activeTab = pageRoot.dataset.activeTab || 'all';
  let activeView = pageRoot.dataset.activeView || 'grid';
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
      return;
    }

    selectedCategory.children.forEach(function (child) {
      const option = document.createElement('option');
      option.value = child.id;
      option.textContent = child.name;
      if (String(new URLSearchParams(window.location.search).get('subcategory_id') || '') === String(child.id)) {
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
    if (premiumFilter && premiumFilter.checked) params.set('premium', '1'); else params.delete('premium');
    if (verifiedFilter && verifiedFilter.checked) params.set('verified', '1'); else params.delete('verified');
    if (paymentFilter && paymentFilter.value) params.set('payment', paymentFilter.value); else params.delete('payment');
    if (radiusFilter && radiusFilter.value && hasLocation) params.set('radius', radiusFilter.value); else params.delete('radius');
    if (sortFilter && sortFilter.value) params.set('sort', sortFilter.value); else params.delete('sort');
    if (activeTab && activeTab !== 'all') params.set('tab', activeTab); else params.delete('tab');
    if (activeView === 'list') params.set('view', 'list'); else params.delete('view');

    url.search = params.toString();
    return url.toString();
  }

  function syncViewClasses() {
    if (!vendorsGrid) return;
    vendorsGrid.classList.toggle('is-list-view', activeView === 'list');
    viewButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.dataset.vendorsView === activeView);
    });
  }

  function syncTabClasses() {
    tabButtons.forEach(function (button) {
      button.classList.toggle('is-active', button.dataset.vendorsTab === activeTab);
    });
  }

  function updateSummary(payload) {
    if (!summaryText) return;

    if (payload.total > 0) {
      summaryText.textContent = 'Showing 1 to ' + payload.loaded_to + ' of ' + payload.total + ' results';
      summaryText.classList.remove('d-none');
    } else {
      summaryText.textContent = '';
      summaryText.classList.add('d-none');
    }
  }

  async function reloadVendorsFromStart() {
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
        throw new Error('Failed to search vendors');
      }

      const payload = await response.json();
      vendorsGrid.innerHTML = payload.html || '';
      nextPageUrl = payload.next_page_url || '';
      vendorsGrid.dataset.nextPageUrl = nextPageUrl;
      syncViewClasses();
      updateSummary(payload);
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
        throw new Error('Failed to load more vendors');
      }

      const payload = await response.json();
      const emptyState = vendorsGrid.querySelector('.vendors-empty-state');

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

  function resetFilters() {
    if (searchFilter) searchFilter.value = '';
    if (categoryFilter) categoryFilter.value = '';
    if (subcategoryFilter) {
      subcategoryFilter.innerHTML = '<option value="">All subcategories</option>';
      subcategoryFilter.disabled = true;
    }
    if (radiusFilter) radiusFilter.value = '';
    if (premiumFilter) premiumFilter.checked = false;
    if (verifiedFilter) verifiedFilter.checked = false;
    if (paymentFilter) paymentFilter.value = '';
    if (sortFilter) sortFilter.value = 'recent';
    activeTab = 'all';
    syncTabClasses();
    reloadVendorsFromStart();
  }

  populateSubcategories();
  syncViewClasses();
  syncTabClasses();

  if (categoryFilter) {
    categoryFilter.addEventListener('change', function () {
      populateSubcategories();
    });
  }

  if (applyFiltersBtn) {
    applyFiltersBtn.addEventListener('click', reloadVendorsFromStart);
  }

  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', resetFilters);
  }

  if (searchFilter) {
    searchFilter.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(reloadVendorsFromStart, 350);
    });
  }

  if (sortFilter) {
    sortFilter.addEventListener('change', reloadVendorsFromStart);
  }

  tabButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      activeTab = button.dataset.vendorsTab || 'all';
      syncTabClasses();
      reloadVendorsFromStart();
    });
  });

  viewButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      activeView = button.dataset.vendorsView || 'grid';
      syncViewClasses();
      reloadVendorsFromStart();
    });
  });

  categoryCards.forEach(function (card) {
    card.addEventListener('click', function (event) {
      event.preventDefault();
      if (!categoryFilter) return;
      categoryFilter.value = card.dataset.vendorsCategoryId || '';
      populateSubcategories();
      reloadVendorsFromStart();
      document.getElementById('vendorsAllSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  if (viewPremiumLink) {
    viewPremiumLink.addEventListener('click', function (event) {
      event.preventDefault();
      if (premiumFilter) premiumFilter.checked = true;
      document.getElementById('vendorsAllSection')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      reloadVendorsFromStart();
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

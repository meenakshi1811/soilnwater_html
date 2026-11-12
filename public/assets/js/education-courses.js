// Education Hub › Courses: the filters, sort and search reload the cards without leaving the page
// (and keep the address bar in step); more courses load as the visitor scrolls to the end.
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var root = document.getElementById('coursesPageRoot');
  if (!root) return;

  var grid = document.getElementById('coursesGrid');
  var search = document.getElementById('coursesFilterSearch');
  var kind = document.getElementById('coursesFilterKind');
  var city = document.getElementById('coursesFilterCity');
  var sort = document.getElementById('coursesSort');
  var apply = document.getElementById('coursesApplyFilters');
  var reset = document.getElementById('coursesResetFilters');
  var summary = document.getElementById('coursesSummaryText');
  var loading = document.getElementById('coursesLoadingText');
  var sentinel = document.getElementById('coursesScrollSentinel');
  var indexUrl = root.dataset.indexUrl || window.location.pathname;

  var nextPageUrl = grid.dataset.nextPageUrl || '';
  var busy = false;
  var request = 0; // (a newer filter change wins over an older one still loading)
  var typingTimer;

  // The address of the courses with the chosen filters (a page URL keeps its page number)
  function urlFor(pageUrl) {
    var url = new URL(pageUrl || indexUrl, window.location.origin);
    var params = url.searchParams;
    var set = function (name, value, skip) {
      if (value && value !== skip) params.set(name, value); else params.delete(name);
    };
    set('q', search ? search.value.trim() : '');
    set('kind', kind ? kind.value : '');
    set('city', city ? city.value : '');
    set('sort', sort ? sort.value : '', 'recommended');
    url.search = params.toString();
    return url.toString();
  }

  function setLoading(on) {
    busy = on;
    root.classList.toggle('is-loading', on);
    if (loading) loading.classList.toggle('d-none', !on);
  }

  function showSummary(payload) {
    if (!summary) return;
    summary.textContent = payload.total > 0
      ? 'Showing 1 to ' + payload.loaded_to + ' of ' + Number(payload.total).toLocaleString('en-IN') + ' courses'
      : 'No courses to show';
  }

  function fetchJson(url) {
    return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Could not load courses');
        return response.json();
      });
  }

  // The end of the list still on screen after a load (a tall screen): keep going
  function moreIfVisible() {
    if (!nextPageUrl || !sentinel) return;
    if (sentinel.getBoundingClientRect().top < window.innerHeight + 400) loadMore();
  }

  // Filters changed: the first page again
  function reload() {
    var mine = ++request;
    setLoading(true);
    fetchJson(urlFor(indexUrl))
      .then(function (payload) {
        if (mine !== request) return;
        grid.innerHTML = payload.html || '';
        nextPageUrl = payload.next_page_url || '';
        showSummary(payload);
        window.history.replaceState({}, '', urlFor(indexUrl));
      })
      .catch(function (error) { console.error(error); })
      .finally(function () {
        if (mine === request) {
          setLoading(false);
          moreIfVisible();
        }
      });
  }

  // Scrolled to the end: the next page below
  function loadMore() {
    if (!nextPageUrl || busy) return;
    var mine = request;
    setLoading(true);
    fetchJson(urlFor(nextPageUrl))
      .then(function (payload) {
        if (mine !== request) return;
        grid.insertAdjacentHTML('beforeend', payload.html || '');
        nextPageUrl = payload.next_page_url || '';
        showSummary(payload);
      })
      .catch(function (error) { console.error(error); })
      .finally(function () {
        if (mine === request) {
          setLoading(false);
          moreIfVisible();
        }
      });
  }

  [kind, city, sort].forEach(function (field) {
    if (field) field.addEventListener('change', reload);
  });

  if (search) {
    search.addEventListener('input', function () {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(reload, 350);
    });
    search.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        clearTimeout(typingTimer);
        reload();
      }
    });
  }

  if (apply) apply.addEventListener('click', reload);

  if (reset) {
    reset.addEventListener('click', function () {
      if (search) search.value = '';
      if (kind) kind.value = '';
      if (city) city.value = '';
      if (sort) sort.value = 'recommended';
      reload();
    });
  }

  if (sentinel && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) loadMore();
      });
    }, { rootMargin: '400px 0px' }).observe(sentinel);
  }
});

// Education Hub listing pages (courses, jobs, scholarships): the search, the filters and the sort
// reload the cards without leaving the page (and keep the address bar in step); more cards load as
// the visitor scrolls to the end. Fields are marked data-filter="<query name>".
document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  var root = document.querySelector('[data-listing-root]');
  if (!root) return;

  var grid = root.querySelector('[data-listing-grid]');
  var fields = Array.prototype.slice.call(root.querySelectorAll('[data-filter]'));
  var search = root.querySelector('[data-filter="q"]');
  var apply = root.querySelector('[data-listing-apply]');
  var reset = root.querySelector('[data-listing-reset]');
  var summary = root.querySelector('[data-listing-summary]');
  var loading = root.querySelector('[data-listing-loading]');
  var sentinel = root.querySelector('[data-listing-sentinel]');
  var indexUrl = root.dataset.indexUrl || window.location.pathname;
  var defaultSort = root.dataset.defaultSort || '';
  var nounOne = root.dataset.nounOne || 'result';
  var nounMany = root.dataset.nounMany || 'results';

  var nextPageUrl = grid.dataset.nextPageUrl || '';
  var busy = false;
  var request = 0; // (a newer change wins over an older one still loading)
  var typingTimer;

  // The address with the chosen filters (a page URL keeps its page number)
  function urlFor(pageUrl) {
    var url = new URL(pageUrl || indexUrl, window.location.origin);
    fields.forEach(function (field) {
      var name = field.dataset.filter;
      var value = (field.value || '').trim();
      if (value && !(name === 'sort' && value === defaultSort)) url.searchParams.set(name, value);
      else url.searchParams.delete(name);
    });
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
      ? 'Showing 1 to ' + payload.loaded_to + ' of ' + Number(payload.total).toLocaleString('en-IN') + ' ' + (payload.total === 1 ? nounOne : nounMany)
      : 'No ' + nounMany + ' to show';
  }

  function fetchJson(url) {
    return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } })
      .then(function (response) {
        if (!response.ok) throw new Error('Could not load ' + nounMany);
        return response.json();
      });
  }

  // The end of the list still on screen after a load (a tall screen): keep going
  function moreIfVisible() {
    if (nextPageUrl && sentinel && sentinel.getBoundingClientRect().top < window.innerHeight + 400) loadMore();
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

  fields.forEach(function (field) {
    if (field !== search) field.addEventListener('change', reload);
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
      fields.forEach(function (field) {
        field.value = field.dataset.filter === 'sort' ? defaultSort : '';
      });
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

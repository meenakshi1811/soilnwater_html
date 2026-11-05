/**
 * Inline (AJAX) editing of a school/institute public page by its owner.
 * - Toggles an "edit mode" that reveals per-section pencils + a section toolbar.
 * - Loads a section's form into an offcanvas, saves via the existing portal endpoints,
 *   then re-renders just the affected public section(s) — no full page reload.
 */
(function () {
  'use strict';

  var root = document.getElementById('schInlineEdit');
  if (!root) return;

  var formUrlBase = root.dataset.formUrlBase;
  var sectionUrlBase = root.dataset.sectionUrlBase;
  var csrf = root.dataset.csrf;

  var offcanvasEl = document.getElementById('schInlineEditor');
  var bodyEl = document.getElementById('schInlineEditorBody');
  var titleEl = document.getElementById('schInlineEditorLabel');
  var offcanvas = null;
  var activeSection = null;

  var SECTION_LABELS = {
    basics: 'About & info', notices: 'Notices', classes: 'Classes', courses: 'Courses',
    performers: 'Results', achievements: 'Achievements', books: 'Books',
    facilities: 'Facilities', gallery: 'Gallery', jobs: 'Jobs', fee: 'Fee structure',
    'diary-holiday': 'Holidays', 'diary-leave': 'Leave rules'
  };
  // Public blocks to re-render after a change in a given editor section.
  // 'diary' is a page-level key: re-fetch the current page and swap its body (used on the diary page).
  var REFRESH_MAP = {
    basics: ['content', 'hero'],
    notices: ['notices'],
    classes: ['content'], courses: ['content'], performers: ['content'],
    achievements: ['content'], books: ['content'], facilities: ['content'],
    gallery: ['content'], jobs: ['content'], fee: ['content'],
    'diary-holiday': ['diary'], 'diary-leave': ['diary']
  };

  function toast(type, msg) {
    if (window.toastr) { window.toastr[type](msg); return; }
    if (type === 'error' || type === 'warning') { window.alert(msg); }
  }

  function buildUrl(base, section) { return base.replace('__SECTION__', section); }

  function getOffcanvas() {
    if (!offcanvas && window.bootstrap && window.bootstrap.Offcanvas) {
      offcanvas = window.bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
    }
    return offcanvas;
  }

  function setEditMode(on) {
    document.body.classList.toggle('sch-edit-mode', on);
    var toggle = root.querySelector('.js-sch-inline-toggle');
    if (toggle) {
      toggle.setAttribute('aria-pressed', on ? 'true' : 'false');
      var label = toggle.querySelector('.js-sch-inline-toggle-label');
      if (label) label.textContent = on ? 'Done editing' : 'Edit page';
    }
  }

  var toggleBtn = root.querySelector('.js-sch-inline-toggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      setEditMode(!document.body.classList.contains('sch-edit-mode'));
    });
  }

  // Open a section editor from a pencil or the toolbar.
  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('.js-sch-inline-edit');
    if (!trigger) return;
    e.preventDefault();
    if (!document.body.classList.contains('sch-edit-mode')) setEditMode(true);
    openSection(trigger.getAttribute('data-inline-edit'));
  });

  function openSection(section) {
    if (!section) return;
    activeSection = section;
    if (titleEl) titleEl.textContent = 'Edit ' + (SECTION_LABELS[section] || section);
    bodyEl.innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span> Loading…</div>';
    var oc = getOffcanvas(); if (oc) oc.show();

    fetch(buildUrl(formUrlBase, section), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { if (!r.ok) throw new Error('load'); return r.text(); })
      .then(function (html) { bodyEl.innerHTML = html; })
      .catch(function () {
        bodyEl.innerHTML = '<div class="alert alert-danger">Could not load the editor. Please try again.</div>';
      });
  }

  // Add: a form submit inside the offcanvas.
  bodyEl.addEventListener('submit', function (e) {
    var form = e.target.closest('form');
    if (!form) return;
    e.preventDefault();
    var storeUrl = form.getAttribute('data-store-url');
    if (!storeUrl) return;

    var btn = form.querySelector('.js-inst-submit-btn');
    setLoading(btn, true);
    clearErrors(form);

    fetch(storeUrl, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin',
      body: new FormData(form)
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, status: r.status, data: d }; }); })
      .then(function (res) {
        if (!res.ok) { handleErrors(form, res); return; }
        toast('success', res.data.message || 'Saved.');
        var addedHtml = res.data.item_html || res.data.html; // content endpoints use item_html, jobs uses html
        if (addedHtml) {
          var section = form.closest('section');
          var list = section ? section.querySelector('.sch-manage-list') : null;
          if (list) {
            var empty = list.querySelector('.sch-manage-empty'); if (empty) empty.remove();
            list.insertAdjacentHTML('afterbegin', addedHtml);
          }
          form.reset(); // clear the "add item" form (edit forms keep their values)
        }
        refreshPublic(activeSection);
      })
      .catch(function () { toast('error', 'Could not save. Please try again.'); })
      .finally(function () { setLoading(btn, false); });
  });

  // Delete: an item remove button inside the offcanvas.
  bodyEl.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-url]');
    if (!btn || !/-delete(\s|$)/.test(btn.className)) return;
    e.preventDefault();
    if (!window.confirm('Remove this item from your public page?')) return;
    btn.disabled = true;

    var payload = new FormData();
    payload.append('_method', 'DELETE');
    payload.append('_token', csrf);
    // JSON-array lists (facilities, gallery, …) identify the item by a data value, not a route id
    var delKey = btn.getAttribute('data-del-key');
    if (delKey) payload.append(delKey, btn.getAttribute('data-del-value') || '');

    fetch(btn.getAttribute('data-url'), {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin',
      body: payload
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
      .then(function (res) {
        if (!res.ok) { toast('error', (res.data && res.data.message) || 'Could not remove.'); btn.disabled = false; return; }
        toast('success', res.data.message || 'Removed.');
        var item = btn.closest('.sch-manage-item'); if (item) item.remove();
        refreshPublic(activeSection);
      })
      .catch(function () { toast('error', 'Could not remove. Please try again.'); btn.disabled = false; });
  });

  // Open/close a job from inside the offcanvas (reuses the portal jobs.update endpoint).
  bodyEl.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-inst-job-close, .js-inst-job-reopen');
    if (!btn) return;
    e.preventDefault();
    btn.disabled = true;

    var payload = new FormData();
    payload.append('_method', 'PUT');
    payload.append('_token', csrf);
    payload.append('status', btn.getAttribute('data-status') || 'open');

    fetch(btn.getAttribute('data-url'), {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin',
      body: payload
    })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
      .then(function (res) {
        if (!res.ok || !res.data.html) { toast('error', (res.data && res.data.message) || 'Could not update.'); btn.disabled = false; return; }
        toast('success', res.data.message || 'Updated.');
        var item = btn.closest('.sch-manage-item');
        if (item) {
          var tmp = document.createElement('div');
          tmp.innerHTML = res.data.html;
          if (tmp.firstElementChild) item.replaceWith(tmp.firstElementChild);
        }
        refreshPublic(activeSection);
      })
      .catch(function () { toast('error', 'Could not update. Please try again.'); btn.disabled = false; });
  });

  function refreshPublic(section) {
    (REFRESH_MAP[section] || []).forEach(function (key) {
      // Page-level refresh (separate pages like the diary): re-fetch this page, swap its body.
      if (key === 'diary') { refetchAndSwap('.sch-body'); return; }

      fetch(buildUrl(sectionUrlBase, key), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      })
        .then(function (r) { return r.ok ? r.text() : Promise.reject(); })
        .then(function (html) { applyRefresh(key, html); })
        .catch(function () { /* keep the popup list as the source of truth on failure */ });
    });
  }

  // Re-fetch the current page and swap one container in place (no full navigation).
  function refetchAndSwap(selector) {
    fetch(window.location.href, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { return r.ok ? r.text() : Promise.reject(); })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = doc.querySelector(selector);
        var current = document.querySelector(selector);
        if (fresh && current) current.replaceWith(fresh);
        if (typeof window.initCardCarousels === 'function') window.initCardCarousels();
      })
      .catch(function () {});
  }

  function applyRefresh(key, html) {
    if (key === 'content') {
      var main = document.querySelector('.sch-content-main');
      if (main) {
        var tmp = document.createElement('div');
        tmp.innerHTML = html;
        var fresh = tmp.querySelector('.sch-content-main') || tmp.firstElementChild;
        if (fresh) main.replaceWith(fresh);
      }
    } else if (key === 'notices') {
      var wrap = document.querySelector('[data-inline-wrap="notices"]');
      if (wrap) wrap.innerHTML = html;
    } else if (key === 'hero') {
      var hero = document.getElementById('sch-overview');
      if (hero) {
        var tmpHero = document.createElement('div');
        tmpHero.innerHTML = html;
        var freshHero = tmpHero.querySelector('#sch-overview') || tmpHero.firstElementChild;
        if (freshHero) hero.replaceWith(freshHero);
      }
    }
    if (typeof window.initCardCarousels === 'function') window.initCardCarousels();
  }

  function setLoading(btn, on) {
    if (!btn) return;
    btn.disabled = on;
    var t = btn.querySelector('.btn-text'); var l = btn.querySelector('.btn-loader');
    if (t) t.classList.toggle('d-none', on);
    if (l) l.classList.toggle('d-none', !on);
  }

  function clearErrors(form) {
    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
    var alert = form.querySelector('.alert'); if (alert) { alert.classList.add('d-none'); alert.innerHTML = ''; }
  }

  function handleErrors(form, res) {
    var data = res.data || {};
    var messages = [];
    if (res.status === 422 && data.errors) {
      Object.keys(data.errors).forEach(function (field) {
        messages = messages.concat(data.errors[field]);
        var input = form.querySelector('[name="' + field + '"]');
        if (input) input.classList.add('is-invalid');
      });
    } else if (data.message) {
      messages.push(data.message);
    }
    if (!messages.length) messages.push('Please check the form and try again.');

    var alert = form.querySelector('.alert');
    if (alert) {
      alert.className = 'alert alert-warning';
      alert.innerHTML = messages.map(function (m) {
        var d = document.createElement('div'); d.textContent = m; return d.outerHTML;
      }).join('');
    }
    toast('warning', messages.length === 1 ? messages[0] : 'Please fix the highlighted fields.');
  }
})();

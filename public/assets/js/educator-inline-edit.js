/**
 * Inline editing of a teacher / tutor's public page by its owner (frontend/educator/show):
 * an Edit page button and section bar, a pencil on each section, and a wide popup that loads the
 * section's form. A save closes the popup and reloads the page where it was (in edit mode);
 * notices and associated schools change inside the popup, and the page reloads when it closes.
 */
(function () {
  'use strict';

  var root = document.getElementById('eduInlineEdit');
  var modalEl = document.getElementById('eduInlineEditor');
  if (!root || !modalEl) return;

  var bodyEl = document.getElementById('eduInlineEditorBody');
  var titleEl = document.getElementById('eduInlineEditorLabel');
  var formUrlBase = root.dataset.formUrlBase;
  var csrf = root.dataset.csrf;
  var STATE_KEY = 'eduInlineEdit:' + window.location.pathname;
  var activeSection = null;
  var changed = false;

  function popup() {
    return window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(modalEl) : null;
  }

  function toast(type, msg) {
    if (window.toastr) { window.toastr[type](msg); return; }
    if (type === 'error' || type === 'warning') { window.alert(msg); }
  }

  function setEditMode(on) {
    document.body.classList.toggle('edu-edit-mode', on);
    var toggle = root.querySelector('.js-edu-inline-toggle');
    toggle.setAttribute('aria-pressed', on ? 'true' : 'false');
    toggle.querySelector('.js-edu-inline-toggle-label').textContent = on ? 'Done editing' : 'Edit page';
  }

  // Back from a save: edit mode on, at the same place on the page
  try {
    var saved = JSON.parse(window.sessionStorage.getItem(STATE_KEY) || 'null');
    if (saved) {
      window.sessionStorage.removeItem(STATE_KEY);
      setEditMode(true);
      window.addEventListener('load', function () { window.scrollTo(0, saved.scroll || 0); });
      if (saved.message) toast('success', saved.message);
    }
  } catch (e) { /* storage blocked: start as usual */ }

  function reloadInEditMode(message) {
    try {
      window.sessionStorage.setItem(STATE_KEY, JSON.stringify({ scroll: window.scrollY, message: message || '' }));
    } catch (e) { /* storage blocked: a plain reload */ }
    window.location.reload();
  }

  root.querySelector('.js-edu-inline-toggle').addEventListener('click', function () {
    setEditMode(!document.body.classList.contains('edu-edit-mode'));
  });

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('.js-edu-inline-edit');
    if (!trigger) return;
    e.preventDefault();
    if (!document.body.classList.contains('edu-edit-mode')) setEditMode(true);
    openSection(trigger.getAttribute('data-inline-edit'), trigger.getAttribute('data-label'));
  });

  function openSection(section, label) {
    activeSection = section;
    if (label) titleEl.textContent = 'Edit ' + label;
    loadForm();
    var p = popup(); if (p) p.show();
  }

  function loadForm() {
    bodyEl.innerHTML = '<div class="text-center text-muted py-5"><span class="spinner-border spinner-border-sm me-2"></span> Loading…</div>';
    fetch(formUrlBase.replace('__SECTION__', activeSection), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { if (!r.ok) throw new Error('load'); return r.text(); })
      .then(function (html) { bodyEl.innerHTML = html; })
      .catch(function () { bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Could not load the editor. Please try again.</div>'; });
  }

  // (a list changed inside the popup: the page reloads once it closes)
  modalEl.addEventListener('hidden.bs.modal', function () {
    if (changed) { changed = false; reloadInEditMode(); }
  });

  function send(url, body) {
    return fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      credentials: 'same-origin',
      body: body
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, status: r.status, data: d }; });
    });
  }

  // A section form: save it (data-save-url), or add an item to a list (data-store-url)
  bodyEl.addEventListener('submit', function (e) {
    var form = e.target.closest('.edu-inline-form');
    if (!form) return;
    e.preventDefault();
    var saveUrl = form.getAttribute('data-save-url');
    var storeUrl = form.getAttribute('data-store-url');
    var btn = form.querySelector('.js-edu-inline-submit');
    setLoading(btn, true);
    clearErrors(form);

    send(saveUrl || storeUrl, new FormData(form))
      .then(function (res) {
        if (!res.ok) { showErrors(form, res); return; }
        var message = (res.data && res.data.message) || 'Saved.';
        if (saveUrl) {
          // the page shows the saved section once it reloads
          changed = false;
          reloadInEditMode(message);
          return;
        }
        toast('success', message);
        changed = true;
        loadForm();
      })
      .catch(function () { toast('error', 'Could not save. Please try again.'); })
      .finally(function () { setLoading(btn, false); });
  });

  // Remove an item from a list (notices, associated schools)
  bodyEl.addEventListener('click', function (e) {
    var btn = e.target.closest('.js-edu-inline-delete');
    if (!btn) return;
    e.preventDefault();
    if (!window.confirm(btn.getAttribute('data-confirm') || 'Remove this?')) return;
    btn.disabled = true;
    var body = new FormData();
    body.append('_method', 'DELETE');
    send(btn.getAttribute('data-url'), body).then(function (res) {
      if (!res.ok) { toast('error', (res.data && res.data.message) || 'Could not remove.'); btn.disabled = false; return; }
      toast('success', (res.data && res.data.message) || 'Removed.');
      changed = true;
      loadForm();
    }).catch(function () { toast('error', 'Could not remove. Please try again.'); btn.disabled = false; });
  });

  // Repeating rows (subjects, experience, education): add from the group's template, remove
  bodyEl.addEventListener('click', function (e) {
    var add = e.target.closest('.js-edu-row-add');
    if (add) {
      var group = add.closest('.js-edu-row-group') || add.closest('form');
      var rows = group.querySelector('.js-edu-rows');
      var template = group.querySelector('.js-edu-row-template');
      var index = parseInt(rows.getAttribute('data-next-index') || '0', 10);
      rows.setAttribute('data-next-index', String(index + 1));
      rows.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__i__/g, String(index)));
      var first = rows.lastElementChild && rows.lastElementChild.querySelector('input, textarea');
      if (first) first.focus();
      return;
    }
    var remove = e.target.closest('.js-edu-row-remove');
    if (remove) {
      var row = remove.closest('.js-edu-row');
      if (row) row.remove();
    }
  });

  // A picked photo previews before saving
  bodyEl.addEventListener('change', function (e) {
    if (!e.target.matches('.js-edu-inline-photo') || !e.target.files || !e.target.files[0]) return;
    var preview = bodyEl.querySelector('.js-edu-inline-photo-preview');
    if (preview) preview.src = URL.createObjectURL(e.target.files[0]);
  });

  // Associated schools: search, then add one
  var searchTimer = null;
  bodyEl.addEventListener('input', function (e) {
    if (!e.target.matches('.js-edu-affiliation-search')) return;
    var box = e.target.closest('.js-edu-affiliation-add');
    var results = box.querySelector('.js-edu-affiliation-results');
    var q = e.target.value.trim();
    window.clearTimeout(searchTimer);
    if (q.length < 2) {
      results.innerHTML = '<p class="text-secondary small mb-0">Type at least 2 letters to search the schools, colleges and institutes on SoilnWater.</p>';
      return;
    }
    searchTimer = window.setTimeout(function () {
      fetch(box.getAttribute('data-search-url') + '?q=' + encodeURIComponent(q), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var list = (data && data.results) || [];
          if (!list.length) { results.innerHTML = '<p class="text-secondary small mb-0">Nothing found for “' + escapeHtml(q) + '”.</p>'; return; }
          results.innerHTML = list.map(function (item) {
            return '<div class="edu-inline-list__item"><div class="flex-grow-1 min-w-0"><strong class="d-block text-truncate">' + escapeHtml(item.name) + '</strong>'
              + '<span class="small text-secondary">' + escapeHtml([item.type, item.city, item.state].filter(Boolean).join(' · ')) + '</span></div>'
              + '<button type="button" class="btn btn-sm btn-outline-primary js-edu-affiliation-pick" data-id="' + item.id + '">Add</button></div>';
          }).join('');
        })
        .catch(function () { results.innerHTML = '<p class="text-danger small mb-0">Search failed. Please try again.</p>'; });
    }, 300);
  });

  bodyEl.addEventListener('click', function (e) {
    var pick = e.target.closest('.js-edu-affiliation-pick');
    if (!pick) return;
    var box = pick.closest('.js-edu-affiliation-add');
    pick.disabled = true;
    var body = new FormData();
    body.append('institute_id', pick.getAttribute('data-id'));
    body.append('role_title', box.querySelector('.js-edu-affiliation-role').value);
    body.append('subject', box.querySelector('.js-edu-affiliation-subject').value);
    send(box.getAttribute('data-store-url'), body).then(function (res) {
      if (!res.ok) { toast('warning', (res.data && res.data.message) || 'Could not add it.'); pick.disabled = false; return; }
      toast('success', (res.data && res.data.message) || 'Added.');
      changed = true;
      loadForm();
    }).catch(function () { toast('error', 'Could not add it. Please try again.'); pick.disabled = false; });
  });

  function escapeHtml(value) {
    var d = document.createElement('div');
    d.textContent = value == null ? '' : String(value);
    return d.innerHTML;
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
    var alert = form.querySelector('.alert'); if (alert) { alert.className = 'alert d-none mb-3'; alert.innerHTML = ''; }
  }

  // Laravel's "subjects.0.name" → the field named "subjects[0][name]"
  function fieldName(key) {
    var parts = key.split('.');
    return parts[0] + parts.slice(1).map(function (p) { return '[' + p + ']'; }).join('');
  }

  function showErrors(form, res) {
    var data = res.data || {};
    var messages = [];
    if (res.status === 422 && data.errors) {
      Object.keys(data.errors).forEach(function (key) {
        messages = messages.concat(data.errors[key]);
        var input = form.querySelector('[name="' + fieldName(key) + '"]');
        if (input) input.classList.add('is-invalid');
      });
    } else if (data.message) {
      messages.push(data.message);
    }
    if (!messages.length) messages.push('Please check the form and try again.');
    var alert = form.querySelector('.alert');
    if (alert) {
      alert.className = 'alert alert-warning mb-3';
      alert.innerHTML = messages.map(function (m) { return '<div>' + escapeHtml(m) + '</div>'; }).join('');
    }
    toast('warning', messages.length === 1 ? messages[0] : 'Please fix the highlighted fields.');
  }
})();

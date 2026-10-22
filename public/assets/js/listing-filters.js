/**
 * Listing pages (vendors, services, consultants, teachers, schools, institutes, notes):
 * on phones and tablets the filter sidebar leaves the page flow and opens as a slide-in drawer
 * from a "Filters" bar above the results, so visitors see results first. Desktop is unchanged.
 */
(function () {
    'use strict';

    var SIDEBARS = '.vendors-page__layout > aside.vendors-sidebar, .sm-layout-3col > aside.sm-filter-sidebar';
    var mobile = window.matchMedia('(max-width: 991.98px)');
    var sidebars = document.querySelectorAll(SIDEBARS);

    if (!sidebars.length) {
        return;
    }

    var backdrop = document.createElement('div');
    backdrop.className = 'listing-filter-backdrop';
    backdrop.setAttribute('aria-hidden', 'true');
    document.body.appendChild(backdrop);

    var openDrawer = null;
    var bars = [];
    var stickyTop = 0;

    // The sticky header's height, so the Filters bar sticks just below it
    function syncStickyOffset() {
        var shell = document.getElementById('headerStickyShell');
        var height = shell && window.getComputedStyle(shell).position === 'sticky'
            ? shell.getBoundingClientRect().height
            : 0;

        stickyTop = Math.round(height);
        document.documentElement.style.setProperty('--listing-filter-top', stickyTop + 'px');
        syncDocked();
    }

    // Once the bar reaches the header it docks there full width
    function syncDocked() {
        bars.forEach(function (bar) {
            bar.classList.toggle('is-stuck', mobile.matches && bar.getBoundingClientRect().top <= stickyTop + 1);
        });
    }

    // Filters the visitor has set: chosen options, typed text and ticked boxes. Sorting, hidden
    // fields and read-only or disabled fields (like the location taken from the header) don't count.
    function activeFilterCount(sidebar) {
        var count = 0;

        sidebar.querySelectorAll('input, select').forEach(function (field) {
            if (field.disabled || field.readOnly || field.type === 'hidden' || /sort/i.test(field.id + ' ' + field.name)) {
                return;
            }

            if (field.type === 'checkbox' || field.type === 'radio') {
                count += field.checked && field.value !== '' ? 1 : 0;
            } else if (field.tagName === 'SELECT') {
                count += field.value !== '' ? 1 : 0;
            } else if (/^(text|search|number|)$/.test(field.type)) {
                count += field.value.trim() !== '' ? 1 : 0;
            }
        });

        return count;
    }

    function setup(sidebar, index) {
        var main = sidebar.parentElement.querySelector(':scope > .vendors-main, :scope > .sm-notes-main');
        if (!main) {
            return;
        }

        sidebar.id = sidebar.id || 'listingFilters' + (index + 1);
        sidebar.classList.add('listing-filter-drawer');
        sidebar.setAttribute('data-filter-drawer', '');

        var head = sidebar.querySelector(':scope > .vendors-sidebar__head, :scope > .sm-filter-sidebar__head');
        var title = head ? head.querySelector('h2') : null;
        var lead = head ? head.querySelector('.vendors-sidebar__lead') : null;
        var apply = sidebar.querySelector('[id$="ApplyFilters"], .vendors-filter-apply, button[type="submit"]');
        var reset = sidebar.querySelector('[id$="ResetFilters"], .vendors-sidebar__reset, .js-sm-notes-reset');
        var results = main.querySelector('[id$="AllSection"], #smNotesResults');
        var hint = lead ? lead.textContent.trim() : 'Narrow down the results.';

        if (title) {
            title.id = title.id || sidebar.id + 'Title';
        }

        // Bar above the results: Filters button, what is applied, and a quick clear
        var bar = document.createElement('div');
        bar.className = 'listing-filter-bar';
        bar.innerHTML =
            '<button type="button" class="listing-filter-bar__toggle" aria-haspopup="dialog" aria-expanded="false">' +
                '<i class="fa-solid fa-sliders" aria-hidden="true"></i><span>Filters</span>' +
                '<span class="listing-filter-bar__count" hidden></span>' +
            '</button>' +
            '<p class="listing-filter-bar__summary"></p>' +
            (reset ? '<button type="button" class="listing-filter-bar__clear" hidden>Clear all</button>' : '');

        bars.push(bar);

        var toggle = bar.querySelector('.listing-filter-bar__toggle');
        var countBadge = bar.querySelector('.listing-filter-bar__count');
        var summary = bar.querySelector('.listing-filter-bar__summary');
        var barClear = bar.querySelector('.listing-filter-bar__clear');
        toggle.setAttribute('aria-controls', sidebar.id);

        var hero = main.querySelector(':scope > .vendors-hero');
        if (hero) {
            hero.insertAdjacentElement('afterend', bar);
        } else {
            main.insertBefore(bar, main.firstChild);
        }

        // Close button in the drawer heading, Clear / Apply pinned to the drawer bottom
        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'listing-filter-drawer__close';
        close.setAttribute('aria-label', 'Close filters');
        close.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
        (head || sidebar).appendChild(close);

        var foot = document.createElement('div');
        foot.className = 'listing-filter-drawer__foot';
        foot.innerHTML =
            (reset ? '<button type="button" class="listing-filter-drawer__clear">Clear all</button>' : '') +
            '<button type="button" class="listing-filter-drawer__apply">Show results</button>';
        sidebar.appendChild(foot);

        if (apply) {
            apply.setAttribute('data-filter-drawer-original', '');
        }
        if (reset) {
            reset.setAttribute('data-filter-drawer-original', '');
        }

        function refresh() {
            var count = activeFilterCount(sidebar);

            countBadge.hidden = count === 0;
            countBadge.textContent = count;
            summary.textContent = count ? count + (count === 1 ? ' filter applied' : ' filters applied') : hint;
            summary.classList.toggle('is-active', count > 0);
            if (barClear) {
                barClear.hidden = count === 0;
            }
        }

        function syncMode() {
            if (mobile.matches) {
                sidebar.setAttribute('role', 'dialog');
                sidebar.setAttribute('aria-modal', 'true');
                if (title) {
                    sidebar.setAttribute('aria-labelledby', title.id);
                } else {
                    sidebar.setAttribute('aria-label', 'Filters');
                }
            } else {
                hide(false);
                ['role', 'aria-modal', 'aria-labelledby', 'aria-label'].forEach(function (name) {
                    sidebar.removeAttribute(name);
                });
            }
        }

        function show() {
            if (!mobile.matches) {
                return;
            }

            if (openDrawer && openDrawer !== hide) {
                openDrawer(false);
            }

            openDrawer = hide;
            sidebar.classList.add('is-open');
            backdrop.classList.add('is-visible');
            document.documentElement.classList.add('listing-filters-open');
            toggle.setAttribute('aria-expanded', 'true');
            sidebar.scrollTop = 0;

            window.requestAnimationFrame(function () {
                close.focus({ preventScroll: true });
            });
        }

        function hide(restoreFocus) {
            if (!sidebar.classList.contains('is-open')) {
                return;
            }

            openDrawer = null;
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-visible');
            document.documentElement.classList.remove('listing-filters-open');
            toggle.setAttribute('aria-expanded', 'false');
            refresh();

            if (restoreFocus !== false) {
                toggle.focus({ preventScroll: true });
            }
        }

        // After applying, bring the results into view if they start further down the page
        function revealResults() {
            var target = results || bar;
            var top = target.getBoundingClientRect().top;

            if (top < 0 || top > window.innerHeight * 0.6) {
                window.scrollTo({ top: window.scrollY + top - stickyTop - bar.offsetHeight - 12, behavior: 'smooth' });
            }
        }

        function clearAll() {
            if (reset) {
                reset.click();
            }
            window.setTimeout(refresh, 60);
        }

        toggle.addEventListener('click', show);
        close.addEventListener('click', function () { hide(); });
        backdrop.addEventListener('click', function () {
            if (openDrawer === hide) {
                hide();
            }
        });

        foot.querySelector('.listing-filter-drawer__apply').addEventListener('click', function () {
            if (apply) {
                apply.click();
            }
            hide();
            revealResults();
        });

        foot.querySelector('.listing-filter-drawer__clear')?.addEventListener('click', clearAll);
        barClear?.addEventListener('click', clearAll);

        // The page's own Apply button (still shown on desktop) keeps the count current too
        if (apply) {
            apply.addEventListener('click', function () {
                window.setTimeout(refresh, 0);
            });
        }

        sidebar.addEventListener('change', refresh);
        sidebar.addEventListener('input', refresh);
        sidebar.addEventListener('submit', function () {
            if (sidebar.classList.contains('is-open')) {
                hide();
            }
        });

        if (typeof mobile.addEventListener === 'function') {
            mobile.addEventListener('change', syncMode);
        } else if (typeof mobile.addListener === 'function') {
            mobile.addListener(syncMode);
        }

        syncMode();
        refresh();
    }

    sidebars.forEach(setup);
    syncStickyOffset();

    window.addEventListener('resize', syncStickyOffset);
    window.addEventListener('load', syncStickyOffset);
    window.addEventListener('scroll', syncDocked, { passive: true });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && openDrawer) {
            openDrawer();
        }
    });
})();

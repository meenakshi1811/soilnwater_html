// Single-page profiles of vendors, consultants and service providers.
(function () {
    'use strict';

    var page = document.getElementById('bizProfilePage');
    if (!page) return;

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        window.alert(message);
    }

    // The tabs and the menu stay under the site's sticky header
    function syncStickyTop() {
        var header = document.querySelector('.header-sticky-shell');
        var height = header ? Math.round(header.getBoundingClientRect().height) : 0;
        page.style.setProperty('--biz-sticky-top', height + 'px');
    }

    syncStickyTop();
    window.addEventListener('resize', syncStickyTop);

    // The section in view is marked in the menu and the tabs
    var navLinks = Array.prototype.slice.call(page.querySelectorAll('[data-biz-nav]'));
    var sections = [];
    navLinks.forEach(function (link) {
        var section = document.getElementById(link.getAttribute('href').slice(1));
        if (section && sections.indexOf(section) === -1) sections.push(section);
    });

    function setActive(id) {
        navLinks.forEach(function (link) {
            var isActive = link.getAttribute('href') === '#' + id;
            link.classList.toggle('is-active', isActive);
            if (isActive && link.classList.contains('biz-tabs__link')) {
                var track = link.parentElement;
                track.scrollTo({ left: link.offsetLeft - (track.clientWidth - link.offsetWidth) / 2, behavior: 'smooth' });
            }
        });
    }

    function sectionInView() {
        var line = (parseInt(getComputedStyle(page).getPropertyValue('--biz-sticky-top'), 10) || 0) + 120;
        var current = sections[0];
        sections.forEach(function (section) {
            if (section.getBoundingClientRect().top <= line) current = section;
        });
        // At the end of the page the last section counts as in view
        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
            current = sections[sections.length - 1];
        }
        return current;
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (ticking || !sections.length) return;
        ticking = true;
        window.requestAnimationFrame(function () {
            var current = sectionInView();
            if (current) setActive(current.id);
            ticking = false;
        });
    }, { passive: true });

    navLinks.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = document.getElementById(link.getAttribute('href').slice(1));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', '#' + target.id);
            setActive(target.id);
        });
    });

    // Services and products: category filters and "show all"
    page.querySelectorAll('[data-biz-filter-scope]').forEach(function (scope) {
        var list = scope.querySelector('[data-biz-items]');
        if (!list) return;

        var items = Array.prototype.slice.call(list.querySelectorAll('[data-biz-item]'));
        var chips = Array.prototype.slice.call(scope.querySelectorAll('[data-biz-filter]'));
        var more = scope.querySelector('[data-biz-more]');
        var baseLimit = parseInt(list.getAttribute('data-biz-limit'), 10) || 9;
        var expanded = false;
        var category = '';

        // Whole rows only (9 on three columns, 8 on two)
        function limit() {
            var columns = getComputedStyle(list).gridTemplateColumns.split(' ').filter(Boolean).length || 1;
            return Math.max(columns, Math.floor(baseLimit / columns) * columns);
        }

        function render() {
            var matching = items.filter(function (item) {
                return category === '' || item.getAttribute('data-biz-category') === category;
            });
            // A chosen category shows all its items
            var shown = expanded || category !== '' ? matching.length : limit();
            items.forEach(function (item) {
                var index = matching.indexOf(item);
                item.hidden = index === -1 || index >= shown;
            });
            if (more) more.hidden = matching.length <= shown;
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                category = chip.getAttribute('data-biz-filter');
                chips.forEach(function (other) { other.classList.toggle('is-active', other === chip); });
                render();
            });
        });

        if (more) {
            more.addEventListener('click', function () {
                expanded = true;
                render();
            });
        }

        window.addEventListener('resize', render);

        // A link to a category (the old category pages) opens with it chosen
        var requested = new URLSearchParams(window.location.search).get('category');
        var requestedChip = requested ? chips.filter(function (chip) { return chip.getAttribute('data-biz-filter') === requested; })[0] : null;
        if (requestedChip) {
            requestedChip.click();
        } else {
            render();
        }
    });

    // Long text (about, text sections, a product's description) shows four lines, then "Read more"
    function lineHeightOf(box) {
        var text = box.querySelector('p, li, div:not([class]), span') || box;
        var style = getComputedStyle(text);
        var height = parseFloat(style.lineHeight);
        return isNaN(height) ? parseFloat(style.fontSize) * 1.6 : height;
    }

    page.querySelectorAll('[data-biz-collapsible]').forEach(function (box) {
        var button = box.parentElement.querySelector('[data-biz-readmore]');
        if (!button) return;
        var foldedHeight = Math.round(lineHeightOf(box) * 4);
        if (box.scrollHeight <= foldedHeight + 8) return;

        function fold(folded) {
            box.classList.toggle('is-collapsed', folded);
            box.style.maxHeight = folded ? foldedHeight + 'px' : '';
            button.firstChild.textContent = folded ? 'Read more ' : 'Show less ';
            button.querySelector('i').className = folded ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up';
            button.setAttribute('aria-expanded', folded ? 'false' : 'true');
        }

        fold(true);
        button.hidden = false;
        button.addEventListener('click', function () {
            fold(!box.classList.contains('is-collapsed'));
        });
    });

    // Business details: the first three, then "More details"
    page.querySelectorAll('[data-biz-facts]').forEach(function (facts) {
        var button = facts.parentElement.querySelector('[data-biz-facts-more]');
        if (!button || facts.children.length <= 3) return;

        function fold(folded) {
            facts.classList.toggle('is-folded', folded);
            button.firstChild.textContent = folded ? 'More details ' : 'Show less ';
            button.querySelector('i').className = folded ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up';
            button.setAttribute('aria-expanded', folded ? 'false' : 'true');
        }

        fold(true);
        button.hidden = false;
        button.addEventListener('click', function () {
            fold(!facts.classList.contains('is-folded'));
        });
    });

    // The owner's picture sections (image grid, image cards, brochure): one row that slides on by
    // itself every few seconds and by hand (arrows, swipe); it pauses while the visitor is on it
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function initPictureRow(track) {
        if (track.children.length < 2) return;

        var wrap = document.createElement('div');
        wrap.className = 'biz-carousel';
        track.parentNode.insertBefore(wrap, track);
        wrap.appendChild(track);
        track.classList.add('biz-carousel__track');

        function step() {
            var first = track.children[0];
            var gap = parseFloat(getComputedStyle(track).columnGap) || 0;
            return first ? first.getBoundingClientRect().width + gap : track.clientWidth;
        }

        function atEnd() {
            return track.scrollLeft >= track.scrollWidth - track.clientWidth - 4;
        }

        function sync() {
            wrap.classList.toggle('can-prev', track.scrollLeft > 4);
            wrap.classList.toggle('can-next', !atEnd());
        }

        var pausedUntil = 0;
        var hovered = false;
        var visible = false;

        function pause(ms) {
            pausedUntil = Date.now() + (ms || 6000);
        }

        [['prev', -1, 'fa-chevron-left', 'Previous pictures'], ['next', 1, 'fa-chevron-right', 'Next pictures']].forEach(function (arrow) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'biz-carousel__arrow biz-carousel__arrow--' + arrow[0];
            button.setAttribute('aria-label', arrow[3]);
            button.innerHTML = '<i class="fa-solid ' + arrow[2] + '" aria-hidden="true"></i>';
            button.addEventListener('click', function () {
                pause();
                track.scrollBy({ left: arrow[1] * step(), behavior: 'smooth' });
            });
            wrap.appendChild(button);
        });

        track.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        window.addEventListener('load', sync);
        sync();

        wrap.addEventListener('mouseenter', function () { hovered = true; });
        wrap.addEventListener('mouseleave', function () { hovered = false; });
        ['touchstart', 'pointerdown', 'wheel', 'focusin'].forEach(function (type) {
            track.addEventListener(type, function () { pause(); }, { passive: true });
        });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                visible = entries[0].isIntersecting;
            }, { threshold: 0.4 }).observe(wrap);
        } else {
            visible = true;
        }

        if (reduceMotion) return;

        // On by itself; back to the first picture after the last
        window.setInterval(function () {
            if (!visible || hovered || Date.now() < pausedUntil || track.scrollWidth <= track.clientWidth + 4) return;
            if (atEnd()) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                track.scrollBy({ left: step(), behavior: 'smooth' });
            }
        }, 3200);
    }

    page.querySelectorAll('[data-biz-gallery-section] .content-body').forEach(function (body) {
        var rows = [];
        body.querySelectorAll('[data-brochure-image-list]').forEach(function (list) { rows.push(list); });
        body.querySelectorAll('[data-grid-image-slot], [data-card-image-slot]').forEach(function (img) {
            var row = img.closest('.row');
            if (row && rows.indexOf(row) === -1) rows.push(row);
        });
        rows.forEach(initPictureRow);
    });

    // Copy the profile link
    document.querySelectorAll('[data-biz-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var url = button.getAttribute('data-biz-copy');
            var done = function () {
                var label = button.textContent;
                button.textContent = 'Copied';
                window.setTimeout(function () { button.textContent = label; }, 1800);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(done).catch(function () { notify('error', 'Could not copy the link.'); });
                return;
            }
            var input = document.getElementById('bizShareUrl');
            if (input) {
                input.select();
                document.execCommand('copy');
                done();
            }
        });
    });

    // Enquiries and reports are sent without leaving the page
    function closeModals() {
        if (!window.bootstrap || !window.bootstrap.Modal) return;
        document.querySelectorAll('.modal.show').forEach(function (modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).hide();
        });
    }

    document.querySelectorAll('form[data-biz-ajax], .profile-report-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var submit = form.querySelector('button[type="submit"]');
            var label = submit ? submit.textContent : '';
            if (submit) {
                submit.disabled = true;
                submit.textContent = 'Sending...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': (form.querySelector('input[name="_token"]') || {}).value || (document.querySelector('meta[name="csrf-token"]') || {}).content || '',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok) {
                            var errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
                            throw new Error(errors || payload.message || 'Something went wrong. Please try again.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    form.reset();
                    closeModals();
                    notify('success', payload.message || 'Sent successfully.');
                })
                .catch(function (error) {
                    notify('error', error.message);
                })
                .finally(function () {
                    if (submit) {
                        submit.disabled = false;
                        submit.textContent = label;
                    }
                });
        });
    });

    // Product pictures: thumbnails, arrows and swipes; the large view opens on the picture shown
    function initGallery(gallery) {
        var main = gallery.querySelector('[data-biz-gallery-main]');
        var thumbs = Array.prototype.slice.call(gallery.querySelectorAll('[data-biz-gallery-thumb]'));
        if (!main || thumbs.length < 2) return null;

        function current() {
            var index = thumbs.findIndex(function (thumb) { return thumb.classList.contains('is-active'); });
            return index < 0 ? 0 : index;
        }

        function show(index) {
            index = (index + thumbs.length) % thumbs.length;
            main.src = thumbs[index].getAttribute('data-biz-gallery-thumb');
            thumbs.forEach(function (thumb, i) { thumb.classList.toggle('is-active', i === index); });
            var strip = thumbs[index].parentElement;
            strip.scrollTo({ left: thumbs[index].offsetLeft - (strip.clientWidth - thumbs[index].offsetWidth) / 2, behavior: 'smooth' });
        }

        thumbs.forEach(function (thumb, index) {
            thumb.addEventListener('click', function () { show(index); });
        });
        gallery.querySelectorAll('[data-biz-gallery-step]').forEach(function (button) {
            button.addEventListener('click', function () { show(current() + parseInt(button.getAttribute('data-biz-gallery-step'), 10)); });
        });

        var startX = null;
        main.addEventListener('touchstart', function (event) { startX = event.touches[0].clientX; }, { passive: true });
        main.addEventListener('touchend', function (event) {
            if (startX === null) return;
            var distance = event.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(distance) > 40) show(current() + (distance < 0 ? 1 : -1));
        });

        return { show: show, current: current };
    }

    var galleries = Array.prototype.map.call(document.querySelectorAll('[data-biz-gallery]'), initGallery);
    var galleryModal = document.getElementById('bizGalleryModal');
    if (galleryModal && galleries[0] && galleries[1]) {
        galleryModal.addEventListener('show.bs.modal', function () { galleries[1].show(galleries[0].current()); });
    }

    // Pictures in the owner's sections open larger
    var imageModal = document.getElementById('bizImageModal');
    var imageModalImg = document.getElementById('bizImageModalImg');
    if (imageModal && imageModalImg) {
        page.querySelectorAll('.content-body [data-brochure-image-slot], .content-body [data-brochure-image-list] img').forEach(function (img) {
            img.addEventListener('click', function () {
                if (!img.src || !window.bootstrap) return;
                imageModalImg.src = img.src;
                imageModalImg.alt = img.alt || '';
                window.bootstrap.Modal.getOrCreateInstance(imageModal).show();
            });
        });
    }
})();

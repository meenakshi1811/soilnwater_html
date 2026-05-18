(function () {
    var sectionIndex = document.querySelectorAll('.vendor-section-block').length;
    var container = document.getElementById('sectionsContainer');
    var template = document.getElementById('sectionTemplate');
    var slidesList = document.getElementById('bannerSlidesList');
    var thumbsWrap = document.getElementById('bannerThumbs');

    function syncEditable(target) {
        var key = target.dataset.syncTarget;
        if (!key) return;
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;
        input.value = target.innerText.replace(/\n{2,}/g, '\n').trim();
    }

    function getEditable(key) {
        return document.querySelector('[data-sync-target="' + key + '"]');
    }

    function renderBannerThumbs() {
        if (!slidesList || !thumbsWrap) return;
        thumbsWrap.innerHTML = '';
        var slides = slidesList.querySelectorAll('.vendor-banner-slide');
        slides.forEach(function (slide, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'vendor-banner-thumb-btn' + (slide.classList.contains('active') ? ' active' : '');
            btn.dataset.index = idx;
            btn.style.backgroundImage = slide.style.backgroundImage;
            btn.title = 'Banner ' + (idx + 1);
            thumbsWrap.appendChild(btn);
        });
    }

    function setActiveSlide(index) {
        if (!slidesList) return;
        var slides = slidesList.querySelectorAll('.vendor-banner-slide');
        slides.forEach(function (slide, idx) {
            slide.classList.toggle('active', idx === index);
        });
        renderBannerThumbs();
    }

    function syncEditable(target) {
        var key = target.dataset.syncTarget;
        if (!key) return;
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;
        var value = target.innerText.replace(/\n{2,}/g, '\n').trim();
        input.value = value;
    }

    document.getElementById('addSectionBtn')?.addEventListener('click', function () {
        if (!template || !container) return;
        var html = template.innerHTML.replace(/__INDEX__/g, sectionIndex++);
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        container.appendChild(wrap.firstElementChild);
    });

    document.addEventListener('input', function (e) {
        if (e.target.matches('.vendor-live-editable')) syncEditable(e.target);
    });

    document.addEventListener('change', function (e) {
        if (e.target.matches('[data-style-target][data-style-prop]')) {
            var el = getEditable(e.target.dataset.styleTarget);
            if (!el) return;
            el.style[e.target.dataset.styleProp] = e.target.value;
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.js-remove-section')) {
            var block = e.target.closest('.vendor-section-block');
            var del = block.querySelector('.section-delete-flag');
            var idInput = block.querySelector('input[name*="[id]"]');
            if (idInput && del) {
                del.value = '1';
                block.style.display = 'none';
            } else {
                block.remove();
            }
        }

        var styleBtn = e.target.closest('[data-style-toggle]');
        if (styleBtn) {
            var editEl = getEditable(styleBtn.dataset.styleTarget);
            if (!editEl) return;
            var prop = styleBtn.dataset.styleProp;
            var activeValue = styleBtn.dataset.styleToggle;
            editEl.style[prop] = editEl.style[prop] === activeValue ? '' : activeValue;
        }

        if (e.target.closest('.vendor-banner-thumb-btn')) {
            setActiveSlide(Number(e.target.closest('.vendor-banner-thumb-btn').dataset.index));
        }

        if (e.target.closest('.js-remove-slide')) {
            var btn = e.target.closest('.js-remove-slide');
            var id = btn.dataset.id;
            if (!confirm('Remove this banner slide?')) return;
            fetch('/vendor/banner-slides/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(function () {
                btn.closest('.vendor-banner-slide')?.remove();
                if (!slidesList.querySelector('.vendor-banner-slide.active')) setActiveSlide(0);
                renderBannerThumbs();
            });
        }
    });

    document.getElementById('bannerSlidesInput')?.addEventListener('change', function () {
        var status = document.getElementById('bannerUploadStatus');
        if (status) status.textContent = this.files.length ? (this.files.length + ' file(s) selected') : 'No files selected';
        if (!slidesList) return;

        slidesList.querySelectorAll('.vendor-banner-slide-temp').forEach(function (el) { el.remove(); });

        Array.from(this.files || []).forEach(function (file) {
            var item = document.createElement('div');
            item.className = 'carousel-item vendor-banner-slide vendor-banner-slide-temp';
            item.style.backgroundImage = 'url("' + URL.createObjectURL(file) + '")';
            slidesList.appendChild(item);
        });

        setActiveSlide(0);
        renderBannerThumbs();
    });

    renderBannerThumbs();
})();

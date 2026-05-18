(function () {
    var sectionIndex = document.querySelectorAll('.vendor-section-block').length;
    var container = document.getElementById('sectionsContainer');
    var template = document.getElementById('sectionTemplate');
    var slidesList = document.getElementById('bannerSlidesList');
    var thumbsWrap = document.getElementById('bannerThumbs');
    var bannerInput = document.getElementById('bannerSlidesInput');
    var pendingUploadFiles = [];

    function syncEditable(target) {
        var key = target.dataset.syncTarget;
        if (!key) return;
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;
        if (target.dataset.syncHtml === '1') {
            var wrapper = document.createElement('div');
            wrapper.innerHTML = target.innerHTML;
            if (target.getAttribute('style')) {
                wrapper.setAttribute('style', target.getAttribute('style'));
            }
            input.value = wrapper.outerHTML.trim();
        } else {
            input.value = target.innerText.replace(/\n{2,}/g, '\n').trim();
        }
    }

    function getEditables(key) {
        return document.querySelectorAll('[data-sync-target="' + key + '"]');
    }

    function getActiveSectionEditable(triggerEl) {
        var block = triggerEl.closest('.vendor-section-block');
        if (!block) return null;

        var targetSelect = block.querySelector('[data-section-target]');
        if (targetSelect && targetSelect.value === 'title') {
            return block.querySelector('[data-sync-target^="section-title-"][contenteditable="true"]');
        }

        return block.querySelector('[data-sync-html="1"][contenteditable="true"]');
    }

    function syncBannerInputFiles() {
        if (!bannerInput) return;
        var dt = new DataTransfer();
        pendingUploadFiles.forEach(function (entry) { dt.items.add(entry.file); });
        bannerInput.files = dt.files;

        var status = document.getElementById('bannerUploadStatus');
        if (status) status.textContent = pendingUploadFiles.length ? (pendingUploadFiles.length + ' file(s) selected') : 'No files selected';
    }

    function renderBannerThumbs() {
        if (!slidesList || !thumbsWrap) return;
        thumbsWrap.innerHTML = '';
        var slides = slidesList.querySelectorAll('.vendor-banner-slide');
        slides.forEach(function (slide, idx) {
            var wrap = document.createElement('div');
            wrap.className = 'position-relative d-inline-block me-2 mb-2';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'vendor-banner-thumb-btn' + (slide.classList.contains('active') ? ' active' : '');
            btn.dataset.index = idx;
            btn.style.backgroundImage = slide.style.backgroundImage;
            btn.title = 'Banner ' + (idx + 1);
            wrap.appendChild(btn);

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 py-0 px-1 js-remove-thumb';
            removeBtn.innerHTML = '&times;';
            removeBtn.dataset.index = idx;
            wrap.appendChild(removeBtn);

            thumbsWrap.appendChild(wrap);
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
            var els = getEditables(e.target.dataset.styleTarget);
            if (!els.length) return;
            els.forEach(function (el) {
                el.style[e.target.dataset.styleProp] = e.target.value;
            });
        }

        if (e.target.matches('[data-section-style]')) {
            var sectionEditable = getActiveSectionEditable(e.target);
            if (!sectionEditable) return;

            var styleProp = e.target.dataset.sectionStyle;
            if (sectionEditable.dataset.syncHtml === '1') {
                sectionEditable.style[styleProp] = e.target.value;
            } else {
                sectionEditable.style[styleProp] = e.target.value;
            }

            syncEditable(sectionEditable);
        }

        if (e.target.matches('[data-section-command="fontSize"]')) {
            var sectionEditable = getActiveSectionEditable(e.target);
            if (!sectionEditable) return;
            sectionEditable.focus();
            document.execCommand('fontSize', false, e.target.value);
            syncEditable(sectionEditable);
        }

        if (e.target.matches('.js-section-image-input')) {
            var imageInput = e.target;
            var block = imageInput.closest('.vendor-section-block');
            var previewImg = block?.querySelector('.section-live-image');
            var file = imageInput.files && imageInput.files[0];
            if (previewImg && file) {
                previewImg.src = URL.createObjectURL(file);
            }
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

        var sectionCmdBtn = e.target.closest('[data-section-command]:not(select)');
        if (sectionCmdBtn) {
            var sectionEditable = getActiveSectionEditable(sectionCmdBtn);
            if (!sectionEditable) return;
            sectionEditable.focus();
            document.execCommand(sectionCmdBtn.dataset.sectionCommand, false, null);
            syncEditable(sectionEditable);
        }

        var styleBtn = e.target.closest('[data-style-toggle]');
        if (styleBtn) {
            var editEls = getEditables(styleBtn.dataset.styleTarget);
            if (!editEls.length) return;
            var prop = styleBtn.dataset.styleProp;
            var activeValue = styleBtn.dataset.styleToggle;
            editEls.forEach(function (editEl) {
                editEl.style[prop] = editEl.style[prop] === activeValue ? '' : activeValue;
            });
        }

        if (e.target.closest('.js-remove-thumb')) {
            var thumbBtn = e.target.closest('.js-remove-thumb');
            var index = Number(thumbBtn.dataset.index);
            var slide = slidesList?.querySelectorAll('.vendor-banner-slide')[index];
            if (!slide) return;

            if (slide.dataset.id) {
                if (!confirm('Remove this banner slide?')) return;
                fetch('/vendor/banner-slides/' + slide.dataset.id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                }).then(function () {
                    slide.remove();
                    if (!slidesList.querySelector('.vendor-banner-slide.active')) setActiveSlide(0);
                    renderBannerThumbs();
                });
            } else if (slide.dataset.tempId) {
                pendingUploadFiles = pendingUploadFiles.filter(function (entry) { return entry.id !== slide.dataset.tempId; });
                URL.revokeObjectURL(slide.dataset.previewUrl || '');
                slide.remove();
                syncBannerInputFiles();
                if (!slidesList.querySelector('.vendor-banner-slide.active')) setActiveSlide(0);
                renderBannerThumbs();
            }
            return;
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

    bannerInput?.addEventListener('change', function () {
        if (!slidesList) return;

        Array.from(this.files || []).forEach(function (file) {
            var tempId = 'temp-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8);
            var previewUrl = URL.createObjectURL(file);
            pendingUploadFiles.push({ id: tempId, file: file });

            var item = document.createElement('div');
            item.className = 'carousel-item vendor-banner-slide vendor-banner-slide-temp';
            item.dataset.tempId = tempId;
            item.dataset.previewUrl = previewUrl;
            item.style.backgroundImage = 'url("' + previewUrl + '")';
            slidesList.appendChild(item);
        });

        syncBannerInputFiles();
        setActiveSlide(0);
        renderBannerThumbs();
    });

    renderBannerThumbs();

    var publicPageForm = document.getElementById('publicPageForm');
    publicPageForm?.addEventListener('submit', function (e) {
        e.preventDefault();

        document.querySelectorAll('.vendor-live-editable[data-sync-target]').forEach(function (el) {
            syncEditable(el);
        });

        var saveBtn = document.getElementById('publicPageSaveBtn');
        var oldHtml = saveBtn ? saveBtn.innerHTML : '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';
        }

        fetch(publicPageForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(publicPageForm)
        }).then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, data: data };
            });
        }).then(function (result) {
            if (result.ok) {
                if (window.toastr && typeof window.toastr.success === 'function') {
                    window.toastr.success(result.data.message || 'Saved successfully.');
                }
            } else {
                var message = result.data?.message || 'Unable to save changes.';
                if (window.toastr && typeof window.toastr.error === 'function') {
                    window.toastr.error(message);
                }
            }
        }).catch(function () {
            if (window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error('Network error while saving. Please try again.');
            }
        }).finally(function () {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = oldHtml;
            }
        });
    });

})();

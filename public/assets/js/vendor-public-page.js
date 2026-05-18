(function () {
    var sectionIndex = document.querySelectorAll('.vendor-section-block').length;
    var container = document.getElementById('sectionsContainer');
    var template = document.getElementById('sectionTemplate');
    var slidesList = document.getElementById('bannerSlidesList');
    var thumbsWrap = document.getElementById('bannerThumbs');
    var bannerInput = document.getElementById('bannerSlidesInput');
    var publicPageForm = document.getElementById('publicPageForm');
    var pendingUploadFiles = [];
    var bannerDeleteBase = publicPageForm?.dataset.bannerDeleteUrl || '/vendor/banner-slides/';

    function syncEditable(target) {
        var key = target.dataset.syncTarget;
        if (!key) return;
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;
        var isSectionTitle = key.indexOf('section-title-') === 0;
        if (target.dataset.syncHtml === '1' || isSectionTitle) {
            input.value = target.innerHTML.trim();
        } else {
            input.value = target.innerText.replace(/\n{2,}/g, '\n').trim();
        }
    }

    function getEditables(key) {
        return document.querySelectorAll('[data-sync-target="' + key + '"]');
    }

    function syncStyleInput(styleTarget, styleProp, styleValue) {
        var input = document.querySelector('[data-style-input="' + styleTarget + '"][data-style-prop="' + styleProp + '"]');
        if (input) input.value = styleValue || '';
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

    function selectAllContents(editable) {
        editable.focus();
        var range = document.createRange();
        range.selectNodeContents(editable);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        return selection;
    }

    function applySectionStyle(editable, prop, value) {
        if (!editable) return;

        var selection = selectAllContents(editable);
        document.execCommand('styleWithCSS', false, true);

        if (prop === 'color') {
            document.execCommand('foreColor', false, value);
        } else if (prop === 'backgroundColor') {
            if (!document.execCommand('hiliteColor', false, value)) {
                document.execCommand('backColor', false, value);
            }
        }

        selection.removeAllRanges();
        syncEditable(editable);
    }

    function initHeroStyleControls() {
        document.querySelectorAll('[data-style-target][data-style-prop]').forEach(function (control) {
            var target = control.dataset.styleTarget;
            var prop = control.dataset.styleProp;
            var hidden = document.querySelector('[data-style-input="' + target + '"][data-style-prop="' + prop + '"]');
            if (!hidden || !hidden.value) return;

            if (control.tagName === 'SELECT' || control.type === 'color') {
                control.value = hidden.value;
            }
        });

        document.querySelectorAll('[data-style-toggle]').forEach(function (btn) {
            var editEl = document.querySelector('[data-sync-target="' + btn.dataset.styleTarget + '"]');
            if (!editEl) return;
            var isActive = editEl.style[btn.dataset.styleProp] === btn.dataset.styleToggle;
            btn.classList.toggle('active', isActive);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function syncBannerInputFiles() {
        if (!bannerInput) return;
        var dt = new DataTransfer();
        pendingUploadFiles.forEach(function (entry) { dt.items.add(entry.file); });
        bannerInput.files = dt.files;

        var status = document.getElementById('bannerUploadStatus');
        if (status) status.textContent = pendingUploadFiles.length ? (pendingUploadFiles.length + ' file(s) ready to upload') : 'No new files selected';
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

    function getVisibleSectionBlocks() {
        if (!container) return [];
        return Array.from(container.querySelectorAll('.vendor-section-block')).filter(function (block) {
            return block.style.display !== 'none' && block.querySelector('.section-delete-flag')?.value !== '1';
        });
    }

    function updateSectionIds(sections) {
        if (!sections || !sections.length) return;

        var blocks = getVisibleSectionBlocks();
        blocks.forEach(function (block, idx) {
            var data = sections[idx];
            if (!data) return;

            var idInput = block.querySelector('input[name*="[id]"]');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                var index = block.dataset.sectionIndex;
                idInput.name = 'sections[' + index + '][id]';
                var anchor = block.querySelector('.section-delete-flag');
                if (anchor) {
                    anchor.insertAdjacentElement('afterend', idInput);
                } else {
                    block.prepend(idInput);
                }
            }
            idInput.value = data.id;

            if (data.image_url) {
                var previewImg = block.querySelector('.section-live-image');
                if (previewImg) previewImg.src = data.image_url;
            }
        });
    }

    function refreshBannerSlides(slides) {
        if (!slidesList || !slides || !slides.length) return;

        slidesList.innerHTML = '';
        slides.forEach(function (slide, idx) {
            var item = document.createElement('div');
            item.className = 'carousel-item vendor-banner-slide' + (idx === 0 ? ' active' : '');
            item.dataset.id = slide.id;
            item.style.backgroundImage = 'url("' + slide.image_url + '")';
            slidesList.appendChild(item);
        });

        pendingUploadFiles = [];
        syncBannerInputFiles();
        renderBannerThumbs();
    }

    function updateLogoPreview(logoUrl) {
        var wrap = document.getElementById('logoPreviewWrap');
        var nameEl = document.getElementById('storeNamePreview');
        if (!wrap) return;

        if (logoUrl) {
            wrap.innerHTML =
                '<img src="' + logoUrl + '" alt="Store logo" class="vendor-logo-dropzone-img" id="logoPreviewImg">' +
                '<span class="vendor-logo-dropzone-hint"><i class="fa-solid fa-camera"></i> Change logo</span>';
            if (nameEl) nameEl.classList.add('d-none');
        } else {
            wrap.innerHTML =
                '<span class="vendor-logo-dropzone-placeholder" id="logoPlaceholder">' +
                '<i class="fa-solid fa-store"></i><span>Add logo</span></span>' +
                '<span class="vendor-logo-dropzone-hint"><i class="fa-solid fa-camera"></i> Upload</span>';
            if (nameEl) nameEl.classList.remove('d-none');
        }
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
                syncStyleInput(e.target.dataset.styleTarget, e.target.dataset.styleProp, e.target.value);
            });
        }

        if (e.target.matches('[data-section-style]')) {
            var sectionEditable = getActiveSectionEditable(e.target);
            if (!sectionEditable) return;
            applySectionStyle(sectionEditable, e.target.dataset.sectionStyle, e.target.value);
        }

        if (e.target.matches('[data-section-command="fontSize"]')) {
            var sectionEditable = getActiveSectionEditable(e.target);
            if (!sectionEditable) return;
            selectAllContents(sectionEditable);
            document.execCommand('fontSize', false, e.target.value);
            window.getSelection().removeAllRanges();
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

        if (e.target.id === 'logoInput') {
            var file = e.target.files && e.target.files[0];
            if (!file) return;
            var previewUrl = URL.createObjectURL(file);
            var wrap = document.getElementById('logoPreviewWrap');
            var nameEl = document.getElementById('storeNamePreview');
            if (wrap) {
                wrap.innerHTML =
                    '<img src="' + previewUrl + '" alt="Logo preview" class="vendor-logo-dropzone-img" id="logoPreviewImg">' +
                    '<span class="vendor-logo-dropzone-hint"><i class="fa-solid fa-camera"></i> Change logo</span>';
            }
            if (nameEl) nameEl.classList.add('d-none');
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
                var nextValue = editEl.style[prop] === activeValue ? '' : activeValue;
                editEl.style[prop] = nextValue;
                syncStyleInput(styleBtn.dataset.styleTarget, prop, nextValue);
            });
            styleBtn.classList.toggle('active', editEls[0].style[prop] === activeValue);
            styleBtn.setAttribute('aria-pressed', editEls[0].style[prop] === activeValue ? 'true' : 'false');
        }

        if (e.target.closest('.js-remove-thumb')) {
            var thumbBtn = e.target.closest('.js-remove-thumb');
            var index = Number(thumbBtn.dataset.index);
            var slide = slidesList?.querySelectorAll('.vendor-banner-slide')[index];
            if (!slide) return;

            if (slide.dataset.id) {
                if (!confirm('Remove this banner slide?')) return;
                fetch(bannerDeleteBase + slide.dataset.id, {
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
            fetch(bannerDeleteBase + id, {
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
    initHeroStyleControls();

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
                if (result.data.sections) {
                    updateSectionIds(result.data.sections);
                }
                if (result.data.banner_slides) {
                    refreshBannerSlides(result.data.banner_slides);
                }
                if (Object.prototype.hasOwnProperty.call(result.data, 'logo_url')) {
                    updateLogoPreview(result.data.logo_url);
                }
                if (window.toastr && typeof window.toastr.success === 'function') {
                    window.toastr.success(result.data.message || 'Saved successfully. Open Live Preview to see your store.');
                }
            } else {
                var message = result.data?.message || 'Unable to save changes.';
                if (result.data?.errors) {
                    var firstError = Object.values(result.data.errors)[0];
                    if (Array.isArray(firstError) && firstError[0]) {
                        message = firstError[0];
                    }
                }
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

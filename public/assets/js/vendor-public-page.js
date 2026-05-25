(function () {
    var sectionIndex = document.querySelectorAll('.vendor-section-block').length;
    var container = document.getElementById('sectionsContainer');
    var template = document.getElementById('sectionTemplate');
    var sectionTypeSelect = document.getElementById('sectionTypeSelect');
    var slidesList = document.getElementById('bannerSlidesList');
    var thumbsWrap = document.getElementById('bannerThumbs');
    var bannerInput = document.getElementById('bannerSlidesInput');
    var publicPageForm = document.getElementById('publicPageForm');
    var pendingUploadFiles = [];
    var bannerDeleteBase = publicPageForm?.dataset.bannerDeleteUrl || '/vendor/banner-slides/';
    var activeSectionEditable = null;
    var activeHeroEditable = null;

    
    function buildSectionPreset(type, index) {
        var title = 'Section title';
        var content = '<p>Write your section content here...</p>';

        if (type === 'image_grid') {
            title = 'Image Grid Section';
            content = '<div class="row g-2">' +
                Array.from({ length: 8 }).map(function (_, i) {
                    return '<div class="col-6 col-md-3"><div class="border rounded p-2 text-center text-muted small">Image ' + (i + 1) + '</div></div>';
                }).join('') +
                '</div>';
        } else if (type === 'text_only') {
            title = 'Text Section';
            content = '<h4>Section heading</h4><p>Add your text content here.</p>';
        } else if (type === 'brochure') {
            title = 'Brochure Section';
            content = '<ul><li>Brochure item 1</li><li>Brochure item 2</li></ul><p>Add brochure links or descriptions here.</p>';
        } else if (type === 'image_text') {
            title = 'Image + Text Cards';
            content = '<div class="row g-3">' +
                Array.from({ length: 4 }).map(function (_, i) {
                    return '' +
                        '<div class="col-12 col-md-6 col-lg-3">' +
                        '<div class="card h-100">' +
                        '<img src="https://via.placeholder.com/600x360/e8ecef/6b7280?text=Card+' + (i + 1) + '" class="card-img-top" alt="Card image ' + (i + 1) + '" data-card-image-slot="' + (i + 1) + '">' +
                        '<div class="card-body">' +
                        '<h6 class="card-title">Card title ' + (i + 1) + '</h6>' +
                        '<p class="card-text">Add short description for this card.</p>' +
                        '</div></div></div>';
                }).join('') +
                '</div>';
        }

        return { title: title, content: content };
    }

    function showToast(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        if (window.jQuery && window.jQuery.toastr && typeof window.jQuery.toastr[type] === 'function') {
            window.jQuery.toastr[type](message);
            return;
        }
        alert(message);
    }

    function parseJsonResponse(res) {
        return res.text().then(function (text) {
            if (!text) return {};
            try {
                return JSON.parse(text);
            } catch (e) {
                return { message: res.ok ? 'Saved.' : 'Server returned an unexpected response.' };
            }
        });
    }

    function syncEditable(target) {
        var key = target.dataset.syncTarget;
        if (!key) return;
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;

        if (target.dataset.sectionField === 'title' || target.dataset.sectionField === 'content') {
            input.value = serializeSectionField(target);
            return;
        }

        var isSectionTitle = key.indexOf('section-title-') === 0;
        if (target.dataset.syncHtml === '1' || isSectionTitle) {
            input.value = target.innerHTML.trim();
        } else {
            input.value = target.innerText.replace(/\n{2,}/g, '\n').trim();
        }
    }

    function serializeSectionField(editable) {
        var style = editable.getAttribute('style') || '';
        var inner = editable.innerHTML.trim();
        var blockClass = editable.dataset.sectionField === 'title'
            ? 'vendor-section-title-block'
            : 'vendor-section-content-block';

        if (style.trim()) {
            return '<div class="' + blockClass + '" style="' + style.replace(/"/g, '&quot;') + '">' + inner + '</div>';
        }

        return inner;
    }

    function hydrateSectionField(editable) {
        var input = document.querySelector('[data-sync-input="' + editable.dataset.syncTarget + '"]');
        if (!input || !input.value) return;

        var wrap = document.createElement('div');
        wrap.innerHTML = input.value.trim();
        var block = wrap.querySelector('.vendor-section-title-block, .vendor-section-content-block');
        if (!block) return;

        var style = block.getAttribute('style');
        if (style) editable.setAttribute('style', style);
        editable.innerHTML = block.innerHTML;
    }

    function getEditables(key) {
        return document.querySelectorAll('[data-sync-target="' + key + '"]');
    }

    function syncStyleInput(styleTarget, styleProp, styleValue) {
        var input = document.querySelector('[data-style-input="' + styleTarget + '"][data-style-prop="' + styleProp + '"]');
        if (input) input.value = styleValue || '';
    }

    function setActiveSectionEditable(editable) {
        if (!editable || !editable.dataset.sectionField) return;

        if (activeSectionEditable && activeSectionEditable !== editable) {
            activeSectionEditable.classList.remove('vendor-section-editable-active');
        }

        activeSectionEditable = editable;
        editable.classList.add('vendor-section-editable-active');

        var block = editable.closest('.vendor-section-block');
        var label = block?.querySelector('[data-section-active-label]');
        if (label) {
            label.textContent = editable.dataset.sectionField === 'title' ? 'Styling: Section title' : 'Styling: Section content';
        }
    }

    function getActiveSectionEditable(triggerEl) {
        var block = triggerEl.closest('.vendor-section-block');
        if (!block) return null;

        if (activeSectionEditable && block.contains(activeSectionEditable)) {
            return activeSectionEditable;
        }

        return block.querySelector('[data-section-field="content"][contenteditable="true"]')
            || block.querySelector('[data-section-field][contenteditable="true"]');
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

    function hasTextSelection(editable) {
        var sel = window.getSelection();
        if (!sel || sel.rangeCount === 0 || sel.isCollapsed) return false;
        var range = sel.getRangeAt(0);
        return editable.contains(range.commonAncestorContainer);
    }

    function applyBoxStyle(editable, prop, value) {
        if (prop === 'color') {
            editable.style.color = value;
        } else if (prop === 'backgroundColor') {
            editable.style.backgroundColor = value;
            if (value && value !== '#ffffff' && value !== '#fff') {
                editable.style.padding = editable.dataset.sectionField === 'title' ? '0.65rem 1rem' : '1rem';
                editable.style.borderRadius = '8px';
            }
        }
        syncEditable(editable);
    }

    function applySectionStyle(editable, prop, value) {
        if (!editable) return;

        if (editable.dataset.sectionField === 'title') {
            applyBoxStyle(editable, prop, value);
            return;
        }

        if (prop === 'backgroundColor') {
            applyBoxStyle(editable, prop, value);
            return;
        }

        if (prop === 'color' && hasTextSelection(editable)) {
            var selection = window.getSelection();
            document.execCommand('styleWithCSS', false, true);
            document.execCommand('foreColor', false, value);
            selection.removeAllRanges();
            syncEditable(editable);
            return;
        }

        if (prop === 'color') {
            applyBoxStyle(editable, prop, value);
        }
    }

    function applySectionFontSize(editable, value) {
        if (!editable) return;

        if (!value) {
            editable.style.fontSize = '';
            syncEditable(editable);
            return;
        }

        if (editable.dataset.sectionField === 'title' || !hasTextSelection(editable)) {
            editable.style.fontSize = value;
            syncEditable(editable);
            return;
        }

        selectAllContents(editable);
        document.execCommand('styleWithCSS', false, true);
        document.execCommand('fontSize', false, '7');
        editable.querySelectorAll('font[size="7"]').forEach(function (fontEl) {
            fontEl.removeAttribute('size');
            fontEl.style.fontSize = value;
        });
        window.getSelection().removeAllRanges();
        syncEditable(editable);
    }

    function applySectionBold(editable) {
        if (!editable) return;

        if (editable.dataset.sectionField === 'title' || !hasTextSelection(editable)) {
            var isBold = editable.style.fontWeight === '700' || editable.style.fontWeight === 'bold';
            editable.style.fontWeight = isBold ? '' : '700';
            syncEditable(editable);
            return;
        }

        editable.focus();
        document.execCommand('bold', false, null);
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

    
    function setActiveHeroEditable(editable) {
        if (!editable || !editable.dataset.heroEditable) return;

        document.querySelectorAll('[data-hero-editable]').forEach(function (el) {
            el.classList.remove('vendor-section-editable-active');
        });

        activeHeroEditable = editable;
        editable.classList.add('vendor-section-editable-active');

        var label = document.querySelector('[data-hero-active-label]');
        if (label) {
            label.textContent = editable.dataset.heroEditable === 'main' ? 'Styling: Main heading' : 'Styling: Subheading';
        }

        var colorControl = document.querySelector('[data-hero-style="color"]');
        var sizeControl = document.querySelector('[data-hero-style="fontSize"]');
        var boldBtn = document.querySelector('[data-hero-toggle="fontWeight"]');

        if (colorControl) colorControl.value = editable.style.color || '#1f2937';
        if (sizeControl) sizeControl.value = editable.style.fontSize || '';
        if (boldBtn) {
            var isBold = editable.style.fontWeight === '700' || editable.style.fontWeight === 'bold';
            boldBtn.classList.toggle('active', isBold);
            boldBtn.setAttribute('aria-pressed', isBold ? 'true' : 'false');
        }
    }

    function initSectionFields() {
        document.querySelectorAll('[data-section-field][contenteditable="true"]').forEach(hydrateSectionField);
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
        document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
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
        document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

    renderBannerThumbs();
    }


    
    function applySectionTypeLayout(block, sectionType) {
        if (!block) return;
        var imageCol = block.querySelector('.js-section-image-col');
        var textCol = block.querySelector('.vendor-section-title-editor')?.closest('.col-lg-7, .col-lg-12');
        var typeInput = block.querySelector('[data-section-type-input]');

        if (typeInput) typeInput.value = sectionType || 'image_text';

        var showImage = sectionType === 'image_grid';
        if (imageCol) {
            imageCol.style.display = showImage ? '' : 'none';
        }
        if (textCol) {
            textCol.classList.remove('col-lg-7', 'col-lg-12');
            textCol.classList.add(showImage ? 'col-lg-7' : 'col-lg-12');
        }

        renderCardImageUploadTools(block);
    }

    
    function renderCardImageUploadTools(block) {
        if (!block) return;
        var toolsWrap = block.querySelector('.js-card-image-tools');
        var sectionType = block.querySelector('[data-section-type-input]')?.value || 'image_text';

        if (sectionType !== 'image_text') {
            if (toolsWrap) toolsWrap.remove();
            return;
        }

        if (!toolsWrap) {
            toolsWrap = document.createElement('div');
            toolsWrap.className = 'js-card-image-tools mt-3 border rounded p-3 bg-light';
            toolsWrap.innerHTML = '<p class="small fw-semibold mb-2">Card images</p><div class="row g-2">' +
                [1,2,3,4].map(function (i) {
                    return '<div class="col-md-3 col-6">' +
                        '<label class="btn btn-sm btn-outline-secondary w-100 mb-1">Upload card ' + i +
                        '<input type="file" accept="image/*" class="d-none js-card-image-input" data-card-image-index="' + i + '"></label>' +
                        '<small class="text-muted d-block">Change image</small>' +
                    '</div>';
                }).join('') + '</div>';
            var stylePanel = block.querySelector('.vendor-section-style-panel');
            if (stylePanel) {
                stylePanel.insertAdjacentElement('beforebegin', toolsWrap);
            }
        }
    }

    function updateCardImageInSection(block, cardIndex, file) {
        if (!block || !cardIndex || !file) return;
        var contentEditable = block.querySelector('[data-section-field="content"]');
        if (!contentEditable) return;

        var reader = new FileReader();
        reader.onload = function (ev) {
            var img = contentEditable.querySelector('[data-card-image-slot="' + cardIndex + '"]');
            if (img) {
                img.src = ev.target.result;
                syncEditable(contentEditable);
            }
        };
        reader.readAsDataURL(file);
    }

    function syncSocialLinksPreview() {
        var linksVisible = 0;

        ['facebook', 'instagram'].forEach(function (platform) {
            var uiInput = document.querySelector('[data-social-input="' + platform + '"]');
            var hiddenInput = document.querySelector('input[name="' + platform + '_url"]');
            var previewLink = document.querySelector('[data-social-preview="' + platform + '"]');
            var value = (uiInput?.value || '').trim();

            if (hiddenInput) {
                hiddenInput.value = value;
            }

            if (!previewLink) return;

            if (value) {
                previewLink.href = value;
                previewLink.classList.remove('d-none');
                linksVisible += 1;
            } else {
                previewLink.href = '#';
                previewLink.classList.add('d-none');
            }
        });

        var emptyState = document.querySelector('[data-social-empty]');
        if (emptyState) {
            emptyState.classList.toggle('d-none', linksVisible > 0);
        }
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
        var idx = sectionIndex++;
        var html = template.innerHTML.replace(/__INDEX__/g, idx);
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        var block = wrap.firstElementChild;

        var sectionType = sectionTypeSelect?.value || 'image_text';
        var preset = buildSectionPreset(sectionType, idx);
        var titleEditable = block.querySelector('[data-section-field="title"]');
        var contentEditable = block.querySelector('[data-section-field="content"]');
        var titleInput = block.querySelector('[data-sync-input="section-title-' + idx + '"]');
        var contentInput = block.querySelector('[data-sync-input="section-content-' + idx + '"]');

        if (titleEditable) titleEditable.innerHTML = preset.title;
        if (contentEditable) contentEditable.innerHTML = preset.content;
        if (titleInput) titleInput.value = preset.title;
        if (contentInput) contentInput.value = preset.content;

        applySectionTypeLayout(block, sectionType);

        var badge = block.querySelector('.badge');
        if (badge) {
            var typeLabel = sectionType.replace('_', ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            badge.innerHTML = '<i class="fa-solid fa-layer-group me-1"></i> ' + typeLabel;
        }

        container.appendChild(block);
        block.querySelectorAll('[data-section-field][contenteditable="true"]').forEach(hydrateSectionField);
    });

    document.addEventListener('focusin', function (e) {
        if (e.target.matches('[data-section-field][contenteditable="true"]')) {
            setActiveSectionEditable(e.target);
        }
        if (e.target.matches('[data-hero-editable][contenteditable="true"]')) {
            setActiveHeroEditable(e.target);
        }
    });

    document.addEventListener('input', function (e) {
        if (e.target.matches('.vendor-live-editable')) syncEditable(e.target);
        if (e.target.matches('[data-social-input]')) syncSocialLinksPreview();
    });

    document.addEventListener('change', function (e) {
        if (e.target.matches('[data-hero-style]')) {
            if (!activeHeroEditable) {
                showToast('warning', 'Click heading or subheading first.');
                return;
            }
            var prop = e.target.dataset.heroStyle;
            activeHeroEditable.style[prop] = e.target.value;
            var syncTarget = activeHeroEditable.dataset.syncTarget;
            syncStyleInput(syncTarget, prop, e.target.value);
            syncEditable(activeHeroEditable);
            return;
        }

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
            if (!sectionEditable) {
                showToast('warning', 'Click the section title or content box first, then pick a color.');
                return;
            }
            applySectionStyle(sectionEditable, e.target.dataset.sectionStyle, e.target.value);
        }

        if (e.target.matches('[data-section-command="fontSize"]')) {
            var sectionEditable = getActiveSectionEditable(e.target);
            if (!sectionEditable) {
                showToast('warning', 'Click the section title or content box first, then choose font size.');
                return;
            }
            applySectionFontSize(sectionEditable, e.target.value);
        }

        if (e.target.matches('.js-card-image-input')) {
            var file = e.target.files && e.target.files[0];
            var block = e.target.closest('.vendor-section-block');
            var idx = e.target.dataset.cardImageIndex;
            if (file) updateCardImageInSection(block, idx, file);
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
            if (!sectionEditable) {
                showToast('warning', 'Click the section title or content box first.');
                return;
            }

            if (sectionCmdBtn.dataset.sectionCommand === 'bold') {
                applySectionBold(sectionEditable);
                return;
            }

            sectionEditable.focus();
            document.execCommand(sectionCmdBtn.dataset.sectionCommand, false, null);
            syncEditable(sectionEditable);
        }

        var heroToggleBtn = e.target.closest('[data-hero-toggle]');
        if (heroToggleBtn) {
            if (!activeHeroEditable) {
                showToast('warning', 'Click heading or subheading first.');
                return;
            }
            var heroProp = heroToggleBtn.dataset.heroToggle;
            var heroActiveValue = heroToggleBtn.dataset.heroToggleValue;
            var next = activeHeroEditable.style[heroProp] === heroActiveValue ? '' : heroActiveValue;
            activeHeroEditable.style[heroProp] = next;
            syncStyleInput(activeHeroEditable.dataset.syncTarget, heroProp, next);
            syncEditable(activeHeroEditable);
            heroToggleBtn.classList.toggle('active', next === heroActiveValue);
            heroToggleBtn.setAttribute('aria-pressed', next === heroActiveValue ? 'true' : 'false');
            return;
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
                    document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

    renderBannerThumbs();
                });
            } else if (slide.dataset.tempId) {
                pendingUploadFiles = pendingUploadFiles.filter(function (entry) { return entry.id !== slide.dataset.tempId; });
                URL.revokeObjectURL(slide.dataset.previewUrl || '');
                slide.remove();
                syncBannerInputFiles();
                if (!slidesList.querySelector('.vendor-banner-slide.active')) setActiveSlide(0);
                document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

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
                document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

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
        document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

    renderBannerThumbs();
    });

    document.querySelectorAll('.vendor-section-block').forEach(function (block) {
        var type = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        applySectionTypeLayout(block, type);
    });

    renderBannerThumbs();
    initHeroStyleControls();
    setActiveHeroEditable(document.querySelector('[data-hero-editable="main"]'));
    initSectionFields();
    syncSocialLinksPreview();

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
        })
            .then(function (res) {
                return parseJsonResponse(res).then(function (data) {
                    return { ok: res.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.ok) {
                    if (result.data.sections) updateSectionIds(result.data.sections);
                    if (result.data.banner_slides) refreshBannerSlides(result.data.banner_slides);
                    if (Object.prototype.hasOwnProperty.call(result.data, 'logo_url')) {
                        updateLogoPreview(result.data.logo_url);
                    }
                    showToast('success', result.data.message || 'Saved successfully. Open Live Preview to see your store.');
                } else {
                    var message = result.data?.message || 'Unable to save changes.';
                    if (result.data?.errors) {
                        var firstError = Object.values(result.data.errors)[0];
                        if (Array.isArray(firstError) && firstError[0]) message = firstError[0];
                    }
                    showToast('error', message);
                }
            })
            .catch(function () {
                showToast('error', 'Network error while saving. Please try again.');
            })
            .finally(function () {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = oldHtml;
                }
            });
    });

})();

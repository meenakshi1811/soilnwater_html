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
    var IMAGE_TEXT_CARD_COUNT = 6;

    function imageTextCardPlaceholder(label) {
        return 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="320" viewBox="0 0 600 320"><rect width="600" height="320" fill="%23e8ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%236b7280" font-family="Arial,sans-serif" font-size="28">' + label + '</text></svg>');
    }

    function buildImageTextCardHtml(cardNumber) {
        return '' +
            '<div class="col-12 col-md-6 col-lg-2">' +
            '<div class="card h-100">' +
            '<img src="' + imageTextCardPlaceholder('Card Image ' + cardNumber) + '" class="card-img-top" alt="Card image ' + cardNumber + '" data-card-image-slot="' + cardNumber + '" style="height:180px;object-fit:cover;">' +
            '<div class="card-body">' +
            '<h6 class="card-title">Card title ' + cardNumber + '</h6>' +
            '<p class="card-text">Add short description for this card.</p>' +
            '</div></div></div>';
    }

    
    function buildSectionPreset(type, index) {
        var title = 'Section title';
        var content = '<p>Write your section content here...</p>';

        if (type === 'image_grid') {
            title = 'Image Grid Section';
            content = '<div class="row g-3">' +
                Array.from({ length: 8 }).map(function (_, i) {
                    return '<div class="col-6 col-md-3">' +
                        '<div class="card h-100">' +
                        '<img src="data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="600" height="320" viewBox="0 0 600 320"><rect width="600" height="320" fill="%23e8ecef"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="%236b7280" font-family="Arial,sans-serif" font-size="28">Grid Image ' + (i + 1) + '</text></svg>') + '" class="card-img-top" alt="Grid image ' + (i + 1) + '" data-grid-image-slot="' + (i + 1) + '" style="height:280px;object-fit:cover;">' +
                        '</div></div>';
                }).join('') +
                '</div>';
        } else if (type === 'text_only') {
            title = 'Text Section';
            content = '<h4>Section heading</h4><p>Add your text content here.</p>';
        } else if (type === 'brochure') {
            title = 'Brochure Section';
            content = '<div class="card border-0 shadow-sm p-3" data-brochure-wrap="1">' +
                '<div class="row g-3 align-items-start">' +
                '<div class="col-12 d-flex flex-wrap gap-2 align-items-start" data-brochure-image-list data-brochure-pdf-list></div>' +
                '</div></div>';
        } else if (type === 'video') {
            title = 'Video Section';
            content = '<p>Add a short intro for your video. Upload a file or add a YouTube embed link from the section controls above.</p>';
        } else if (type === 'image_text') {
            title = 'Image + Text Cards';
            content = '<div class="row g-3">' +
                Array.from({ length: IMAGE_TEXT_CARD_COUNT }).map(function (_, i) {
                    return buildImageTextCardHtml(i + 1);
                }).join('') +
                '</div>';
        }

        return { title: title, content: content };
    }

    function cleanupDanglingDivText(editable) {
        if (!editable) return;
        Array.from(editable.childNodes || []).forEach(function (node) {
            if (node.nodeType !== Node.TEXT_NODE) return;
            var value = (node.textContent || '').trim().toLowerCase();
            if (value === 'div>' || value === '/div>') {
                node.remove();
            }
        });
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
        if (target.dataset.sectionField === 'content') {
            cleanupDanglingDivText(target);
        }
        var input = document.querySelector('[data-sync-input="' + key + '"]');
        if (!input) return;

        if (target.dataset.sectionField === 'title' || target.dataset.sectionField === 'content') {
            input.value = serializeSectionField(target);
            return;
        }

        var isSectionTitle = key.indexOf('section-title-') === 0;
        if (target.dataset.syncHtml === '1' || isSectionTitle) {
            input.value = normalizeLineBreakHtml(target.innerHTML);
        } else {
            input.value = target.innerText.replace(/\n{2,}/g, '\n').trim();
        }
    }

    function normalizeLineBreakHtml(html) {
        if (!html) return '';

        var normalized = html
            .replace(/\r\n|\r|\n/g, '<br>')
            .replace(/<div><br><\/div>/gi, '<br>')
            .replace(/<div>/gi, '<br>')
            .replace(/<\/div>/gi, '')
            .replace(/<p>/gi, '<br>')
            .replace(/<\/p>/gi, '')
            .replace(/(?:<br\s*\/?>\s*){2,}/gi, '<br>')
            .replace(/^(?:<br\s*\/?>)+/i, '')
            .trim();

        return normalized;
    }

    function insertBreakAtCaret(editable) {
        if (!editable) return;

        editable.focus();
        var selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) return;

        var range = selection.getRangeAt(0);
        range.deleteContents();

        var br = document.createElement('br');
        range.insertNode(br);
        range.setStartAfter(br);
        range.setEndAfter(br);
        selection.removeAllRanges();
        selection.addRange(range);
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
        cleanupDanglingDivText(editable);
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

    function updateGridImageInSection(block, imageIndex, file) {
        if (!block || !imageIndex || !file) return;
        var contentEditable = block.querySelector('[data-section-field="content"]');
        if (!contentEditable) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            var img = contentEditable.querySelector('[data-grid-image-slot="' + imageIndex + '"]');
            if (img) {
                img.src = ev.target.result;
                img.style.height = '280px';
                img.style.objectFit = 'cover';
                syncEditable(contentEditable);
            }
        };
        reader.readAsDataURL(file);
    }

    
    function applySectionTypeLayout(block, sectionType) {
        if (!block) return;
        var imageCol = block.querySelector('.js-section-image-col');
        var textCol = block.querySelector('.vendor-section-title-editor')?.closest('.col-lg-7, .col-lg-12');
        var typeInput = block.querySelector('[data-section-type-input]');
        var videoFields = block.querySelector('.js-video-fields');

        if (typeInput) typeInput.value = sectionType || 'image_text';

        var showImage = false;
        if (imageCol) {
            imageCol.style.display = showImage ? '' : 'none';
        }
        if (textCol) {
            textCol.classList.remove('col-lg-7', 'col-lg-12');
            textCol.classList.add(showImage ? 'col-lg-7' : 'col-lg-12');
        }

        if (videoFields) {
            var showVideoFields = (sectionType === 'video');
            videoFields.classList.toggle('d-none', !showVideoFields);
        }

        renderCardImageUploadTools(block);
        renderGridImageUploadTools(block);
        renderBrochureUploadTools(block);
    }

    function detectSectionTypeFromContent(block) {
        if (!block) return 'image_text';
        var contentEditable = block.querySelector('[data-section-field="content"]');
        if (!contentEditable) return 'image_text';

        if (
            contentEditable.querySelector('[data-brochure-wrap]') ||
            contentEditable.querySelector('[data-brochure-pdf-slot]') ||
            contentEditable.querySelector('[data-brochure-image-slot]')
        ) return 'brochure';

        if (contentEditable.querySelector('[data-grid-image-slot]')) return 'image_grid';
        if (contentEditable.querySelector('[data-card-image-slot]')) return 'image_text';
        var imageCount = contentEditable.querySelectorAll('img').length;
        if (imageCount >= 8) return 'image_grid';
        if (imageCount === 0) return 'text_only';

        return 'image_text';
    }

    
    function ensureImageTextCards(contentEditable) {
        if (!contentEditable) return false;

        var changed = false;
        var cards = Array.from(contentEditable.querySelectorAll('[data-card-image-slot]'));
        cards.forEach(function (img, index) {
            var slot = index + 1;
            img.setAttribute('data-card-image-slot', String(slot));
            img.setAttribute('alt', img.getAttribute('alt') || ('Card image ' + slot));

            var cardCol = img.closest('[class*="col-"]');
            if (cardCol) {
                cardCol.classList.remove('col-lg-3', 'col-lg-4');
                cardCol.classList.add('col-lg-2');
                changed = true;
            }
        });

        var firstCardImage = contentEditable.querySelector('[data-card-image-slot]');
        var row = firstCardImage?.closest('.row') || contentEditable.querySelector('.row');
        if (!row) {
            row = document.createElement('div');
            row.className = 'row g-3';
            contentEditable.appendChild(row);
            changed = true;
        }

        cards = Array.from(contentEditable.querySelectorAll('[data-card-image-slot]'));
        for (var i = cards.length + 1; i <= IMAGE_TEXT_CARD_COUNT; i++) {
            row.insertAdjacentHTML('beforeend', buildImageTextCardHtml(i));
            changed = true;
        }

        if (changed) syncEditable(contentEditable);
        return changed;
    }

    function renderCardImageUploadTools(block) {
        if (!block) return;
        var toolsWrap = block.querySelector('.js-card-image-tools');
        var sectionType = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        var contentEditable = block.querySelector('[data-section-field="content"]');

        if (sectionType !== 'image_text') {
            if (toolsWrap) toolsWrap.remove();
            return;
        }

        var cardCount = IMAGE_TEXT_CARD_COUNT;
        if (contentEditable) {
            ensureImageTextCards(contentEditable);
            var cardSlots = contentEditable.querySelectorAll('[data-card-image-slot]');
            if (cardSlots.length > 0) {
                cardCount = Math.max(cardCount, cardSlots.length);
            } else {
                var imageCount = contentEditable.querySelectorAll('img').length;
                if (imageCount > IMAGE_TEXT_CARD_COUNT) {
                    cardCount = Math.max(cardCount, imageCount);
                }
            }
        }

        if (!toolsWrap) {
            toolsWrap = document.createElement('div');
            toolsWrap.className = 'js-card-image-tools mt-3 border rounded p-3 bg-light';
            var stylePanel = block.querySelector('.vendor-section-style-panel');
            if (stylePanel) {
                stylePanel.insertAdjacentElement('beforebegin', toolsWrap);
            }
        }

        toolsWrap.innerHTML = '<p class="small fw-semibold mb-2">Card images</p><div class="row g-2">' +
            Array.from({ length: cardCount }).map(function (_, index) {
                var i = index + 1;
                return '<div class="col-md-2 col-6">' +
                    '<label class="btn btn-sm btn-outline-secondary w-100 mb-1">Upload card ' + i +
                    '<input type="file" accept="image/*" class="d-none js-card-image-input" data-card-image-index="' + i + '"></label>' +
                    '<small class="text-muted d-block">Change image</small>' +
                '</div>';
            }).join('') + '</div>';
    }

    function updateCardImageInSection(block, cardIndex, file) {
        if (!block || !cardIndex || !file) return;
        var contentEditable = block.querySelector('[data-section-field="content"]');
        if (!contentEditable) return;

        var reader = new FileReader();
        reader.onload = function (ev) {
            var img = contentEditable.querySelector('[data-card-image-slot="' + cardIndex + '"]');
            if (!img) {
                var fallbackImgs = contentEditable.querySelectorAll('img');
                var fallbackIndex = Number(cardIndex) - 1;
                if (fallbackIndex >= 0 && fallbackImgs[fallbackIndex]) {
                    img = fallbackImgs[fallbackIndex];
                    img.setAttribute('data-card-image-slot', String(cardIndex));
                }
            }
            if (img) {
                img.src = ev.target.result;
                img.style.height = '180px';
                img.style.objectFit = 'cover';
                syncEditable(contentEditable);
            }
        };
        reader.readAsDataURL(file);
    }

    
    function renderGridImageUploadTools(block) {
        if (!block) return;
        var toolsWrap = block.querySelector('.js-grid-image-tools');
        var sectionType = block.querySelector('[data-section-type-input]')?.value || 'image_text';

        if (sectionType !== 'image_grid') {
            if (toolsWrap) toolsWrap.remove();
            return;
        }

        if (!toolsWrap) {
            toolsWrap = document.createElement('div');
            toolsWrap.className = 'js-grid-image-tools mt-3 border rounded p-3 bg-light';
            toolsWrap.innerHTML = '<p class="small fw-semibold mb-2">Grid images</p><div class="row g-2">' +
                Array.from({ length: 8 }).map(function (_, i) {
                    var n = i + 1;
                    return '<div class="col-md-3 col-6">' +
                        '<label class="btn btn-sm btn-outline-secondary w-100 mb-1">Upload image ' + n +
                        '<input type="file" accept="image/*" class="d-none js-grid-image-input" data-grid-image-index="' + n + '"></label>' +
                    '</div>';
                }).join('') + '</div>';
            var stylePanel = block.querySelector('.vendor-section-style-panel');
            if (stylePanel) stylePanel.insertAdjacentElement('beforebegin', toolsWrap);
        }
    }

    function renderBrochureUploadTools(block) {
        if (!block) return;
        var toolsWrap = block.querySelector('.js-brochure-tools');
        var sectionType = block.querySelector('[data-section-type-input]')?.value || 'image_text';
        if (sectionType !== 'brochure') {
            if (toolsWrap) toolsWrap.remove();
            return;
        }

        if (!toolsWrap) {
            toolsWrap = document.createElement('div');
            toolsWrap.className = 'js-brochure-tools mt-3 border rounded p-3 bg-light';
            var stylePanel = block.querySelector('.vendor-section-style-panel');
            if (stylePanel) stylePanel.insertAdjacentElement('beforebegin', toolsWrap);
        }

        toolsWrap.innerHTML = '<p class="small fw-semibold mb-2">Brochure assets</p>' +
            '<div class="row g-2">' +
            '<div class="col-md-6"><label class="btn btn-sm btn-outline-secondary w-100 mb-1">Upload brochure image(s)<input type="file" accept="image/*" multiple class="d-none js-brochure-image-input"></label></div>' +
            '<div class="col-md-6"><label class="btn btn-sm btn-outline-secondary w-100 mb-1">Upload brochure PDF(s)<input type="file" accept=\"application/pdf\" multiple class=\"d-none js-brochure-pdf-input\"></label></div>' +
            '</div><small class="text-muted d-block mt-1">You can also edit brochure text directly in the content box above.</small>';

        var contentEditable = block.querySelector('[data-section-field="content"]');
        normalizeBrochureImageItems(contentEditable);
        normalizeBrochurePdfItems(contentEditable);
    }

    function normalizeBrochureImageItems(contentEditable) {
        if (!contentEditable) return;
        var row = contentEditable.querySelector('[data-brochure-wrap] .row') || contentEditable.querySelector('.row');
        if (!row) return;
        row.classList.add('d-flex', 'flex-wrap', 'align-items-start');

        var imageList = contentEditable.querySelector('[data-brochure-image-list]');
        if (!imageList) {
            imageList = document.createElement('div');
            imageList.className = 'col-12 d-flex flex-wrap gap-2 align-items-start';
            imageList.setAttribute('data-brochure-image-list', '');
            row.insertAdjacentElement('afterbegin', imageList);
        }
        imageList.classList.add('d-flex', 'flex-wrap', 'gap-2', 'align-items-start');

        row.querySelectorAll('img[data-brochure-image-slot], img').forEach(function (img, i) {
            var slot = i + 1;
            img.setAttribute('data-brochure-image-slot', String(slot));
            if (!img.getAttribute('alt')) img.setAttribute('alt', 'Brochure image ' + slot);
            img.style.width = '220px';
            img.style.height = '280px';
            img.style.objectFit = 'cover';

            var col = img.closest('.js-brochure-image-col');
            if (!col) {
                col = img.closest('.js-brochure-image-col') || document.createElement('div');
                if (!col.parentElement) {
                    col.className = 'js-brochure-image-col';
                    imageList.insertAdjacentElement('beforeend', col);
                    col.appendChild(img);
                } else {
                    col.classList.add('js-brochure-image-col');
                }
            }
            if (col.parentElement !== imageList) {
                imageList.appendChild(col);
            }
            col.classList.add('d-flex', 'justify-content-start');
            col.style.flex = '0 0 220px';
            col.style.width = '220px';
            col.style.maxWidth = '220px';

            var wrap = img.closest('.position-relative');
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'position-relative d-inline-block w-100';
                img.insertAdjacentElement('beforebegin', wrap);
                wrap.appendChild(img);
            }

            var removeBtn = wrap.querySelector('.js-remove-brochure-image');
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-1 js-remove-brochure-image';
                removeBtn.setAttribute('title', 'Delete image');
                removeBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
                wrap.appendChild(removeBtn);
            }
        });
    }

    function cleanupLegacyBrochureText(contentEditable) {
        if (!contentEditable) return;
        var rightCol = contentEditable.querySelector('.col-md-8, .col-md-4:last-child') || contentEditable;
        var heading = rightCol.querySelector('h5');
        var paragraph = rightCol.querySelector('p');

        if (heading && heading.textContent && heading.textContent.trim().toLowerCase() === 'brochure title') {
            heading.remove();
        }
        if (paragraph && paragraph.textContent && paragraph.textContent.trim().toLowerCase() === 'add your brochure description text here.') {
            paragraph.remove();
        }
    }

    function getBrochureTileSize(contentEditable) {
        var width = 220;
        var height = 280;
        return { width: width, height: height };
    }

    function applyBrochurePdfTileStyle(contentEditable) {
        if (!contentEditable) return;
        var size = getBrochureTileSize(contentEditable);
        var list = contentEditable.querySelector('[data-brochure-pdf-list]');
        if (list) {
            list.classList.add('d-flex', 'flex-wrap', 'gap-2', 'align-items-start');
        }
        contentEditable.querySelectorAll('[data-brochure-pdf-slot]').forEach(function (link) {
            link.classList.remove('btn-primary', 'btn-outline-primary', 'btn-sm');
            link.classList.add('d-flex', 'align-items-center', 'justify-content-center', 'text-danger', 'border', 'rounded', 'bg-white');
            link.style.width = '100%';
            link.style.height = size.height + 'px';
            link.style.fontSize = '72px';
            link.style.textDecoration = 'none';
            link.style.lineHeight = '1';
        });
    }

    function normalizeBrochurePdfItems(contentEditable) {
        if (!contentEditable) return;
        cleanupLegacyBrochureText(contentEditable);
        var list = contentEditable.querySelector('[data-brochure-pdf-list]');
        if (!list) {
            var rightCol = contentEditable.querySelector('[data-brochure-image-list], .col-md-8') || contentEditable;
            var existingLinks = rightCol.querySelectorAll('a[data-brochure-pdf-slot], a.btn.btn-primary, a.btn.btn-outline-primary');
            if (!existingLinks.length) return;

            list = document.createElement('div');
            list.className = 'd-flex flex-wrap gap-2';
            list.setAttribute('data-brochure-pdf-list', '');
            rightCol.appendChild(list);
            existingLinks.forEach(function (lnk) { list.appendChild(lnk); });
        }

        list.querySelectorAll('a[data-brochure-pdf-slot]').forEach(function (link, i) {
            var slot = i + 1;
            link.setAttribute('data-brochure-pdf-slot', String(slot));

            var item = link.closest('[data-brochure-pdf-item]');
            if (!item) {
                item = document.createElement('div');
                item.className = 'd-inline-flex align-items-start gap-1 position-relative brochure-pdf-item';
                item.setAttribute('data-brochure-pdf-item', String(slot));
                link.insertAdjacentElement('beforebegin', item);
                item.appendChild(link);
            } else {
                item.setAttribute('data-brochure-pdf-item', String(slot));
                item.classList.add('position-relative');
            }
            item.style.flex = '0 0 220px';
            item.style.width = '220px';
            item.style.maxWidth = '220px';

            var removeBtn = item.querySelector('.js-remove-brochure-pdf');
            if (!removeBtn) {
                removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger js-remove-brochure-pdf position-absolute top-0 end-0 m-1';
                removeBtn.setAttribute('title', 'Delete PDF');
                removeBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
                item.appendChild(removeBtn);
            }
        });
        applyBrochurePdfTileStyle(contentEditable);
    }

    function updateBrochureImageInSection(block, file) {
        var contentEditable = block?.querySelector('[data-section-field="content"]');
        if (!contentEditable || !file) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            var existing = contentEditable.querySelectorAll('[data-brochure-image-slot]');
            var nextSlot = existing.length + 1;
            var img = contentEditable.querySelector('[data-brochure-image-slot="1"]') || contentEditable.querySelector('img');
            var isPlaceholder = img && img.getAttribute('data-brochure-placeholder') === '1';
            if (img && existing.length <= 1 && isPlaceholder) {
                img.src = ev.target.result;
                img.setAttribute('data-brochure-image-slot', '1');
                img.removeAttribute('data-brochure-placeholder');
            } else {
                var row = contentEditable.querySelector('[data-brochure-wrap] .row') || contentEditable.querySelector('.row');
                if (!row) return;
                var imageList = contentEditable.querySelector('[data-brochure-image-list]');
                if (!imageList) {
                    imageList = document.createElement('div');
                    imageList.className = 'col-12 d-flex flex-wrap gap-2 align-items-start';
                    imageList.setAttribute('data-brochure-image-list', '');
                    row.insertAdjacentElement('afterbegin', imageList);
                }
                var col = document.createElement('div');
                col.className = 'js-brochure-image-col';
                col.innerHTML = '<div class="position-relative d-inline-block w-100">' +
                    '<img src="' + ev.target.result + '" class="img-fluid rounded" data-brochure-image-slot="' + nextSlot + '" alt="Brochure image ' + nextSlot + '">' +
                    '<button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 js-remove-brochure-image" title="Delete image"><i class="fa-solid fa-trash"></i></button>' +
                    '</div>';
                imageList.insertAdjacentElement('beforeend', col);
            }
            normalizeBrochureImageItems(contentEditable);
            applyBrochurePdfTileStyle(contentEditable);
            syncEditable(contentEditable);
        };
        reader.readAsDataURL(file);
    }

    function updateBrochurePdfInSection(block, file) {
        var contentEditable = block?.querySelector('[data-section-field="content"]');
        if (!contentEditable || !file) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            var existingLinks = contentEditable.querySelectorAll('[data-brochure-pdf-slot]');
            var nextSlot = existingLinks.length + 1;
            var list = contentEditable.querySelector('[data-brochure-pdf-list]');
            var link = existingLinks.length === 0
                ? (contentEditable.querySelector('[data-brochure-pdf-slot="1"]') || contentEditable.querySelector('a'))
                : null;
            if (!list) {
                var rightCol = contentEditable.querySelector('[data-brochure-image-list], .col-md-8') || contentEditable;
                var wrap = document.createElement('div');
                wrap.className = 'd-flex flex-wrap gap-2';
                wrap.setAttribute('data-brochure-pdf-list', '');
                rightCol.appendChild(wrap);
                list = wrap;
            }
            if (!link) {
                link = document.createElement('a');
                link.className = 'btn btn-primary btn-sm';
                link.setAttribute('data-brochure-pdf-slot', String(nextSlot));
                list.appendChild(link);
            }
            if (!link) return;
            link.href = ev.target.result;
            link.innerHTML = '<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>';
            link.classList.remove('disabled');
            if (!link.getAttribute('data-brochure-pdf-slot')) link.setAttribute('data-brochure-pdf-slot', String(nextSlot));
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
            link.setAttribute('download', file.name || 'brochure.pdf');
            link.setAttribute('title', file.name || 'Open brochure PDF');
            link.setAttribute('aria-label', 'Open brochure PDF');
            normalizeBrochurePdfItems(contentEditable);
            applyBrochurePdfTileStyle(contentEditable);
            syncEditable(contentEditable);
        };
        reader.readAsDataURL(file);
    }

    function updateGridImageInSection(block, imageIndex, file) {
        if (!block || !imageIndex || !file) return;
        var contentEditable = block.querySelector('[data-section-field="content"]');
        if (!contentEditable) return;
        var reader = new FileReader();
        reader.onload = function (ev) {
            var img = contentEditable.querySelector('[data-grid-image-slot="' + imageIndex + '"]');
            if (img) {
                img.src = ev.target.result;
                img.style.height = '280px';
                img.style.objectFit = 'cover';
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

    function refreshSectionOrderLabels() {
        getVisibleSectionBlocks().forEach(function (block, idx) {
            block.classList.toggle('alt', idx % 2 === 1);
            var label = block.querySelector('.js-section-label');
            if (label) {
                label.innerHTML = '<i class="fa-solid fa-layer-group me-1"></i> Section ' + (idx + 1);
            }
        });
    }

    function initSectionDragAndDrop(block) {
        if (!block) return;
        block.draggable = true;
        var dragHandle = block.querySelector('.js-drag-handle');
        if (dragHandle) {
            dragHandle.addEventListener('mousedown', function () {
                block.setAttribute('data-drag-enabled', '1');
            });
            dragHandle.addEventListener('mouseup', function () {
                block.removeAttribute('data-drag-enabled');
            });
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
        cleanupDanglingDivText(contentEditable);
        if (titleInput) titleInput.value = preset.title;
        if (contentInput) contentInput.value = preset.content;

        applySectionTypeLayout(block, sectionType);

        var badge = block.querySelector('.badge');
        if (badge) {
            var typeLabel = sectionType.replace('_', ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
            badge.innerHTML = '<i class="fa-solid fa-layer-group me-1"></i> ' + typeLabel;
        }

        container.appendChild(block);
        initSectionDragAndDrop(block);
        refreshSectionOrderLabels();
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

    document.addEventListener('keydown', function (e) {
        if (!e.target.matches('[data-sync-target="hero-sub"][contenteditable="true"]')) return;
        if (e.key !== 'Enter' || !e.shiftKey) return;

        e.preventDefault();
        insertBreakAtCaret(e.target);
        syncEditable(e.target);
    });

    container?.addEventListener('dragstart', function (e) {
        var block = e.target.closest('.vendor-section-block');
        if (!block || block.getAttribute('data-drag-enabled') !== '1') {
            e.preventDefault();
            return;
        }
        block.classList.add('opacity-50');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', block.dataset.sectionIndex || '');
    });

    container?.addEventListener('dragend', function (e) {
        var block = e.target.closest('.vendor-section-block');
        if (!block) return;
        block.classList.remove('opacity-50');
        block.removeAttribute('data-drag-enabled');
    });

    container?.addEventListener('dragover', function (e) {
        e.preventDefault();
        var dragging = container.querySelector('.vendor-section-block.opacity-50');
        var target = e.target.closest('.vendor-section-block');
        if (!dragging || !target || dragging === target) return;

        var targetRect = target.getBoundingClientRect();
        var shouldInsertBefore = e.clientY < (targetRect.top + targetRect.height / 2);
        if (shouldInsertBefore) {
            container.insertBefore(dragging, target);
        } else {
            container.insertBefore(dragging, target.nextSibling);
        }
    });

    container?.addEventListener('drop', function (e) {
        e.preventDefault();
        refreshSectionOrderLabels();
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

        if (e.target.matches('.js-grid-image-input')) {
            var gfile = e.target.files && e.target.files[0];
            var gblock = e.target.closest('.vendor-section-block');
            var gidx = e.target.dataset.gridImageIndex;
            if (gfile) updateGridImageInSection(gblock, gidx, gfile);
        }

        if (e.target.matches('.js-card-image-input')) {
            var file = e.target.files && e.target.files[0];
            var block = e.target.closest('.vendor-section-block');
            var idx = e.target.dataset.cardImageIndex;
            if (file) updateCardImageInSection(block, idx, file);
        }
        if (e.target.matches('.js-brochure-image-input')) {
            var bImgFiles = Array.from(e.target.files || []);
            var bImgBlock = e.target.closest('.vendor-section-block');
            bImgFiles.forEach(function (f) { updateBrochureImageInSection(bImgBlock, f); });
        }
        if (e.target.matches('.js-brochure-pdf-input')) {
            var bPdfFiles = Array.from(e.target.files || []);
            var bPdfBlock = e.target.closest('.vendor-section-block');
            bPdfFiles.forEach(function (f) { updateBrochurePdfInSection(bPdfBlock, f); });
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
            refreshSectionOrderLabels();
        }

        if (e.target.closest('.js-remove-brochure-image')) {
            e.preventDefault();
            var brochureBlock = e.target.closest('.vendor-section-block');
            var brochureContent = brochureBlock?.querySelector('[data-section-field="content"]');
            var brochureCol = e.target.closest('.js-brochure-image-col');
            if (brochureCol) brochureCol.remove();

            if (brochureContent) {
                brochureContent.querySelectorAll('[data-brochure-image-slot]').forEach(function (img, i) {
                    img.setAttribute('data-brochure-image-slot', String(i + 1));
                    img.setAttribute('alt', 'Brochure image ' + (i + 1));
                });
                syncEditable(brochureContent);
            }
            return;
        }

        if (e.target.closest('.js-remove-brochure-pdf')) {
            e.preventDefault();
            var pdfBlock = e.target.closest('.vendor-section-block');
            var pdfContent = pdfBlock?.querySelector('[data-section-field="content"]');
            var pdfItem = e.target.closest('[data-brochure-pdf-item]');
            if (pdfItem) {
                pdfItem.remove();
            }
            normalizeBrochurePdfItems(pdfContent);
            if (pdfContent) syncEditable(pdfContent);
            return;
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
        initSectionDragAndDrop(block);
        var typeInput = block.querySelector('[data-section-type-input]');
        var currentType = typeInput?.value || '';
        var detectedType = detectSectionTypeFromContent(block);
        var effectiveType = currentType || detectedType;
        if (
            currentType === 'image_text' &&
            ['text_only', 'image_grid', 'brochure', 'video'].includes(detectedType)
        ) {
            effectiveType = detectedType;
        }
        if (typeInput) typeInput.value = effectiveType;
        applySectionTypeLayout(block, effectiveType);
    });

    renderBannerThumbs();
    refreshSectionOrderLabels();
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

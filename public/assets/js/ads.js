(function ($) {
    if (!$) {
        return;
    }

    function showToast(type, message) {
        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(type, message);
            return;
        }
        if (type === 'danger') {
            window.alert(message || 'Something went wrong.');
            return;
        }
        console.log(message || 'Done');
    }

    function initUserAdsTable() {
        var $table = $('#userAdsTable');
        if (!$table.length || !$.fn.DataTable) return;

        var dt = $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url')
            },
            order: [[7, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'category_name', name: 'category.name', orderable: false, searchable: false },
                { data: 'subcategory_name', name: 'subcategory.name', orderable: false, searchable: false },
                { data: 'location_name', name: 'location', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'banner_preview', name: 'banner_preview', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'valid_until', name: 'valid_until', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(5).html(data.status_badge);
                $(row).find('td').eq(6).html(data.banner_preview);
                $(row).find('td').eq(9).html(data.actions);
            }
        });

        $(document).on('click', '.js-delete-user-ad', function () {
            var id = $(this).data('id');

            var runDelete = function () {
                $.ajax({
                    url: '/dashboard/ads/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    showToast('success', (response && response.message) ? response.message : 'Ad deleted successfully.');
                    dt.ajax.reload(null, false);
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error))
                        ? (xhr.responseJSON.message || xhr.responseJSON.error)
                        : 'Unable to delete ad.';
                    showToast('danger', message);
                });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Delete this ad?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });

                return;
            }

            if (window.confirm('Are you sure you want to delete this ad?')) {
                runDelete();
            }
        });
    }

    function initAdminTemplatesTable() {
        var $table = $('#adminAdTemplatesTable');
        if (!$table.length || !$.fn.DataTable) return;

        var $sizeFilter = $('#adminTemplateFilterSize');
        var dt = $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url'),
                data: function (d) {
                    var sizeType = $sizeFilter.val();
                    if (sizeType) d.size_type = sizeType;
                }
            },
            order: [[4, 'desc']],
            columns: [
                { data: 'preview_html', name: 'preview_html', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(0).html(data.preview_html);
                $(row).find('td').eq(3).html(data.status_badge);
                $(row).find('td').eq(5).html(data.actions);
            }
        });

        if ($sizeFilter.length) {
            $sizeFilter.on('change', function () {
                dt.ajax.reload();
            });
        }

        $table.on('draw.dt', function () {
            applyScaledPreview($table.find('.js-ads-scaled-preview'));
        });

        applyScaledPreview($table.find('.js-ads-scaled-preview'));
    }

    function applyScaledPreview($items) {
        if (!$items || !$items.length) return;

        $items.each(function () {
            var $item = $(this);
            var $inner = $item.find('.ads-mini-preview-inner').first();
            if (!$inner.length) return;

            var sourceWidth = parseFloat($item.data('source-width')) || 0;
            var sourceHeight = parseFloat($item.data('source-height')) || 0;
            if (!sourceWidth || !sourceHeight) {
                $inner.css({ transform: '', width: '100%', height: '100%' });
                return;
            }

            var targetWidth = $item.innerWidth();
            var targetHeight = $item.innerHeight();
            if (!targetWidth || !targetHeight) return;

            var scale = Math.min(targetWidth / sourceWidth, targetHeight / sourceHeight);
            // Keep a small safety margin to avoid sub-pixel clipping of text descenders.
            // Short-height banners (e.g. 879x118) need a little more breathing room.
            var isShortBanner = sourceHeight <= 140;
            var safetyMargin = isShortBanner ? 0.01 : 0.003;
            scale = Math.max(scale - safetyMargin, 0);
            var offsetY = isShortBanner ? -1 : 0;
            $inner.css({
                width: sourceWidth + 'px',
                height: sourceHeight + 'px',
                transform: 'translateY(' + offsetY + 'px) scale(' + scale + ')'
            });
        });
    }

    function initScaledPreviews() {
        var $items = $('.js-ads-scaled-preview');
        if (!$items.length) return;

        var runScale = function () {
            applyScaledPreview($items);
        };

        runScale();
        $(window).on('resize load', runScale);

        if (window.ResizeObserver) {
            var ro = new ResizeObserver(runScale);
            $items.each(function () {
                ro.observe(this);
            });
        }
    }

    function initAdminSubmissionsTable() {
        var $table = $('#adminAdSubmissionsTable');
        if (!$table.length || !$.fn.DataTable) return;

        var dt = $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url'),
                data: function (d) {
                    var sizeType = $('#adminAdsFilterSize').val();
                    var status = $('#adminAdsFilterStatus').val();
                    if (sizeType) d.size_type = sizeType;
                    if (status) d.status = status;
                }
            },
            order: [[5, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'user_name', name: 'user.full_name', orderable: false, searchable: false },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'banner_preview', name: 'banner_preview', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'valid_until', name: 'valid_until', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(3).html(data.status_badge);
                $(row).find('td').eq(4).html(data.banner_preview);
                $(row).find('td').eq(7).html(data.actions);
            }
        });

        $('#adminAdsApplyFilters').on('click', function () {
            dt.ajax.reload();
        });

        $(document).on('click', '.js-delete-submission', function () {
            var id = $(this).data('id');

            var runDelete = function () {
                $.ajax({
                    url: '/admin/ads/submissions/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    showToast('success', (response && response.message) ? response.message : 'Ad deleted successfully.');
                    dt.ajax.reload(null, false);
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error))
                        ? (xhr.responseJSON.message || xhr.responseJSON.error)
                        : 'Unable to delete ad.';
                    showToast('danger', message);
                });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Delete this ad submission?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });

                return;
            }

            if (window.confirm('Are you sure you want to delete this ad submission?')) {
                runDelete();
            }
        });
    }

    function initAjaxAdSubmit() {
        if (!window.FormHelper || typeof window.FormHelper.attachAjaxForm !== 'function') return;
        var $form = $('form[action*="/dashboard/ads/create/"]');
        if (!$form.length) return;

        var $submit = $form.find('button[type="submit"]').first();
        FormHelper.attachAjaxForm({
            formSelector: $form,
            buttonSelector: $submit,
            alertSelector: '#adCustomizeAlert',
            defaultText: $submit.text().trim() || 'Submit',
            loadingText: 'Submitting...',
            onSuccess: function (response) {
                    showToast('success', response.message || 'Submitted.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                showToast('danger', message || 'Failed to submit.');
            }
        });
    }

    function initAjaxTemplateForm() {
        if (!window.FormHelper || typeof window.FormHelper.attachAjaxForm !== 'function') return;
        var $form = $('form[action*="/admin/ads/templates"]');
        if (!$form.length) return;

        var $submit = $form.find('button[type="submit"]').first();
        FormHelper.attachAjaxForm({
            formSelector: $form,
            buttonSelector: $submit,
            alertSelector: '#adminAdTemplateAlert',
            defaultText: $submit.text().trim() || 'Save',
            loadingText: 'Saving...',
            onSuccess: function (response) {
                    showToast('success', response.message || 'Saved.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                showToast('danger', message || 'Failed to save.');
            }
        });
    }

    function initAjaxApprovalActions() {
        var $approveForm = $('form[action*="/admin/ads/submissions/"][action$="/approve"]');
        var $rejectForm = $('form[action*="/admin/ads/submissions/"][action$="/reject"]');

        if ($approveForm.length) {
            $approveForm.on('submit', function (e) {
                e.preventDefault();
                var $btn = $approveForm.find('button[type="submit"]');
                $btn.prop('disabled', true);
                var fd = new FormData($approveForm.get(0));
                $.ajax({
                    url: $approveForm.attr('action'),
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    showToast('success', (res && res.message) ? res.message : 'Approved.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.';
                    showToast('danger', msg);
                    $btn.prop('disabled', false);
                });
            });
        }

        if ($rejectForm.length) {
            $rejectForm.on('submit', function (e) {
                e.preventDefault();
                var $btn = $rejectForm.find('button[type="submit"]');
                $btn.prop('disabled', true);
                var fd = new FormData($rejectForm.get(0));
                $.ajax({
                    url: $rejectForm.attr('action'),
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    showToast('success', (res && res.message) ? res.message : 'Rejected.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : null;
                    var msg = errors && errors.review_note && errors.review_note[0]
                        ? errors.review_note[0]
                        : ((xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.');
                    showToast('danger', msg);
                    $btn.prop('disabled', false);
                });
            });
        }
    }

    function initAdminTemplateLivePreview() {
        var $form = $('form[action*="/admin/ads/templates"]');
        if (!$form.length) return;

        var $layoutInput = $form.find('textarea[name="layout_html"]');
        var $schemaInput = $form.find('textarea[name="schema_json"]');
        var $sizeInput = $form.find('select[name="size_type"], input[name="size_type"]').first();
        var $previewWrap = $('#adminTemplateLivePreviewWrap');
        var $preview = $('#adminTemplateLivePreview');
        var $message = $('#adminTemplateLivePreviewMessage');
        var $placeholderContainer = $('#adminTemplatePreviewPlaceholders');

        if (!$layoutInput.length || !$preview.length) return;

        var sizeMap = {
            square: { ratio: '1 / 1', w: 640, h: 640 },
            vertical_rectangle: { ratio: '2 / 3', w: 600, h: 900 },
            horizontal: { ratio: '3 / 2', w: 900, h: 600 },
            square_large: { ratio: '1 / 1', w: 900, h: 900 },
            banner: { ratio: '4 / 1', w: 1200, h: 300 },
            full_page: { ratio: '3 / 4', w: 900, h: 1200 },
            top_categories_ad_1: { ratio: '879 / 118', w: 879, h: 118 },
            top_categories_ad_2: { ratio: '296 / 292', w: 296, h: 292 },
            sponsored_listings_ad: { ratio: '296 / 624', w: 296, h: 624 },
            below_sponsored_ad: { ratio: '1232 / 145', w: 1232, h: 145 },
            ecommerce_ad: { ratio: '289 / 186', w: 289, h: 186 },
            offer_discount_ad_1: { ratio: '884 / 160', w: 884, h: 160 },
            offer_discount_ad_2: { ratio: '277 / 340', w: 277, h: 340 },
            explore_products_ad: { ratio: '1191 / 138', w: 1191, h: 138 },
            top_vendors_ad_1: { ratio: '1191 / 77', w: 1191, h: 77 },
            top_vendors_ad_2: { ratio: '301 / 247', w: 301, h: 247 },
            popular_greenwood_ad: { ratio: '382 / 749', w: 382, h: 749 },
            popular_properties_ad: { ratio: '462 / 413', w: 462, h: 413 },
            below_popular_ad: { ratio: '1232 / 145', w: 1232, h: 145 },
            builders_developers_ad: { ratio: '292 / 271', w: 292, h: 271 },
            below_builders_ad: { ratio: '1232 / 145', w: 1232, h: 145 }
        };

        function escapeHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function parseSchema() {
            if (!$schemaInput.length) return { fields: [] };
            try {
                var parsed = JSON.parse($schemaInput.val() || '{}');
                return (parsed && typeof parsed === 'object') ? parsed : { fields: [] };
            } catch (e) {
                return null;
            }
        }

        function getSampleValue(field) {
            var key = String(field.key || '').toLowerCase();
            if (field.type === 'image') {
                return 'https://via.placeholder.com/1200x800?text=Preview';
            }
            if (key.indexOf('headline') !== -1) return 'Your Headline';
            if (key.indexOf('subheadline') !== -1) return 'Subheadline goes here';
            if (key.indexOf('cta') !== -1) return 'Learn More';
            if (key.indexOf('phone') !== -1) return '+1 555 123 4567';
            if (key.indexOf('website') !== -1) return 'www.example.com';
            return field.label ? String(field.label) : 'Sample';
        }

        function updatePreviewScale() {
            var sizeKey = ($sizeInput.val() || '').toString();
            var sizeDef = sizeMap[sizeKey] || { ratio: '1 / 1', w: 640, h: 640 };
            $previewWrap.css('aspect-ratio', sizeDef.ratio);
            $previewWrap.attr('data-source-width', sizeDef.w);
            $previewWrap.attr('data-source-height', sizeDef.h);
            applyScaledPreview($previewWrap);
        }

        function render() {
            var layoutHtml = $layoutInput.val() || '';
            if (!layoutHtml.trim()) {
                $preview.html('');
                $placeholderContainer.html('');
                $message.removeClass('text-danger').addClass('text-secondary').text('Add HTML and placeholders (like {{headline}}) to see your final rendering.');
                return;
            }

            var schema = parseSchema();
            if (schema === null) {
                $message.removeClass('text-secondary').addClass('text-danger').text('Schema JSON is invalid. Fix it to build better live placeholder previews.');
            } else {
                $message.removeClass('text-danger').addClass('text-secondary').text('Updates instantly as you type. Placeholders are auto-filled from schema fields.');
            }

            var fields = (schema && Array.isArray(schema.fields)) ? schema.fields : [];
            var sampleData = {};
            var placeholderChips = [];

            fields.forEach(function (field) {
                if (!field || typeof field !== 'object' || !field.key) return;
                sampleData[field.key] = getSampleValue(field);
                placeholderChips.push('<span class="badge rounded-pill text-bg-light border">{{' + escapeHtml(field.key) + '}}</span>');
            });

            if (!fields.length) {
                var fromLayout = layoutHtml.match(/\{\{([a-zA-Z][a-zA-Z0-9_]*)\}\}/g) || [];
                fromLayout.forEach(function (token) {
                    var key = token.replace(/[{}]/g, '');
                    if (!sampleData[key]) {
                        sampleData[key] = 'Sample';
                        placeholderChips.push('<span class="badge rounded-pill text-bg-light border">{{' + escapeHtml(key) + '}}</span>');
                    }
                });
            }

            $placeholderContainer.html(placeholderChips.join(''));

            var rendered = layoutHtml;
            Object.keys(sampleData).forEach(function (key) {
                var pattern = new RegExp('\\{\\{' + key + '\\}\\}', 'g');
                rendered = rendered.replace(pattern, escapeHtml(sampleData[key]));
            });
            rendered = rendered.replace(/\{\{[a-zA-Z][a-zA-Z0-9_]*\}\}/g, '');

            var $canvas = $('<div class="ad-canvas"></div>').html(rendered);
            $preview.html($canvas);
            updatePreviewScale();
        }

        $layoutInput.on('input', render);
        $schemaInput.on('input', render);
        $sizeInput.on('change', function () {
            updatePreviewScale();
            render();
        });
        $(window).on('resize', updatePreviewScale);

        render();
    }

    function initAdSizeCustomizerPage() {
        var page = document.getElementById('adsSizeCustomizerPage');
        if (!page) return;

        var form = page.querySelector('form[action*="/dashboard/ads/create/"]');
        if (!form) return;

        var previewFrame = document.getElementById('adPreviewFrame');
        var preview = document.getElementById('adPreview');
        var canvasWrap = document.getElementById('canvasWrap');
        var customHtmlInput = document.getElementById('customHtmlInput');
        var generatedImageDataInput = document.getElementById('generatedImageDataInput');
        var uploadInput = document.getElementById('uploadImageInput');
        var dropzone = document.getElementById('adDropzone');
        var categorySelect = document.getElementById('categorySelect');
        var subcategorySelect = document.getElementById('subcategorySelect');
        var sizeW = Number(previewFrame?.dataset.sourceWidth || 0);
        var sizeH = Number(previewFrame?.dataset.sourceHeight || 0);
        var selectedLayer = null;
        var cropper = null;
        var cropSourceObjectUrl = '';
        var adImageCropModalEl = document.getElementById('adImageCropModal');
        var adCropImage = document.getElementById('adCropImage');
        var adCropSaveBtn = document.getElementById('adCropSaveBtn');
        var adImageCropModal = (window.bootstrap && adImageCropModalEl) ? new window.bootstrap.Modal(adImageCropModalEl) : null;

        function setMode(mode) {
            var uploadWrap = document.getElementById('uploadWrap');
            var customizeWrap = document.getElementById('customizeWrap');
            if (uploadWrap) uploadWrap.classList.toggle('d-none', mode !== 'upload');
            if (customizeWrap) customizeWrap.classList.toggle('d-none', mode !== 'customize');

            document.querySelectorAll('.banner-mode-card').forEach(function (card) { card.classList.remove('is-active'); });
            var checkedRadio = document.querySelector('input[name="design_mode"]:checked');
            var activeCard = checkedRadio && checkedRadio.closest('.banner-mode-option')
                ? checkedRadio.closest('.banner-mode-option').querySelector('.banner-mode-card')
                : null;
            if (activeCard) activeCard.classList.add('is-active');

            if (mode === 'customize') {
                canvasWrap?.classList.remove('d-none');
            } else if (!preview?.querySelector('img')) {
                canvasWrap?.classList.add('d-none');
            }
        }

        document.querySelectorAll('input[name="design_mode"]').forEach(function (radio) {
            radio.addEventListener('change', function () { setMode(radio.value); });
        });

        function makeDraggable(el) {
            var sx = 0, sy = 0, ox = 0, oy = 0, dragging = false;
            el.addEventListener('mousedown', function (e) {
                if (e.target.closest('[contenteditable="true"]')) return;
                dragging = true;
                sx = e.clientX; sy = e.clientY;
                ox = parseFloat(el.style.left || '20'); oy = parseFloat(el.style.top || '20');
                selectedLayer = el;
            });
            window.addEventListener('mousemove', function (e) {
                if (!dragging) return;
                el.style.left = Math.max(0, Math.min(sizeW - el.offsetWidth, ox + e.clientX - sx)) + 'px';
                el.style.top = Math.max(0, Math.min(sizeH - el.offsetHeight, oy + e.clientY - sy)) + 'px';
            });
            window.addEventListener('mouseup', function () { dragging = false; });
        }

        function addLayer(el) {
            if (!preview) return;
            el.style.position = 'absolute';
            el.style.left = '20px';
            el.style.top = '20px';
            el.style.zIndex = String(Date.now() % 100000);
            makeDraggable(el);
            el.addEventListener('click', function (e) { e.stopPropagation(); selectedLayer = el; });
            preview.appendChild(el);
            selectedLayer = el;
        }

        document.getElementById('addTextBtn')?.addEventListener('click', function () {
            var t = document.createElement('div');
            t.textContent = 'Edit text';
            t.style.fontSize = '30px';
            t.style.fontWeight = '700';
            t.style.color = '#111';
            t.style.padding = '4px 6px';
            t.setAttribute('contenteditable', 'true');
            addLayer(t);
        });

        document.getElementById('addImageBtn')?.addEventListener('click', function () {
            document.getElementById('customImageInput')?.click();
        });
        document.getElementById('customImageInput')?.addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            if (!file) return;
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.width = Math.round(sizeW * 0.25) + 'px';
            img.style.height = 'auto';
            addLayer(img);
            e.target.value = '';
        });

        document.getElementById('removeLayerBtn')?.addEventListener('click', function () {
            if (!selectedLayer) return;
            selectedLayer.remove();
            selectedLayer = null;
        });

        function applyUploadedImage(src) {
            if (!preview) return;
            preview.innerHTML = '';
            var img = document.createElement('img');
            img.src = src;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            preview.appendChild(img);
            canvasWrap?.classList.remove('d-none');
        }

        function cleanupCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            if (cropSourceObjectUrl) {
                URL.revokeObjectURL(cropSourceObjectUrl);
                cropSourceObjectUrl = '';
            }
        }

        uploadInput?.addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            if (!file || !preview) return;
            if (!window.Cropper || !adImageCropModal || !adCropImage || !adCropSaveBtn) {
                applyUploadedImage(URL.createObjectURL(file));
                return;
            }

            cleanupCropper();
            cropSourceObjectUrl = URL.createObjectURL(file);
            adCropImage.src = cropSourceObjectUrl;

            adImageCropModalEl?.addEventListener('shown.bs.modal', function onShown() {
                adImageCropModalEl.removeEventListener('shown.bs.modal', onShown);
                cropper = new window.Cropper(adCropImage, {
                    aspectRatio: sizeW / sizeH,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true,
                    movable: true,
                    zoomable: true,
                    rotatable: false,
                    scalable: false
                });
            });

            adImageCropModalEl?.addEventListener('hidden.bs.modal', function onHidden() {
                adImageCropModalEl.removeEventListener('hidden.bs.modal', onHidden);
                cleanupCropper();
            });

            adCropSaveBtn.onclick = function () {
                if (!cropper) return;
                var canvas = cropper.getCroppedCanvas({
                    width: sizeW,
                    height: sizeH,
                    imageSmoothingQuality: 'high'
                });
                if (!canvas) return;
                applyUploadedImage(canvas.toDataURL('image/png'));
                adImageCropModal.hide();
            };

            adImageCropModal.show();
        });

        if (dropzone && uploadInput) {
            dropzone.addEventListener('click', function () { uploadInput.click(); });
            dropzone.addEventListener('dragover', function (event) {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
            dropzone.addEventListener('dragleave', function () {
                dropzone.classList.remove('is-dragover');
            });
            dropzone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
                var file = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;
                if (!file) return;
                var dt = new DataTransfer();
                dt.items.add(file);
                uploadInput.files = dt.files;
                uploadInput.dispatchEvent(new Event('change'));
            });
        }

        async function exportPreviewAsPng() {
            var exportWidth = sizeW || preview.scrollWidth || 0;
            var exportHeight = sizeH || preview.scrollHeight || 0;
            var exportPixelRatio = 2;
            var clone = preview.cloneNode(true);
            var sandbox = document.createElement('div');
            sandbox.style.position = 'fixed';
            sandbox.style.left = '-10000px';
            sandbox.style.top = '0';
            sandbox.style.width = exportWidth + 'px';
            sandbox.style.height = exportHeight + 'px';
            sandbox.style.overflow = 'hidden';
            sandbox.style.zIndex = '-1';

            clone.style.position = 'static';
            clone.style.inset = 'auto';
            clone.style.transform = 'none';
            clone.style.transformOrigin = 'top left';
            clone.style.width = exportWidth + 'px';
            clone.style.height = exportHeight + 'px';
            clone.style.maxWidth = 'none';
            clone.style.maxHeight = 'none';
            clone.style.overflow = 'hidden';

            sandbox.appendChild(clone);
            document.body.appendChild(sandbox);

            var waitForImages = async function (root, timeoutMs) {
                var imgs = Array.from(root.querySelectorAll('img'));
                if (!imgs.length) return;
                await Promise.race([
                    Promise.all(imgs.map(function (img) {
                        if (img.complete) return Promise.resolve();
                        return new Promise(function (resolve) {
                            var done = function () { resolve(); };
                            img.addEventListener('load', done, { once: true });
                            img.addEventListener('error', done, { once: true });
                        });
                    })),
                    new Promise(function (resolve) { setTimeout(resolve, timeoutMs || 6000); })
                ]);
            };

            try {
                await waitForImages(clone, 6000);
                if (window.htmlToImage && typeof window.htmlToImage.toPng === 'function') {
                    return await window.htmlToImage.toPng(clone, {
                        pixelRatio: exportPixelRatio,
                        canvasWidth: exportWidth * exportPixelRatio,
                        canvasHeight: exportHeight * exportPixelRatio,
                        cacheBust: true,
                        skipFonts: true,
                        fontEmbedCSS: ''
                    });
                }
                if (window.html2canvas) {
                    var canvas = await window.html2canvas(clone, {
                        width: exportWidth,
                        height: exportHeight,
                        windowWidth: exportWidth,
                        windowHeight: exportHeight,
                        scale: exportPixelRatio,
                        useCORS: true,
                        allowTaint: false,
                        logging: false
                    });
                    return canvas.toDataURL('image/png');
                }
            } finally {
                document.body.removeChild(sandbox);
            }
            return '';
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();
            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }
            if (!preview) return;
            customHtmlInput.value = '<div class="ad-canvas" style="width:' + sizeW + 'px;height:' + sizeH + 'px;overflow:hidden;position:relative;">' + preview.innerHTML + '</div>';
            generatedImageDataInput.value = await exportPreviewAsPng();
            if (!generatedImageDataInput.value) {
                showToast('danger', 'Could not generate ad image.');
                return;
            }
            var response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            var payload = {};
            var responseText = '';
            try {
                payload = await response.json();
            } catch (jsonError) {
                responseText = await response.text();
            }
            if (!response.ok) {
                showToast('danger', payload.message || 'Unable to save ad.');
                return;
            }
            if (!payload || typeof payload !== 'object' || (!payload.message && !payload.redirect_url)) {
                showToast('info', 'Request sent successfully. Debug response received; staying on this page.');
                if (responseText) {
                    console.info('Ad save debug response:', responseText);
                }
                return;
            }
            showToast('success', payload.message || 'Saved successfully');
            var config = window.adsSizeCustomizerConfig || {};
            setTimeout(function () {
                window.location.href = payload.redirect_url || config.adsIndexUrl || '/dashboard/ads';
            }, 700);
        });

        async function loadSubcategories(categoryId) {
            var base = form.dataset.subcategoryUrlBase || '';
            if (!categoryId || !base || !subcategorySelect) {
                if (subcategorySelect) {
                    subcategorySelect.innerHTML = '<option value="">— Select a category first —</option>';
                    subcategorySelect.disabled = true;
                }
                return;
            }
            var response = await fetch(base + '/' + categoryId + '/subcategories', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            var data = await response.json();
            var options = ['<option value="">— Select subcategory —</option>'];
            (Array.isArray(data) ? data : []).forEach(function (item) {
                options.push('<option value=\"' + item.id + '\">' + item.name + '</option>');
            });
            subcategorySelect.innerHTML = options.join('');
            subcategorySelect.disabled = false;
        }
        categorySelect?.addEventListener('change', function () { loadSubcategories(this.value); });

        window.initAdLocationAutocomplete = function () {
            var locationInput = document.getElementById('adLocation');
            var locationLatInput = document.getElementById('adLocationLat');
            var locationLngInput = document.getElementById('adLocationLng');
            if (!locationInput || !window.google || !google.maps || !google.maps.places) return;
            var autocomplete = new google.maps.places.Autocomplete(locationInput, { fields: ['formatted_address', 'geometry', 'name'] });
            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                var lat = place && place.geometry && place.geometry.location && place.geometry.location.lat ? place.geometry.location.lat() : null;
                var lng = place && place.geometry && place.geometry.location && place.geometry.location.lng ? place.geometry.location.lng() : null;
                locationInput.value = (place && (place.formatted_address || place.name)) ? (place.formatted_address || place.name) : locationInput.value;
                if (locationLatInput) locationLatInput.value = typeof lat === 'number' ? String(lat) : '';
                if (locationLngInput) locationLngInput.value = typeof lng === 'number' ? String(lng) : '';
            });
        };

        setMode('upload');
    }

    $(function () {
        initUserAdsTable();
        initAdminTemplatesTable();
        initAdminSubmissionsTable();
        initAjaxAdSubmit();
        initAjaxTemplateForm();
        initAjaxApprovalActions();
        initAdminTemplateLivePreview();
        initScaledPreviews();
        initAdSizeCustomizerPage();
    });
})(window.jQuery);

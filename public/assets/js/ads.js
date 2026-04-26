(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    function initUserAdsTable() {
        var $table = $('#userAdsTable');
        if (!$table.length || !$.fn.DataTable) return;

        $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url')
            },
            order: [[7, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'template_name', name: 'template.name', orderable: false, searchable: false },
                { data: 'category_name', name: 'category.name', orderable: false, searchable: false },
                { data: 'subcategory_name', name: 'subcategory.name', orderable: false, searchable: false },
                { data: 'location_name', name: 'location', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(6).html(data.status_badge);
                $(row).find('td').eq(8).html(data.actions);
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
            $inner.css({
                width: sourceWidth + 'px',
                height: sourceHeight + 'px',
                transform: 'scale(' + scale + ')'
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
                { data: 'template_name', name: 'template.name', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(4).html(data.status_badge);
                $(row).find('td').eq(6).html(data.actions);
            }
        });

        $('#adminAdsApplyFilters').on('click', function () {
            dt.ajax.reload();
        });
    }

    function initAjaxAdSubmit() {
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
                FormHelper.showToast('success', response.message || 'Submitted.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                FormHelper.showToast('danger', message || 'Failed to submit.');
            }
        });
    }

    function initAjaxTemplateForm() {
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
                FormHelper.showToast('success', response.message || 'Saved.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                FormHelper.showToast('danger', message || 'Failed to save.');
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
                    FormHelper.showToast('success', (res && res.message) ? res.message : 'Approved.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.';
                    FormHelper.showToast('danger', msg);
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
                    FormHelper.showToast('success', (res && res.message) ? res.message : 'Rejected.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : null;
                    var msg = errors && errors.review_note && errors.review_note[0]
                        ? errors.review_note[0]
                        : ((xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.');
                    FormHelper.showToast('danger', msg);
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

    $(function () {
        initUserAdsTable();
        initAdminTemplatesTable();
        initAdminSubmissionsTable();
        initAjaxAdSubmit();
        initAjaxTemplateForm();
        initAjaxApprovalActions();
        initAdminTemplateLivePreview();
        initScaledPreviews();
    });
})(window.jQuery);

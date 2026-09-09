(function ($, window) {
    'use strict';

    if (!$) {
        return;
    }

    function initMaterialUploadForm(config) {
        config = config || {};
        var activeType = config.activeType || 'notes';
        var isEdit = !!config.isEdit;

        var $page = $('.sm-upload-page');
        var $form = $('#studyMaterialForm');
        if (!$form.length) {
            return;
        }

        var $alert = $('#studyMaterialAlert');
        var $prevBtn = $('#studyMaterialPrevBtn');
        var $nextBtn = $('#studyMaterialNextBtn');
        var $submitBtn = $('#studyMaterialSubmitBtn');
        var $progress = $('#materialWizardProgress');
        var currentStepIndex = 0;
        var steps = [];
        var typeConfigCache = {};
        var typeConfigUrl = config.typeConfigUrl || '';

        $page.attr('data-active-type', activeType);

        function buildTypeConfigUrl(type) {
            return typeConfigUrl.replace('__TYPE__', encodeURIComponent(type));
        }

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function renderSidebarList($list, items, iconClass) {
            $list.empty();
            (items || []).forEach(function (item) {
                $list.append(
                    '<li><i class="fa-solid ' + iconClass + '"></i><span>' + escapeHtml(item) + '</span></li>'
                );
            });
        }

        function renderOptionsGrid(options) {
            var $grid = $('#smUploadOptionsGrid');
            var checked = {};

            $grid.find('input:checked').each(function () {
                checked[$(this).val()] = true;
            });

            $grid.empty();
            $.each(options || {}, function (key, label) {
                var isChecked = !!checked[key] || (key === 'allow_download' && $.isEmptyObject(checked));
                $grid.append(
                    '<label class="sm-upload-option">' +
                        '<input class="form-check-input" type="checkbox" name="meta[options][]" value="' + escapeHtml(key) + '"' +
                        (isChecked ? ' checked' : '') + '>' +
                        '<span>' + escapeHtml(label) + '</span>' +
                    '</label>'
                );
            });
        }

        function applyTypeVisibility(type) {
            $form.find('[data-types]').each(function () {
                var types = String($(this).attr('data-types') || '').split(/\s+/).filter(Boolean);
                var show = types.indexOf('all') !== -1 || types.indexOf(type) !== -1;
                $(this).toggleClass('d-none', !show);
            });

            $form.find('[data-type-panel="videos"]').toggleClass('d-none', type !== 'videos');

            if (type !== 'videos') {
                $('.js-file-dropzone').removeClass('d-none');
            }
        }

        function applyMaterialType(type, cfg, isInitial) {
            activeType = type;
            $page.attr('data-active-type', type);
            $('input[name="material_type"]').val(type);

            $('.sm-upload-type-card').removeClass('is-active');
            $('.sm-upload-type-card[data-type="' + type + '"]').addClass('is-active');

            $('.js-type-icon').attr('class', 'fa-solid ' + cfg.icon + ' js-type-icon');
            $('.js-type-title').text(isEdit ? ('Edit: ' + cfg.title) : cfg.title);
            $('.js-type-subtitle').text(cfg.subtitle);
            $('.js-type-quote').text(cfg.quote);
            $('.js-section-details-title').text(cfg.details_title);
            $('.js-upload-section-title').html(
                escapeHtml(cfg.upload_title) + ' <span class="sm-upload-section__req">*</span>'
            );

            renderSidebarList($('.js-type-tips'), cfg.tips, 'fa-check');
            $('.js-type-guidelines-title').text(cfg.guidelines_title);
            renderSidebarList($('.js-type-guidelines'), cfg.guidelines, 'fa-check');
            renderSidebarList($('.js-type-not-allowed'), cfg.not_allowed, 'fa-xmark');
            $('.js-type-preview-label').text(cfg.preview_label);
            $('.js-type-preview-caption').text(cfg.preview_caption);
            $('.js-type-footer').text(cfg.footer);

            $('#materialFileInput').attr('accept', cfg.accept);
            $('.js-file-hint').text(cfg.file_hint);

            if (type === 'videos') {
                $('.js-dropzone-text').text('Drag & drop your lesson file here or click to browse');
                $('.js-dropzone-btn-label').text('Choose File');
                $('input[name="meta[link_type]"]').val('upload');
                $('.sm-upload-link-tab').removeClass('is-active')
                    .filter('[data-link-tab="upload"]').addClass('is-active');
                $('.js-link-panel[data-link-panel="link"]').addClass('d-none');
                $('.js-link-panel[data-link-panel="upload"]').removeClass('d-none');
                $('.js-file-dropzone').removeClass('d-none');
            } else {
                $('.js-dropzone-text').text('Drag & drop your file here or click to browse');
                $('.js-dropzone-btn-label').text('Choose File(s)');
                $('input[name="meta[link_type]"]').val('upload');
                $('.sm-upload-link-tab').removeClass('is-active')
                    .filter('[data-link-tab="upload"]').addClass('is-active');
                $('.js-link-panel[data-link-panel="link"]').addClass('d-none');
            }

            renderOptionsGrid(cfg.options);
            applyTypeVisibility(type);

            if (!isInitial) {
                var $file = $('#materialFileInput');
                $file.val('');
                $('#fileDropzone .sm-upload-dropzone__file-name').text('');
            }

            if (window.history && window.history.replaceState) {
                window.history.replaceState({}, '', window.location.pathname);
            }
        }

        function switchMaterialType(type, silent) {
            if (!type || type === activeType) {
                return $.Deferred().resolve().promise();
            }

            if (typeConfigCache[type]) {
                applyMaterialType(type, typeConfigCache[type], false);
                if (!silent) {
                    toast('success', 'Switched to ' + typeConfigCache[type].short_title + '.');
                }
                return $.Deferred().resolve().promise();
            }

            var $card = $('.sm-upload-type-card[data-type="' + type + '"]');
            $card.addClass('is-loading');

            return $.ajax({
                url: buildTypeConfigUrl(type),
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function (response) {
                if (!response.ok || !response.config) {
                    toast('error', 'Unable to load material type settings.');
                    return;
                }

                typeConfigCache[type] = response.config;
                applyMaterialType(type, response.config, false);
                if (!silent) {
                    toast('success', 'Switched to ' + response.config.short_title + '.');
                }
            }).fail(function () {
                toast('error', 'Unable to load material type. Please try again.');
                $('.sm-upload-type-card').removeClass('is-active');
                $('.sm-upload-type-card[data-type="' + activeType + '"]').addClass('is-active');
            }).always(function () {
                $card.removeClass('is-loading');
            });
        }

        function loadInitialTypeConfig() {
            var initialType = activeType;

            if (!isEdit) {
                var urlParams = new URLSearchParams(window.location.search);
                var urlType = urlParams.get('type');
                if (urlType) {
                    initialType = urlType;
                }
            }

            applyTypeVisibility(initialType);

            if (isEdit || !typeConfigUrl) {
                return;
            }

            $.ajax({
                url: buildTypeConfigUrl(initialType),
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function (response) {
                if (response.ok && response.config) {
                    typeConfigCache[initialType] = response.config;
                    applyMaterialType(initialType, response.config, true);
                }
            }).fail(function () {
                if (initialType !== activeType) {
                    toast('warning', 'Could not load the requested material type. Using default.');
                    applyTypeVisibility(activeType);
                }
            });
        }

        function toast(type, message) {
            if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                window.FormHelper.showToast(type, message);
            }
        }

        function clearStepErrors($step) {
            ($step || $form).find('.is-invalid').removeClass('is-invalid');
            ($step || $form).find('span.ajax-error').remove();
        }

        function markInvalid($input, message) {
            if (!$input || !$input.length) {
                return;
            }

            $input.addClass('is-invalid');
            var $target = $input;
            if ($input.attr('type') === 'radio' || $input.attr('type') === 'checkbox') {
                $target = $input.closest('.sm-upload-role-card, .sm-upload-option, .form-check').first();
            }

            if (!$target.next('.ajax-error').length) {
                $('<span class="invalid-feedback d-block ajax-error"></span>')
                    .text(message)
                    .insertAfter($target);
            }
        }

        function collectSteps() {
            steps = $form.find('.sm-upload-step').filter(function () {
                return $(this).find(':input, textarea, select, .sm-upload-dropzone, .sm-upload-type-grid').length > 0;
            }).toArray();

            $progress.empty();
            steps.forEach(function (stepEl, index) {
                var label = stepEl.getAttribute('data-step-label') || ('Step ' + (index + 1));
                var $pill = $(
                    '<button type="button" class="sm-upload-wizard__pill" data-step-index="' + index + '">' +
                        '<span class="sm-upload-wizard__pill-num">' + (index + 1) + '</span>' +
                        '<span>' + label + '</span>' +
                    '</button>'
                );
                $progress.append($pill);
            });
        }

        function updateProgressUI() {
            $progress.find('.sm-upload-wizard__pill').each(function (index) {
                var $pill = $(this);
                $pill.toggleClass('is-active', index === currentStepIndex);
                $pill.toggleClass('is-complete', index < currentStepIndex);
            });

            $prevBtn.toggleClass('d-none', currentStepIndex <= 0);
            var isLast = currentStepIndex >= steps.length - 1;
            $nextBtn.toggleClass('d-none', isLast);
            $submitBtn.toggleClass('d-none', !isLast);
        }

        function showStep(index, scroll) {
            if (!steps.length) {
                return;
            }

            currentStepIndex = Math.max(0, Math.min(index, steps.length - 1));
            $form.find('.sm-upload-step').removeClass('is-active');
            $(steps[currentStepIndex]).addClass('is-active');
            updateProgressUI();

            if (scroll !== false) {
                var $main = $('.sm-upload-main');
                if ($main.length) {
                    $('html, body').animate({ scrollTop: $main.offset().top - 20 }, 180);
                }
            }
        }

        function findStepForElement($element) {
            var $step = $element.closest('.sm-upload-step');
            if (!$step.length) {
                return -1;
            }

            return steps.indexOf($step.get(0));
        }

        function validateUploadStep($step) {
            var valid = true;

            if (activeType === 'videos') {
                var linkType = $('input[name="meta[link_type]"]').val() || 'upload';
                if (linkType !== 'upload') {
                    var $url = $('input[name="meta[external_url]"]');
                    if (!$url.val()) {
                        valid = false;
                        markInvalid($url, 'Please provide a lesson URL or switch to file upload.');
                    }
                    return valid;
                }
            }

            var $file = $('#materialFileInput');
            if (!isEdit && (!$file.get(0) || !$file.get(0).files || !$file.get(0).files.length)) {
                valid = false;
                markInvalid($file, 'Please upload a file.');
                toast('warning', 'Please upload a file before continuing.');
            }

            return valid;
        }

        function validateStep($step) {
            clearStepErrors($step);
            var valid = true;
            var stepId = $step.data('step-id');

            if (stepId === 'type') {
                if (!activeType || !$step.find('.sm-upload-type-card.is-active').length) {
                    valid = false;
                    toast('warning', 'Please select a material type.');
                }
            }

            if (stepId === 'role') {
                if (!$step.find('input[name="meta[uploader_role]"]:checked').length) {
                    valid = false;
                    markInvalid($step.find('input[name="meta[uploader_role]"]').first(), 'Please select who is uploading.');
                }
            }

            if (stepId === 'details') {
                $step.find('input[required], textarea[required], select[required]').each(function () {
                    var $field = $(this);
                    var $typeGroup = $field.closest('[data-types]');
                    if ($typeGroup.length && $typeGroup.hasClass('d-none')) {
                        return;
                    }
                    if (!$field.is(':visible')) {
                        return;
                    }

                    if (!$field.val() || !String($field.val()).trim()) {
                        valid = false;
                        markInvalid($field, 'This field is required.');
                    }
                });
            }

            if (stepId === 'upload') {
                valid = validateUploadStep($step) && valid;
            }

            if (stepId === 'terms') {
                var $terms = $('#termsAccepted');
                if (!$terms.is(':checked')) {
                    valid = false;
                    markInvalid($terms, 'You must agree to the Terms & Conditions.');
                }
            }

            if (!valid) {
                toast('warning', 'Please fix the highlighted fields on this step.');
            }

            return valid;
        }

        function validateAllSteps() {
            var valid = true;
            for (var i = 0; i < steps.length; i++) {
                if (!validateStep($(steps[i]))) {
                    valid = false;
                    showStep(i);
                    break;
                }
            }
            return valid;
        }

        function submitViaAjax() {
            if (!window.FormHelper) {
                return;
            }

            if (!validateAllSteps()) {
                return;
            }

            window.FormHelper.clearFormErrors($form);
            $alert.addClass('d-none').removeClass('alert-success alert-danger alert-warning').text('');

            window.FormHelper.setButtonLoading($submitBtn, true, isEdit ? 'Updating...' : 'Submitting...', config.submitText || 'Submit for Review');

            var formEl = $form.get(0);
            var requestData = new FormData(formEl);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: requestData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || ''
                }
            }).done(function (response) {
                var message = response.message || (isEdit
                    ? 'Study material updated and sent for admin approval.'
                    : 'Study material submitted for admin approval.');

                toast('success', message);
                window.FormHelper.showAlert($alert, 'success', message);

                window.setTimeout(function () {
                    window.location.href = response.redirect || config.indexUrl;
                }, 900);
            }).fail(function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var firstField = window.FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
                    var validationMessage = xhr.responseJSON.message || 'Please fix the highlighted fields and try again.';
                    toast('warning', validationMessage);
                    window.FormHelper.showAlert($alert, 'warning', validationMessage);

                    if (firstField && firstField.length) {
                        var stepIndex = findStepForElement(firstField);
                        if (stepIndex >= 0) {
                            showStep(stepIndex);
                        }
                        firstField.trigger('focus');
                    }
                    return;
                }

                var message = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Unable to save study material. Please try again.';

                toast('error', message);
                window.FormHelper.showAlert($alert, 'danger', message);
            }).always(function () {
                window.FormHelper.setButtonLoading($submitBtn, false, isEdit ? 'Updating...' : 'Submitting...', config.submitText || 'Submit for Review');
            });
        }

        $('.sm-upload-role-card').on('click', function () {
            var $card = $(this);
            $card.find('input[type="radio"]').prop('checked', true);
            $('.sm-upload-role-card').removeClass('is-active');
            $card.addClass('is-active');
        });

        $('.sm-upload-role-card input:checked').closest('.sm-upload-role-card').addClass('is-active');

        $('[data-char-max]').each(function () {
            var $field = $(this);
            var max = Number($field.data('char-max') || 1000);
            var $counter = $($field.data('char-counter'));
            var update = function () {
                var len = String($field.val() || '').length;
                if ($counter.length) {
                    $counter.text(len + '/' + max + ' characters');
                }
            };
            $field.on('input', update);
            update();
        });

        function bindDropzone(zoneSelector, inputSelector) {
            var $zone = $(zoneSelector);
            var $input = $(inputSelector);
            if (!$zone.length || !$input.length) {
                return;
            }

            var $name = $zone.find('.sm-upload-dropzone__file-name');

            $zone.on('click', function (e) {
                if ($(e.target).is('button, a, input, label')) {
                    return;
                }
                $input.trigger('click');
            });

            $zone.on('dragover dragenter', function (e) {
                e.preventDefault();
                $zone.addClass('is-dragover');
            });

            $zone.on('dragleave dragend drop', function (e) {
                e.preventDefault();
                $zone.removeClass('is-dragover');
            });

            $zone.on('drop', function (e) {
                var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
                if (!files || !files.length) {
                    return;
                }
                $input[0].files = files;
                $input.trigger('change');
            });

            $input.on('change', function () {
                var file = this.files && this.files[0];
                if ($name.length) {
                    $name.text(file ? file.name : '');
                }
                $(this).removeClass('is-invalid');
                $(this).closest('.sm-upload-step').find('.ajax-error').remove();
            });
        }

        bindDropzone('#fileDropzone', '#materialFileInput');
        bindDropzone('#coverDropzone', '#materialCoverInput');

        $('.sm-upload-link-tab').on('click', function () {
            var tab = $(this).data('link-tab');
            $('.sm-upload-link-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $('.js-link-panel').addClass('d-none');
            if (tab === 'upload') {
                $('.js-link-panel[data-link-panel="upload"]').removeClass('d-none');
                $('.js-file-dropzone').removeClass('d-none');
            } else {
                $('.js-link-panel[data-link-panel="link"]').removeClass('d-none');
                $('.js-file-dropzone').addClass('d-none');
            }
            $('input[name="meta[link_type]"]').val(tab);
        });

        $('input[name="meta[is_series]"]').on('change', function () {
            $('.js-series-part').toggleClass('d-none', !this.checked);
        }).trigger('change');

        if (!isEdit) {
            $('.sm-upload-type-card').on('click', function () {
                var type = $(this).data('type');
                if (!type || type === activeType) {
                    return;
                }

                switchMaterialType(type);
            });
        }

        $nextBtn.on('click', function () {
            var $step = $(steps[currentStepIndex]);
            if (!validateStep($step)) {
                return;
            }
            showStep(currentStepIndex + 1);
        });

        $prevBtn.on('click', function () {
            showStep(currentStepIndex - 1);
        });

        $progress.on('click', '.sm-upload-wizard__pill', function () {
            var targetIndex = Number($(this).data('step-index'));
            if (targetIndex <= currentStepIndex) {
                showStep(targetIndex);
                return;
            }

            for (var i = currentStepIndex; i < targetIndex; i++) {
                if (!validateStep($(steps[i]))) {
                    showStep(i);
                    return;
                }
            }
            showStep(targetIndex);
        });

        $submitBtn.on('click', function (e) {
            e.preventDefault();
            submitViaAjax();
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            if (currentStepIndex >= steps.length - 1) {
                submitViaAjax();
            } else {
                $nextBtn.trigger('click');
            }
        });

        if (window.FormHelper && typeof window.FormHelper.ensureButtonParts === 'function') {
            window.FormHelper.ensureButtonParts($submitBtn);
        }

        collectSteps();
        loadInitialTypeConfig();
        showStep(0, false);
    }

    window.EducatorMaterialUpload = {
        init: initMaterialUploadForm
    };
})(window.jQuery, window);

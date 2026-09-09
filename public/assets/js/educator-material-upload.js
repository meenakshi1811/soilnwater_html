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

        $page.attr('data-active-type', activeType);

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
                return $(this).find(':input, textarea, select, .sm-upload-dropzone').length > 0;
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

            if (stepId === 'role') {
                if (!$step.find('input[name="meta[uploader_role]"]:checked').length) {
                    valid = false;
                    markInvalid($step.find('input[name="meta[uploader_role]"]').first(), 'Please select who is uploading.');
                }
            }

            if (stepId === 'details') {
                $step.find('input[required], textarea[required], select[required]').each(function () {
                    var $field = $(this);
                    if (!$field.is(':visible') || $field.closest('[data-types]').length && !$field.closest('[data-types]').is(':visible')) {
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
            } else {
                $('.js-link-panel[data-link-panel="link"]').removeClass('d-none');
            }
            $('input[name="meta[link_type]"]').val(tab);
        });

        $('input[name="meta[is_series]"]').on('change', function () {
            $('.js-series-part').toggleClass('d-none', !this.checked);
        }).trigger('change');

        if (!isEdit) {
            $('.sm-upload-type-card').on('click', function (e) {
                e.preventDefault();
                var type = $(this).data('type');
                if (!type || type === activeType) {
                    return;
                }

                var url = new URL(window.location.href);
                url.searchParams.set('type', type);
                window.location.href = url.toString();
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
        showStep(0, false);
    }

    window.EducatorMaterialUpload = {
        init: initMaterialUploadForm
    };
})(window.jQuery, window);

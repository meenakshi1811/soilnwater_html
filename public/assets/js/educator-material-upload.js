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

        $page.attr('data-active-type', activeType);

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

        if (window.FormHelper) {
            window.FormHelper.attachAjaxForm({
                formSelector: '#studyMaterialForm',
                buttonSelector: '#studyMaterialSubmitBtn',
                alertSelector: '#studyMaterialAlert',
                defaultText: config.submitText || 'Submit for Review',
                loadingText: isEdit ? 'Updating...' : 'Submitting...',
                validationMessage: 'Please fix the highlighted fields and try again.',
                fallbackErrorMessage: 'Unable to save study material. Please try again.',
                beforeSubmit: function () {
                    if (config.beforeSubmit) {
                        config.beforeSubmit();
                    }
                },
                onInvalid: function () {
                    window.FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                },
                onValidationError: function (xhr, message) {
                    window.FormHelper.showToast('warning', message);
                    window.FormHelper.showAlert($('#studyMaterialAlert'), 'warning', message);
                },
                onError: function (xhr, message) {
                    window.FormHelper.showToast('error', message);
                    window.FormHelper.showAlert($('#studyMaterialAlert'), 'danger', message);
                },
                onSuccess: function (response) {
                    var message = response.message || (isEdit
                        ? 'Study material updated and sent for admin approval.'
                        : 'Study material submitted for admin approval.');

                    window.FormHelper.showToast('success', message);
                    window.FormHelper.showAlert($('#studyMaterialAlert'), 'success', message);

                    window.setTimeout(function () {
                        window.location.href = response.redirect || config.indexUrl;
                    }, 900);
                },
                rules: {
                    title: { required: true, maxlength: 255 },
                    material_type: { required: true },
                    terms_accepted: { required: true },
                    file: isEdit ? {} : {
                        required: function () {
                            if (activeType !== 'videos') {
                                return true;
                            }
                            return !$('input[name="meta[external_url]"]').val();
                        }
                    }
                },
                messages: {
                    title: { required: 'Please enter a title.' },
                    material_type: { required: 'Please select a material type.' },
                    terms_accepted: { required: 'You must agree to the Terms & Conditions.' },
                    file: { required: 'Please upload a file or provide a valid link.' }
                }
            });
        }
    }

    window.EducatorMaterialUpload = {
        init: initMaterialUploadForm
    };
})(window.jQuery, window);

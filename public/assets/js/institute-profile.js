(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    $(function () {
        var $form = $('#schoolInstituteProfileForm');
        if (!$form.length || !window.FormHelper) {
            return;
        }

        var sections = ['grades', 'facilities', 'gallery'];

        function galleryRemainingCount() {
            var $existing = $('#galleryExistingWrap .inst-gallery-manage-card');
            if (!$existing.length) {
                return 0;
            }
            var removed = $('#galleryExistingWrap input[name="removed_gallery[]"]:checked:enabled').length;
            return Math.max(0, $existing.length - removed);
        }

        function galleryUploadCount() {
            var input = document.getElementById('gallery_uploads');
            if (!input || input.disabled) {
                return 0;
            }
            return (input.files || []).length;
        }

        $.validator.addMethod('gallerySectionFilled', function () {
            if (!$('#gallery_section_enabled').is(':checked')) {
                return true;
            }
            return galleryRemainingCount() + galleryUploadCount() >= 1;
        }, 'Add at least one gallery photo or video when the gallery section is enabled.');

        function syncRepeatFieldRules(section) {
            var enabled = $('#' + section + '_section_enabled').is(':checked');

            if (section === 'grades') {
                $('#gradesWrap .js-grade-class').each(function () {
                    var $input = $(this);
                    $input.rules('remove');
                    if (enabled) {
                        $input.rules('add', {
                            required: true,
                            maxlength: 80,
                            messages: {
                                required: 'Each class name is required.',
                                maxlength: 'Class name cannot exceed 80 characters.'
                            }
                        });
                    }
                });
                $('#gradesWrap .js-grade-sections, #gradesWrap .js-grade-students').each(function () {
                    var $input = $(this);
                    var isSections = $input.hasClass('js-grade-sections');
                    $input.rules('remove');
                    if (enabled) {
                        $input.rules('add', {
                            number: true,
                            min: 0,
                            max: isSections ? 999 : 9999,
                            messages: {
                                number: 'Please enter a valid number.',
                                min: 'Value cannot be negative.',
                                max: isSections
                                    ? 'Number of sections cannot exceed 999.'
                                    : 'Students per section cannot exceed 9999.'
                            }
                        });
                    }
                });
                return;
            }

            $('#facilitiesWrap input[name="facilities[]"]').each(function () {
                var $input = $(this);
                $input.rules('remove');
                if (enabled) {
                    $input.rules('add', {
                        required: true,
                        maxlength: 120,
                        messages: {
                            required: 'Each facility entry is required.'
                        }
                    });
                }
            });
        }

        function nextGradeRowIndex() {
            var maxIndex = -1;
            $('#gradesWrap .js-grade-class').each(function () {
                var name = this.getAttribute('name') || '';
                var match = name.match(/grades_offered\[(\d+)]\[class]/);
                if (match) {
                    maxIndex = Math.max(maxIndex, parseInt(match[1], 10));
                }
            });
            return maxIndex + 1;
        }

        function gradeRowHtml(index) {
            return (
                '<div class="inst-grade-row js-repeat-row js-grade-row mb-2" data-section="grades">' +
                '<div class="row g-2 align-items-start">' +
                '<div class="col-md-5">' +
                '<label class="form-label d-md-none small text-muted mb-1">Class</label>' +
                '<input type="text" class="form-control js-section-field js-grade-class" name="grades_offered[' + index + '][class]" placeholder="e.g. Class 1" data-section="grades">' +
                '</div>' +
                '<div class="col-md-3">' +
                '<label class="form-label d-md-none small text-muted mb-1">Number of sections</label>' +
                '<input type="number" min="0" max="999" class="form-control js-section-field js-grade-sections" name="grades_offered[' + index + '][sections]" placeholder="e.g. 3" inputmode="numeric" data-section="grades">' +
                '</div>' +
                '<div class="col-md-3">' +
                '<label class="form-label d-md-none small text-muted mb-1">Students per section</label>' +
                '<input type="number" min="0" max="9999" class="form-control js-section-field js-grade-students" name="grades_offered[' + index + '][students_per_section]" placeholder="e.g. 40" inputmode="numeric" data-section="grades">' +
                '</div>' +
                '<div class="col-md-1 d-flex justify-content-md-center">' +
                '<button type="button" class="btn btn-outline-danger js-remove-row mt-md-4" aria-label="Remove grade">&times;</button>' +
                '</div></div></div>'
            );
        }

        function bindGradesRepeat() {
            var addBtn = document.getElementById('addGradeRow');
            var wrap = document.getElementById('gradesWrap');
            if (!addBtn || !wrap) {
                return;
            }

            addBtn.addEventListener('click', function () {
                if (addBtn.disabled) {
                    return;
                }
                wrap.insertAdjacentHTML('beforeend', gradeRowHtml(nextGradeRowIndex()));
                syncRepeatFieldRules('grades');
            });

            wrap.addEventListener('click', function (e) {
                if (e.target.closest('.js-remove-row')) {
                    var rows = wrap.querySelectorAll('.js-repeat-row');
                    var row = e.target.closest('.js-repeat-row');
                    if (rows.length > 1) {
                        row.remove();
                    }
                    syncRepeatFieldRules('grades');
                }
            });
        }

        function syncSectionToggle(section) {
            var $toggle = $('#' + section + '_section_enabled');
            var enabled = $toggle.is(':checked');
            var $body = $('[data-section-body="' + section + '"]');

            $body.toggleClass('d-none', !enabled);
            $body.find('input, button, textarea, select').not($toggle).prop('disabled', !enabled);

            if (section === 'grades' || section === 'facilities') {
                syncRepeatFieldRules(section);
            }

            if (section === 'gallery') {
                var validator = $form.data('validator');
                if (validator) {
                    $('#gallerySectionValidator').valid();
                }
            }
        }

        function bindRepeat(addBtnId, wrapId, inputName, placeholder, section) {
            var addBtn = document.getElementById(addBtnId);
            var wrap = document.getElementById(wrapId);
            if (!addBtn || !wrap) {
                return;
            }

            addBtn.addEventListener('click', function () {
                if (addBtn.disabled) {
                    return;
                }
                var row = document.createElement('div');
                row.className = 'input-group mb-2 js-repeat-row';
                row.innerHTML =
                    '<input type="text" class="form-control js-section-field" name="' + inputName + '" placeholder="' + placeholder + '" data-section="' + section + '">' +
                    '<button type="button" class="btn btn-outline-danger js-remove-row">&times;</button>';
                wrap.appendChild(row);
                syncRepeatFieldRules(section);
            });

            wrap.addEventListener('click', function (e) {
                if (e.target.closest('.js-remove-row')) {
                    var rows = wrap.querySelectorAll('.js-repeat-row');
                    var row = e.target.closest('.js-repeat-row');
                    if (rows.length > 1) {
                        row.remove();
                    }
                    syncRepeatFieldRules(section);
                }
            });
        }

        bindGradesRepeat();
        bindRepeat('addFacilityRow', 'facilitiesWrap', 'facilities[]', 'e.g. Library', 'facilities');

        $('.js-profile-section-toggle').on('change', function () {
            syncSectionToggle($(this).data('section'));
        });

        var galleryInput = document.getElementById('gallery_uploads');
        var galleryError = document.getElementById('instGalleryUploadError');
        if (galleryInput) {
            galleryInput.addEventListener('change', function () {
                if (!galleryError) {
                    return;
                }
                var imageMax = Number(galleryInput.dataset.imageMax || 0);
                var videoMax = Number(galleryInput.dataset.videoMax || 0);
                var imageLabel = galleryInput.dataset.imageLabel || '2 MB';
                var videoLabel = galleryInput.dataset.videoLabel || '20 MB';
                var invalid = [];

                Array.from(galleryInput.files || []).forEach(function (file) {
                    var isVideo = (file.type || '').indexOf('video/') === 0;
                    var maxBytes = isVideo ? videoMax : imageMax;
                    var label = isVideo ? videoLabel : imageLabel;
                    if (maxBytes > 0 && file.size > maxBytes) {
                        invalid.push(file.name + ' exceeds ' + label);
                    }
                });

                if (invalid.length) {
                    galleryError.textContent = invalid.join(' ');
                    galleryError.classList.remove('d-none');
                    galleryInput.value = '';
                    return;
                }

                galleryError.classList.add('d-none');
                galleryError.textContent = '';
                $('#gallerySectionValidator').valid();
            });
        }

        $(document).on('change', '#galleryExistingWrap input[name="removed_gallery[]"]', function () {
            $('#gallerySectionValidator').valid();
        });

        var hasLogo = String($form.data('has-logo') || '') === '1';

        FormHelper.attachAjaxForm({
            formSelector: '#schoolInstituteProfileForm',
            buttonSelector: '#schoolInstituteProfileSubmitBtn',
            alertSelector: '#schoolInstituteProfileAlert',
            defaultText: 'Save profile',
            loadingText: 'Saving...',
            fallbackErrorMessage: 'Unable to update profile right now. Please try again.',
            validationMessage: 'Please fix the highlighted fields and try again.',
            rules: $.extend({
                institution_name: { required: true, maxlength: 255 },
                contact_person: { required: true, maxlength: 255 },
                institution_type: { required: true },
                board_affiliation: { required: true, maxlength: 255 },
                tagline: { required: true, maxlength: 255 },
                about: { required: true, minlength: 10 },
                whatsapp_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                address: { required: true, minlength: 5, maxlength: 500 },
                city: { required: true, maxlength: 120 },
                state: { required: true, maxlength: 120 },
                pincode: { required: true, digits: true, minlength: 4, maxlength: 10 },
                date_of_establishment: { required: true, date: true },
                logo: {
                    required: function () {
                        return !hasLogo;
                    }
                },
                gallery_section_validator: { gallerySectionFilled: true }
            }, window.ProfilePhoneNumbers ? ProfilePhoneNumbers.validationRules() : {
                phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 }
            }),
            messages: $.extend({
                institution_name: { required: 'Please enter the institution name.' },
                contact_person: { required: 'Please enter the contact person name.' },
                institution_type: { required: 'Please select an institution type.' },
                board_affiliation: { required: 'Please enter board or affiliation.' },
                tagline: { required: 'Please enter a tagline.' },
                about: {
                    required: 'Please enter the about section.',
                    minlength: 'About must be at least 10 characters.'
                },
                whatsapp_number: {
                    required: 'Please enter your WhatsApp number.',
                    digits: 'WhatsApp number should contain only digits.',
                    minlength: 'WhatsApp number must be at least 10 digits.',
                    maxlength: 'WhatsApp number cannot exceed 15 digits.'
                },
                address: {
                    required: 'Please enter your address.',
                    minlength: 'Address must be at least 5 characters.'
                },
                city: { required: 'Please enter your city.' },
                state: { required: 'Please enter your state.' },
                pincode: {
                    required: 'Please enter your pincode.',
                    digits: 'Pincode should contain only digits.',
                    minlength: 'Pincode must be at least 4 digits.',
                    maxlength: 'Pincode cannot exceed 10 digits.'
                },
                date_of_establishment: {
                    required: 'Please enter the founded date.',
                    date: 'Please enter a valid founded date.'
                },
                logo: { required: 'Please upload your institution logo.' }
            }, window.ProfilePhoneNumbers ? ProfilePhoneNumbers.validationMessages() : {
                phone_number: {
                    required: 'Please enter your phone number.',
                    digits: 'Phone number should contain only digits.',
                    minlength: 'Phone number must be at least 10 digits.',
                    maxlength: 'Phone number cannot exceed 15 digits.'
                }
            }),
            beforeSubmit: function (ctx) {
                sections.forEach(syncSectionToggle);
                FormHelper.profileFormBeforeSubmit(ctx);
            },
            onInvalid: function () {
                FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
            },
            onValidationError: function (xhr, message) {
                FormHelper.showToast('warning', message);
                FormHelper.showAlert($('#schoolInstituteProfileAlert'), 'warning', message);
            },
            onError: function (xhr, message) {
                FormHelper.showToast('error', message);
                FormHelper.showAlert($('#schoolInstituteProfileAlert'), 'danger', message);
            },
            onSuccess: function (response) {
                var message = response.message || 'Profile updated successfully.';
                FormHelper.showToast('success', message);
                FormHelper.showAlert($('#schoolInstituteProfileAlert'), 'success', message);

                if (response.reload) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });

        sections.forEach(syncSectionToggle);
    });
})(window.jQuery);

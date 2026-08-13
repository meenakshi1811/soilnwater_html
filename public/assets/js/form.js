(function ($) {
    if (!$) {
        return;
    }

    var FormHelper = {
        alertTimers: {},
        toastTimers: {},

        hideStackedAlerts: function ($alert) {
            if (!$alert || !$alert.length) {
                return;
            }

            var $scope = $alert.closest('.card-body');
            if (!$scope.length) {
                $scope = $alert.parent();
            }

            $scope.find('.alert, .login-alert-floating').not($alert).addClass('d-none');
        },

        autoHideAlert: function ($alert, type) {
            if (!$alert || !$alert.length) {
                return;
            }

            var shouldAutoHide = type === 'success' || type === 'danger';
            var alertId = $alert.attr('id') || $alert.data('alert-key') || ('alert-' + Math.random().toString(36).slice(2));
            $alert.data('alert-key', alertId);

            if (this.alertTimers[alertId]) {
                clearTimeout(this.alertTimers[alertId]);
                delete this.alertTimers[alertId];
            }

            if (!shouldAutoHide) {
                return;
            }

            this.alertTimers[alertId] = setTimeout(function () {
                $alert.addClass('d-none').empty();
            }, 10000);
        },

        ensureButtonParts: function ($button) {
            var $text = $button.find('.btn-text');
            if (!$text.length) {
                var currentHtml = $.trim($button.html());
                $button.html('<span class="btn-text">' + currentHtml + '</span>');
                $text = $button.find('.btn-text');
            }

            var $loader = $button.find('.btn-loader');
            if (!$loader.length) {
                $loader = $('<span class="btn-loader d-none" aria-hidden="true"></span>');
                $button.append(' ');
                $button.append($loader);
            }

            return { text: $text, loader: $loader };
        },

        setButtonLoading: function ($button, isLoading, loadingText, defaultText) {
            if (!$button || !$button.length) {
                return;
            }

            var parts = this.ensureButtonParts($button);
            $button.prop('disabled', isLoading);
            parts.text.text(isLoading ? loadingText : defaultText);
            parts.loader.toggleClass('d-none', !isLoading);
        },

        showAlert: function ($alert, type, message) {
            if (!$alert || !$alert.length) {
                return;
            }
            this.hideStackedAlerts($alert);
            $alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .text(message);
            this.autoHideAlert($alert, type);
        },

        showAlertHtml: function ($alert, type, html) {
            if (!$alert || !$alert.length) {
                return;
            }
            this.hideStackedAlerts($alert);
            $alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .html(html);
            this.autoHideAlert($alert, type);
        },

        getToastContainer: function () {
            var containerId = 'jqueryToastContainer';
            var $container = $('#' + containerId);
            if ($container.length) {
                return $container;
            }

            $container = $('<div id="' + containerId + '" class="jquery-toast-container" aria-live="polite" aria-atomic="true"></div>');
            $('body').append($container);
            return $container;
        },

        showToast: function (type, message) {
            var toastrType = type === 'danger' ? 'error' : type;
            if (window.toastr && typeof window.toastr[toastrType] === 'function') {
                window.toastr.options = $.extend({}, window.toastr.options || {}, {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 4500
                });
                window.toastr[toastrType](message || '');
                return;
            }

            var styles = {
                success: '#198754',
                danger: '#dc3545',
                warning: '#fd7e14',
                info: '#0d6efd'
            };
            var bg = styles[type] || styles.info;
            var toastId = 'toast-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
            var $container = this.getToastContainer();
            var $toast = $(
                '<div id="' + toastId + '" class="jquery-toast-item" role="status">' +
                    '<button type="button" class="jquery-toast-close" aria-label="Close">&times;</button>' +
                    '<div class="jquery-toast-message"></div>' +
                '</div>'
            );

            $toast.css({
                backgroundColor: bg,
                color: '#fff',
                padding: '12px 42px 12px 14px',
                borderRadius: '8px',
                boxShadow: '0 10px 24px rgba(0,0,0,0.2)',
                fontSize: '14px',
                lineHeight: '1.4',
                position: 'relative',
                marginTop: '10px',
                minWidth: '260px',
                maxWidth: '380px',
                opacity: 0,
                transform: 'translateY(-8px)',
                transition: 'all 0.2s ease'
            });

            $toast.find('.jquery-toast-message').text(message || '');
            $toast.find('.jquery-toast-close').css({
                position: 'absolute',
                top: '8px',
                right: '10px',
                border: 0,
                background: 'transparent',
                color: '#fff',
                fontSize: '18px',
                lineHeight: 1,
                cursor: 'pointer'
            });

            $container.css({
                position: 'fixed',
                top: '16px',
                right: '16px',
                zIndex: 1080
            });

            $container.append($toast);
            requestAnimationFrame(function () {
                $toast.css({ opacity: 1, transform: 'translateY(0)' });
            });

            var self = this;
            var removeToast = function () {
                $toast.css({ opacity: 0, transform: 'translateY(-8px)' });
                setTimeout(function () {
                    $toast.remove();
                }, 220);
            };

            if (self.toastTimers[toastId]) {
                clearTimeout(self.toastTimers[toastId]);
            }
            self.toastTimers[toastId] = setTimeout(removeToast, 4500);

            $toast.find('.jquery-toast-close').on('click', function () {
                clearTimeout(self.toastTimers[toastId]);
                removeToast();
            });
        },

        clearFormErrors: function ($form) {
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('span.ajax-error').remove();
        },

        renderFieldErrors: function ($form, errors) {
            $.each(errors, function (field, messages) {
                var $input = $form.find('[name="' + field + '"]');
                $input.addClass('is-invalid');
                $('<span class="invalid-feedback d-block ajax-error"></span>')
                    .text(messages[0])
                    .insertAfter($input);
            });
        },

        attachAjaxForm: function (config) {
            var self = this;
            var $form = $(config.formSelector);
            if (!$form.length) {
                return;
            }

            var $button = $(config.buttonSelector);
            var $alert = $(config.alertSelector);
            var defaultText = config.defaultText || 'Submit';
            var loadingText = config.loadingText || 'Please wait...';
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            self.ensureButtonParts($button);

            if (config.showLoaderOnClick === true && $button.length) {
                $button.off('click.ajaxLoader').on('click.ajaxLoader', function () {
                    self.setButtonLoading($button, true, loadingText, defaultText);
                });
            }

            var submitViaAjax = function () {
                    self.clearFormErrors($form);
                    if ($alert.length) {
                        $alert.addClass('d-none').text('');
                    }

                    if (typeof config.beforeSubmit === 'function') {
                        config.beforeSubmit({ form: $form, button: $button, alert: $alert });
                    }

                    self.setButtonLoading($button, true, loadingText, defaultText);

                    var hasFileInputs = $form.find('input[type="file"]').length > 0;
                    var isMultipart = (($form.attr('enctype') || '').toLowerCase() === 'multipart/form-data');
                    var requestData = (hasFileInputs || isMultipart) ? new FormData($form.get(0)) : $form.serialize();

                    $.ajax({
                        url: $form.attr('action'),
                        method: config.method || $form.attr('method') || 'POST',
                        data: requestData,
                        processData: !(hasFileInputs || isMultipart),
                        contentType: (hasFileInputs || isMultipart) ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        }
                    }).done(function (response) {
                        if (typeof config.onSuccess === 'function') {
                            config.onSuccess(response, { form: $form, button: $button, alert: $alert });
                            return;
                        }

                        self.showAlert($alert, 'success', response.message || 'Request successful.');
                    }).fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            self.renderFieldErrors($form, xhr.responseJSON.errors);
                            var validationMessage = config.validationMessage || 'Please fix the highlighted fields and try again.';
                            if (typeof config.onValidationError === 'function') {
                                config.onValidationError(xhr, validationMessage, { form: $form, button: $button, alert: $alert });
                                return;
                            }

                            self.showAlert($alert, 'warning', validationMessage);
                            return;
                        }

                        var message = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : (config.fallbackErrorMessage || 'Something went wrong. Please try again.');

                        if (typeof config.onError === 'function') {
                            config.onError(xhr, message, { form: $form, button: $button, alert: $alert });
                            return;
                        }

                        self.showAlert($alert, 'danger', message);
                    }).always(function () {
                        self.setButtonLoading($button, false, loadingText, defaultText);
                    });
            };

            if ($.fn && typeof $.fn.validate === 'function') {
                $form.validate({
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback d-block');
                        error.insertAfter(element);
                    },
                    highlight: function (element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function (element) {
                        $(element).removeClass('is-invalid');
                    },
                    invalidHandler: function () {
                        self.setButtonLoading($button, false, loadingText, defaultText);
                        if (typeof config.onInvalid === 'function') {
                            config.onInvalid({ form: $form, button: $button, alert: $alert });
                        }
                    },
                    rules: config.rules || {},
                    messages: config.messages || {},
                    submitHandler: submitViaAjax
                });
            } else {
                $form.off('submit.ajaxForm').on('submit.ajaxForm', function (e) {
                    e.preventDefault();
                    submitViaAjax();
                });
            }
        },

        initOtpTimer: function (selector) {
            var $timer = $(selector);
            if (!$timer.length) {
                return;
            }

            var expiresAtRaw = $timer.data('expires-at');
            if (!expiresAtRaw) {
                return;
            }

            var expiresAt = new Date(expiresAtRaw).getTime();
            if (isNaN(expiresAt)) {
                return;
            }

            var updateCountdown = function () {
                var distance = expiresAt - Date.now();

                if (distance <= 0) {
                    $timer.text('00:00 (Expired)');
                    return;
                }

                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                $timer.text(String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0'));
            };

            updateCountdown();
            var existingInterval = $timer.data('timer-interval');
            if (existingInterval) {
                clearInterval(existingInterval);
            }

            var intervalId = setInterval(updateCountdown, 1000);
            $timer.data('timer-interval', intervalId);
        },

        resetOtpTimer: function (selector, expiresAt) {
            var $timer = $(selector);
            if (!$timer.length || !expiresAt) {
                return;
            }

            $timer.attr('data-expires-at', expiresAt);
            $timer.data('expires-at', expiresAt);
            this.initOtpTimer(selector);
        },

        placeAutocompleteComponentValue: function (components, type) {
            var match = (components || []).find(function (component) {
                return component.types.indexOf(type) !== -1;
            });

            return match ? match.long_name : '';
        },

        bindRegisterPlaceAutocomplete: function (options) {
            var addressInput = document.getElementById(options.addressInputId);
            var cityInput = options.cityInputId ? document.getElementById(options.cityInputId) : null;
            var pincodeInput = options.pincodeInputId ? document.getElementById(options.pincodeInputId) : null;
            var latitudeInput = options.latitudeInputId ? document.getElementById(options.latitudeInputId) : null;
            var longitudeInput = options.longitudeInputId ? document.getElementById(options.longitudeInputId) : null;
            var retryMethod = options.retryMethod;
            var onPlaceChanged = typeof options.onPlaceChanged === 'function' ? options.onPlaceChanged : null;

            if (!addressInput || addressInput.dataset.googlePlacesReady === 'true') {
                return;
            }

            if (!window.google || !google.maps || !google.maps.places) {
                var attempts = Number(addressInput.dataset.googlePlacesAttempts || 0);
                if (attempts >= 20) {
                    return;
                }

                addressInput.dataset.googlePlacesAttempts = String(attempts + 1);
                window.setTimeout(function () {
                    if (window.FormHelper && retryMethod) {
                        window.FormHelper[retryMethod](options.onPlaceChanged);
                    }
                }, 500);
                return;
            }

            addressInput.dataset.googlePlacesReady = 'true';

            var fields = ['address_components', 'formatted_address'];
            if (latitudeInput && longitudeInput) {
                fields.push('geometry');
            }

            var autocomplete = new google.maps.places.Autocomplete(addressInput, {
                componentRestrictions: { country: 'in' },
                fields: fields,
                types: ['geocode']
            });

            var self = this;

            autocomplete.addListener('place_changed', function () {
                var place = autocomplete.getPlace();
                var components = place.address_components || [];

                if (place.formatted_address) {
                    addressInput.value = place.formatted_address;
                }

                var city = self.placeAutocompleteComponentValue(components, 'locality')
                    || self.placeAutocompleteComponentValue(components, 'postal_town')
                    || self.placeAutocompleteComponentValue(components, 'administrative_area_level_3')
                    || self.placeAutocompleteComponentValue(components, 'administrative_area_level_2');
                var pincode = self.placeAutocompleteComponentValue(components, 'postal_code');

                if (cityInput && city) {
                    cityInput.value = city;
                    $(cityInput).trigger('input').trigger('change');
                }

                if (pincodeInput && pincode) {
                    pincodeInput.value = pincode;
                    $(pincodeInput).trigger('input').trigger('change');
                }

                if (latitudeInput && longitudeInput && place.geometry && place.geometry.location) {
                    var lat = place.geometry.location.lat();
                    var lng = place.geometry.location.lng();

                    if (typeof lat === 'number' && Number.isFinite(lat)) {
                        latitudeInput.value = String(lat);
                    }

                    if (typeof lng === 'number' && Number.isFinite(lng)) {
                        longitudeInput.value = String(lng);
                    }

                    addressInput.dataset.placeJustSelected = '1';
                }

                $(addressInput).trigger('input').trigger('change');

                if (onPlaceChanged) {
                    onPlaceChanged();
                }
            });
        },

        initRegisterPlaceAutocomplete: function () {
            this.bindRegisterPlaceAutocomplete({
                addressInputId: 'address',
                cityInputId: 'city',
                pincodeInputId: 'pincode',
                retryMethod: 'initRegisterPlaceAutocomplete'
            });
        },

        initGoogleRegisterPlaceAutocomplete: function (onPlaceChanged) {
            this.bindRegisterPlaceAutocomplete({
                addressInputId: 'google_address',
                cityInputId: 'google_city',
                pincodeInputId: 'google_pincode',
                latitudeInputId: 'google_latitude',
                longitudeInputId: 'google_longitude',
                retryMethod: 'initGoogleRegisterPlaceAutocomplete',
                onPlaceChanged: onPlaceChanged
            });
        },

        initRegisterBusinessFields: function () {
            var $role = $('#role');
            var $businessFields = $('#businessRegistrationFields');
            var $pan = $('#pan_number');
            var $gstWrap = $('#gstNumberWrap');
            var $gst = $('#gst_number');
            var $certificate = $('#government_certificate_number');
            var $profileImageWrap = $('#profileImageWrap');
            var $profileImage = $('#profile_image');

            // Only run on registration / Google complete forms — otherwise this clears shared fields like PAN on profile pages.
            if (! $role.length) {
                return;
            }

            if (!$('#registerForm').length && !$('#googleCompleteForm').length) {
                return;
            }

            var isBusinessRole = function () {
                var role = $role.val();
                return role === 'vendor' || role === 'consultant' || role === 'service_provider';
            };
            var toggleGst = function () {
                var showGst = isBusinessRole() && $('input[name="has_gst"]:checked').val() === '1';
                $gstWrap.toggleClass('d-none', !showGst);
                $gst.prop('required', showGst);
                if (!showGst) {
                    $gst.val('');
                }
            };
            var toggleBusinessFields = function () {
                var role = $role.val();
                var showBusinessFields = isBusinessRole();
                var showProfileImage = role === 'user' || role === 'vendor' || role === 'consultant' || role === 'service_provider';
                $businessFields.toggleClass('d-none', !showBusinessFields);
                $profileImageWrap.toggleClass('d-none', !showProfileImage);
                $profileImage.prop('required', showProfileImage);
                $pan.prop('required', showBusinessFields);
                $('input[name="has_gst"]').prop('required', showBusinessFields);
                if (!showProfileImage) {
                    $profileImage.val('');
                }
                if (!showBusinessFields) {
                    $pan.val('');
                    $certificate.val('');
                    $('#has_gst_no').prop('checked', true);
                }
                toggleGst();
            };

            $role.off('change.businessFields').on('change.businessFields', toggleBusinessFields);
            $('input[name="has_gst"]').off('change.businessFields').on('change.businessFields', toggleGst);
            toggleBusinessFields();
        },

        initGoogleCompleteForm: function () {
            if (!document.getElementById('googleCompleteForm')) {
                return;
            }

            this.bindRegisterPlaceAutocomplete({
                addressInputId: 'address',
                cityInputId: 'city',
                pincodeInputId: 'pincode',
                latitudeInputId: 'complete_latitude',
                longitudeInputId: 'complete_longitude',
                retryMethod: 'initGoogleCompleteForm'
            });
            this.initRegisterBusinessFields();
        },

        initAdminCreateUserPlaceAutocomplete: function () {
            if (!document.getElementById('createAddress')) {
                return;
            }

            this.bindRegisterPlaceAutocomplete({
                addressInputId: 'createAddress',
                cityInputId: 'createCity',
                pincodeInputId: 'createPincode',
                latitudeInputId: 'createLatitude',
                longitudeInputId: 'createLongitude',
                retryMethod: 'initAdminCreateUserPlaceAutocomplete'
            });
        },

        initRegisterForm: function () {
            this.initRegisterPlaceAutocomplete();
            this.initRegisterBusinessFields();
            this.attachAjaxForm({
                formSelector: '#registerForm',
                buttonSelector: '#registerSubmitBtn',
                alertSelector: '#registerAlert',
                defaultText: 'Create Account',
                loadingText: 'Creating Account...',
                rules: {
                    fullname: { required: true, minlength: 3, maxlength: 255 },
                    email: { required: true, email: true },
                    whatsapp_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    address: { required: true, minlength: 5, maxlength: 500 },
                    city: { required: true, maxlength: 120 },
                    pincode: { required: true, digits: true, minlength: 4, maxlength: 10 },
                    role: { required: true },
                    pan_number: {
                        required: function () {
                            var role = $('#role').val();
                            return role === 'vendor' || role === 'consultant' || role === 'service_provider';
                        },
                        maxlength: 20
                    },
                    has_gst: {
                        required: function () {
                            var role = $('#role').val();
                            return role === 'vendor' || role === 'consultant' || role === 'service_provider';
                        }
                    },
                    gst_number: {
                        required: function () {
                            return $('input[name="has_gst"]:checked').val() === '1';
                        },
                        maxlength: 20
                    },
                    government_certificate_number: { maxlength: 100 },
                    profile_image: {
                        required: function () {
                            var role = $('#role').val();
                            return role === 'user' || role === 'vendor' || role === 'consultant' || role === 'service_provider';
                        }
                    },
                    date_of_birth: { required: true, date: true },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: '#password' },
                    accept_terms: { required: true }
                },
                messages: {
                    fullname: {
                        required: 'Please enter your full name.',
                        minlength: 'Full name must be at least 3 characters.'
                    },
                    email: {
                        required: 'Please enter your email address.',
                        email: 'Please enter a valid email address.'
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
                    city: {
                        required: 'Please enter your city.'
                    },
                    pincode: {
                        required: 'Please enter your pincode.',
                        digits: 'Pincode should contain only digits.',
                        minlength: 'Pincode must be at least 4 digits.',
                        maxlength: 'Pincode cannot exceed 10 digits.'
                    },
                    role: {
                        required: 'Please select your role.'
                    },
                    pan_number: {
                        required: 'Please enter your PAN number.',
                        maxlength: 'PAN number cannot exceed 20 characters.'
                    },
                    has_gst: {
                        required: 'Please select whether you have a GST number.'
                    },
                    gst_number: {
                        required: 'Please enter your GST number.',
                        maxlength: 'GST number cannot exceed 20 characters.'
                    },
                    government_certificate_number: {
                        maxlength: 'Government certificate number cannot exceed 100 characters.'
                    },
                    date_of_birth: {
                        required: 'Please enter your date of birth.',
                        date: 'Please enter a valid date of birth.'
                    },
                    password: {
                        required: 'Please create a password.',
                        minlength: 'Password must be at least 8 characters long.'
                    },
                    password_confirmation: {
                        required: 'Please confirm your password.',
                        equalTo: 'Password confirmation does not match.'
                    },
                    accept_terms: {
                        required: 'Please accept the terms and conditions to continue.'
                    }
                },
                fallbackErrorMessage: 'Unable to register right now. Please try again.',
                validationMessage: 'Please fix the highlighted fields and try again.',
                beforeSubmit: function () {
                    FormHelper.showToast('info', 'Submitting your registration...');
                },
                onInvalid: function () {
                    FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                },
                onValidationError: function (xhr, message) {
                    FormHelper.showToast('warning', message || 'Please fix the highlighted fields and try again.');
                    FormHelper.showAlert($('#registerAlert'), 'warning', message || 'Please fix the highlighted fields and try again.');
                },
                onError: function (xhr, message) {
                    FormHelper.showToast('danger', message || 'Unable to register right now. Please try again.');
                    FormHelper.showAlert($('#registerAlert'), 'danger', message || 'Unable to register right now. Please try again.');
                },
                onSuccess: function (response) {
                    var successMessage = response.message || 'Registration successful. Redirecting...';
                    FormHelper.showToast('success', successMessage);
                    FormHelper.showAlert($('#registerAlert'), 'success', successMessage);
                    window.setTimeout(function () {
                        window.location.href = response.redirect || '/verification/contact';
                    }, 1500);
                }
            });
        },

        initLoginForms: function () {
            this.attachAjaxForm({
                formSelector: '#passwordLoginForm',
                buttonSelector: '#passwordSubmitBtn',
                alertSelector: '#loginAlert',
                defaultText: 'Login with Password',
                loadingText: 'Signing in...',
                rules: {
                    login: { required: true, email: true },
                    password: { required: true, minlength: 8 }
                },
                messages: {
                    login: {
                        required: 'Please enter your email address.',
                        email: 'Please enter a valid email address.'
                    },
                    password: {
                        required: 'Please enter your password.',
                        minlength: 'Password must be at least 8 characters long.'
                    }
                },
                fallbackErrorMessage: 'Unable to sign in right now. Please try again.',
                beforeSubmit: function (ctx) {
                    var $loginInput = ctx.form.find('[name="login"]');
                    if ($loginInput.length) {
                        $loginInput.val($.trim($loginInput.val()));
                    }

                    if ($loginInput.length && $loginInput.val()) {
                        return;
                    }

                    var legacyLoginValue = $.trim(ctx.form.find('[name="email"]').val() || '');
                    if (!legacyLoginValue) {
                        return;
                    }

                    if ($loginInput.length) {
                        $loginInput.val(legacyLoginValue);
                        return;
                    }

                    $('<input type="hidden" name="login">')
                        .val(legacyLoginValue)
                        .appendTo(ctx.form);
                },
                onError: function (xhr, message) {
                    var firstError = message;
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        if (xhr.responseJSON.errors.login && xhr.responseJSON.errors.login[0]) {
                            firstError = xhr.responseJSON.errors.login[0];
                        } else if (xhr.responseJSON.errors.email && xhr.responseJSON.errors.email[0]) {
                            firstError = xhr.responseJSON.errors.email[0];
                        }
                    }
                    if (xhr.responseJSON && xhr.responseJSON.verification_redirect) {
                        var verificationLink = xhr.responseJSON.verification_redirect;
                        FormHelper.showAlertHtml(
                            $('#loginAlert'),
                            'warning',
                            firstError + ' <a href="' + verificationLink + '" class="fw-semibold ms-1">Verify your account</a>'
                        );
                        return;
                    }

                    FormHelper.showAlert($('#loginAlert'), 'warning', firstError);
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#loginAlert'), 'success', 'Login successful. Redirecting...');
                    window.location.href = response.redirect || '/home';
                }
            });

            this.attachAjaxForm({
                formSelector: '#otpSendForm',
                buttonSelector: '#otpSendBtn',
                alertSelector: '#loginAlert',
                defaultText: 'Send OTP',
                loadingText: 'Sending OTP...',
                rules: {
                    login_contact: { required: true, email: true }
                },
                messages: {
                    login_contact: {
                        required: 'Please enter your email address.',
                        email: 'Please enter a valid email address.'
                    }
                },
                fallbackErrorMessage: 'Unable to send OTP right now. Please try again.',
                onError: function (xhr, message) {
                    var firstError = message;
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        if (xhr.responseJSON.errors.login_contact && xhr.responseJSON.errors.login_contact[0]) {
                            firstError = xhr.responseJSON.errors.login_contact[0];
                        } else if (xhr.responseJSON.errors.email && xhr.responseJSON.errors.email[0]) {
                            firstError = xhr.responseJSON.errors.email[0];
                        }
                    }

                    if (xhr.responseJSON && xhr.responseJSON.verification_redirect) {
                        var verificationLink = xhr.responseJSON.verification_redirect;
                        FormHelper.showAlertHtml(
                            $('#loginAlert'),
                            'warning',
                            firstError + ' <a href="' + verificationLink + '" class="fw-semibold ms-1">Verify your account</a>'
                        );
                        return;
                    }

                    FormHelper.showAlert($('#loginAlert'), 'warning', firstError);
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#loginAlert'), 'success', response.message || 'OTP sent successfully. Redirecting...');
                    window.location.href = response.redirect || '/login/otp';
                }
            });

            this.attachAjaxForm({
                formSelector: '#resendVerificationForm',
                buttonSelector: '#resendVerificationBtn',
                alertSelector: '#loginAlert',
                defaultText: 'Resend Verification Email',
                loadingText: 'Sending verification link...',
                rules: {
                    email: { required: true, email: true }
                },
                fallbackErrorMessage: 'Unable to send verification email right now. Please try again.'
            });
        },

        initOtpVerifyForm: function () {
            this.attachAjaxForm({
                formSelector: '#otpVerifyForm',
                buttonSelector: '#otpVerifyBtn',
                alertSelector: '#otpAlert',
                defaultText: 'Verify & Login',
                loadingText: 'Verifying...',
                rules: {
                    otp: { required: true, digits: true, minlength: 6, maxlength: 6 }
                },
                messages: {
                    otp: {
                        required: 'Please enter the OTP sent to your email.',
                        digits: 'OTP must contain only numbers.',
                        minlength: 'OTP must be 6 digits.',
                        maxlength: 'OTP must be 6 digits.'
                    }
                },
                fallbackErrorMessage: 'Unable to verify OTP right now. Please try again.',
                onError: function (xhr, message) {
                    if (xhr.responseJSON && xhr.responseJSON.verification_redirect) {
                        FormHelper.showAlertHtml(
                            $('#otpAlert'),
                            'warning',
                            message + ' <a href="' + xhr.responseJSON.verification_redirect + '" class="fw-semibold ms-1">Verify your account</a>'
                        );
                        return;
                    }

                    FormHelper.showAlert($('#otpAlert'), 'danger', message);
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#otpAlert'), 'success', 'OTP verified. Redirecting...');
                    window.location.href = response.redirect || '/home';
                }
            });

            this.attachAjaxForm({
                formSelector: '#contactVerifyForm',
                buttonSelector: '#contactVerifyBtn',
                alertSelector: '#contactVerifyAlert',
                defaultText: 'Verify Email',
                loadingText: 'Verifying...',
                rules: {
                    email_otp: { required: true, digits: true, minlength: 6, maxlength: 6 }
                },
                messages: {
                    email_otp: {
                        required: 'Please enter the email verification code.',
                        digits: 'Email code must contain only numbers.',
                        minlength: 'Email code must be 6 digits.',
                        maxlength: 'Email code must be 6 digits.'
                    }
                },
                fallbackErrorMessage: 'Unable to verify code right now. Please try again.',
                onSuccess: function (response) {
                    FormHelper.showAlert($('#contactVerifyAlert'), 'success', response.message || 'Verified. Redirecting...');
                    window.location.href = response.redirect || '/login';
                }
            });

            this.attachAjaxForm({
                formSelector: '#contactResendForm',
                buttonSelector: '#contactResendBtn',
                alertSelector: '#contactVerifyAlert',
                defaultText: 'Resend Verification Code',
                loadingText: 'Sending code...',
                fallbackErrorMessage: 'Unable to resend code right now. Please try again.',
                onSuccess: function (response) {
                    FormHelper.showAlert($('#contactVerifyAlert'), 'success', response.message || 'Verification code resent successfully.');

                    if (response.expires_at) {
                        FormHelper.resetOtpTimer('#otp-timer', response.expires_at);
                    }
                }
            });
        },

        initPhoneVerificationForm: function () {
            this.attachAjaxForm({
                formSelector: '#phoneOtpSendForm',
                buttonSelector: '#phoneOtpSendBtn',
                alertSelector: '#phoneVerifyAlert',
                defaultText: 'Send OTP',
                loadingText: 'Sending OTP...',
                rules: {
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 }
                },
                messages: {
                    phone_number: {
                        required: 'Please enter your mobile number.',
                        digits: 'Mobile number should contain only digits.',
                        minlength: 'Mobile number must be at least 10 digits.',
                        maxlength: 'Mobile number cannot exceed 15 digits.'
                    }
                },
                fallbackErrorMessage: 'Unable to send OTP right now. Please try again.',
                beforeSubmit: function (ctx) {
                    var $phoneInput = ctx.form.find('[name="phone_number"]');
                    if (!$phoneInput.length) {
                        return;
                    }

                    var normalizedPhoneNumber = $.trim($phoneInput.val() || '').replace(/\D+/g, '');
                    $phoneInput.val(normalizedPhoneNumber);
                },
                onSuccess: function (response, ctx) {
                    FormHelper.showAlert($('#phoneVerifyAlert'), 'success', response.message || 'OTP sent successfully.');

                    if (ctx && ctx.form && ctx.form.length) {
                        var currentPhoneNumber = $.trim(ctx.form.find('[name="phone_number"]').val() || '');
                        $('form#phoneOtpVerifyForm').find('input[name="phone_number"]').val(currentPhoneNumber);
                    }

                    if (response.expires_at) {
                        FormHelper.resetOtpTimer('#otp-timer', response.expires_at);
                    }
                }
            });

            this.attachAjaxForm({
                formSelector: '#phoneOtpVerifyForm',
                buttonSelector: '#phoneOtpVerifyBtn',
                alertSelector: '#phoneVerifyAlert',
                defaultText: 'Verify Number',
                loadingText: 'Verifying...',
                rules: {
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    otp: { required: true, digits: true, minlength: 6, maxlength: 6 }
                },
                messages: {
                    phone_number: {
                        required: 'Please enter your mobile number.',
                        digits: 'Mobile number should contain only digits.',
                        minlength: 'Mobile number must be at least 10 digits.',
                        maxlength: 'Mobile number cannot exceed 15 digits.'
                    },
                    otp: {
                        required: 'Please enter the OTP sent to your mobile number.',
                        digits: 'OTP must contain only numbers.',
                        minlength: 'OTP must be 6 digits.',
                        maxlength: 'OTP must be 6 digits.'
                    }
                },
                fallbackErrorMessage: 'Unable to verify OTP right now. Please try again.',
                beforeSubmit: function (ctx) {
                    var $phoneInput = ctx.form.find('[name="phone_number"]');
                    if (!$phoneInput.length) {
                        return;
                    }

                    var normalizedPhoneNumber = $.trim($phoneInput.val() || '').replace(/\D+/g, '');
                    $phoneInput.val(normalizedPhoneNumber);
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#phoneVerifyAlert'), 'success', response.message || 'Mobile number verified successfully.');
                    window.location.href = response.redirect || '/login';
                }
            });
        },

        initAdminProfileForm: function () {
            this.attachAjaxForm({
                formSelector: '#adminProfileForm',
                buttonSelector: '#adminProfileSubmitBtn',
                alertSelector: '#adminProfileAlert',
                defaultText: 'Save Changes',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 3, maxlength: 255 },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    email: { required: true, email: true, maxlength: 255 },
                    password: { minlength: 8 },
                    password_confirmation: {
                        required: function () {
                            return $.trim($('#password').val()).length > 0;
                        },
                        equalTo: '#password'
                    }
                },
                messages: {
                    name: {
                        required: 'Please enter your full name.',
                        minlength: 'Full name must be at least 3 characters.'
                    },
                    phone_number: {
                        required: 'Please enter your phone number.',
                        digits: 'Phone number should contain only digits.',
                        minlength: 'Phone number must be at least 10 digits.',
                        maxlength: 'Phone number cannot exceed 15 digits.'
                    },
                    email: {
                        required: 'Please enter your email address.',
                        email: 'Please enter a valid email address.'
                    },
                    password: {
                        minlength: 'Password must be at least 8 characters long.'
                    },
                    password_confirmation: {
                        required: 'Please confirm your password.',
                        equalTo: 'Password confirmation does not match.'
                    }
                },
                fallbackErrorMessage: 'Unable to update profile right now. Please try again.',
                onSuccess: function (response, ctx) {
                    if (ctx && ctx.form && ctx.form.length) {
                        ctx.form.find('input[name="password"], input[name="password_confirmation"]').val('');
                    }

                    FormHelper.showAlert(
                        $('#adminProfileAlert'),
                        'success',
                        response.message || 'Profile updated successfully.'
                    );
                }
            });
        },

        profileValidationRules: function (includeMarketplaceFields) {
            var rules = {
                name: { required: true, minlength: 3, maxlength: 255 },
                phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                whatsapp_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                address: { required: true, minlength: 5, maxlength: 500 },
                city: { required: true, maxlength: 120 },
                pincode: { required: true, digits: true, minlength: 4, maxlength: 10 },
                date_of_birth: { required: true, date: true },
                password: { minlength: 8 },
                password_confirmation: {
                    required: function () {
                        return $.trim($('#password').val()).length > 0;
                    },
                    equalTo: '#password'
                }
            };

            if (includeMarketplaceFields) {
                rules.pan_number = { required: true, maxlength: 20 };
                rules.has_gst = { required: true };
                rules.gst_number = {
                    required: function () {
                        return $('input[name="has_gst"]:checked').val() === '1';
                    },
                    maxlength: 20
                };
                rules.government_certificate_number = { maxlength: 100 };
            }

            return rules;
        },

        profileValidationMessages: function (includeMarketplaceFields) {
            var messages = {
                name: {
                    required: 'Please enter your full name.',
                    minlength: 'Full name must be at least 3 characters.'
                },
                phone_number: {
                    required: 'Please enter your phone number.',
                    digits: 'Phone number should contain only digits.',
                    minlength: 'Phone number must be at least 10 digits.',
                    maxlength: 'Phone number cannot exceed 15 digits.'
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
                city: {
                    required: 'Please enter your city.'
                },
                pincode: {
                    required: 'Please enter your pincode.',
                    digits: 'Pincode should contain only digits.',
                    minlength: 'Pincode must be at least 4 digits.',
                    maxlength: 'Pincode cannot exceed 10 digits.'
                },
                date_of_birth: {
                    required: 'Please enter your date of birth.',
                    date: 'Please enter a valid date of birth.'
                },
                password: {
                    minlength: 'Password must be at least 8 characters long.'
                },
                password_confirmation: {
                    required: 'Please confirm your password.',
                    equalTo: 'Password confirmation does not match.'
                }
            };

            if (includeMarketplaceFields) {
                messages.pan_number = {
                    required: 'Please enter your PAN number.',
                    maxlength: 'PAN number cannot exceed 20 characters.'
                };
                messages.has_gst = {
                    required: 'Please select whether you have a GST number.'
                };
                messages.gst_number = {
                    required: 'Please enter your GST number.',
                    maxlength: 'GST number cannot exceed 20 characters.'
                };
                messages.government_certificate_number = {
                    maxlength: 'Government certificate number cannot exceed 100 characters.'
                };
            }

            return messages;
        },

        toggleGstProfileField: function () {
            var hasGst = $('input[name="has_gst"]:checked').val() === '1';
            $('.js-gst-number-field').toggleClass('d-none', !hasGst);
            if (!hasGst) {
                $('#gst_number').val('').removeClass('is-invalid');
            }
        },

        attachProfileUpdateForm: function (formSelector, buttonSelector, alertSelector, includeMarketplaceFields) {
            this.attachAjaxForm({
                formSelector: formSelector,
                buttonSelector: buttonSelector,
                alertSelector: alertSelector,
                defaultText: 'Save Changes',
                loadingText: 'Saving...',
                rules: this.profileValidationRules(includeMarketplaceFields),
                messages: this.profileValidationMessages(includeMarketplaceFields),
                fallbackErrorMessage: 'Unable to update profile right now. Please try again.',
                beforeSubmit: function (ctx) {
                    ctx.form.find('[name="phone_number"], [name="whatsapp_number"], [name="pincode"]').each(function () {
                        $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
                    });
                },
                onSuccess: function (response, ctx) {
                    var message = response.message || 'Profile updated successfully.';

                    if (ctx && ctx.form && ctx.form.length) {
                        ctx.form.find('input[name="password"], input[name="password_confirmation"]').val('');
                    }

                    if (response.redirect) {
                        FormHelper.showToast('warning', message);
                        window.setTimeout(function () {
                            window.location.href = response.redirect;
                        }, 1500);
                        return;
                    }

                    FormHelper.showToast('success', message);
                    FormHelper.showAlert($(alertSelector), 'success', message);
                }
            });
        },

        initUserProfileForm: function () {
            this.attachProfileUpdateForm('#userProfileForm', '#userProfileSubmitBtn', '#userProfileAlert', false);
        },

        initMarketplaceProfileForms: function () {
            this.toggleGstProfileField();
            $(document).off('change.profileGst', 'input[name="has_gst"]').on('change.profileGst', 'input[name="has_gst"]', function () {
                FormHelper.toggleGstProfileField();
            });

            this.attachProfileUpdateForm('#vendorProfileForm', '#vendorProfileSubmitBtn', '#vendorProfileAlert', true);
            this.attachProfileUpdateForm('#consultantProfileForm', '#consultantProfileSubmitBtn', '#consultantProfileAlert', true);
        },

        init: function () {
            this.initRegisterForm();
            this.initGoogleCompleteForm();
            this.initAdminCreateUserPlaceAutocomplete();
            this.initLoginForms();
            this.initOtpVerifyForm();
            this.initPhoneVerificationForm();
            this.initAdminProfileForm();
            this.initUserProfileForm();
            this.initMarketplaceProfileForms();
            this.initOtpTimer('#otp-timer');
        }
    };

    window.FormHelper = FormHelper;

    $(function () {
        FormHelper.init();
    });
})(window.jQuery);

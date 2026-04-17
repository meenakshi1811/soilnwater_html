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
                },
                rules: config.rules || {},
                messages: config.messages || {},
                submitHandler: function () {
                    self.clearFormErrors($form);
                    if ($alert.length) {
                        $alert.addClass('d-none').text('');
                    }

                    if (typeof config.beforeSubmit === 'function') {
                        config.beforeSubmit({ form: $form, button: $button, alert: $alert });
                    }

                    self.setButtonLoading($button, true, loadingText, defaultText);

                    $.ajax({
                        url: $form.attr('action'),
                        method: config.method || $form.attr('method') || 'POST',
                        data: $form.serialize(),
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
                            self.showAlert($alert, 'warning', config.validationMessage || 'Please fix the highlighted fields and try again.');
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
                }
            });
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

        initRegisterForm: function () {
            this.attachAjaxForm({
                formSelector: '#registerForm',
                buttonSelector: '#registerSubmitBtn',
                alertSelector: '#registerAlert',
                defaultText: 'Create Account',
                loadingText: 'Creating Account...',
                rules: {
                    fullname: { required: true, minlength: 3, maxlength: 255 },
                    email: { required: true, email: true },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    role: { required: true },
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
                    phone_number: {
                        required: 'Please enter your phone number.',
                        digits: 'Phone number should contain only digits.',
                        minlength: 'Phone number must be at least 10 digits.',
                        maxlength: 'Phone number cannot exceed 15 digits.'
                    },
                    role: {
                        required: 'Please select your role.'
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
                onSuccess: function (response) {
                    FormHelper.showAlert($('#registerAlert'), 'success', response.message || 'Registration successful. Redirecting...');
                    window.location.href = response.redirect || '/verification/contact';
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
                    login: { required: true },
                    password: { required: true, minlength: 8 }
                },
                messages: {
                    login: {
                        required: 'Please enter your email or phone number.'
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
                    login_contact: { required: true }
                },
                messages: {
                    login_contact: {
                        required: 'Please enter your email or phone number.'
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
                defaultText: 'Verify Account',
                loadingText: 'Verifying...',
                rules: {
                    email_otp: { required: true, digits: true, minlength: 6, maxlength: 6 },
                    phone_otp: { required: true, digits: true, minlength: 6, maxlength: 6 }
                },
                messages: {
                    email_otp: {
                        required: 'Please enter the email verification code.',
                        digits: 'Email code must contain only numbers.',
                        minlength: 'Email code must be 6 digits.',
                        maxlength: 'Email code must be 6 digits.'
                    },
                    phone_otp: {
                        required: 'Please enter the phone verification code.',
                        digits: 'Phone code must contain only numbers.',
                        minlength: 'Phone code must be 6 digits.',
                        maxlength: 'Phone code must be 6 digits.'
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
                    FormHelper.showAlert($('#contactVerifyAlert'), 'success', response.message || 'Verification codes resent successfully.');

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
                showLoaderOnClick: true,
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
                showLoaderOnClick: true,
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

        initUserProfileForm: function () {
            this.attachAjaxForm({
                formSelector: '#userProfileForm',
                buttonSelector: '#userProfileSubmitBtn',
                alertSelector: '#userProfileAlert',
                defaultText: 'Save Changes',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 3, maxlength: 255 },
                    phone_number: { digits: true, minlength: 10, maxlength: 15 },
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
                        digits: 'Phone number should contain only digits.',
                        minlength: 'Phone number must be at least 10 digits.',
                        maxlength: 'Phone number cannot exceed 15 digits.'
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
                        $('#userProfileAlert'),
                        'success',
                        response.message || 'Profile updated successfully.'
                    );
                }
            });
        },

        init: function () {
            this.initRegisterForm();
            this.initLoginForms();
            this.initOtpVerifyForm();
            this.initPhoneVerificationForm();
            this.initAdminProfileForm();
            this.initUserProfileForm();
            this.initOtpTimer('#otp-timer');
        }
    };

    window.FormHelper = FormHelper;

    $(function () {
        FormHelper.init();
    });
})(window.jQuery);

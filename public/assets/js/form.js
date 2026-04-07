(function ($) {
    if (!$) {
        return;
    }

    var FormHelper = {
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

            if ($button.hasClass('js-auto-loader') && !$button.data('loaderStyled')) {
                $loader.css({
                    width: '1rem',
                    height: '1rem',
                    border: '2px solid rgba(255,255,255,.35)',
                    borderTopColor: '#fff',
                    borderRadius: '50%',
                    display: 'inline-block',
                    verticalAlign: 'middle',
                    animation: 'formBtnSpin .75s linear infinite'
                });
                $button.data('loaderStyled', true);
            }

            if (!document.getElementById('form-helper-spinner-style')) {
                $('head').append('<style id="form-helper-spinner-style">@keyframes formBtnSpin{to{transform:rotate(360deg);}}</style>');
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
            $alert.removeClass('d-none alert-success alert-danger alert-warning alert-info')
                .addClass('alert-' + type)
                .text(message);
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

            self.ensureButtonParts($button);

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
                rules: config.rules || {},
                messages: config.messages || {},
                submitHandler: function () {
                    self.clearFormErrors($form);
                    if ($alert.length) {
                        $alert.addClass('d-none').text('');
                    }

                    self.setButtonLoading($button, true, loadingText, defaultText);

                    $.ajax({
                        url: $form.attr('action'),
                        method: config.method || $form.attr('method') || 'POST',
                        data: $form.serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
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
        }
    };

    window.FormHelper = FormHelper;

    FormHelper.attachAjaxForm({
        formSelector: '#registerForm',
        buttonSelector: '#registerSubmitBtn',
        alertSelector: '#registerAlert',
        defaultText: 'Create Account',
        loadingText: 'Creating Account...',
        rules: {
            fullname: { required: true, minlength: 3, maxlength: 255 },
            email: { required: true, email: true },
            phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
            password: { required: true, minlength: 8 },
            password_confirmation: { required: true, equalTo: '#password' }
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
            password: {
                required: 'Please create a password.',
                minlength: 'Password must be at least 8 characters long.'
            },
            password_confirmation: {
                required: 'Please confirm your password.',
                equalTo: 'Password confirmation does not match.'
            }
        },
        fallbackErrorMessage: 'Unable to register right now. Please try again.',
        onSuccess: function () {
            FormHelper.showAlert($('#registerAlert'), 'success', 'Registration successful. Redirecting...');
            window.location.href = '/home';
        }
    });
})(window.jQuery);

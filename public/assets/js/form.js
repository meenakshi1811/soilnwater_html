$(function () {
    var $form = $('#registerForm');
    if (!$form.length) {
        return;
    }

    var $submitBtn = $('#registerSubmitBtn');
    var $btnText = $submitBtn.find('.btn-text');
    var $btnLoader = $submitBtn.find('.btn-loader');
    var $alert = $('#registerAlert');

    function setLoadingState(isLoading) {
        $submitBtn.prop('disabled', isLoading);
        $btnText.text(isLoading ? 'Creating Account...' : 'Create Account');
        $btnLoader.toggleClass('d-none', !isLoading);
    }

    function showAlert(type, message) {
        $alert.removeClass('d-none alert-success alert-danger').addClass('alert-' + type).text(message);
    }

    function clearValidationErrors() {
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('span.ajax-error').remove();
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
        submitHandler: function () {
            clearValidationErrors();
            $alert.addClass('d-none').text('');
            setLoadingState(true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).done(function () {
                showAlert('success', 'Registration successful. Redirecting...');
                window.location.href = '/home';
            }).fail(function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        var $input = $form.find('[name="' + field + '"]');
                        $input.addClass('is-invalid');
                        $('<span class="invalid-feedback d-block ajax-error"></span>')
                            .text(messages[0])
                            .insertAfter($input);
                    });
                    showAlert('danger', 'Please fix the highlighted fields and try again.');
                    return;
                }

                var fallbackMessage = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Unable to register right now. Please try again.';
                showAlert('danger', fallbackMessage);
            }).always(function () {
                setLoadingState(false);
            });
        }
    });
});

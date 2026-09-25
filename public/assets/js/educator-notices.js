(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    if (window.toastr) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4500,
            extendedTimeOut: 2000,
        };
    }

    var $form = $('#educatorNoticeForm');
    if (!$form.length) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val();
    var $alert = $('#educatorNoticeAlert');

    function notify(type, message) {
        if (!message) {
            return;
        }

        var toastType = type === 'danger' ? 'error' : type;
        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr[toastType](message);
            return;
        }

        if ($alert.length) {
            $alert.removeClass('d-none alert-success alert-danger alert-warning')
                .addClass('alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'))
                .text(message);
        }
    }

    function clearFormErrors() {
        if (window.FormHelper && typeof window.FormHelper.clearFormErrors === 'function') {
            window.FormHelper.clearFormErrors($form);
            return;
        }

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.ajax-error').remove();
    }

    function renderFieldErrors(errors) {
        if (window.FormHelper && typeof window.FormHelper.renderFieldErrors === 'function') {
            window.FormHelper.renderFieldErrors($form, errors);
        }
    }

    function showValidationErrors(xhr) {
        var response = xhr.responseJSON || {};
        var messages = [];

        if (xhr.status === 422 && response.errors) {
            renderFieldErrors(response.errors);
            Object.keys(response.errors).forEach(function (key) {
                messages = messages.concat(response.errors[key]);
            });
        } else if (response.message) {
            messages.push(response.message);
        }

        if (!messages.length) {
            messages.push('Unable to publish notice right now. Please try again.');
        }

        var summary = messages.length === 1
            ? messages[0]
            : 'Please fix the highlighted fields and try again.';

        notify('warning', summary);

        if ($alert.length && messages.length > 1) {
            $alert.removeClass('d-none alert-success alert-danger')
                .addClass('alert-warning')
                .html(messages.map(function (msg) {
                    return '<div>' + $('<div>').text(msg).html() + '</div>';
                }).join(''));
        }
    }

    function setSubmitting(isSubmitting) {
        var $btn = $('#educatorNoticeSubmitBtn');
        $btn.prop('disabled', isSubmitting);
        $btn.find('.btn-text').toggleClass('d-none', isSubmitting);
        $btn.find('.btn-loader').toggleClass('d-none', !isSubmitting);
    }

    $form.on('submit', function (event) {
        event.preventDefault();

        clearFormErrors();
        if ($alert.length) {
            $alert.addClass('d-none').empty();
        }

        setSubmitting(true);

        var formData = new FormData($form[0]);

        $.ajax({
            url: window.educatorNoticeStoreUrl || '/educator/notices',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
        })
            .done(function (response) {
                notify('success', response.message || 'Notice published successfully.');

                if ($alert.length) {
                    $alert.removeClass('d-none alert-danger alert-warning')
                        .addClass('alert-success')
                        .text(response.message || 'Notice published successfully.');
                }

                if (response.item_html) {
                    $('#educatorNoticeEmpty').remove();
                    $('#educatorNoticeList').prepend(response.item_html);
                }

                $form[0].reset();
                $('#noticeExpiresAt').attr('min', new Date().toISOString().slice(0, 10));
            })
            .fail(showValidationErrors)
            .always(function () {
                setSubmitting(false);
            });
    });

    $(document).on('click', '.js-edu-notice-delete', function () {
        var $button = $(this);
        var url = $button.data('url');
        var $item = $button.closest('.edu-notice-manage-item');

        if (!url || !window.confirm('Remove this notice from your public profile?')) {
            return;
        }

        $button.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: csrfToken,
                _method: 'DELETE',
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
        })
            .done(function (response) {
                notify('success', response.message || 'Notice removed successfully.');
                $item.remove();

                if (!$('#educatorNoticeList .edu-notice-manage-item').length) {
                    $('#educatorNoticeList').html('<p class="edu-notice-manage-empty mb-0" id="educatorNoticeEmpty">No notices published yet.</p>');
                }
            })
            .fail(showValidationErrors)
            .always(function () {
                $button.prop('disabled', false);
            });
    });
})(window.jQuery);

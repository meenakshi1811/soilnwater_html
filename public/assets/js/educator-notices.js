(function ($) {
    'use strict';

    if (!$ || !window.toastr) {
        return;
    }

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 3500,
        extendedTimeOut: 2000,
    };

    var $form = $('#educatorNoticeForm');
    if (!$form.length) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content') || $form.find('input[name="_token"]').val();

    function setSubmitting(isSubmitting) {
        var $btn = $('#educatorNoticeSubmitBtn');
        $btn.prop('disabled', isSubmitting);
        $btn.find('.btn-text').toggleClass('d-none', isSubmitting);
        $btn.find('.btn-loader').toggleClass('d-none', !isSubmitting);
    }

    function showValidationErrors(xhr) {
        var response = xhr.responseJSON || {};
        var messages = [];

        if (response.errors) {
            Object.keys(response.errors).forEach(function (key) {
                messages = messages.concat(response.errors[key]);
            });
        } else if (response.message) {
            messages.push(response.message);
        }

        if (!messages.length) {
            messages.push('Unable to publish notice right now. Please try again.');
        }

        messages.forEach(function (message) {
            toastr.warning(message);
        });
    }

    $form.on('submit', function (event) {
        event.preventDefault();

        if (!$form[0].reportValidity()) {
            toastr.warning('Please fill in the required notice details.');
            return;
        }

        setSubmitting(true);

        $.ajax({
            url: window.educatorNoticeStoreUrl || '/educator/notices',
            method: 'POST',
            data: $form.serialize(),
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
        })
            .done(function (response) {
                toastr.success(response.message || 'Notice published successfully.');

                if (response.item_html) {
                    $('#educatorNoticeEmpty').remove();
                    $('#educatorNoticeList').prepend(response.item_html);
                }

                $form[0].reset();
                $('#noticeExpiresAt').attr('min', new Date().toISOString().slice(0, 10));
            })
            .fail(function (xhr) {
                showValidationErrors(xhr);
            })
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
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
        })
            .done(function (response) {
                toastr.success(response.message || 'Notice removed successfully.');
                $item.remove();

                if (!$('#educatorNoticeList .edu-notice-manage-item').length) {
                    $('#educatorNoticeList').html('<p class="edu-notice-manage-empty mb-0" id="educatorNoticeEmpty">No notices published yet.</p>');
                }
            })
            .fail(function (xhr) {
                $button.prop('disabled', false);
                showValidationErrors(xhr);
            });
    });
})(window.jQuery);

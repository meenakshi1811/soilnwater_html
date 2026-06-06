(function ($) {
    'use strict';

    if (!$) return;

    function notify(type, message) {
        var toastType = type === 'danger' ? 'error' : type;

        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 4500
            };
            window.toastr[toastType](message);
            return;
        }

        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(type, message);
        }
    }

    function submitDecision($button, confirmationMessage) {
        if (!window.confirm(confirmationMessage)) return;

        var originalHtml = $button.html();
        var $buttons = $('[data-review-page] .service-page-review__actions .btn');
        $buttons.prop('disabled', true);
        $button.html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...');

        $.ajax({
            url: $button.data('url'),
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        }).done(function (response) {
            notify('success', response.message || 'Public page review completed.');
            window.setTimeout(function () {
                window.location.href = response.redirect_url || '/admin/services';
            }, 900);
        }).fail(function (xhr) {
            notify('danger', xhr.responseJSON?.message || 'Unable to complete the public page review.');
            $buttons.prop('disabled', false);
            $button.html(originalHtml);
        });
    }

    $(document).on('click', '.js-approve-public-page', function () {
        submitDecision($(this), 'Approve and publish this public page?');
    });

    $(document).on('click', '.js-decline-public-page', function () {
        submitDecision($(this), 'Decline these public page changes?');
    });
})(window.jQuery);

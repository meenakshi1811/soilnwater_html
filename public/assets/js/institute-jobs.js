(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var routes = window.instJobRoutes || {};

    function notify(type, message) {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
            };
            toastr[type](message);
            return;
        }

        var alertEl = document.getElementById('instJobsAlert');
        if (!alertEl) {
            return;
        }

        alertEl.textContent = message;
        alertEl.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger');
        alertEl.classList.remove('d-none');
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
            messages.push('Unable to save right now. Please try again.');
        }

        messages.forEach(function (message) {
            notify('warning', message);
        });
    }

    function setSubmitting($btn, isSubmitting) {
        $btn.prop('disabled', isSubmitting);
        $btn.find('.btn-text').toggleClass('d-none', isSubmitting);
        $btn.find('.btn-loader').toggleClass('d-none', !isSubmitting);
    }

    $('#instJobCreateForm').on('submit', function (event) {
        event.preventDefault();
        var $form = $(this);
        var $btn = $form.find('.js-inst-job-submit');

        setSubmitting($btn, true);

        $.ajax({
            url: routes.store,
            method: 'POST',
            data: $form.serialize(),
            headers: { Accept: 'application/json' },
        })
            .done(function (response) {
                notify('success', response.message || 'Job posted successfully.');
                $('#instJobsEmpty').remove();
                $('#instJobsList').prepend(response.html || '');
                $form.trigger('reset');
            })
            .fail(function (xhr) {
                showValidationErrors(xhr);
            })
            .always(function () {
                setSubmitting($btn, false);
            });
    });

    $(document).on('click', '.js-inst-job-delete', function () {
        var $btn = $(this);
        var url = $btn.data('url');
        if (!url || !window.confirm('Remove this job and all applications?')) {
            return;
        }

        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        })
            .done(function (response) {
                notify('success', response.message || 'Job removed.');
                $btn.closest('.sch-job-manage-item').remove();
            })
            .fail(function (xhr) {
                showValidationErrors(xhr);
            });
    });

    $(document).on('click', '.js-inst-job-close, .js-inst-job-reopen', function () {
        var $btn = $(this);
        var url = $btn.data('url');
        var status = $btn.data('status');

        $.ajax({
            url: url,
            method: 'PUT',
            data: {
                _token: csrfToken,
                status: status,
            },
            headers: { Accept: 'application/json' },
        })
            .done(function (response) {
                notify('success', response.message || 'Job updated.');
                var $item = $btn.closest('.sch-job-manage-item');
                if (response.html) {
                    $item.replaceWith(response.html);
                }
            })
            .fail(function (xhr) {
                showValidationErrors(xhr);
            });
    });

    $(document).on('submit', '.js-inst-app-status-form', function (event) {
        event.preventDefault();
        var $form = $(this);
        var $btn = $form.find('.js-inst-app-submit');
        var url = $form.data('url');

        setSubmitting($btn, true);

        $.ajax({
            url: url,
            method: 'PATCH',
            data: $form.serialize(),
            headers: { Accept: 'application/json' },
        })
            .done(function (response) {
                notify('success', response.message || 'Application updated.');
                var $row = $form.closest('tr');
                if (response.html) {
                    $row.replaceWith(response.html);
                }
            })
            .fail(function (xhr) {
                showValidationErrors(xhr);
            })
            .always(function () {
                setSubmitting($btn, false);
            });
    });
})(window.jQuery);

// Dashboard: a school's / college's / institute's scholarships. The form adds one, or (after Edit)
// saves changes to one; the list's buttons close / reopen and delete.
(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var routes = window.instScholarshipRoutes || {};
    var $form = $('#instScholarshipForm');
    var editing = null; // the update URL of the scholarship being edited

    function notify(type, message) {
        if (window.toastr) {
            toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
            toastr[type](message);
            return;
        }
        var alertEl = document.getElementById('instScholarshipsAlert');
        if (alertEl) {
            alertEl.textContent = message;
            alertEl.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger');
        }
    }

    function showErrors(xhr) {
        var response = xhr.responseJSON || {};
        var messages = [];
        if (response.errors) {
            Object.keys(response.errors).forEach(function (key) {
                messages = messages.concat(response.errors[key]);
            });
        } else if (response.message) {
            messages.push(response.message);
        }
        (messages.length ? messages : ['Unable to save right now. Please try again.']).forEach(function (message) {
            notify('warning', message);
        });
    }

    function setSubmitting($btn, busy) {
        $btn.prop('disabled', busy);
        $btn.find('.btn-text').toggleClass('d-none', busy);
        $btn.find('.btn-loader').toggleClass('d-none', !busy);
    }

    // Back to adding a new one
    function resetForm() {
        editing = null;
        $form.trigger('reset');
        $('.js-scholarship-form-title').text('Add a scholarship');
        $('.js-scholarship-submit .btn-text').text('Publish scholarship');
        $('.js-scholarship-cancel, .js-scholarship-status-wrap').addClass('d-none');
        $('.sch-scholarship-manage-item').removeClass('is-editing');
    }

    $form.on('submit', function (event) {
        event.preventDefault();
        var $btn = $form.find('.js-scholarship-submit');
        var data = $form.serializeArray();
        if (editing) {
            data.push({ name: '_method', value: 'PUT' });
        }
        setSubmitting($btn, true);

        $.ajax({ url: editing || routes.store, method: 'POST', data: $.param(data), headers: { Accept: 'application/json' } })
            .done(function (response) {
                notify('success', response.message || 'Saved.');
                if (editing) {
                    $('.sch-scholarship-manage-item[data-update-url="' + editing + '"]').replaceWith(response.html || '');
                } else {
                    $('#instScholarshipsEmpty').remove();
                    $('#instScholarshipsList').prepend(response.html || '');
                }
                resetForm();
            })
            .fail(showErrors)
            .always(function () {
                setSubmitting($btn, false);
            });
    });

    // Edit: its details into the form
    $(document).on('click', '.js-scholarship-edit', function () {
        var $item = $(this).closest('.sch-scholarship-manage-item');
        var values = $item.data('scholarship') || {};
        resetForm();
        Object.keys(values).forEach(function (name) {
            $form.find('[name="' + name + '"]').val(values[name] === null ? '' : values[name]);
        });
        editing = $item.data('update-url');
        $item.addClass('is-editing');
        $('.js-scholarship-form-title').text('Edit scholarship');
        $('.js-scholarship-submit .btn-text').text('Save changes');
        $('.js-scholarship-cancel, .js-scholarship-status-wrap').removeClass('d-none');
        $('html, body').animate({ scrollTop: $form.closest('.chart-card').offset().top - 90 }, 250);
        $form.find('[name="title"]').trigger('focus');
    });

    $(document).on('click', '.js-scholarship-cancel', resetForm);

    $(document).on('click', '.js-scholarship-status', function () {
        var $btn = $(this);
        var $item = $btn.closest('.sch-scholarship-manage-item');
        $.ajax({
            url: $item.data('update-url'),
            method: 'POST',
            data: { _token: csrfToken, _method: 'PUT', status: $btn.data('status') },
            headers: { Accept: 'application/json' },
        })
            .done(function (response) {
                notify('success', response.message || 'Updated.');
                if (response.html) {
                    $item.replaceWith(response.html);
                }
            })
            .fail(showErrors);
    });

    $(document).on('click', '.js-scholarship-delete', function () {
        var $btn = $(this);
        var $item = $btn.closest('.sch-scholarship-manage-item');
        if (!window.confirm('Delete this scholarship? It will be removed from your profile and the Education Hub.')) {
            return;
        }
        $.ajax({ url: $btn.data('url'), method: 'POST', data: { _token: csrfToken, _method: 'DELETE' }, headers: { Accept: 'application/json' } })
            .done(function (response) {
                notify('success', response.message || 'Deleted.');
                if (editing === $item.data('update-url')) {
                    resetForm();
                }
                $item.remove();
            })
            .fail(showErrors);
    });
})(window.jQuery);

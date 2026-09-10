(function ($) {
    if (!$) {
        return;
    }

    function toast(type, message) {
        if (window.toastr) {
            toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 4000 };
            toastr[type === 'success' ? 'success' : 'error'](message);
            return;
        }
        if (window.FormHelper?.showToast) {
            FormHelper.showToast(type === 'success' ? 'success' : 'danger', message);
            return;
        }
        alert(message);
    }

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || window.ParentProfileConfig?.csrfToken || '';
    }

    // Toggle on profile pages
    $(document).on('change', '#parentProfileEnabledSwitch', function () {
        var $switch = $(this);
        var enabled = $switch.is(':checked');
        var url = $switch.data('toggle-url');

        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: csrfToken(), enabled: enabled ? 1 : 0 },
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .done(function (response) {
                toast('success', response.message || 'Parent profile updated.');
                if (enabled && response.dashboard_url) {
                    setTimeout(function () {
                        window.location.href = response.dashboard_url;
                    }, 700);
                } else {
                    window.location.reload();
                }
            })
            .fail(function (xhr) {
                $switch.prop('checked', !enabled);
                toast('error', xhr.responseJSON?.message || 'Unable to update parent profile setting.');
            });
    });

    // Open add child modal
    $(document).on('click', '.js-open-add-child', function () {
        var modalEl = document.getElementById('addChildModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    // Add child form
    $(document).on('submit', '#addChildForm', function (event) {
        event.preventDefault();
        var $form = $(this);
        var $btn = $('#addChildSubmitBtn');
        var formData = new FormData(this);
        formData.set('is_primary', $('#child_is_primary').is(':checked') ? '1' : '0');

        $btn.prop('disabled', true);

        $.ajax({
            url: window.ParentProfileConfig?.storeChildUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .done(function (response) {
                toast('success', response.message || 'Child profile submitted.');
                var modalEl = document.getElementById('addChildModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
                setTimeout(function () { window.location.reload(); }, 800);
            })
            .fail(function (xhr) {
                var message = xhr.responseJSON?.message || 'Unable to submit child profile.';
                if (xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                toast('error', message);
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    });

    // Edit parent profile
    $(document).on('submit', '#editParentProfileForm', function (event) {
        event.preventDefault();
        var $btn = $('#editParentProfileSubmitBtn');
        $btn.prop('disabled', true);

        $.ajax({
            url: window.ParentProfileConfig?.updateProfileUrl,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
            .done(function (response) {
                toast('success', response.message || 'Profile updated.');
                setTimeout(function () { window.location.reload(); }, 700);
            })
            .fail(function (xhr) {
                toast('error', xhr.responseJSON?.message || 'Unable to update profile.');
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    });

    // Delete child
    $(document).on('click', '.js-delete-child', function () {
        var id = $(this).data('id');
        var name = $(this).data('name') || 'this child profile';
        var deleteUrl = (window.ParentProfileConfig?.deleteChildUrlBase || '/parent/children') + '/' + id;

        var performDelete = function () {
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _token: csrfToken(), _method: 'DELETE' },
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
                .done(function (response) {
                    toast('success', response.message || 'Child profile deleted.');
                    setTimeout(function () { window.location.reload(); }, 700);
                })
                .fail(function (xhr) {
                    toast('error', xhr.responseJSON?.message || 'Unable to delete child profile.');
                });
        };

        if (window.Swal) {
            Swal.fire({
                title: 'Delete child profile?',
                text: 'Remove ' + name + '? This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc3545',
            }).then(function (result) {
                if (result.isConfirmed) {
                    performDelete();
                }
            });
            return;
        }

        if (confirm('Delete ' + name + '?')) {
            performDelete();
        }
    });
})(window.jQuery);

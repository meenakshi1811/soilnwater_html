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

    function calculateAgeFromDob(day, month, year) {
        if (!day || !month || !year) {
            return null;
        }

        var today = new Date();
        var birthDate = new Date(Number(year), Number(month) - 1, Number(day));

        if (Number.isNaN(birthDate.getTime())) {
            return null;
        }

        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age -= 1;
        }

        return age >= 0 ? age : null;
    }

    function syncChildDobFields($scope) {
        var $root = $scope && $scope.length ? $scope : $(document);
        var day = $root.find('.js-dob-day').val();
        var month = $root.find('.js-dob-month').val();
        var year = $root.find('.js-dob-year').val();
        var $combined = $root.find('.js-dob-combined');
        var $age = $root.find('.js-child-age');

        if (day && month && year) {
            var paddedMonth = String(month).padStart(2, '0');
            var paddedDay = String(day).padStart(2, '0');
            $combined.val(year + '-' + paddedMonth + '-' + paddedDay);

            var age = calculateAgeFromDob(day, month, year);
            if (age !== null && $age.length) {
                $age.val(age);
            }
        } else {
            $combined.val('');
        }
    }

    $(document).on('change', '.js-dob-day, .js-dob-month, .js-dob-year', function () {
        syncChildDobFields($(this).closest('.date-of-birth-dropdown, .parent-child-modal__form, form'));
    });

    function initChildSchoolAutocomplete() {
        var modal = document.getElementById('addChildModal');

        if (window.SoilnWaterGooglePlaces && typeof window.SoilnWaterGooglePlaces.initSchoolInstituteSearchFields === 'function') {
            window.SoilnWaterGooglePlaces.initSchoolInstituteSearchFields(modal || document);
        }
    }

    window.ParentProfile = window.ParentProfile || {};
    window.ParentProfile.initChildSchoolAutocomplete = initChildSchoolAutocomplete;

    // Open add child modal
    $(document).on('click', '.js-open-add-child', function () {
        var modalEl = document.getElementById('addChildModal');
        if (modalEl && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });

    function syncChildBoardField() {
        var $toggle = $('#child_has_board');
        var $wrap = $('.js-child-board-wrap');
        var $board = $('#child_board');
        var isEnabled = $toggle.is(':checked');

        $wrap.toggleClass('d-none', !isEnabled);
        $board.prop('required', isEnabled);

        if (!isEnabled) {
            $board.val('');
        }
    }

    $(document).on('change', '.js-child-has-board', syncChildBoardField);

    $(document).on('shown.bs.modal', '#addChildModal', function () {
        hideGooglePlacesDropdown();
        initChildSchoolAutocomplete();
        syncChildBoardField();
    });

    function hideGooglePlacesDropdown() {
        if (window.SoilnWaterGooglePlaces && typeof window.SoilnWaterGooglePlaces.dismissPacDropdown === 'function') {
            window.SoilnWaterGooglePlaces.dismissPacDropdown();
            return;
        }

        document.querySelectorAll('.pac-container').forEach(function (container) {
            container.style.display = 'none';
            container.style.visibility = 'hidden';
        });
    }

    function resetAddChildForm() {
        var form = document.getElementById('addChildForm');

        if (form) {
            form.reset();
        }

        $('#child_has_board').prop('checked', false);
        syncChildBoardField();
        hideGooglePlacesDropdown();
    }

    $(document).on('hidden.bs.modal', '#addChildModal', function () {
        resetAddChildForm();
    });

    // Add child form
    $(document).on('submit', '#addChildForm', function (event) {
        event.preventDefault();
        var $form = $(this);
        var $btn = $('#addChildSubmitBtn');
        var token = csrfToken();
        syncChildDobFields($form);

        var day = $form.find('.js-dob-day').val();
        var month = $form.find('.js-dob-month').val();
        var year = $form.find('.js-dob-year').val();
        var age = $form.find('.js-child-age').val();

        if (!day || !month || !year) {
            toast('error', 'Please select the full date of birth.');
            return;
        }

        if (!age) {
            toast('error', 'Age could not be calculated from the date of birth.');
            return;
        }

        var formData = new FormData(this);
        formData.set('_token', token);
        formData.set('dob_day', day);
        formData.set('dob_month', month);
        formData.set('dob_year', year);
        formData.set('age', age);
        formData.set('is_primary', $('#child_is_primary').is(':checked') ? '1' : '0');
        formData.set('has_board', $('#child_has_board').is(':checked') ? '1' : '0');
        formData.delete('phone_number');

        if (!$('#child_has_board').is(':checked')) {
            formData.delete('board');
        }

        $('#addChildAlert').addClass('d-none').text('');
        $btn.prop('disabled', true);

        $.ajax({
            url: window.ParentProfileConfig?.storeChildUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
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
                $('#addChildAlert').removeClass('d-none alert-success').addClass('alert-danger').text(message);
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

        var token = csrfToken();

        $.ajax({
            url: window.ParentProfileConfig?.updateProfileUrl,
            method: 'POST',
            data: $(this).serialize() + '&_method=PUT&_token=' + encodeURIComponent(token),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
            },
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
            var token = csrfToken();

            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _token: token, _method: 'DELETE' },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
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

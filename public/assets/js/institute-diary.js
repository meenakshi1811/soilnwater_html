(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var routes = window.instDiaryRoutes || {};

    function notify(type, message) {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
            };
            toastr[type](message);
        }
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

    function setSubmitting($form, isSubmitting) {
        $form.find('.js-diary-submit-btn').each(function () {
            var $btn = $(this);
            $btn.prop('disabled', isSubmitting);
            $btn.find('.btn-text').toggleClass('d-none', isSubmitting);
            $btn.find('.btn-loader').toggleClass('d-none', !isSubmitting);
        });
    }

    function serializeForm($form) {
        var data = $form.serializeArray();
        var payload = {};

        data.forEach(function (entry) {
            if (entry.name.endsWith('[]')) {
                var key = entry.name.slice(0, -2);
                if (!payload[key]) {
                    payload[key] = [];
                }
                payload[key].push(entry.value);
                return;
            }
            payload[entry.name] = entry.value;
        });

        ['is_recurring', 'is_active', 'is_paid'].forEach(function (flag) {
            payload[flag] = $form.find('[name="' + flag + '"]').is(':checked') ? 1 : 0;
        });

        if (!payload.applicable_to) {
            payload.applicable_to = [];
        }

        return payload;
    }

    function prependItem(listSelector, emptySelector, html) {
        var $list = $(listSelector);
        if (!$list.length) {
            return;
        }
        $(emptySelector).remove();
        $list.prepend(html);
    }

    function restoreEmpty(listSelector, itemSelector, emptySelector, emptyText) {
        var $list = $(listSelector);
        if ($list.find(itemSelector).length) {
            return;
        }
        $list.html('<p class="sch-manage-empty mb-0" id="' + emptySelector.replace('#', '') + '">' + emptyText + '</p>');
    }

    function bindCreateForm(formSelector, url, listSelector, emptySelector) {
        var $form = $(formSelector);
        if (!$form.length || !url) {
            return;
        }

        $form.on('submit', function (event) {
            event.preventDefault();

            if (!$form[0].reportValidity()) {
                notify('warning', 'Please fill in the required fields.');
                return;
            }

            setSubmitting($form, true);

            $.ajax({
                url: url,
                method: 'POST',
                data: serializeForm($form),
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            })
                .done(function (response) {
                    notify('success', response.message || 'Saved successfully.');
                    if (response.item_html) {
                        prependItem(listSelector, emptySelector, response.item_html);
                    }
                    $form[0].reset();
                    $form.find('[name="is_active"]').prop('checked', true);
                    $form.find('[name="is_paid"]').prop('checked', true);
                    var yearField = $form.find('[name="academic_year"]');
                    if (yearField.length && !yearField.val()) {
                        yearField.val($('#diaryFilterYear').val() || '');
                    }
                })
                .fail(showValidationErrors)
                .always(function () {
                    setSubmitting($form, false);
                });
        });
    }

    bindCreateForm('#diaryHolidayForm', routes.holidaysStore, '#diaryHolidayList', '#diaryHolidayEmpty');
    bindCreateForm('#diaryLeaveForm', routes.leaveStore, '#diaryLeaveList', '#diaryLeaveEmpty');

    function bindDelete(selector, listSelector, itemSelector, emptySelector) {
        $(document).on('click', selector, function () {
            var $button = $(this);
            var url = $button.data('url');
            var $item = $button.closest(itemSelector);

            if (!url || !window.confirm('Remove this item?')) {
                return;
            }

            $button.prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: csrfToken, _method: 'DELETE' },
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            })
                .done(function (response) {
                    notify('success', response.message || 'Removed successfully.');
                    $item.remove();
                    var emptyText = $(listSelector).data('empty-text') || 'No items yet.';
                    restoreEmpty(listSelector, itemSelector, emptySelector, emptyText);
                })
                .fail(function (xhr) {
                    $button.prop('disabled', false);
                    showValidationErrors(xhr);
                });
        });
    }

    bindDelete('.js-diary-holiday-delete', '#diaryHolidayList', '.js-diary-holiday-item', '#diaryHolidayEmpty');
    bindDelete('.js-diary-leave-delete', '#diaryLeaveList', '.js-diary-leave-item', '#diaryLeaveEmpty');

    var holidayModalEl = document.getElementById('diaryHolidayEditModal');
    var holidayModal = holidayModalEl && window.bootstrap ? new bootstrap.Modal(holidayModalEl) : null;
    var $holidayEditForm = $('#diaryHolidayEditForm');
    var holidayUpdateUrl = '';

    $(document).on('click', '.js-diary-holiday-edit', function () {
        var $item = $(this).closest('.js-diary-holiday-item');
        holidayUpdateUrl = $item.data('update-url');

        $holidayEditForm.find('[name="name"]').val($item.data('name'));
        $holidayEditForm.find('[name="holiday_type"]').val($item.data('holiday-type'));
        $holidayEditForm.find('[name="start_date"]').val($item.data('start-date'));
        $holidayEditForm.find('[name="end_date"]').val($item.data('end-date'));
        $holidayEditForm.find('[name="description"]').val($item.data('description'));
        $holidayEditForm.find('[name="academic_year"]').val($item.data('academic-year'));
        $holidayEditForm.find('[name="is_recurring"]').prop('checked', String($item.data('is-recurring')) === '1');
        $holidayEditForm.find('[name="is_active"]').prop('checked', String($item.data('is-active')) === '1');

        if (holidayModal) {
            holidayModal.show();
        }
    });

    $holidayEditForm.on('submit', function (event) {
        event.preventDefault();
        if (!$holidayEditForm[0].reportValidity() || !holidayUpdateUrl) {
            notify('warning', 'Please fill in the required fields.');
            return;
        }

        setSubmitting($holidayEditForm, true);

        $.ajax({
            url: holidayUpdateUrl,
            method: 'POST',
            data: Object.assign(serializeForm($holidayEditForm), { _method: 'PUT' }),
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
        })
            .done(function (response) {
                notify('success', response.message || 'Updated.');
                var $existing = $('.js-diary-holiday-item[data-item-id="' + response.id + '"]');
                if ($existing.length && response.item_html) {
                    $existing.replaceWith(response.item_html);
                }
                if (holidayModal) {
                    holidayModal.hide();
                }
            })
            .fail(showValidationErrors)
            .always(function () {
                setSubmitting($holidayEditForm, false);
            });
    });

    var leaveModalEl = document.getElementById('diaryLeaveEditModal');
    var leaveModal = leaveModalEl && window.bootstrap ? new bootstrap.Modal(leaveModalEl) : null;
    var $leaveEditForm = $('#diaryLeaveEditForm');
    var leaveUpdateUrl = '';

    $(document).on('click', '.js-diary-leave-edit', function () {
        var $item = $(this).closest('.js-diary-leave-item');
        leaveUpdateUrl = $item.data('update-url');

        var audiences = [];
        try {
            audiences = JSON.parse($item.attr('data-applicable-to') || '[]');
        } catch (error) {
            audiences = [];
        }

        $leaveEditForm.find('[name="leave_type"]').val($item.data('leave-type'));
        $leaveEditForm.find('[name="allowed_days"]').val($item.data('allowed-days'));
        $leaveEditForm.find('[name="description"]').val($item.data('description'));
        $leaveEditForm.find('[name="is_paid"]').prop('checked', String($item.data('is-paid')) === '1');
        $leaveEditForm.find('[name="is_active"]').prop('checked', String($item.data('is-active')) === '1');
        $leaveEditForm.find('.js-diary-leave-edit-audience').each(function () {
            var $checkbox = $(this);
            $checkbox.prop('checked', audiences.indexOf($checkbox.val()) !== -1);
        });

        if (leaveModal) {
            leaveModal.show();
        }
    });

    $leaveEditForm.on('submit', function (event) {
        event.preventDefault();
        if (!$leaveEditForm[0].reportValidity() || !leaveUpdateUrl) {
            notify('warning', 'Please fill in the required fields.');
            return;
        }

        setSubmitting($leaveEditForm, true);

        $.ajax({
            url: leaveUpdateUrl,
            method: 'POST',
            data: Object.assign(serializeForm($leaveEditForm), { _method: 'PUT' }),
            headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
        })
            .done(function (response) {
                notify('success', response.message || 'Updated.');
                var $existing = $('.js-diary-leave-item[data-item-id="' + response.id + '"]');
                if ($existing.length && response.item_html) {
                    $existing.replaceWith(response.item_html);
                }
                if (leaveModal) {
                    leaveModal.hide();
                }
            })
            .fail(showValidationErrors)
            .always(function () {
                setSubmitting($leaveEditForm, false);
            });
    });

    document.querySelectorAll('.sch-portal-nav__link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var href = link.getAttribute('href');
            if (!href || href.charAt(0) !== '#') {
                return;
            }

            event.preventDefault();
            document.querySelector(href)?.scrollIntoView({ behavior: 'smooth', block: 'start' });

            document.querySelectorAll('.sch-portal-nav__link').forEach(function (navLink) {
                navLink.classList.toggle('is-active', navLink === link);
            });
        });
    });
})(window.jQuery);

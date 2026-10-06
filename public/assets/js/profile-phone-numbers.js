(function ($) {
    'use strict';

    function reindexPhoneRows($list) {
        $list.find('.js-profile-phone-row').each(function (index) {
            var $row = $(this);
            $row.find('.js-profile-phone-index').text(index + 1);
            var $remove = $row.find('.js-profile-phone-remove');
            if (index === 0) {
                $remove.remove();
                $row.find('.js-profile-phone-input').prop('required', true);
            } else if (!$remove.length) {
                var $btn = $('<button>', {
                    type: 'button',
                    class: 'btn btn-outline-danger js-profile-phone-remove',
                    'aria-label': 'Remove phone number',
                    html: '<i class="fa-solid fa-trash-can" aria-hidden="true"></i>'
                });
                $row.append($btn);
                $row.find('.js-profile-phone-input').prop('required', false);
            }
        });
    }

    function bindProfilePhoneList($scope) {
        $scope.find('.js-profile-phone-list').each(function () {
            var $list = $(this);
            if ($list.data('profilePhoneBound')) {
                return;
            }
            $list.data('profilePhoneBound', true);
            reindexPhoneRows($list);
        });
    }

    function phoneListForAddButton($button) {
        var $scope = $button.closest('.col-md-6, .mb-3, .js-profile-phone-field');
        var $list = $scope.find('.js-profile-phone-list').first();
        if ($list.length) {
            return $list;
        }

        return $button.closest('div').siblings('.js-profile-phone-list').first();
    }

    $(document).on('click', '.js-profile-phone-add', function () {
        var $list = phoneListForAddButton($(this));
        var $row = $('<div>', { class: 'input-group js-profile-phone-row' });
        $row.append('<span class="input-group-text text-muted js-profile-phone-index">1</span>');
        $row.append(
            $('<input>', {
                type: 'tel',
                name: 'phone_numbers[]',
                class: 'form-control js-profile-phone-input',
                inputmode: 'numeric',
                autocomplete: 'tel'
            })
        );
        $list.append($row);
        reindexPhoneRows($list);
        $row.find('input').trigger('focus');
    });

    $(document).on('click', '.js-profile-phone-remove', function () {
        var $list = $(this).closest('.js-profile-phone-list');
        $(this).closest('.js-profile-phone-row').remove();
        if (!$list.find('.js-profile-phone-row').length) {
            var $row = $('<div>', { class: 'input-group js-profile-phone-row' });
            $row.append('<span class="input-group-text text-muted js-profile-phone-index">1</span>');
            $row.append(
                $('<input>', {
                    type: 'tel',
                    name: 'phone_numbers[]',
                    class: 'form-control js-profile-phone-input',
                    inputmode: 'numeric',
                    autocomplete: 'tel',
                    required: true
                })
            );
            $list.append($row);
        }
        reindexPhoneRows($list);
    });

    $(function () {
        bindProfilePhoneList($(document));
    });

    window.ProfilePhoneNumbers = {
        bind: bindProfilePhoneList,
        reindex: reindexPhoneRows,
        validationRules: function () {
            return {
                'phone_numbers[]': {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 15
                }
            };
        },
        validationMessages: function () {
            return {
                'phone_numbers[]': {
                    required: 'Please enter a phone number.',
                    digits: 'Phone number should contain only digits.',
                    minlength: 'Phone number must be at least 10 digits.',
                    maxlength: 'Phone number cannot exceed 15 digits.'
                }
            };
        },
        normalizeInputs: function ($form) {
            $form.find('.js-profile-phone-input').each(function () {
                $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
            });
        }
    };
})(jQuery);

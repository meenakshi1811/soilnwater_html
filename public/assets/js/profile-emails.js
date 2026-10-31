// A profile's contact emails (backend/partials/profile-emails-fields): add and remove rows, the
// first one required, like the phone numbers (profile-phone-numbers.js)
(function () {
    'use strict';

    function reindex(list) {
        Array.prototype.forEach.call(list.querySelectorAll('.js-profile-email-row'), function (row, index) {
            row.querySelector('.js-profile-email-index').textContent = String(index + 1);
            var input = row.querySelector('.js-profile-email-input');
            var remove = row.querySelector('.js-profile-email-remove');
            input.required = index === 0;
            if (index === 0 && remove) {
                remove.remove();
            } else if (index > 0 && !remove) {
                row.insertAdjacentHTML('beforeend', '<button type="button" class="btn btn-outline-danger js-profile-email-remove" aria-label="Remove email"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>');
            }
        });

        var max = parseInt(list.getAttribute('data-max'), 10) || 5;
        var add = list.closest('.js-profile-email-field').querySelector('.js-profile-email-add');
        if (add) {
            add.disabled = list.querySelectorAll('.js-profile-email-row').length >= max;
        }
    }

    function newRow() {
        var row = document.createElement('div');
        row.className = 'input-group js-profile-email-row';
        row.innerHTML = '<span class="input-group-text text-muted js-profile-email-index">1</span>' +
            '<input type="email" name="emails[]" class="form-control js-profile-email-input" autocomplete="email" maxlength="255">';
        return row;
    }

    document.addEventListener('click', function (event) {
        var add = event.target.closest('.js-profile-email-add');
        if (add) {
            var list = add.closest('.js-profile-email-field').querySelector('.js-profile-email-list');
            var row = newRow();
            list.appendChild(row);
            reindex(list);
            row.querySelector('input').focus();
            return;
        }

        var remove = event.target.closest('.js-profile-email-remove');
        if (remove) {
            var rowList = remove.closest('.js-profile-email-list');
            remove.closest('.js-profile-email-row').remove();
            reindex(rowList);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.querySelectorAll('.js-profile-email-list'), reindex);
    });
})();

// The fee structure page (backend/institute/fee-structure): the fee tables as editable grids (their
// own columns and rows, a live total row), drawn from window.instituteFeeStructure and sent back as
// JSON in tables_json; the text sections; the save.
(function ($) {
    'use strict';

    var config = window.instituteFeeStructure || { tables: [], presets: {} };
    var MAX_TABLES = 10;
    var MAX_COLUMNS = 12;
    var MAX_ROWS = 60;
    var tables = (config.tables || []).map(copy);
    var holder = document.getElementById('feeTables');

    function copy(table) {
        return JSON.parse(JSON.stringify(table));
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    // An amount in a cell ("2,500", "Rs. 1100/-", "₹ 4,500"), or null for text like "NA"
    function amount(value) {
        var clean = String(value || '').replace(/(rs\.?|inr|₹|\/-|,|\s)/gi, '');
        return clean !== '' && !isNaN(clean) ? parseFloat(clean) : null;
    }

    function totals(table) {
        return table.columns.map(function (_, c) {
            var sum = null;
            table.rows.forEach(function (row) {
                var value = amount(row.values[c]);
                if (value !== null) {
                    sum = (sum || 0) + value;
                }
            });
            return sum === null ? '' : sum.toLocaleString('en-IN', { maximumFractionDigits: 2 });
        });
    }

    function tableHtml(table, t) {
        var head = '<th><input type="text" class="form-control form-control-sm" data-field="row_heading" value="' + escapeHtml(table.row_heading) + '" maxlength="60" placeholder="e.g. Class" aria-label="First column heading"></th>';
        table.columns.forEach(function (column, c) {
            head += '<th><div class="input-group input-group-sm">' +
                '<input type="text" class="form-control" data-column="' + c + '" value="' + escapeHtml(column) + '" maxlength="60" placeholder="Column" aria-label="Column heading">' +
                '<button type="button" class="btn btn-outline-danger js-fee-remove-column" data-column="' + c + '" aria-label="Remove column">&times;</button></div></th>';
        });
        head += '<th class="fee-grid__tool"><button type="button" class="btn btn-sm btn-outline-secondary js-fee-add-column"' + (table.columns.length >= MAX_COLUMNS ? ' disabled' : '') + ' title="Add a column">+ Column</button></th>';

        var body = table.rows.map(function (row, r) {
            var cells = '<td><input type="text" class="form-control form-control-sm" data-row="' + r + '" data-field="label" value="' + escapeHtml(row.label) + '" maxlength="120" placeholder="Row" aria-label="Row name"></td>';
            table.columns.forEach(function (_, c) {
                cells += '<td><input type="text" class="form-control form-control-sm fee-grid__value" data-row="' + r + '" data-column="' + c + '" value="' + escapeHtml(row.values[c]) + '" maxlength="60" aria-label="Fee"></td>';
            });
            return '<tr>' + cells + '<td class="fee-grid__tool"><button type="button" class="btn btn-sm btn-outline-danger js-fee-remove-row" data-row="' + r + '" aria-label="Remove row">&times;</button></td></tr>';
        }).join('');

        var foot = table.show_total
            ? '<tfoot><tr><th>Total</th>' + totals(table).map(function (sum) { return '<td class="fee-grid__total">' + escapeHtml(sum) + '</td>'; }).join('') + '<td></td></tr></tfoot>'
            : '';

        return '<div class="fee-table-card" data-table="' + t + '">' +
            '<div class="fee-table-card__head">' +
                '<input type="text" class="form-control" data-field="title" value="' + escapeHtml(table.title) + '" maxlength="120" placeholder="Table title, e.g. Monthly fee" aria-label="Table title">' +
                '<div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" role="switch" id="fee_total_' + t + '" data-field="show_total"' + (table.show_total ? ' checked' : '') + '><label class="form-check-label" for="fee_total_' + t + '">Total row</label></div>' +
                '<div class="btn-group btn-group-sm">' +
                    '<button type="button" class="btn btn-outline-secondary js-fee-move" data-step="-1"' + (t === 0 ? ' disabled' : '') + ' aria-label="Move up"><i class="fa-solid fa-arrow-up"></i></button>' +
                    '<button type="button" class="btn btn-outline-secondary js-fee-move" data-step="1"' + (t === tables.length - 1 ? ' disabled' : '') + ' aria-label="Move down"><i class="fa-solid fa-arrow-down"></i></button>' +
                    '<button type="button" class="btn btn-outline-danger js-fee-remove-table" aria-label="Remove table"><i class="fa-solid fa-trash-can"></i></button>' +
                '</div>' +
            '</div>' +
            '<div class="table-responsive"><table class="table table-sm align-middle fee-grid mb-2"><thead><tr>' + head + '</tr></thead><tbody>' + body + '</tbody>' + foot + '</table></div>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary js-fee-add-row"' + (table.rows.length >= MAX_ROWS ? ' disabled' : '') + '>+ Row</button>' +
            '<textarea class="form-control form-control-sm mt-2" data-field="note" rows="2" maxlength="1000" placeholder="Note under the table (optional), e.g. Security deposit is refundable on leaving." aria-label="Note">' + escapeHtml(table.note) + '</textarea>' +
        '</div>';
    }

    function render() {
        holder.innerHTML = tables.length
            ? tables.map(tableHtml).join('')
            : '<p class="text-secondary small mb-2">No fee tables yet. Start from one below.</p>';
        $('.js-fee-add-table').prop('disabled', tables.length >= MAX_TABLES);
    }

    function refreshTotals(card, table) {
        var cells = card.querySelectorAll('.fee-grid__total');
        totals(table).forEach(function (sum, c) {
            if (cells[c]) {
                cells[c].textContent = sum;
            }
        });
    }

    if (holder) {
        render();

        // Typing: kept in the tables (the grid isn't redrawn, so the cursor stays)
        holder.addEventListener('input', function (e) {
            var card = e.target.closest('.fee-table-card');
            if (!card) {
                return;
            }
            var table = tables[+card.dataset.table];
            var input = e.target;
            if (input.dataset.row !== undefined && input.dataset.column !== undefined) {
                table.rows[+input.dataset.row].values[+input.dataset.column] = input.value;
                refreshTotals(card, table);
            } else if (input.dataset.row !== undefined) {
                table.rows[+input.dataset.row].label = input.value;
            } else if (input.dataset.column !== undefined) {
                table.columns[+input.dataset.column] = input.value;
            } else if (input.dataset.field === 'show_total') {
                return; // (the change event below redraws the total row)
            } else if (input.dataset.field) {
                table[input.dataset.field] = input.value;
            }
        });

        holder.addEventListener('change', function (e) {
            if (e.target.dataset.field === 'show_total') {
                var card = e.target.closest('.fee-table-card');
                tables[+card.dataset.table].show_total = e.target.checked;
                render();
            }
        });

        // Columns, rows and tables added, removed or moved
        holder.addEventListener('click', function (e) {
            var button = e.target.closest('button');
            var card = e.target.closest('.fee-table-card');
            if (!button || !card) {
                return;
            }
            var t = +card.dataset.table;
            var table = tables[t];

            if (button.classList.contains('js-fee-add-column')) {
                table.columns.push('');
                table.rows.forEach(function (row) { row.values.push(''); });
            } else if (button.classList.contains('js-fee-remove-column')) {
                var c = +button.dataset.column;
                table.columns.splice(c, 1);
                table.rows.forEach(function (row) { row.values.splice(c, 1); });
            } else if (button.classList.contains('js-fee-add-row')) {
                table.rows.push({ label: '', values: table.columns.map(function () { return ''; }) });
            } else if (button.classList.contains('js-fee-remove-row')) {
                table.rows.splice(+button.dataset.row, 1);
            } else if (button.classList.contains('js-fee-remove-table')) {
                if (!window.confirm('Remove this fee table?')) {
                    return;
                }
                tables.splice(t, 1);
            } else if (button.classList.contains('js-fee-move')) {
                var to = t + (+button.dataset.step);
                tables.splice(to, 0, tables.splice(t, 1)[0]);
            } else {
                return;
            }
            render();
        });

        // A new table from a starter (filled with the school's classes)
        $('.js-fee-add-table').on('click', function () {
            var preset = (config.presets || {})[this.dataset.preset];
            if (!preset || tables.length >= MAX_TABLES) {
                return;
            }
            tables.push(copy(preset));
            render();
            var cards = holder.querySelectorAll('.fee-table-card');
            var last = cards[cards.length - 1];
            last.scrollIntoView({ behavior: 'smooth', block: 'center' });
            last.querySelector('[data-field="title"]').focus();
        });
    }

    // Sections: add one (with a suggested heading) from the template, remove one
    $('[data-repeater]').each(function () {
        var box = this;
        var list = box.querySelector('.js-repeater-list');
        var template = box.querySelector('.js-repeater-template');
        var nextIndex = parseInt(list.getAttribute('data-next-index'), 10) || 0;

        $(box).on('click', '.js-repeater-add', function () {
            var wrap = document.createElement('div');
            wrap.innerHTML = template.innerHTML.replace(/__INDEX__/g, String(nextIndex++));
            var row = wrap.querySelector('.js-repeater-row');
            list.appendChild(row);
            row.querySelector('input').value = this.dataset.heading || '';
            row.querySelector('textarea').focus();
        });

        $(list).on('click', '.js-repeater-remove', function () {
            this.closest('.js-repeater-row').remove();
        });
    });

    $(function () {
        if (!window.FormHelper) {
            return;
        }

        FormHelper.attachAjaxForm({
            formSelector: '#feeStructureForm',
            buttonSelector: '#feeStructureSubmitBtn',
            alertSelector: '#feeStructureAlert',
            defaultText: 'Save fee structure',
            loadingText: 'Saving...',
            fallbackErrorMessage: 'Unable to save the fee structure right now. Please try again.',
            validationMessage: 'Please fix the highlighted fields and try again.',
            rules: {},
            messages: {},
            beforeSubmit: function () {
                document.getElementById('feeTablesJson').value = JSON.stringify(tables);
            },
            onValidationError: function (xhr, message) {
                FormHelper.showToast('warning', message);
            },
            onError: function (xhr, message) {
                FormHelper.showToast('error', message);
            },
            onSuccess: function (response) {
                FormHelper.showToast('success', response.message || 'Fee structure saved.');
                if (response.reload) {
                    window.setTimeout(function () {
                        window.location.reload();
                    }, 1000);
                }
            }
        });
    });
})(window.jQuery);

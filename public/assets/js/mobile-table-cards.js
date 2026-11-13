/**
 * Mobile table cards
 *
 * Tables with the class "table-mobile-cards" are shown as one card per row on phones
 * (the layout itself is in admin.css). The card shows each value with its column heading,
 * so this script copies every column heading onto its cells as data-label. It watches the
 * table, so rows that DataTables draws later (paging, search, filters) get labels too.
 */
(function () {
    'use strict';

    function headings(table) {
        var row = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
        if (!row) {
            return [];
        }

        return Array.prototype.map.call(row.cells, function (cell) {
            return (cell.textContent || '').replace(/\s+/g, ' ').trim();
        });
    }

    function label(table) {
        var names = headings(table);
        if (!names.length) {
            return;
        }

        Array.prototype.forEach.call(table.tBodies, function (body) {
            Array.prototype.forEach.call(body.rows, function (row) {
                var column = 0;
                Array.prototype.forEach.call(row.cells, function (cell) {
                    var name = names[column] || '';
                    if (cell.getAttribute('data-label') !== name) {
                        cell.setAttribute('data-label', name);
                    }
                    column += cell.colSpan || 1;
                });
            });
        });
    }

    function watch(table) {
        if (table.dataset.mobileCardsReady) {
            return;
        }
        table.dataset.mobileCardsReady = '1';
        label(table);

        if ('MutationObserver' in window) {
            new MutationObserver(function () {
                label(table);
            }).observe(table, { childList: true, subtree: true });
        }
    }

    function init() {
        document.querySelectorAll('table.table-mobile-cards').forEach(watch);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

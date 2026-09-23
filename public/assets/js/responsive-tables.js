/**
 * Label every table cell with its column header (data-label) and mark the table as
 * stackable, so on phones each row can be shown as a card (see responsive-tables.css).
 * Tables whose body is replaced by AJAX (items, stock in) are relabelled automatically.
 */
(function () {
    'use strict';

    function headerLabels(table) {
        var row = table.tHead && table.tHead.rows[table.tHead.rows.length - 1];
        if (!row) return null;
        var labels = [];
        Array.prototype.forEach.call(row.cells, function (th) {
            var text = th.textContent.replace(/\s+/g, ' ').trim();
            for (var i = 0; i < (th.colSpan || 1); i++) labels.push(text);
        });
        return labels;
    }

    function labelTable(table) {
        var labels = headerLabels(table);
        if (!labels) return;
        table.classList.add('table-stack');
        Array.prototype.forEach.call(table.tBodies, function (body) {
            Array.prototype.forEach.call(body.rows, function (tr) {
                var col = 0;
                Array.prototype.forEach.call(tr.cells, function (td) {
                    // a cell spanning the whole row (empty state) gets no label
                    var spansAll = (td.colSpan || 1) >= labels.length && labels.length > 1;
                    td.setAttribute('data-label', spansAll ? '' : (labels[col] || ''));
                    col += td.colSpan || 1;
                });
            });
        });
    }

    function labelAll(root) {
        (root || document).querySelectorAll('table.table').forEach(labelTable);
    }

    document.addEventListener('DOMContentLoaded', function () {
        labelAll();
        // Re-label when rows are added or replaced (AJAX filters, pagination, cart updates)
        var timer;
        new MutationObserver(function () {
            clearTimeout(timer);
            timer = setTimeout(labelAll, 50);
        }).observe(document.body, { childList: true, subtree: true });
    });

    window.labelResponsiveTables = labelAll;
})();

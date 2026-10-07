(function () {
    'use strict';

    function deliveryOfferingRow(deliveryKey, index, isOnline) {
        var batchField = isOnline
            ? ''
            : '<div><label class="form-label d-md-none">Students per batch</label><input type="text" name="tuition_delivery_options[' +
              deliveryKey +
              '][offerings][' +
              index +
              '][batch_strength]" class="form-control" placeholder="e.g. 8"></div>';

        return (
            '<div class="edu-delivery-offering js-repeat-row" data-delivery-offering-row>' +
            '<div class="edu-delivery-offering__grid">' +
            '<div><label class="form-label d-md-none">Class</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][class]" class="form-control" placeholder="Class"></div>' +
            '<div><label class="form-label d-md-none">Subject</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][subject]" class="form-control" placeholder="Subject"></div>' +
            '<div><label class="form-label d-md-none">Board</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][board]" class="form-control" placeholder="Board"></div>' +
            batchField +
            '<div><label class="form-label d-md-none">Fee / charges</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][fee]" class="form-control" placeholder="₹ / month"></div>' +
            '<div><label class="form-label d-md-none">Batch timings</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][timings]" class="form-control" placeholder="Weekdays 5–8 PM"></div>' +
            '<div class="edu-delivery-offering__enrol"><label class="form-label d-md-none">Enrolment</label><label class="edu-availability-pill mb-0 h-100"><input class="form-check-input mt-0 me-2" type="checkbox" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][enrolment_open]" value="1" checked><span>Enrolment open</span></label></div>' +
            '<div class="edu-delivery-offering__note"><label class="form-label d-md-none">Note</label><input type="text" name="tuition_delivery_options[' +
            deliveryKey +
            '][offerings][' +
            index +
            '][note]" class="form-control" placeholder="Optional note"></div>' +
            '<div class="edu-delivery-offering__actions"><button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove">&times;</button></div>' +
            '</div></div>'
        );
    }

    function tuitionBatchDaysField(index) {
        var weekdays = [
            ['Sunday', 'Sun'],
            ['Monday', 'Mon'],
            ['Tuesday', 'Tue'],
            ['Wednesday', 'Wed'],
            ['Thursday', 'Thu'],
            ['Friday', 'Fri'],
            ['Saturday', 'Sat'],
        ];
        var html =
            '<div class="tuition-batch-card__days"><span class="form-label mb-1">Days</span><div class="tuition-batch-days" role="group" aria-label="Batch days">';
        weekdays.forEach(function (pair) {
            html +=
                '<label class="tuition-batch-day"><input type="checkbox" name="tuition_batches[' +
                index +
                '][days][]" value="' +
                pair[0] +
                '"><span>' +
                pair[1] +
                '</span></label>';
        });
        html += '</div></div>';
        return html;
    }

    var templates = {
        availability: function (i) {
            return (
                '<div class="edu-repeat-row edu-repeat-row--availability js-repeat-row">' +
                '<input type="text" name="availability[' +
                i +
                '][day]" class="form-control" placeholder="Monday">' +
                '<input type="text" name="availability[' +
                i +
                '][slots]" class="form-control" placeholder="4:00 PM – 7:00 PM">' +
                '<button type="button" class="btn btn-outline-danger edu-btn-remove js-remove-row" title="Remove">&times;</button></div>'
            );
        },
        tuitionBatch: function (i) {
            return (
                '<div class="tuition-batch-card js-repeat-row"><div class="tuition-batch-card__grid">' +
                '<div><label class="form-label d-md-none">Class</label><input type="text" name="tuition_batches[' +
                i +
                '][class]" class="form-control" placeholder="Class 10"></div>' +
                '<div><label class="form-label d-md-none">Subject</label><input type="text" name="tuition_batches[' +
                i +
                '][subject]" class="form-control" placeholder="Physics"></div>' +
                '<div><label class="form-label d-md-none">Batch type</label><input type="text" name="tuition_batches[' +
                i +
                '][batch_type]" class="form-control" placeholder="Small group" list="tuitionBatchTypeOptions"></div>' +
                '<div><label class="form-label d-md-none">Batch time</label><input type="text" name="tuition_batches[' +
                i +
                '][batch_time]" class="form-control" placeholder="5:00 PM – 7:00 PM"></div>' +
                '<div><label class="form-label d-md-none">Students</label><input type="number" name="tuition_batches[' +
                i +
                '][student_count]" class="form-control" min="1" placeholder="8"></div>' +
                '<div><label class="form-label d-md-none">Cost</label><input type="text" name="tuition_batches[' +
                i +
                '][cost]" class="form-control" placeholder="₹500 / month"></div>' +
                '<div><label class="form-label d-md-none">Seats</label><select name="tuition_batches[' +
                i +
                '][seats_status]" class="form-select"><option value="available">Seats available</option><option value="full">Batch full</option></select></div>' +
                '<div class="tuition-batch-card__actions"><button type="button" class="btn btn-outline-danger edu-btn-remove w-100 js-remove-row" title="Remove batch">&times;</button></div>' +
                '</div>' +
                tuitionBatchDaysField(i) +
                '</div>'
            );
        },
    };

    document.querySelectorAll('[data-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var wrap = document.querySelector(btn.dataset.add);
            if (!wrap || !templates[btn.dataset.template]) {
                return;
            }
            var i = wrap.querySelectorAll('.js-repeat-row').length;
            wrap.insertAdjacentHTML('beforeend', templates[btn.dataset.template](i));
        });
    });

    document.querySelectorAll('.js-add-delivery-offering').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.dataset.deliveryKey;
            var wrap = btn.closest('[data-delivery-option]')?.querySelector('.edu-delivery-offerings-wrap');
            if (!wrap || !key) {
                return;
            }
            var isOnline = wrap.dataset.online === '1';
            var i = wrap.querySelectorAll('[data-delivery-offering-row]').length;
            wrap.insertAdjacentHTML('beforeend', deliveryOfferingRow(key, i, isOnline));
        });
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('js-delivery-option-toggle')) {
            var fields = e.target.closest('[data-delivery-option]')?.querySelector('.edu-delivery-option__fields');
            if (fields) {
                fields.classList.toggle('d-none', !e.target.checked);
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('js-remove-row')) {
            return;
        }
        var row = e.target.closest('.js-repeat-row');
        var wrap = row?.parentElement;
        if (!row || !wrap) {
            return;
        }
        var rows = wrap.querySelectorAll('.js-repeat-row');
        if (rows.length > 1) {
            row.remove();
        }
    });
})();

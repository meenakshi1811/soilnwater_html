(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    function showAlert(type, message) {
        var $alert = $('#offerPriceAlert');
        $alert.removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type)
            .text(message);
    }

    function updateDisplayPrice($row, formattedPrice, isFree) {
        var $display = $row.find('[data-role="display-price"]');
        $display.toggleClass('is-free', !!isFree);
        $display.text(isFree ? 'Free' : formattedPrice);
    }

    function refreshAllRows(amount, formattedPrice) {
        var normalizedAmount = Number(amount).toFixed(2);
        var isFree = Number(amount) <= 0;

        $('.offer-price-row').each(function () {
            var $row = $(this);
            $row.find('input[name="offer_price"]').val(normalizedAmount);
            updateDisplayPrice($row, formattedPrice, isFree);
        });
    }

    function confirmApplyAll(amount) {
        var priceLabel = Number(amount) <= 0 ? 'Free (₹0.00)' : ('₹' + Number(amount).toFixed(2));

        if (window.Swal && typeof window.Swal.fire === 'function') {
            return window.Swal.fire({
                title: 'Apply price to all categories?',
                html: 'Set <strong>' + priceLabel + ' per day</strong> for every offer category and subcategory.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, apply to all',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then(function (result) {
                return !!result.isConfirmed;
            });
        }

        return Promise.resolve(window.confirm('Apply ' + priceLabel + ' per day to all offer categories and subcategories?'));
    }

    function submitApplyAll($form, $btn, amount) {
        FormHelper.setButtonLoading($btn, true, 'Applying...', 'Apply to All');
        FormHelper.clearFormErrors($form);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                offer_price: amount
            },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            var formattedPrice = response.formatted_price || ('₹' + Number(amount).toFixed(2));
            refreshAllRows(response.offer_price || amount, formattedPrice);
            showAlert('success', response.message || 'Offer prices updated successfully.');
            FormHelper.showToast('success', response.message || 'Offer prices updated successfully.');
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
            }

            var message = (xhr.responseJSON && xhr.responseJSON.message)
                ? xhr.responseJSON.message
                : 'Unable to apply offer prices.';
            showAlert('danger', message);
            FormHelper.showToast('danger', message);
        }).always(function () {
            FormHelper.setButtonLoading($btn, false, 'Applying...', 'Apply to All');
        });
    }

    $(document).on('submit', '.js-offer-price-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $row = $form.closest('.offer-price-row');
        var $btn = $row.find('.js-offer-price-save');
        var amount = $form.find('input[name="offer_price"]').val();

        FormHelper.setButtonLoading($btn, true, 'Saving...', 'Save');
        FormHelper.clearFormErrors($form);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT',
                offer_price: amount
            },
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (response) {
            var category = response.category || {};
            var isFree = !!category.is_free;
            updateDisplayPrice($row, category.formatted_price || ('₹' + amount), isFree);
            showAlert('success', response.message || 'Offer price updated successfully.');
            FormHelper.showToast('success', response.message || 'Offer price updated successfully.');
        }).fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
            }

            var message = (xhr.responseJSON && xhr.responseJSON.message)
                ? xhr.responseJSON.message
                : 'Unable to update offer price.';
            showAlert('danger', message);
            FormHelper.showToast('danger', message);
        }).always(function () {
            FormHelper.setButtonLoading($btn, false, 'Saving...', 'Save');
        });
    });

    $('#offerPriceApplyAllForm').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.js-offer-price-apply-all');
        var amount = $form.find('input[name="offer_price"]').val();

        confirmApplyAll(amount).then(function (confirmed) {
            if (!confirmed) {
                return;
            }

            submitApplyAll($form, $btn, amount);
        });
    });
})(window.jQuery);

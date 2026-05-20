(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var AdSizesAdmin = {
        table: null,
        modal: null,
        isEdit: false,

        initTable: function () {
            this.table = $('#adSizesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/ads/sizes/data'
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'size_key', name: 'size_key' },
                    { data: 'dimensions', name: 'dimensions', orderable: false, searchable: false },
                    { data: 'placement', name: 'placement', orderable: false, searchable: false },
                    { data: 'status_toggle', name: 'is_active', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']]
            });
        },

        resetForm: function () {
            $('#adSizeForm')[0].reset();
            $('#adSizeId').val('');
            $('#adSizeForm').find('input[name="_method"]').remove();
            $('#adSizeForm').find('.is-invalid').removeClass('is-invalid');
            $('#adSizeForm').find('span.ajax-error, span.invalid-feedback').remove();
            $('#categoryPriceModeAll').prop('checked', false);
            $('#applyAllCategoriesPriceWrap').addClass('d-none');
            $('#categoryPricingFieldsSection').removeClass('d-none');
            $('#applyAllCategoriesPriceInput').val('');
            $('#adSizeIsPaid').prop('checked', false);
            $('#modulePricingFieldsSection').addClass('d-none');
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('adSizeModal'));

            $('#openAdSizeModalBtn').on('click', function () {
                self.isEdit = false;
                $('#adSizeModalTitle').text('Add Ad Size');
                self.resetForm();
                $('#adSizeForm').attr('action', '/admin/ads/sizes').attr('method', 'POST');
                self.modal.show();
            });


            var syncAllCategoriesPrice = function () {
                var useAll = $('#categoryPriceModeAll').is(':checked');
                var value = $('#applyAllCategoriesPriceInput').val();
                if (!useAll) {
                    return;
                }

                $('input[name^="category_prices["]').val(value);
            };

            var syncModulePricingVisibility = function () {
                var isModulePriceEnabled = $('#adSizeIsPaid').is(':checked');
                $('#modulePricingFieldsSection').toggleClass('d-none', !isModulePriceEnabled);
            };

            $('#categoryPriceModeAll').on('change', function () {
                var isAllMode = this.checked;
                $('#applyAllCategoriesPriceWrap').toggleClass('d-none', !isAllMode);
                $('#categoryPricingFieldsSection').toggleClass('d-none', isAllMode);
                if (isAllMode) {
                    syncAllCategoriesPrice();
                }
            });

            $('#applyAllCategoriesPriceInput').on('input', function () {
                syncAllCategoriesPrice();
            });

            $('#adSizeIsPaid').on('change', function () {
                syncModulePricingVisibility();
            });

            $(document).on('click', '.js-edit-ad-size', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#adSizeModalTitle').text('Edit Ad Size');
                self.resetForm();
                $('#adSizeId').val(id);

                $.ajax({
                    url: '/admin/ads/sizes/' + id,
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    var size = response.size || {};
                    $('#adSizeName').val(size.name || '');
                    $('#adSizeKey').val(size.size_key || '');
                    $('#adSizeWidth').val(size.width || '');
                    $('#adSizeHeight').val(size.height || '');
                    $('#adSizeAdminOnly').prop('checked', !!size.admin_only);
                    $('#adSizeIsPaid').prop('checked', !!size.is_paid);
                    syncModulePricingVisibility();
                    var modulePrices = response.module_prices || {};
                    $('input[name^="module_prices["]').each(function () {
                        var name = $(this).attr('name');
                        var key = name.substring(14, name.length - 1);
                        $(this).val(modulePrices[key] || '');
                    });
                    $('#adSizeForm').attr('action', '/admin/ads/sizes/' + id).attr('method', 'POST');
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load ad size details.');
                });
            });



            $(document).on('change', '.js-size-status-toggle', function () {
                var $toggle = $(this);
                var id = $toggle.data('id');
                var isActive = $toggle.is(':checked') ? 1 : 0;
                var previousState = !isActive;

                $toggle.prop('disabled', true);

                $.ajax({
                    url: '/admin/ads/sizes/' + id + '/status',
                    method: 'POST',
                    data: { is_active: isActive },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    var statusLabel = response.status_label || (isActive ? 'Active' : 'Inactive');
                    $toggle.closest('.form-check').find('.form-check-label').text(statusLabel);
                    FormHelper.showToast('success', response.message || 'Status updated successfully.');
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update status.';
                    $toggle.prop('checked', !!previousState);
                    $toggle.closest('.form-check').find('.form-check-label').text(previousState ? 'Active' : 'Inactive');
                    FormHelper.showToast('danger', message);
                }).always(function () {
                    $toggle.prop('disabled', false);
                });
            });

            $(document).on('click', '.js-delete-ad-size', function () {
                var id = $(this).data('id');
                self.confirmDelete(id);
            });
        },

        confirmDelete: function (id) {
            var self = this;

            var runDelete = function () {
                $.ajax({
                    url: '/admin/ads/sizes/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    FormHelper.showToast('success', response.message || 'Deleted successfully.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete ad size.';
                    FormHelper.showToast('danger', msg);
                });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Delete ad size?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        runDelete();
                    }
                });
                return;
            }

            if (window.confirm('Are you sure you want to delete this ad size?')) {
                runDelete();
            }
        },

        initForm: function () {
            var self = this;
            var $form = $('#adSizeForm');
            var $submitBtn = $('#adSizeSubmitBtn');

            $form.on('submit', function (e) {
                e.preventDefault();

                if (typeof $form.valid === 'function' && !$form.valid()) {
                    return;
                }

                $form.find('input[name="_method"]').remove();

                if ($('#categoryPriceModeAll').is(':checked')) {
                    var allCategoryPrice = $('#applyAllCategoriesPriceInput').val();
                    $form.find('input[name^="category_prices["]').val(allCategoryPrice);
                }
                if (self.isEdit) {
                    $('<input type="hidden" name="_method" value="PUT">').appendTo($form);
                }

                FormHelper.setButtonLoading($submitBtn, true, 'Saving...', 'Save Size');
                FormHelper.clearFormErrors($form);
                $('#adSizeAlert').addClass('d-none').text('');

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).done(function (response) {
                    FormHelper.showToast('success', response.message || 'Saved successfully.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }).fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
                        return;
                    }

                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to save ad size.';
                    FormHelper.showToast('danger', message);
                }).always(function () {
                    FormHelper.setButtonLoading($submitBtn, false, 'Saving...', 'Save Size');
                });
            });

            if ($.fn && typeof $.fn.validate === 'function') {
                if ($.validator && typeof $.validator.addMethod === 'function' && !$.validator.methods.sizeKeyFormat) {
                    $.validator.addMethod('sizeKeyFormat', function (value, element) {
                        return this.optional(element) || /^[a-z0-9_]+$/.test(value);
                    }, 'Use lowercase letters, numbers, and underscore only.');
                }

                $form.validate({
                    errorElement: 'span',
                    errorPlacement: function (error, element) {
                        error.addClass('invalid-feedback d-block');
                        error.insertAfter(element);
                    },
                    highlight: function (element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function (element) {
                        $(element).removeClass('is-invalid');
                    },
                    rules: {
                        name: { required: true, maxlength: 120 },
                        size_key: { required: true, maxlength: 60, sizeKeyFormat: true },
                        width: { required: true, min: 1, max: 5000 },
                        height: { required: true, min: 1, max: 5000 }
                    }
                });
            }
        },

        init: function () {
            if (!$('#adSizesTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        AdSizesAdmin.init();
    });
})(window.jQuery);
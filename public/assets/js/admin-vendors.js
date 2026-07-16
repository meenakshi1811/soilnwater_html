(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var VendorsAdmin = {
        table: null,
        modal: null,
        vendorId: null,

        initTable: function () {
            this.table = $('#vendorsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '/admin/vendors/data' },
                columns: [
                    { data: 'company_name', name: 'company_name' },
                    { data: 'owner_name', name: 'user.name', orderable: false },
                    { data: 'owner_email', name: 'user.email', orderable: false },
                    { data: 'contact_numbers', name: 'phone', orderable: false },
                    { data: 'location', name: 'city', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'public_page_link', name: 'public_page_status', orderable: false, searchable: false },
                    { data: 'premium_toggle', name: 'is_premium', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[8, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('vendorModal'));

            $(document).on('click', '.js-edit-vendor', function () {
                var id = $(this).data('id');
                self.vendorId = id;
                $.get('/admin/vendors/' + id + '/edit').done(function (response) {
                    var v = response.vendor || {};
                    $('#vendorCompany').val(v.company_name || '');
                    $('#vendorContact').val(v.contact_person || '');
                    $('#vendorSlug').val(v.slug || '').trigger('input');
                    $('#vendorStatus').val(v.status || 'pending');
                    $('#vendorPhone').val(v.phone || '');
                    $('#vendorWhatsapp').val(v.whatsapp || '');
                    $('#vendorEmail').val(v.email || '');
                    $('#vendorCity').val(v.city || '');
                    $('#vendorState').val(v.state || '');
                    $('#vendorPincode').val(v.pincode || '');
                    $('#vendorAddress').val(v.address || '');
                    $('#vendorPan').val(v.pan_number || '');
                    $('input[name="has_gst"][value="' + (v.has_gst || '0') + '"]').prop('checked', true);
                    $('#vendorGst').val(v.gst_number || '');
                    $('#vendorGovernmentCertificate').val(v.government_certificate_number || '');
                    self.toggleGstField();
                    $('#vendorForm').attr('action', '/admin/vendors/' + id);
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load vendor.');
                });
            });

            $('#vendorSlug').on('input', function () {
                $('#slugPreview').text($(this).val() || 'slug');
            });

            $(document).on('change', '.js-vendor-has-gst', function () {
                self.toggleGstField();
            });

            $(document).on('click', '.js-approve-vendor', function () {
                var id = $(this).data('id');
                $.post('/admin/vendors/' + id + '/approve', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    });
            });

            $(document).on('click', '.js-reject-vendor', function () {
                var id = $(this).data('id');
                self.confirmReject(id);
            });

            $(document).on('click', '.js-delete-vendor', function () {
                if (!confirm('Delete this vendor permanently?')) return;
                var id = $(this).data('id');
                $.ajax({
                    url: '/admin/vendors/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                }).done(function (r) {
                    FormHelper.showToast('success', r.message);
                    self.table.ajax.reload(null, false);
                });
            });

            $(document).on('change', '.js-premium-toggle', function () {
                var checkbox = $(this);
                var id = checkbox.data('id');
                var nextState = checkbox.is(':checked');

                checkbox.prop('disabled', true);

                $.post('/admin/vendors/' + id + '/toggle-premium', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        checkbox.prop('checked', !!r.is_premium);
                        FormHelper.showToast('success', r.message);
                    })
                    .fail(function () {
                        checkbox.prop('checked', !nextState);
                        FormHelper.showToast('danger', 'Unable to update premium status.');
                    })
                    .always(function () {
                        checkbox.prop('disabled', false);
                    });
            });
        },

        toggleGstField: function () {
            var hasGst = $('input[name="has_gst"]:checked').val() === '1';
            $('#vendorGstWrap').toggleClass('d-none', !hasGst);
            $('#vendorGst').prop('required', hasGst);
            if (!hasGst) {
                $('#vendorGst').val('');
            }
        },

        confirmReject: function (id) {
            var self = this;
            var submitReject = function (reason) {
                $.post('/admin/vendors/' + id + '/reject', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    reason: reason
                })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    })
                    .fail(function (xhr) {
                        var message = xhr.responseJSON?.message
                            || xhr.responseJSON?.errors?.reason?.[0]
                            || 'Unable to reject vendor.';
                        FormHelper.showToast('danger', message);
                    });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Reject this vendor?',
                    input: 'textarea',
                    inputLabel: 'Rejection reason',
                    inputPlaceholder: 'Explain why this application is being rejected...',
                    inputAttributes: { 'aria-label': 'Rejection reason' },
                    showCancelButton: true,
                    confirmButtonText: 'Reject',
                    confirmButtonColor: '#dc3545',
                    inputValidator: function (value) {
                        if (!value || !String(value).trim()) {
                            return 'Please enter a rejection reason.';
                        }
                        if (String(value).trim().length < 5) {
                            return 'Reason must be at least 5 characters.';
                        }
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitReject(String(result.value || '').trim());
                    }
                });
                return;
            }

            var reason = prompt('Enter the rejection reason:');
            if (reason === null) {
                return;
            }
            reason = String(reason).trim();
            if (reason.length < 5) {
                FormHelper.showToast('danger', 'Reason must be at least 5 characters.');
                return;
            }
            submitReject(reason);
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#vendorForm',
                buttonSelector: '#vendorSubmitBtn',
                rules: {
                    company_name: { required: true },
                    slug: { required: true },
                    pan_number: { required: true },
                    has_gst: { required: true },
                    gst_number: {
                        required: function () {
                            return $('input[name="has_gst"]:checked').val() === '1';
                        }
                    }
                },
                beforeSubmit: function () {
                    $('#vendorForm').find('input[name="_method"]').remove();
                    $('<input type="hidden" name="_method" value="PUT">').appendTo('#vendorForm');
                },
                onSuccess: function (r) {
                    FormHelper.showToast('success', r.message);
                    self.table.ajax.reload(null, false);
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#vendorsTable').length) return;
            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () { VendorsAdmin.init(); });
})(window.jQuery);

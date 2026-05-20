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
                    { data: 'location', name: 'city', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'premium_toggle', name: 'is_premium', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']]
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
                    $('#vendorGst').val(v.gst_number || '');
                    $('#vendorForm').attr('action', '/admin/vendors/' + id);
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load vendor.');
                });
            });

            $('#vendorSlug').on('input', function () {
                $('#slugPreview').text($(this).val() || 'slug');
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
                if (!confirm('Reject this vendor?')) return;
                var id = $(this).data('id');
                $.post('/admin/vendors/' + id + '/reject', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    });
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

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#vendorForm',
                buttonSelector: '#vendorSubmitBtn',
                rules: {
                    company_name: { required: true },
                    slug: { required: true }
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

(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var ServiceProvidersAdmin = {
        table: null,
        modal: null,
        service_providerId: null,

        initTable: function () {
            this.table = $('#service_providersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: '/admin/services/data' },
                columns: [
                    { data: 'company_name', name: 'company_name' },
                    { data: 'owner_name', name: 'user.name', orderable: false },
                    { data: 'owner_email', name: 'user.email', orderable: false },
                    { data: 'contact_numbers', name: 'phone', orderable: false },
                    { data: 'location', name: 'city', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'public_page_badge', name: 'public_page_status', orderable: false },
                    { data: 'premium_toggle', name: 'is_premium', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[8, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('service_providerModal'));

            $(document).on('click', '.js-edit-service_provider', function () {
                var id = $(this).data('id');
                self.service_providerId = id;
                $.get('/admin/services/' + id + '/edit').done(function (response) {
                    var v = response.service_provider || {};
                    $('#service_providerCompany').val(v.company_name || '');
                    $('#service_providerContact').val(v.contact_person || '');
                    $('#service_providerSlug').val(v.slug || '').trigger('input');
                    $('#service_providerStatus').val(v.status || 'pending');
                    $('#service_providerPhone').val(v.phone || '');
                    $('#service_providerWhatsapp').val(v.whatsapp || '');
                    $('#service_providerEmail').val(v.email || '');
                    $('#service_providerCity').val(v.city || '');
                    $('#service_providerState').val(v.state || '');
                    $('#service_providerPincode').val(v.pincode || '');
                    $('#service_providerAddress').val(v.address || '');
                    $('#service_providerPan').val(v.pan_number || '');
                    $('input[name="has_gst"][value="' + (v.has_gst || '0') + '"]').prop('checked', true);
                    $('#service_providerGst').val(v.gst_number || '');
                    $('#service_providerGovernmentCertificate').val(v.government_certificate_number || '');
                    self.toggleGstField();
                    $('#service_providerForm').attr('action', '/admin/services/' + id);
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load service.');
                });
            });

            $('#service_providerSlug').on('input', function () {
                $('#slugPreview').text($(this).val() || 'slug');
            });

            $(document).on('change', '.js-service_provider-has-gst', function () {
                self.toggleGstField();
            });

            $(document).on('click', '.js-approve-service_provider', function () {
                var id = $(this).data('id');
                $.post('/admin/services/' + id + '/approve', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    });
            });

            $(document).on('click', '.js-approve-service-provider-page', function () {
                if (!confirm('Approve and publish this public page submission?')) return;
                var id = $(this).data('id');
                $.post('/admin/services/' + id + '/approve-public-page', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    })
                    .fail(function (xhr) {
                        FormHelper.showToast('danger', xhr.responseJSON?.message || 'Unable to approve the public page.');
                    });
            });

            $(document).on('click', '.js-reject-service_provider', function () {
                if (!confirm('Reject this service?')) return;
                var id = $(this).data('id');
                $.post('/admin/services/' + id + '/reject', { _token: $('meta[name="csrf-token"]').attr('content') })
                    .done(function (r) {
                        FormHelper.showToast('success', r.message);
                        self.table.ajax.reload(null, false);
                    });
            });

            $(document).on('click', '.js-delete-service_provider', function () {
                if (!confirm('Delete this service permanently?')) return;
                var id = $(this).data('id');
                $.ajax({
                    url: '/admin/services/' + id,
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

                $.post('/admin/services/' + id + '/toggle-premium', { _token: $('meta[name="csrf-token"]').attr('content') })
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
            $('#service_providerGstWrap').toggleClass('d-none', !hasGst);
            $('#service_providerGst').prop('required', hasGst);
            if (!hasGst) {
                $('#service_providerGst').val('');
            }
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#service_providerForm',
                buttonSelector: '#service_providerSubmitBtn',
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
                    $('#service_providerForm').find('input[name="_method"]').remove();
                    $('<input type="hidden" name="_method" value="PUT">').appendTo('#service_providerForm');
                },
                onSuccess: function (r) {
                    FormHelper.showToast('success', r.message);
                    self.table.ajax.reload(null, false);
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#service_providersTable').length) return;
            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () { ServiceProvidersAdmin.init(); });
})(window.jQuery);

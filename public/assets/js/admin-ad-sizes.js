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
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']]
            });
        },

        resetForm: function () {
            $('#adSizeForm')[0].reset();
            $('#adSizeId').val('');
            $('#adSizeForm').find('input[name="_method"]').remove();
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

            $(document).on('click', '.js-edit-ad-size', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#adSizeModalTitle').text('Edit Ad Size');
                self.resetForm();
                $('#adSizeId').val(id);

                $.get('/admin/ads/sizes/' + id, function (response) {
                    var size = response.size || {};
                    $('#adSizeName').val(size.name || '');
                    $('#adSizeKey').val(size.size_key || '');
                    $('#adSizeWidth').val(size.width || '');
                    $('#adSizeHeight').val(size.height || '');
                    $('#adSizeAdminOnly').prop('checked', !!size.admin_only);
                    $('#adSizeForm').attr('action', '/admin/ads/sizes/' + id).attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-delete-ad-size', function () {
                var id = $(this).data('id');
                if (!confirm('Are you sure you want to delete this ad size?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/ads/sizes/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
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
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#adSizeForm',
                buttonSelector: '#adSizeSubmitBtn',
                alertSelector: '#adSizeAlert',
                defaultText: 'Save Size',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, maxlength: 120 },
                    size_key: { required: true, maxlength: 60, pattern: /^[a-z0-9_]+$/ },
                    width: { required: true, min: 1, max: 5000 },
                    height: { required: true, min: 1, max: 5000 }
                },
                messages: {
                    size_key: { pattern: 'Use lowercase letters, numbers, and underscore only.' }
                },
                beforeSubmit: function () {
                    $('#adSizeForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#adSizeForm');
                    }
                },
                onSuccess: function (response) {
                    FormHelper.showToast('success', response.message || 'Saved successfully.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                },
                onError: function (xhr, message) {
                    FormHelper.showToast('danger', message || 'Unable to save ad size.');
                }
            });
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

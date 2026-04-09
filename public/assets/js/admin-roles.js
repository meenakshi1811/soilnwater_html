(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var RolesAdmin = {
        table: null,
        modal: null,
        isEdit: false,

        clearPermissionChecks: function () {
            $('.js-perm-check').prop('checked', false);
        },

        applyPermissionNames: function (names) {
            this.clearPermissionChecks();
            (names || []).forEach(function (name) {
                var parts = String(name).split('.');
                if (parts.length !== 2) {
                    return;
                }
                var module = parts[0];
                var action = parts[1];
                $('#perm_' + module + '_' + action).prop('checked', true);
            });
        },

        initTable: function () {
            this.table = $('#rolesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/roles/data'
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'permissions_count', name: 'permissions_count', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[2, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('roleModal'));

            $('#openRoleModalBtn').on('click', function () {
                self.isEdit = false;
                $('#roleModalTitle').text('Add Role');
                $('#roleForm')[0].reset();
                $('#roleId').val('');
                self.clearPermissionChecks();
                $('#roleForm').attr('action', '/admin/roles').attr('method', 'POST');
                self.modal.show();
            });

            $(document).on('click', '.js-edit-role', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#roleModalTitle').text('Edit Role');
                $('#roleForm')[0].reset();
                $('#roleId').val(id);

                $.get('/admin/roles/' + id, function (response) {
                    var role = response.role || {};
                    $('#roleName').val(role.name || '');
                    self.applyPermissionNames(role.permission_names || []);
                    $('#roleForm').attr('action', '/admin/roles/' + id).attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-delete-role', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this role? Employees assigned to it cannot be deleted until reassigned.')) {
                    return;
                }

                $.ajax({
                    url: '/admin/roles/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#roleAlert'), 'success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete role.';
                    FormHelper.showAlert($('#roleAlert'), 'danger', msg);
                });
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#roleForm',
                buttonSelector: '#roleSubmitBtn',
                alertSelector: '#roleAlert',
                defaultText: 'Save Role',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 2, maxlength: 255 }
                },
                messages: {
                    name: { required: 'Please enter a role name.' }
                },
                beforeSubmit: function () {
                    $('#roleForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#roleForm');
                    }
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#roleAlert'), 'success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#rolesTable').length) {
                return;
            }
            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        RolesAdmin.init();
    });
})(window.jQuery);

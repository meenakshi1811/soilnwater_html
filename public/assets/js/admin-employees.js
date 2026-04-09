(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var EmployeesAdmin = {
        table: null,
        modal: null,
        isEdit: false,

        loadRoles: function () {
            return $.get('/admin/roles/options').done(function (response) {
                var $select = $('#employeeRoleId');
                var current = $select.val();
                $select.find('option:not(:first)').remove();
                (response.roles || []).forEach(function (r) {
                    $select.append(
                        $('<option></option>').attr('value', r.id).text(r.name)
                    );
                });
                if (current) {
                    $select.val(current);
                }
            });
        },

        initTable: function () {
            this.table = $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/employees/data'
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone_number', name: 'phone_number' },
                    { data: 'role_name', name: 'role_name', orderable: false, searchable: true },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: true },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('employeeModal'));

            $('#openEmployeeModalBtn').on('click', function () {
                self.isEdit = false;
                $('#employeeModalTitle').text('Add Employee');
                $('#employeeForm')[0].reset();
                $('#employeeId').val('');
                $('#employeeStatus').prop('checked', true);
                self.loadRoles().always(function () {
                    $('#employeeForm').attr('action', '/admin/employees').attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-edit-employee', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#employeeModalTitle').text('Edit Employee');
                $('#employeeForm')[0].reset();
                $('#employeeId').val(id);

                $.when($.get('/admin/employees/' + id), self.loadRoles()).done(function (a) {
                    var response = a[0];
                    var employee = response.employee || {};
                    $('#employeeName').val(employee.name || '');
                    $('#employeeEmail').val(employee.email || '');
                    $('#employeePhone').val(employee.phone_number || '');
                    $('#employeeRoleId').val(employee.role_id ? String(employee.role_id) : '');
                    $('#employeeStatus').prop('checked', !!employee.is_active);
                    $('#employeePassword').val('');
                    $('#employeePasswordConfirmation').val('');
                    $('#employeeForm').attr('action', '/admin/employees/' + id).attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-delete-employee', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this employee?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/employees/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#employeeAlert'), 'success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function () {
                    FormHelper.showAlert($('#employeeAlert'), 'danger', 'Unable to delete employee.');
                });
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#employeeForm',
                buttonSelector: '#employeeSubmitBtn',
                alertSelector: '#employeeAlert',
                defaultText: 'Save Employee',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 3 },
                    email: { required: true, email: true },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 },
                    role_id: { required: true },
                    password: {
                        required: function () {
                            return !self.isEdit;
                        },
                        minlength: 8
                    },
                    password_confirmation: {
                        required: function () {
                            if (!self.isEdit) {
                                return true;
                            }

                            return $('#employeePassword').val().length > 0;
                        },
                        equalTo: '#employeePassword'
                    }
                },
                messages: {
                    role_id: { required: 'Please select a role.' }
                },
                beforeSubmit: function () {
                    $('#employeeForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#employeeForm');
                    }

                    $('#employeeForm').find('input[name="is_active"]').remove();
                    $('<input type="hidden" name="is_active">')
                        .val($('#employeeStatus').is(':checked') ? '1' : '0')
                        .appendTo('#employeeForm');
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#employeeAlert'), 'success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#employeesTable').length) {
                return;
            }
            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        EmployeesAdmin.init();
    });
})(window.jQuery);

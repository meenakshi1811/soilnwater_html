(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var UsersAdmin = {
        table: null,
        modal: null,

        initTable: function () {
            this.table = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/users/data'
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email_display', name: 'email' },
                    { data: 'phone_display', name: 'phone_number' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: true },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('userModal'));

            $(document).on('click', '.js-edit-user', function () {
                var id = $(this).data('id');
                $('#userForm')[0].reset();
                $('#userId').val(id);

                $.get('/admin/users/' + id).done(function (response) {
                    var user = response.user || {};
                    $('#userName').val(user.name || '');
                    $('#userEmail').val(user.email || '');
                    $('#userPhone').val(user.phone_number || '');
                    $('#userStatus').prop('checked', !!user.is_active);
                    $('#userForm').attr('action', '/admin/users/' + id).attr('method', 'POST');
                    self.modal.show();
                }).fail(function () {
                    FormHelper.showToast('danger', 'Unable to load user details.');
                });
            });

            $(document).on('click', '.js-delete-user', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this user?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/users/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showToast('success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete user.';
                    FormHelper.showToast('danger', message);
                });
            });
        },

        initForm: function () {
            var self = this;

            FormHelper.attachAjaxForm({
                formSelector: '#userForm',
                buttonSelector: '#userSubmitBtn',
                alertSelector: '#userAlert',
                defaultText: 'Save User',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 3 },
                    email: { required: true, email: true },
                    phone_number: { required: true, digits: true, minlength: 10, maxlength: 15 }
                },
                beforeSubmit: function () {
                    $('#userForm').find('input[name="_method"]').remove();
                    $('<input type="hidden" name="_method" value="PUT">').appendTo('#userForm');

                    $('#userForm').find('input[name="is_active"]').remove();
                    $('<input type="hidden" name="is_active">')
                        .val($('#userStatus').is(':checked') ? '1' : '0')
                        .appendTo('#userForm');
                },
                onSuccess: function (response) {
                    FormHelper.showToast('success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                },
                onError: function (xhr, message) {
                    if (xhr.status === 422) {
                        FormHelper.showToast('warning', 'Please fix the highlighted fields and try again.');
                        return;
                    }

                    FormHelper.showToast('danger', message);
                }
            });
        },

        init: function () {
            if (!$('#usersTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        UsersAdmin.init();
    });
})(window.jQuery);

(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var TermsAdmin = {
        table: null,
        modal: null,
        isEdit: false,
        editor: null,

        initEditor: function () {
            var self = this;
            return ClassicEditor
                .create(document.querySelector('#termsContent'))
                .then(function (editor) {
                    self.editor = editor;
                });
        },

        loadModuleOptions: function (editingId, selected) {
            return $.get('/admin/terms-and-conditions/modules', { editing_id: editingId || '' }).done(function (response) {
                var $select = $('#moduleKey');
                $select.find('option:not(:first)').remove();

                (response.modules || []).forEach(function (module) {
                    $select.append(
                        $('<option></option>').attr('value', module.key).text(module.name)
                    );
                });

                if (selected) {
                    $select.val(selected);
                }
            });
        },

        initTable: function () {
            this.table = $('#termsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/terms-and-conditions/data'
                },
                columns: [
                    { data: 'module_name', name: 'module_name' },
                    { data: 'content_preview', name: 'content', orderable: false, searchable: false },
                    { data: 'updated_at', name: 'updated_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[2, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('termsModal'));

            $('#openTermsModalBtn').on('click', function () {
                self.isEdit = false;
                $('#termsModalTitle').text('Add Terms & Conditions');
                $('#termsForm')[0].reset();
                $('#termsId').val('');
                if (self.editor) {
                    self.editor.setData('');
                }

                self.loadModuleOptions().always(function () {
                    $('#termsForm').attr('action', '/admin/terms-and-conditions').attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-edit-terms', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#termsModalTitle').text('Edit Terms & Conditions');
                $('#termsForm')[0].reset();
                $('#termsId').val(id);

                $.get('/admin/terms-and-conditions/' + id).done(function (response) {
                    var item = response.item || {};
                    self.loadModuleOptions(id, item.module_key).always(function () {
                        if (self.editor) {
                            self.editor.setData(item.content || '');
                        }
                        $('#termsForm').attr('action', '/admin/terms-and-conditions/' + id).attr('method', 'POST');
                        self.modal.show();
                    });
                });
            });

            $(document).on('click', '.js-delete-terms', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this terms and conditions entry?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/terms-and-conditions/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#termsAlert'), 'success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function () {
                    FormHelper.showAlert($('#termsAlert'), 'danger', 'Unable to delete terms and conditions.');
                });
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#termsForm',
                buttonSelector: '#termsSubmitBtn',
                alertSelector: '#termsAlert',
                defaultText: 'Save Terms',
                loadingText: 'Saving...',
                rules: {
                    module_key: { required: true },
                    content: { required: true, minlength: 10 }
                },
                messages: {
                    module_key: { required: 'Please select a module.' },
                    content: { required: 'Please enter terms and conditions content.' }
                },
                beforeSubmit: function () {
                    $('#termsForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#termsForm');
                    }

                    if (self.editor) {
                        $('#termsContent').val(self.editor.getData());
                    }
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#termsAlert'), 'success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#termsTable').length) {
                return;
            }

            var self = this;
            this.initEditor().then(function () {
                self.initTable();
                self.bindUi();
                self.initForm();
            });
        }
    };

    $(function () {
        TermsAdmin.init();
    });
})(window.jQuery);

(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var FoulWordsAdmin = {
        table: null,
        modal: null,
        isEdit: false,

        initTable: function () {
            this.table = $('#foulWordsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: $('#foulWordsTable').data('source-url')
                },
                columns: [
                    { data: 'word', name: 'word' },
                    { data: 'status_badge', name: 'status_badge', orderable: false },
                    { data: 'status_toggle', name: 'status_toggle', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']]
            });
        },

        resetForm: function () {
            var $form = $('#foulWordForm');
            $form[0].reset();
            $('#foulWordId').val('');
            $('#foulWordActive').prop('checked', true);
            if ($form.data('validator')) {
                $form.validate().resetForm();
            }
            $form.find('.is-invalid').removeClass('is-invalid');
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('foulWordModal'));

            $('#openFoulWordModalBtn').on('click', function () {
                self.isEdit = false;
                $('#foulWordModalTitle').text('Add Foul Word');
                self.resetForm();
                $('#foulWordForm').attr('action', '/admin/foul-words').attr('method', 'POST');
                self.modal.show();
            });

            $(document).on('click', '.js-edit-foul-word', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#foulWordModalTitle').text('Edit Foul Word');
                self.resetForm();
                $('#foulWordId').val(id);

                $.get('/admin/foul-words/' + id, function (response) {
                    var word = response.foul_word || {};
                    $('#foulWordValue').val(word.word || '');
                    $('#foulWordActive').prop('checked', !!word.is_active);
                    $('#foulWordForm').attr('action', '/admin/foul-words/' + id).attr('method', 'POST');
                    self.modal.show();
                });
            });

            $(document).on('click', '.js-delete-foul-word', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this foul word?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/foul-words/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#foulWordAlert'), 'success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete foul word.';
                    FormHelper.showAlert($('#foulWordAlert'), 'danger', msg);
                });
            });

            $(document).on('change', '.js-toggle-foul-word', function () {
                var $toggle = $(this);
                var id = $toggle.data('id');
                var willActivate = $toggle.is(':checked');

                $toggle.prop('disabled', true);

                $.ajax({
                    url: '/admin/foul-words/' + id + '/toggle-status',
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#foulWordAlert'), 'success', response.message || 'Updated.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    $toggle.prop('checked', !willActivate).prop('disabled', false);
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update status.';
                    FormHelper.showAlert($('#foulWordAlert'), 'danger', msg);
                });
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#foulWordForm',
                buttonSelector: '#foulWordSubmitBtn',
                alertSelector: '#foulWordAlert',
                defaultText: 'Save Word',
                loadingText: 'Saving...',
                rules: {
                    word: { required: true, minlength: 1, maxlength: 80 }
                },
                beforeSubmit: function () {
                    $('#foulWordForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#foulWordForm');
                    }
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#foulWordAlert'), 'success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#foulWordsTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        FoulWordsAdmin.init();
    });
})(jQuery);

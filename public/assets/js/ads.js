(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    function initUserAdsTable() {
        var $table = $('#userAdsTable');
        if (!$table.length || !$.fn.DataTable) return;

        $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url')
            },
            order: [[4, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'template_name', name: 'template.name', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(3).html(data.status_badge);
                $(row).find('td').eq(5).html(data.actions);
            }
        });
    }

    function initAdminTemplatesTable() {
        var $table = $('#adminAdTemplatesTable');
        if (!$table.length || !$.fn.DataTable) return;

        $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url'),
                data: function (d) {
                    var sizeType = $('select[name="size_type"]').val();
                    if (sizeType) d.size_type = sizeType;
                }
            },
            order: [[4, 'desc']],
            columns: [
                { data: 'preview_html', name: 'preview_html', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(0).html(data.preview_html);
                $(row).find('td').eq(3).html(data.status_badge);
                $(row).find('td').eq(5).html(data.actions);
            }
        });
    }

    function initAdminSubmissionsTable() {
        var $table = $('#adminAdSubmissionsTable');
        if (!$table.length || !$.fn.DataTable) return;

        var dt = $table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $table.data('url'),
                data: function (d) {
                    var sizeType = $('#adminAdsFilterSize').val();
                    var status = $('#adminAdsFilterStatus').val();
                    if (sizeType) d.size_type = sizeType;
                    if (status) d.status = status;
                }
            },
            order: [[5, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'user_name', name: 'user.full_name', orderable: false, searchable: false },
                { data: 'size_label', name: 'size_type', orderable: false, searchable: false },
                { data: 'template_name', name: 'template.name', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'submitted_at', name: 'submitted_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            createdRow: function (row, data) {
                $(row).find('td').eq(4).html(data.status_badge);
                $(row).find('td').eq(6).html(data.actions);
            }
        });

        $('#adminAdsApplyFilters').on('click', function () {
            dt.ajax.reload();
        });
    }

    function initAjaxAdSubmit() {
        var $form = $('form[action*="/dashboard/ads/create/"]');
        if (!$form.length) return;

        var $submit = $form.find('button[type="submit"]').first();
        FormHelper.attachAjaxForm({
            formSelector: $form,
            buttonSelector: $submit,
            alertSelector: '#adCustomizeAlert',
            defaultText: $submit.text().trim() || 'Submit',
            loadingText: 'Submitting...',
            onSuccess: function (response) {
                FormHelper.showToast('success', response.message || 'Submitted.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                FormHelper.showToast('danger', message || 'Failed to submit.');
            }
        });
    }

    function initAjaxTemplateForm() {
        var $form = $('form[action*="/admin/ads/templates"]');
        if (!$form.length) return;

        var $submit = $form.find('button[type="submit"]').first();
        FormHelper.attachAjaxForm({
            formSelector: $form,
            buttonSelector: $submit,
            alertSelector: '#adminAdTemplateAlert',
            defaultText: $submit.text().trim() || 'Save',
            loadingText: 'Saving...',
            onSuccess: function (response) {
                FormHelper.showToast('success', response.message || 'Saved.');
                if (response.redirect_url) {
                    setTimeout(function () {
                        window.location.href = response.redirect_url;
                    }, 700);
                }
            },
            onError: function (message) {
                FormHelper.showToast('danger', message || 'Failed to save.');
            }
        });
    }

    function initAjaxApprovalActions() {
        var $approveForm = $('form[action*="/admin/ads/submissions/"][action$="/approve"]');
        var $rejectForm = $('form[action*="/admin/ads/submissions/"][action$="/reject"]');

        if ($approveForm.length) {
            $approveForm.on('submit', function (e) {
                e.preventDefault();
                var $btn = $approveForm.find('button[type="submit"]');
                $btn.prop('disabled', true);
                var fd = new FormData($approveForm.get(0));
                $.ajax({
                    url: $approveForm.attr('action'),
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    FormHelper.showToast('success', (res && res.message) ? res.message : 'Approved.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.';
                    FormHelper.showToast('danger', msg);
                    $btn.prop('disabled', false);
                });
            });
        }

        if ($rejectForm.length) {
            $rejectForm.on('submit', function (e) {
                e.preventDefault();
                var $btn = $rejectForm.find('button[type="submit"]');
                $btn.prop('disabled', true);
                var fd = new FormData($rejectForm.get(0));
                $.ajax({
                    url: $rejectForm.attr('action'),
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false
                }).done(function (res) {
                    FormHelper.showToast('success', (res && res.message) ? res.message : 'Rejected.');
                    setTimeout(function () { window.location.reload(); }, 600);
                }).fail(function (xhr) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : null;
                    var msg = errors && errors.review_note && errors.review_note[0]
                        ? errors.review_note[0]
                        : ((xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Failed.');
                    FormHelper.showToast('danger', msg);
                    $btn.prop('disabled', false);
                });
            });
        }
    }

    $(function () {
        initUserAdsTable();
        initAdminTemplatesTable();
        initAdminSubmissionsTable();
        initAjaxAdSubmit();
        initAjaxTemplateForm();
        initAjaxApprovalActions();
    });
})(window.jQuery);

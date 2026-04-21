(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var CategoriesAdmin = {
        table: null,
        modal: null,
        isEdit: false,

        clearModuleChecks: function () {
            $('.js-module-check').prop('checked', false);
        },

        setModuleChecks: function (slugs) {
            this.clearModuleChecks();
            (slugs || []).forEach(function (slug) {
                $('#category_module_' + slug).prop('checked', true);
            });
        },

        selectedModules: function () {
            return $('.js-module-check:checked').map(function () {
                return $(this).val();
            }).get();
        },

        appendHiddenModules: function () {
            $('#categoryForm').find('input[name="modules[]"][data-generated="1"]').remove();
            this.selectedModules().forEach(function (slug) {
                $('<input type="hidden" name="modules[]" data-generated="1">').val(slug).appendTo('#categoryForm');
            });
        },

        loadParents: function (selectedParentId, excludeId) {
            return $.get('/admin/categories/parents/options', { exclude_id: excludeId || '' }).done(function (response) {
                var $select = $('#categoryParentId');
                var current = selectedParentId || '';
                $select.find('option:not(:first)').remove();

                (response.categories || []).forEach(function (item) {
                    $select.append(
                        $('<option></option>')
                            .attr('value', item.id)
                            .attr('data-modules', JSON.stringify(item.modules || []))
                            .text(item.name)
                    );
                });

                $select.val(current ? String(current) : '');
                $select.trigger('change');
            });
        },

        syncModulesForParent: function () {
            var selected = $('#categoryParentId option:selected');
            var parentId = $('#categoryParentId').val();
            var isSubCategory = !!parentId;
            var inherited = [];

            if (isSubCategory) {
                try {
                    inherited = JSON.parse(selected.attr('data-modules') || '[]');
                } catch (e) {
                    inherited = [];
                }
                this.setModuleChecks(inherited);
            }

            $('.js-module-check').prop('disabled', isSubCategory);
            $('#modulesHelpText').text(
                isSubCategory
                    ? 'Modules are inherited automatically from the selected parent category.'
                    : 'Select one or more modules for this category.'
            );
        },

        initTable: function () {
            this.table = $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/categories/data'
                },
                columns: [
                    { data: 'category_name', name: 'category_name', orderable: false, searchable: true },
                    { data: 'subcategory_name', name: 'subcategory_name', orderable: false, searchable: true },
                    { data: 'modules_list', name: 'modules_list', orderable: false, searchable: true },
                    { data: 'children_count', name: 'children_count', searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[4, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('categoryModal'));

            $('#openCategoryModalBtn').on('click', function () {
                self.isEdit = false;
                $('#categoryModalTitle').text('Add Category');
                $('#categoryForm')[0].reset();
                $('#categoryId').val('');
                self.clearModuleChecks();

                self.loadParents('', '').always(function () {
                    $('#categoryForm').attr('action', '/admin/categories').attr('method', 'POST');
                    self.modal.show();
                });
            });

            $('#categoryParentId').on('change', function () {
                self.syncModulesForParent();
            });

            $(document).on('click', '.js-edit-category', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#categoryModalTitle').text('Edit Category');
                $('#categoryForm')[0].reset();
                $('#categoryId').val(id);

                $.get('/admin/categories/' + id, function (response) {
                    var category = response.category || {};

                    $('#categoryName').val(category.name || '');
                    self.setModuleChecks(category.modules || []);
                    self.loadParents(category.parent_id || '', id).always(function () {
                        $('#categoryForm').attr('action', '/admin/categories/' + id).attr('method', 'POST');
                        self.modal.show();
                    });
                });
            });

            $(document).on('click', '.js-delete-category', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this category?')) {
                    return;
                }

                $.ajax({
                    url: '/admin/categories/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#categoryAlert'), 'success', response.message || 'Deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete category.';
                    FormHelper.showAlert($('#categoryAlert'), 'danger', msg);
                });
            });
        },

        initForm: function () {
            var self = this;
            FormHelper.attachAjaxForm({
                formSelector: '#categoryForm',
                buttonSelector: '#categorySubmitBtn',
                alertSelector: '#categoryAlert',
                defaultText: 'Save Category',
                loadingText: 'Saving...',
                rules: {
                    name: { required: true, minlength: 2, maxlength: 255 }
                },
                beforeSubmit: function () {
                    $('#categoryForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#categoryForm');
                    }

                    self.appendHiddenModules();
                },
                onSuccess: function (response) {
                    FormHelper.showAlert($('#categoryAlert'), 'success', response.message || 'Saved.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                    self.modal.hide();
                }
            });
        },

        init: function () {
            if (!$('#categoriesTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        CategoriesAdmin.init();
    });
})(window.jQuery);

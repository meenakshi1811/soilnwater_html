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
            $('#categoryForm').find('input[name="modules_present"][data-generated="1"]').remove();
            $('<input type="hidden" name="modules_present" value="1" data-generated="1">').appendTo('#categoryForm');
            $('#categoryForm').find('input[name="modules[]"][data-generated="1"]').remove();
            this.selectedModules().forEach(function (slug) {
                $('<input type="hidden" name="modules[]" data-generated="1">').val(slug).appendTo('#categoryForm');
            });
        },

        syncAdsPriceState: function () {
            var hasAdsModule = this.selectedModules().indexOf('ads') !== -1;
            var $input = $('#categoryAdsPrice');
            var $help = $('#adsPriceHelpText');
            if (!$input.length) return;

            if (!hasAdsModule) {
                $input.val('0').prop('readonly', true);
                $help.text('Ads module is not selected, so ads price is fixed to 0.00 (Free).');
                return;
            }

            $input.prop('readonly', false);
            $help.text('Set ad posting price for this category/subcategory. 0.00 means Free.');
        },
        
        syncNameFields: function () {
            var subcategoryName = $.trim($('#subcategoryName').val());
            var parentId = $('#categoryParentId').val();
            var useSubcategory = subcategoryName.length > 0;
            $('#categoryParentId').prop('required', useSubcategory);
            $('#categoryName').prop('readonly', useSubcategory);

            if (useSubcategory) {
                $('#categoryName').val(subcategoryName);
            }

            if (!useSubcategory && parentId) {
                $('#categoryParentId').val('').trigger('change');
            }
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

            $('#modulesHelpText').text(
                isSubCategory
                    ? 'All parent category modules are selected by default. You can deselect modules for this sub category.'
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
                    { data: 'ads_price_display', name: 'ads_price_display', orderable: false, searchable: false },
                    { data: 'children_count', name: 'children_count', searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']]
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
                $('#categoryName').val('');
                $('#subcategoryName').val('');
                self.clearModuleChecks();
                $('#categoryAdsPrice').val('0');

                self.loadParents('', '').always(function () {
                    $('#categoryForm').attr('action', '/admin/categories').attr('method', 'POST');
                    self.syncNameFields();
                    self.syncAdsPriceState();
                    self.modal.show();
                });
            });

            $('#categoryName, #subcategoryName').on('input', function () {
                self.syncNameFields();
            });

            $('#categoryParentId').on('change', function () {
                self.syncModulesForParent();
                self.syncAdsPriceState();
            });

            $('.js-module-check').on('change', function () {
                self.syncAdsPriceState();
            });

            $(document).on('click', '.js-edit-category', function () {
                var id = $(this).data('id');
                self.isEdit = true;
                $('#categoryModalTitle').text('Edit Category');
                $('#categoryForm')[0].reset();
                $('#categoryId').val(id);
                $('#categoryName').val('');
                $('#subcategoryName').val('');

                $.get('/admin/categories/' + id, function (response) {
                    var category = response.category || {};

                    if (category.parent_id) {
                        $('#subcategoryName').val(category.name || '');
                    } else {
                        $('#categoryName').val(category.name || '');
                    }
                    self.setModuleChecks(category.modules || []);
                    $('#categoryAdsPrice').val(category.ads_price || 0);
                    self.loadParents(category.parent_id || '', id).always(function () {
                        self.syncNameFields();
                        self.syncAdsPriceState();
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
                    name: { required: true, minlength: 2, maxlength: 255 },
                    parent_id: {
                        required: function () {
                            return $.trim($('#subcategoryName').val()).length > 0;
                        }
                    }
                },
                messages: {
                    parent_id: {
                        required: 'Please choose a parent category for the sub category.'
                    }
                },
                beforeSubmit: function () {
                    $('#categoryForm').find('input[name="_method"]').remove();
                    if (self.isEdit) {
                        $('<input type="hidden" name="_method" value="PUT">').appendTo('#categoryForm');
                    }

                    self.syncNameFields();
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

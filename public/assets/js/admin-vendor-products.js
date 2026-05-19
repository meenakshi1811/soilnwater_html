(function ($) {
    if (!$) return;

    var token = $('meta[name="csrf-token"]').attr('content');
    var table = null;

    function toast(type, message) {
        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(type, message);
            return;
        }

        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    function refreshTable() {
        if (table && typeof table.ajax !== 'undefined') {
            table.ajax.reload(null, false);
        }
    }

    function postAction(url, successMessage) {
        $.post(url, { _token: token })
            .done(function (r) {
                toast('success', r.message || successMessage);
                refreshTable();
            })
            .fail(function (xhr) {
                toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to process request.');
            });
    }

    function deleteAction(id) {
        var proceed = function () {
            $.ajax({
                url: '/admin/vendor-products/' + id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token }
            })
                .done(function (r) {
                    toast('success', r.message || 'Product deleted successfully.');
                    refreshTable();
                })
                .fail(function (xhr) {
                    toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete product.');
                });
        };

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Are you sure?',
                text: 'Delete this product permanently?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    proceed();
                }
            });
            return;
        }

        if (confirm('Delete this product permanently?')) {
            proceed();
        }
    }

    $(function () {
        if ($('#vendorProductsTable').length && $.fn.DataTable) {
            table = $('#vendorProductsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/admin/vendor-products/data',
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'category_display', name: 'category', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']]
            });

            $('#statusFilter').on('change', function () {
                table.ajax.reload();
            });
        }

        $(document).on('click', '.js-approve', function () {
            var id = $(this).data('id');
            postAction('/admin/vendor-products/' + id + '/approve', 'Product approved.');
        });

        $(document).on('click', '.js-reject', function () {
            var id = $(this).data('id');
            postAction('/admin/vendor-products/' + id + '/reject', 'Product rejected.');
        });

        $(document).on('click', '.js-delete', function () {
            var id = $(this).data('id');
            deleteAction(id);
        });
    });
})(window.jQuery);

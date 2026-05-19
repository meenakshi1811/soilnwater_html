(function ($) {
    if (!$) return;

    var token = $('meta[name="csrf-token"]').attr('content');
    var table = null;

    function toast(type, message) {
        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(type, message);
            return;
        }
        alert(message);
    }

    function postAction(url, successMessage, redirectToIndex) {
        $.post(url, { _token: token })
            .done(function (r) {
                toast('success', r.message || successMessage);
                if (table) {
                    table.ajax.reload(null, false);
                }
                if (redirectToIndex) {
                    window.location.href = '/admin/vendor-products';
                } else {
                    window.location.reload();
                }
            });
    }

    function deleteAction(id, redirectToIndex) {
        if (!confirm('Delete this product permanently?')) return;
        $.ajax({
            url: '/admin/vendor-products/' + id,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token }
        }).done(function (r) {
            toast('success', r.message || 'Product deleted successfully.');
            if (table) {
                table.ajax.reload(null, false);
            }
            if (redirectToIndex) {
                window.location.href = '/admin/vendor-products';
            }
        });
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
            var onShowPage = !$('#vendorProductsTable').length;
            postAction('/admin/vendor-products/' + id + '/approve', 'Product approved.', onShowPage);
        });

        $(document).on('click', '.js-reject', function () {
            var id = $(this).data('id');
            var onShowPage = !$('#vendorProductsTable').length;
            postAction('/admin/vendor-products/' + id + '/reject', 'Product rejected.', onShowPage);
        });

        $(document).on('click', '.js-delete', function () {
            var id = $(this).data('id');
            var onShowPage = !$('#vendorProductsTable').length;
            deleteAction(id, onShowPage);
        });
    });
})(window.jQuery);

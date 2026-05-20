(function ($) {
    if (!$) return;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content')
            || $('input[name="_token"]').first().val()
            || (window.Laravel && window.Laravel.csrfToken)
            || '';
    }
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

        if (window.jQuery && window.jQuery.toastr && typeof window.jQuery.toastr[type] === 'function') {
            window.jQuery.toastr[type](message);
            return;
        }

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon: type === 'error' ? 'error' : 'success', text: message });
            return;
        }

        alert(message);
    }

    function refreshTable() {
        if (table && typeof table.ajax !== 'undefined') {
            table.ajax.reload(null, false);
            return true;
        }

        return false;
    }

    function postAction(url, successMessage) {
        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: csrfToken() },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            }
        })
            .done(function (r) {
                toast('success', r.message || successMessage);
                if (!refreshTable()) {
                    window.location.reload();
                }
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
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                data: { _token: csrfToken() }
            })
                .done(function (r) {
                    toast('success', r.message || 'Product deleted successfully.');
                    if (!refreshTable()) {
                        window.location.href = '/admin/vendor-products';
                    }
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

        if (window.swal && typeof window.swal === 'function') {
            window.swal({
                title: 'Are you sure?',
                text: 'Delete this product permanently?',
                icon: 'warning',
                buttons: true,
                dangerMode: true
            }).then(function (ok) {
                if (ok) proceed();
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
                    url: '/admin/vendor-products/data'
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

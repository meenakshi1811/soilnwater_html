(function ($) {
    if (!$) return;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content')
            || $('input[name="_token"]').first().val()
            || (window.Laravel && window.Laravel.csrfToken)
            || '';
    }

    function toast(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    function deletePost(slug) {
        var baseUrl = $('#myCommunityPostsTable').data('delete-base-url') || '/dashboard/community-posts';
        var proceed = function () {
            $.ajax({
                url: baseUrl + '/' + slug,
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken()
                },
                data: { _token: csrfToken() }
            })
                .done(function (response) {
                    toast('success', response.message || 'Community post deleted successfully.');
                    $('#myCommunityPostsTable').DataTable().ajax.reload(null, false);
                })
                .fail(function (xhr) {
                    toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to delete this post.');
                });
        };

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Delete this post?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545'
            }).then(function (result) {
                if (result.isConfirmed) {
                    proceed();
                }
            });
            return;
        }

        if (confirm('Delete this post?')) {
            proceed();
        }
    }

    $(document).on('click', '.js-delete-post', function () {
        deletePost($(this).data('slug'));
    });

    if ($('#myCommunityPostsTable').length) {
        $('#myCommunityPostsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#myCommunityPostsTable').data('source-url'),
            order: [[5, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'writing_purpose_display', name: 'writing_purpose' },
                { data: 'type_label', name: 'content_type', orderable: false, searchable: false },
                { data: 'category_display', name: 'category' },
                { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                { data: 'published_display', name: 'published_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ]
        });
    }
})(window.jQuery);

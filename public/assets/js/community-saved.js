(function ($) {
    if (!$) return;

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    $(document).on('click', '.js-unsave-post', function () {
        var slug = $(this).data('slug');
        var baseUrl = $('#communitySavedPostsTable').data('unsave-base-url') || '/community';

        $.ajax({
            url: baseUrl + '/' + slug + '/save',
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken()
            },
            data: { _token: csrfToken() }
        }).done(function (response) {
            if (window.toastr) {
                window.toastr.success(response.message || 'Post removed from saved list.');
            }
            $('#communitySavedPostsTable').DataTable().ajax.reload(null, false);
        }).fail(function (xhr) {
            if (window.toastr) {
                window.toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Unable to remove saved post.');
            }
        });
    });

    if ($('#communitySavedPostsTable').length) {
        $('#communitySavedPostsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#communitySavedPostsTable').data('source-url'),
            order: [[4, 'desc']],
            columns: [
                { data: 'title', name: 'title' },
                { data: 'type_label', name: 'type_label', orderable: false, searchable: false },
                { data: 'category_display', name: 'category_display', orderable: false, searchable: false },
                { data: 'published_display', name: 'published_display', orderable: false, searchable: false },
                { data: 'saved_display', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ]
        });
    }
})(window.jQuery);

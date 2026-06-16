(function ($) {
    if (!$) return;

    if ($('#communityPostReportsTable').length) {
        $('#communityPostReportsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#communityPostReportsTable').data('source-url'),
            order: [[3, 'desc']],
            columns: [
                { data: 'post_title', name: 'post_title', orderable: false, searchable: false },
                { data: 'reporter_name', name: 'reporter_name', orderable: false, searchable: false },
                { data: 'reason', name: 'reason' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ]
        });
    }
})(window.jQuery);

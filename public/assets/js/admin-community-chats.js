(function ($) {
    if (!$) {
        return;
    }

    var table = $('#communityChatsTable');
    if (!table.length) {
        return;
    }

    table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: table.data('source-url')
        },
        columns: [
            { data: 'chat_title', name: 'chat_title', orderable: false },
            { data: 'chat_type', name: 'chat_type', orderable: false },
            { data: 'author_name', name: 'author_name', orderable: false },
            { data: 'messages_count', name: 'messages_count', searchable: false, orderable: false },
            { data: 'last_activity', name: 'last_activity', searchable: false, orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [],
        ordering: false
    });
})(jQuery);

(function ($) {
    if (!$) {
        return;
    }

    var $table = $('#communityChatUsersTable');
    if (!$table.length) {
        return;
    }

    var table = $table.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $table.data('source-url')
        },
        columns: [
            { data: 'name_display', name: 'name_display', orderable: false },
            { data: 'email', name: 'email' },
            { data: 'role_badge', name: 'role_badge', orderable: false },
            { data: 'chat_status', name: 'chat_status', orderable: false },
            { data: 'chat_toggle', name: 'chat_toggle', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' }
        ],
        order: [[5, 'desc']]
    });

    function showAlert(type, message) {
        var $alert = $('#chatUsersAlert');
        $alert.removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type)
            .text(message);
    }

    $(document).on('change', '.js-toggle-chat-block', function () {
        var $toggle = $(this);
        var id = $toggle.data('id');
        var willBlock = $toggle.is(':checked');

        $toggle.prop('disabled', true);

        $.ajax({
            url: '/admin/community-chats/users/' + id + '/toggle-block',
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        }).done(function (response) {
            showAlert(response.is_chat_blocked ? 'warning' : 'success', response.message || 'Updated.');
            table.ajax.reload(null, false);
        }).fail(function (xhr) {
            $toggle.prop('checked', !willBlock).prop('disabled', false);
            var message = (xhr.responseJSON && xhr.responseJSON.message)
                ? xhr.responseJSON.message
                : 'Unable to update chat block.';
            showAlert('danger', message);
        });
    });
})(jQuery);

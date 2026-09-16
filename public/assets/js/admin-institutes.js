(function ($) {
  if (!$ || !$('#institutesTable').length) return;

  var adminBase = $('#institutesTable').data('admin-base') || '/admin/institutes';

  var table = $('#institutesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: adminBase + '/data' },
    columns: [
      { data: 'name', name: 'institution_name' },
      { data: 'type_label', name: 'institution_type' },
      { data: 'email_display', name: 'email', orderable: false },
      { data: 'phone_display', name: 'phone', orderable: false },
      { data: 'city_display', name: 'city' },
      { data: 'status_badge', name: 'status', orderable: false },
      { data: 'public_page_link', name: 'status', orderable: false, searchable: false },
      { data: 'created_at', name: 'created_at' },
      { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[7, 'desc']]
  });

  function toast(type, message) {
    if (window.FormHelper?.showToast) FormHelper.showToast(type === 'success' ? 'success' : 'danger', message);
    else if (window.toastr) toastr[type]?.(message);
    else alert(message);
  }

  $(document).on('click', '.js-approve-institute', function () {
    var id = $(this).data('id');
    $.post(adminBase + '/' + id + '/approve', { _token: $('meta[name="csrf-token"]').attr('content') })
      .done(function (r) { toast('success', r.message); table.ajax.reload(null, false); })
      .fail(function () { toast('error', 'Unable to approve record.'); });
  });

  $(document).on('click', '.js-reject-institute', function () {
    var id = $(this).data('id');
    var submitReject = function (reason) {
      $.post(adminBase + '/' + id + '/reject', { _token: $('meta[name="csrf-token"]').attr('content'), reason: reason })
        .done(function (r) { toast('success', r.message); table.ajax.reload(null, false); })
        .fail(function (xhr) {
          toast('error', xhr.responseJSON?.errors?.reason?.[0] || xhr.responseJSON?.message || 'Unable to reject.');
        });
    };
    if (window.Swal) {
      Swal.fire({
        title: 'Reject this application?',
        input: 'textarea',
        inputLabel: 'Rejection reason',
        inputPlaceholder: 'Explain why this application is being rejected...',
        showCancelButton: true,
        confirmButtonText: 'Reject',
        confirmButtonColor: '#dc3545',
        inputValidator: function (value) {
          if (!value || String(value).trim().length < 5) return 'Reason must be at least 5 characters.';
        }
      }).then(function (result) {
        if (result.isConfirmed) submitReject(String(result.value || '').trim());
      });
    } else {
      var reason = prompt('Enter rejection reason (min 5 characters):');
      if (reason && reason.trim().length >= 5) submitReject(reason.trim());
    }
  });

  $(document).on('click', '.js-delete-institute', function () {
    if (!confirm('Delete this record permanently?')) return;
    var id = $(this).data('id');
    $.ajax({
      url: adminBase + '/' + id,
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    }).done(function (r) {
      toast('success', r.message);
      table.ajax.reload(null, false);
    }).fail(function () { toast('error', 'Unable to delete record.'); });
  });
})(window.jQuery);

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

  // Edit: the popup filled from the record, saved without leaving the list
  var editModalEl = document.getElementById('instituteEditModal');
  var editModal = editModalEl && window.bootstrap ? new bootstrap.Modal(editModalEl) : null;
  var editFields = {
    institution_name: '#instituteName',
    display_name: '#instituteDisplayName',
    contact_person: '#instituteContact',
    slug: '#instituteSlug',
    institution_type: '#instituteType',
    board_affiliation: '#instituteBoard',
    tagline: '#instituteTagline',
    about: '#instituteAbout',
    phone: '#institutePhone',
    whatsapp: '#instituteWhatsapp',
    email: '#instituteEmail',
    website_url: '#instituteWebsite',
    address: '#instituteAddress',
    city: '#instituteCity',
    state: '#instituteState',
    pincode: '#institutePincode',
    date_of_establishment: '#instituteFounded',
    pan_number: '#institutePan',
    gst_number: '#instituteGst',
    government_certificate_number: '#instituteCertificate'
  };

  $(document).on('click', '.js-edit-institute', function () {
    if (!editModal) return;
    var id = $(this).data('id');
    $.get(adminBase + '/' + id + '/edit').done(function (r) {
      var record = r.institute || {};
      var $form = $('#instituteEditForm');
      $form.find('.is-invalid').removeClass('is-invalid');
      $form.find('.invalid-feedback, label.error').remove();
      $.each(editFields, function (key, selector) {
        $(selector).val(record[key] || '');
      });
      if (!$('#instituteType').val()) $('#instituteType').val('other');
      $('#instituteSlugPreview').text(record.slug || 'slug');
      $form.attr('action', adminBase + '/' + id);
      editModal.show();
    }).fail(function () { toast('error', 'Unable to load this record.'); });
  });

  $('#instituteSlug').on('input', function () {
    $('#instituteSlugPreview').text($(this).val() || 'slug');
  });

  $('#instituteEditForm').on('input blur', '[name="phone"], [name="whatsapp"], [name="pincode"]', function () {
    $(this).val($.trim($(this).val() || '').replace(/\D+/g, ''));
  });

  if (window.FormHelper && typeof FormHelper.attachAjaxForm === 'function' && $('#instituteEditForm').length) {
    FormHelper.attachAjaxForm({
      formSelector: '#instituteEditForm',
      buttonSelector: '#instituteEditSubmitBtn',
      rules: {
        institution_name: { required: true, maxlength: 255 },
        slug: { required: true, maxlength: 120 },
        institution_type: { required: true },
        phone: { digits: true, minlength: 10, maxlength: 15 },
        whatsapp: { digits: true, minlength: 10, maxlength: 15 },
        email: { email: true },
        website_url: { url: true },
        pincode: { digits: true, minlength: 4, maxlength: 10 }
      },
      beforeSubmit: function () {
        $('#instituteEditForm').find('input[name="_method"]').remove();
        $('<input type="hidden" name="_method" value="PUT">').appendTo('#instituteEditForm');
      },
      onSuccess: function (r) {
        toast('success', r.message);
        table.ajax.reload(null, false);
        if (editModal) editModal.hide();
      }
    });
  }

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

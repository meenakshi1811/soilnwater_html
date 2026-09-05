@extends('backend.layouts.app')
@section('title','Study Material Approvals')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@section('content')
<div class="admin-panel ems-page">
  <div class="mb-4">
    <p class="ems-kicker mb-1">Admin Portal</p>
    <h2 class="admin-title mb-0">Study Material Approvals</h2>
  </div>

  <div class="chart-card p-3 mb-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label for="statusFilter" class="form-label mb-1">Status filter</label>
        <select id="statusFilter" class="form-select">
          <option value="pending" @selected(($statusFilter ?? 'pending') === 'pending')>Pending</option>
          <option value="approved" @selected(($statusFilter ?? '') === 'approved')>Approved</option>
          <option value="rejected" @selected(($statusFilter ?? '') === 'rejected')>Rejected</option>
          <option value="">All</option>
        </select>
      </div>
    </div>
  </div>

  <div class="chart-card p-3 p-lg-4">
    <div class="table-responsive">
      <table id="studyMaterialsApprovalTable" class="table table-hover align-middle w-100">
        <thead>
          <tr>
            <th>Title</th>
            <th>Educator</th>
            <th>Type</th>
            <th>Subject / Class</th>
            <th>Status</th>
            <th>Updated</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function ($) {
  if (!$) return;

  var table = $('#studyMaterialsApprovalTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[5, 'desc']],
    ajax: {
      url: @json(route('admin.study-materials.data')),
      data: function (d) {
        d.status = $('#statusFilter').val() || '';
      }
    },
    columns: [
      { data: 'title', name: 'title' },
      { data: 'educator_name', name: 'educator.display_name', orderable: false },
      { data: 'type_label', name: 'material_type' },
      { data: 'subject_class', orderable: false, searchable: false },
      { data: 'status_badge', name: 'status', orderable: false },
      { data: 'updated_at', name: 'updated_at' },
      { data: 'actions', orderable: false, searchable: false }
    ]
  });

  $('#statusFilter').on('change', function () {
    table.ajax.reload();
  });

  function notify(type, message) {
    if (window.toastr) window.toastr[type](message);
  }

  function postAction(id, action) {
    var url = action === 'approve'
      ? @json(url('/admin/study-materials')) + '/' + id + '/approve'
      : @json(url('/admin/study-materials')) + '/' + id + '/reject';

    fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    }).then(function (r) {
      if (!r.ok) throw new Error();
      return r.json().catch(function () { return {}; });
    }).then(function (data) {
      table.ajax.reload(null, false);
      notify('success', data.message || 'Updated.');
    }).catch(function () {
      notify('error', 'Unable to update study material.');
    });
  }

  $(document).on('click', '.js-approve', function () {
    postAction(this.dataset.id, 'approve');
  });
  $(document).on('click', '.js-reject', function () {
    postAction(this.dataset.id, 'reject');
  });
})(window.jQuery);
</script>
@endpush

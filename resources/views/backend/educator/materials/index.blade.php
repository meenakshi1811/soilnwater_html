@extends('backend.layouts.app')
@section('title','My Study Materials')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Educator Portal</p>
      <h2 class="admin-title mb-0">Study Materials</h2>
    </div>
    <a href="{{ route('educator.materials.create') }}" class="btn btn-primary ems-btn-primary">
      <i class="fa-solid fa-plus me-1"></i>Upload material
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="chart-card p-3 mb-3">
    <div class="row g-2 align-items-end">
      <div class="col-md-3">
        <label for="statusFilter" class="form-label mb-1">Status filter</label>
        <select id="statusFilter" class="form-select">
          <option value="">All</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>
  </div>

  <div class="chart-card p-3 p-lg-4">
    <div class="table-responsive">
      <table id="educatorMaterialsTable" class="table table-hover align-middle w-100">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Downloads</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function ($) {
  if (!$) return;

  var table = $('#educatorMaterialsTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[5, 'desc']],
    language: { emptyTable: 'No study materials yet.' },
    ajax: {
      url: @json(route('educator.materials.data')),
      data: function (d) {
        d.status = $('#statusFilter').val() || '';
      }
    },
    columns: [
      { data: 'title_display', name: 'title' },
      { data: 'type_label', name: 'material_type' },
      { data: 'subject_display', name: 'subject' },
      { data: 'status_badge', name: 'status', orderable: false },
      { data: 'downloads_display', name: 'downloads_count' },
      { data: 'updated_at', name: 'updated_at' },
      { data: 'actions', orderable: false, searchable: false }
    ]
  });

  $('#statusFilter').on('change', function () {
    table.ajax.reload();
  });

  function notify(type, message) {
    if (window.toastr) window.toastr[type](message);
    else alert(message);
  }

  $(document).on('click', '.js-delete', function () {
    var id = this.dataset.id;
    var doDelete = function () {
      fetch(@json(url('/educator/materials')) + '/' + id, {
        method: 'DELETE',
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
        notify('success', data.message || 'Study material deleted.');
      }).catch(function () {
        notify('error', 'Unable to delete study material.');
      });
    };

    if (window.Swal) {
      Swal.fire({
        title: 'Delete this material?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then(function (result) {
        if (result.isConfirmed) doDelete();
      });
      return;
    }

    if (confirm('Delete this material?')) doDelete();
  });
})(window.jQuery);
</script>
@endpush

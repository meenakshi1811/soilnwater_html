@extends('backend.layouts.app')
@section('title','Study Material Approvals')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Admin Portal</p>
      <h2 class="admin-title mb-0">Study Material Approvals</h2>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.study-materials.index', ['status' => 'pending']) }}" class="btn btn-sm {{ ($statusFilter ?? '') === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">Pending</a>
      <a href="{{ route('admin.study-materials.index', ['status' => 'approved']) }}" class="btn btn-sm {{ ($statusFilter ?? '') === 'approved' ? 'btn-primary' : 'btn-outline-primary' }}">Approved</a>
      <a href="{{ route('admin.study-materials.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ ($statusFilter ?? '') === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rejected</a>
      <a href="{{ route('admin.study-materials.index', ['status' => 'all']) }}" class="btn btn-sm {{ ($statusFilter ?? '') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
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
(function () {
  if (!window.jQuery) return;
  const status = @json($statusFilter ?? 'pending');
  const table = jQuery('#studyMaterialsApprovalTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[5, 'desc']],
    ajax: {
      url: '{{ route('admin.study-materials.data') }}',
      data: function (d) { d.status = status === 'all' ? '' : status; }
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
  function notify(type, message) { if (window.toastr) window.toastr[type](message); }
  function postAction(id, action) {
    var url = action === 'approve'
      ? @json(url('/admin/study-materials')) + '/' + id + '/approve'
      : @json(url('/admin/study-materials')) + '/' + id + '/reject';
    fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' }
    }).then(function (r) { if (!r.ok) throw new Error(); return r.json().catch(function () { return {}; }); })
      .then(function (data) { table.ajax.reload(null, false); notify('success', data.message || 'Updated.'); })
      .catch(function () { notify('error', 'Unable to update study material.'); });
  }
  jQuery(document).on('click', '.js-approve', function () { postAction(this.dataset.id, 'approve'); });
  jQuery(document).on('click', '.js-reject', function () { postAction(this.dataset.id, 'reject'); });
})();
</script>
@endpush

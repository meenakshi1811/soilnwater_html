@extends('backend.layouts.app')
@section('title','My Study Materials')
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <p class="ems-kicker mb-1">Educator Portal</p>
      <h2 class="admin-title mb-0">Study Materials</h2>
    </div>
    <a href="{{ route('educator.materials.create') }}" class="btn btn-primary ems-btn-primary"><i class="fa-solid fa-plus me-1"></i>Upload material</a>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="chart-card p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle" id="educatorMaterialsTable">
        <thead>
          <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Downloads</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($materials as $material)
            <tr>
              <td>
                <div class="fw-semibold">{{ $material->title }}</div>
                <div class="small text-muted">{{ $material->class_course ?: '—' }}</div>
              </td>
              <td>{{ $material->materialTypeLabel() }}</td>
              <td>{{ $material->subject ?: '—' }}</td>
              <td><span class="badge bg-{{ $material->status === 'approved' ? 'success' : ($material->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($material->status) }}</span></td>
              <td>{{ number_format($material->downloads_count) }}</td>
              <td class="text-end">
                <a href="{{ route('educator.materials.edit', $material) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="{{ $material->id }}">Delete</button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
if (window.jQuery) jQuery('#educatorMaterialsTable').DataTable({ order: [], language: { emptyTable: 'No study materials yet.' } });
document.querySelectorAll('.js-delete').forEach(function (btn) {
  btn.addEventListener('click', function () {
    if (!confirm('Delete this material?')) return;
    fetch('{{ url('/educator/materials') }}/' + btn.dataset.id, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' }
    }).then(function (r) { if (!r.ok) throw new Error(); location.reload(); }).catch(function () { alert('Unable to delete.'); });
  });
});
</script>
@endpush

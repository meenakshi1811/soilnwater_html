@extends('backend.layouts.app')
@section('title','Manage Consultation Services')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<style>
  #consultantServicesTable {
    min-width: 1120px;
  }

  #consultantServicesTable th,
  #consultantServicesTable td {
    vertical-align: middle;
  }

  #consultantServicesTable .consultant-services-actions {
    min-width: 220px;
    white-space: nowrap;
  }

  #consultantServicesTable .consultant-services-actions__group {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.35rem;
    flex-wrap: nowrap;
  }
</style>
@endpush
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2"><div><p class="ems-kicker mb-1">Consultant Portal</p><h2 class="admin-title mb-0">Manage Consultation Services</h2></div><a href="{{ route('consultant.services.create') }}" class="btn btn-primary ems-btn-primary"><i class="fa-solid fa-plus me-1"></i>Create Service</a></div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="chart-card p-3 p-lg-4">
    <div class="table-responsive">
      <table id="consultantServicesTable" class="table table-hover align-middle">
        <thead><tr><th>Service</th><th>Category</th><th>Charges</th><th>Consultation Type</th><th>Business Type</th><th>Status</th><th class="text-end consultant-services-actions">Actions</th></tr></thead>
        <tbody>
          @foreach($services as $service)
            <tr>
              <td><div class="fw-semibold">{{ $service->name }}</div></td>
              <td>{{ $service->categoryModel?->name ?? $service->category ?? '-' }}<div class="small text-muted">{{ $service->subcategoryModel?->name ?? '-' }}</div></td>
              <td>{{ $service->formattedConsultationCharges() }}</td>
              <td>{{ ucfirst($service->consultation_type ?: ($service->is_online ? 'online' : 'offline')) }}</td>
              <td>{{ $service->business_type ?: '-' }}</td>
              <td><span class="badge bg-{{ $service->status === 'approved' ? 'success' : ($service->status === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($service->status ?? 'pending') }}</span></td>
              <td class="text-end consultant-services-actions">
                <div class="consultant-services-actions__group">
                  <a href="{{ route('consultant.services.show', $service) }}" class="btn btn-sm btn-outline-secondary">View</a>
                  <a href="{{ route('consultant.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                  <button type="button" class="btn btn-sm btn-outline-danger js-delete" data-id="{{ $service->id }}">Delete</button>
                </div>
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
var consultantServicesDataTable = null;
if (window.jQuery && document.getElementById('consultantServicesTable')) {
  consultantServicesDataTable = window.jQuery('#consultantServicesTable').DataTable({
    pageLength: 10,
    order: [],
    scrollX: true,
    autoWidth: false,
    language: { emptyTable: 'No consultation services found.' }
  });
}
document.querySelectorAll('.js-delete').forEach(function (button) {
  button.addEventListener('click', function () {
    var row = button.closest('tr');
    var onConfirmDelete = function () {
      fetch('/consultant/services/' + button.dataset.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' } })
        .then(function (response) { if (!response.ok) throw new Error(); if (consultantServicesDataTable && row) consultantServicesDataTable.row(row).remove().draw(false); else row?.remove(); window.toastr?.success?.('Consultation service deleted successfully.'); })
        .catch(function () { window.toastr?.error?.('Unable to delete consultation service.'); });
    };
    if (window.Swal?.fire) { window.Swal.fire({ title: 'Delete service?', text: 'This action cannot be undone.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes', cancelButtonText: 'No' }).then(function (result) { if (result.isConfirmed) onConfirmDelete(); }); return; }
    if (confirm('Delete this consultation service permanently?')) onConfirmDelete();
  });
});
</script>
@endpush

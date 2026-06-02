@extends('backend.layouts.app')
@section('title','Consultation Service Approvals')
@push('styles')<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">@endpush
@section('content')
<div class="admin-panel ems-page">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2"><div><p class="ems-kicker mb-1">Admin Portal</p><h2 class="admin-title mb-0">Consultation Service Approvals</h2></div><a href="{{ route('admin.consultant-services.all.index') }}" class="btn btn-outline-primary">All Consultation Services</a></div>
  <div class="chart-card p-3 p-lg-4"><div class="table-responsive"><table id="consultantServicesApprovalTable" class="table table-hover align-middle w-100"><thead><tr><th>Service</th><th>Consultant</th><th>Category</th><th>Charges</th><th>Status</th><th>Submitted</th><th class="text-end">Actions</th></tr></thead></table></div></div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
(function () {
  if (!window.jQuery) return;
  const table = jQuery('#consultantServicesApprovalTable').DataTable({ processing: true, serverSide: true, ajax: '{{ route('admin.consultant-services.data') }}', columns: [ { data: 'name', name: 'name' }, { data: 'consultant_name', name: 'consultant.company_name', orderable: false }, { data: 'category_display', name: 'category', orderable: false }, { data: 'price_display', name: 'price', orderable: false }, { data: 'status_badge', name: 'status', orderable: false, searchable: false }, { data: 'created_at', name: 'created_at' }, { data: 'actions', orderable: false, searchable: false } ] });
  function postAction(id, action) { fetch('/admin/consultant-services/' + id + '/' + action, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' } }).then(function (response) { if (!response.ok) throw new Error(); table.ajax.reload(null, false); window.toastr?.success?.('Consultation service ' + (action === 'approve' ? 'approved' : 'rejected') + '.'); }).catch(function () { window.toastr?.error?.('Unable to update consultation service.'); }); }
  jQuery(document).on('click', '.js-approve', function () { postAction(this.dataset.id, 'approve'); });
  jQuery(document).on('click', '.js-reject', function () { postAction(this.dataset.id, 'reject'); });
  jQuery(document).on('click', '.js-delete', function () { const id = this.dataset.id; const run = function () { fetch('/admin/consultant-services/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' } }).then(function (response) { if (!response.ok) throw new Error(); table.ajax.reload(null, false); window.toastr?.success?.('Consultation service deleted.'); }).catch(function () { window.toastr?.error?.('Unable to delete consultation service.'); }); }; if (window.Swal?.fire) { window.Swal.fire({ title: 'Delete service?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes' }).then(function (result) { if (result.isConfirmed) run(); }); } else if (confirm('Delete this consultation service?')) run(); });
})();
</script>
@endpush

@extends('backend.layouts.app')
@section('title','Product Inquiries')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card"><div class="card-body">
        <h4 class="mb-3">Product Inquiries</h4>
        <div class="table-responsive"><table id="vendorInquiriesTable" class="table table-striped"><thead><tr><th>Date</th><th>Product</th><th>Email</th><th>Phone</th><th>Contact Via</th><th>Reason</th></tr></thead><tbody>
            @forelse($inquiries as $i)
                <tr><td data-order="{{ $i->created_at?->timestamp ?? 0 }}">{{ $i->created_at->format('d M Y H:i') }}</td><td>{{ $i->product?->name ?? '—' }}</td><td>{{ $i->email }}</td><td>{{ $i->phone_number }}</td><td>{{ ucfirst($i->preferred_contact) }}</td><td>{{ $i->reason }}</td></tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No inquiries yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
if (window.jQuery && document.getElementById('vendorInquiriesTable')) {
  window.jQuery('#vendorInquiriesTable').DataTable({
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    order: [[0, 'desc']]
  });
}
</script>
@endpush

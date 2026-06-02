@extends('backend.layouts.app')
@section('title','Consultation Inquiries')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="card"><div class="card-body">
        <h4 class="mb-3">Consultation Inquiries</h4>
        <div class="table-responsive"><table id="consultantInquiriesTable" class="table table-striped align-middle"><thead><tr><th>Date</th><th>Service</th><th>Category</th><th>Client</th><th>Phone</th><th>Email</th><th>Occupation</th><th>DOB</th><th>Question</th><th>Image</th></tr></thead><tbody>
            @forelse($inquiries as $inquiry)
                <tr>
                    <td data-order="{{ $inquiry->created_at?->timestamp ?? 0 }}">{{ $inquiry->created_at?->format('d M Y H:i') }}</td>
                    <td>{{ $inquiry->service?->name ?? '—' }}</td>
                    <td>{{ $inquiry->service?->categoryModel?->name ?? '—' }}</td>
                    <td>{{ $inquiry->client_name }}</td>
                    <td>{{ $inquiry->phone_number }}</td>
                    <td>{{ $inquiry->email }}</td>
                    <td>{{ $inquiry->occupation ?: '—' }}</td>
                    <td>{{ $inquiry->date_of_birth?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $inquiry->question }}</td>
                    <td>@if($inquiry->image_path)<a href="{{ asset($inquiry->image_path) }}" target="_blank" rel="noopener">View</a>@else — @endif</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">No consultation inquiries yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </div></div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
if (window.jQuery && document.getElementById('consultantInquiriesTable')) {
  window.jQuery('#consultantInquiriesTable').DataTable({
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
    order: [[0, 'desc']]
  });
}
</script>
@endpush

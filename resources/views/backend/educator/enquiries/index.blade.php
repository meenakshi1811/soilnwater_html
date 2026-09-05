@extends('backend.layouts.app')
@section('title','Educator Enquiries')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="mb-4">
        <p class="ems-kicker mb-1">Educator Portal</p>
        <h2 class="admin-title mb-0">Enquiries</h2>
    </div>
    <div class="chart-card">
        <div class="table-responsive">
            <table id="educatorEnquiriesTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enquiries as $enquiry)
                        <tr>
                            <td data-order="{{ $enquiry->created_at?->timestamp ?? 0 }}">{{ $enquiry->created_at?->format('d M Y H:i') }}</td>
                            <td>{{ $enquiry->name }}</td>
                            <td>{{ $enquiry->email ?: '—' }}</td>
                            <td>{{ $enquiry->phone ?: '—' }}</td>
                            <td>{{ $enquiry->subject ?: '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($enquiry->message, 120) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($enquiry->status) }}</span></td>
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
if (window.jQuery && document.getElementById('educatorEnquiriesTable')) {
  window.jQuery('#educatorEnquiriesTable').DataTable({
    pageLength: 10,
    order: [[0, 'desc']],
    language: { emptyTable: 'No enquiries yet.' }
  });
}
</script>
@endpush

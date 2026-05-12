@extends('backend.layouts.app')

@section('title', 'Contact Support Requests')

@section('content')
<div class="admin-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="admin-title mb-0">Contact Support Requests</h2>
    </div>

    <div class="chart-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Source</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td>#{{ $request->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $request->user?->name ?? 'Guest' }}</div>
                                <div class="text-muted small">{{ $request->user?->email ?? 'N/A' }}</div>
                            </td>
                            <td>{{ $request->subject }}</td>
                            <td style="min-width:280px;white-space:pre-wrap;">{{ $request->message }}</td>
                            <td>{{ $request->source ?? '-' }}</td>
                            <td>{{ $request->created_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No contact support requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $requests->links() }}</div>
</div>
@endsection

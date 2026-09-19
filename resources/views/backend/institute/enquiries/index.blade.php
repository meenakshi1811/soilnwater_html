@extends('backend.layouts.app')

@section('title', 'Institute Enquiries')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <p class="ems-kicker mb-1">{{ $institute->roleLabel() }}</p>
        <h2 class="admin-title mb-1">Enquiries</h2>
        <p class="mb-0 text-secondary">Messages received from your public profile.</p>
    </div>

    <div class="chart-card">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Contact</th>
                </tr>
                </thead>
                <tbody>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>{{ $enquiry->created_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($enquiry->status === 'new')
                                <span class="badge bg-warning text-dark">New</span>
                            @else
                                <span class="badge bg-light text-secondary border">Seen</span>
                            @endif
                        </td>
                        <td>
                            {{ $enquiry->name }}
                            @if($enquiry->user)
                                <div class="small text-muted">Account: {{ $enquiry->user->name }}</div>
                            @endif
                        </td>
                        <td>{{ $enquiry->subject ?: '—' }}</td>
                        <td style="white-space:pre-wrap">{{ $enquiry->message }}</td>
                        <td>
                            @if($enquiry->email)<div>{{ $enquiry->email }}</div>@endif
                            @if($enquiry->phone)<div>{{ $enquiry->phone }}</div>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">No enquiries yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $enquiries->links() }}
    </div>
</div>
@endsection

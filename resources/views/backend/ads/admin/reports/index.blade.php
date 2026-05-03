@extends('backend.layouts.app')

@section('title', 'Reported Ads')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h1 class="h5 mb-0">Reported Ads</h1>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ad</th>
                            <th>Reporter</th>
                            <th>Reason</th>
                            <th>Reported At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->ad?->title ?? 'Deleted ad' }}</td>
                                <td>{{ $report->reporter?->full_name ?? $report->reporter?->name ?? 'Guest' }}</td>
                                <td>{{ $report->reason }}</td>
                                <td>{{ $report->created_at->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    @if($report->ad)
                                        <form method="POST" action="{{ route('admin.ads.reports.delete-ad', $report->ad) }}" onsubmit="return confirm('Delete this ad?')" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete Ad</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4">No reports found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">{{ $reports->links() }}</div>
    </div>
</div>
@endsection

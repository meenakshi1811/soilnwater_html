@extends('backend.layouts.app')

@section('title', 'Profile engagement')

@section('content')
<div class="admin-panel ems-page institute-portal">
    <div class="ems-hero mb-4">
        <p class="ems-kicker mb-1">{{ $institute->roleLabel() }}</p>
        <h2 class="admin-title mb-1">Followers &amp; activity</h2>
        <p class="mb-0 text-secondary">Track follows, bookmarks, brochure downloads, and compare actions from your public profile.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="chart-card h-100 p-3">
                <div class="text-secondary small">Followers</div>
                <div class="fs-3 fw-bold">{{ number_format($institute->followers_count) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-card h-100 p-3">
                <div class="text-secondary small">Bookmarks</div>
                <div class="fs-3 fw-bold">{{ number_format($institute->bookmarks_count) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-card h-100 p-3">
                <div class="text-secondary small">Brochure downloads</div>
                <div class="fs-3 fw-bold">{{ number_format($brochureDownloads) }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card h-100 p-3">
                <div class="text-secondary small">Helpful votes — Yes</div>
                <div class="fs-3 fw-bold">{{ number_format($helpfulYes) }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-card h-100 p-3">
                <div class="text-secondary small">Helpful votes — No</div>
                <div class="fs-3 fw-bold">{{ number_format($helpfulNo) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="chart-card">
                <h5 class="mb-3">Followers</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($followers as $follower)
                            <tr>
                                <td>{{ $follower->name }}</td>
                                <td>
                                    @if($follower->email)<div>{{ $follower->email }}</div>@endif
                                    @if($follower->phone_number)<div>{{ $follower->phone_number }}</div>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary py-4">No followers yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $followers->links() }}
            </div>
        </div>
        <div class="col-lg-6">
            <div class="chart-card">
                <h5 class="mb-3">Recent activity</h5>
                <ul class="list-group list-group-flush">
                    @forelse($recentActivity as $activity)
                        <li class="list-group-item px-0">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <strong>{{ str_replace('_', ' ', ucfirst($activity->action)) }}</strong>
                                    @if($activity->user)
                                        <div class="small text-secondary">By {{ $activity->user->name }}</div>
                                    @endif
                                </div>
                                <span class="small text-muted text-nowrap">{{ $activity->created_at?->diffForHumans() }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-secondary">No activity logged yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('backend.layouts.app')

@section('title', 'My Branches')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <p class="ems-kicker mb-1">Service Provider Panel</p>
            <h2 class="admin-title mb-0">My Branches</h2>
        </div>
        <a href="{{ route('service_provider.branches.create') }}" class="btn btn-primary ems-btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Add Branch
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3">
        @forelse($branches as $branch)
            <div class="col-md-6 col-lg-4">
                <div class="chart-card h-100">
                    @if($branch->logo)
                        <img src="{{ asset($branch->logo) }}" alt="" class="rounded-circle mb-3" width="64" height="64" style="object-fit:cover">
                    @endif
                    <h5>{{ $branch->branch_name }}
                        @if($branch->is_primary)<span class="badge text-bg-primary">Primary</span>@endif
                    </h5>
                    @if($branch->occupation)
                        <p class="small fw-semibold text-primary mb-1">{{ $branch->occupation }}</p>
                    @endif
                    <p class="small text-secondary mb-2">{{ $branch->city }}, {{ $branch->state }}</p>
                    <p class="small mb-3">{{ $branch->phone }}</p>
                    <div class="d-flex gap-2">
                        <a href="{{ route('service_provider.branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                        <button type="button" class="btn btn-sm btn-outline-danger js-delete-branch" data-id="{{ $branch->id }}">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="chart-card text-center text-secondary py-5">No branches yet. Add your first branch.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-delete-branch').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Delete this branch permanently?')) return;
        fetch('/service-provider/branches/' + btn.dataset.id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
    });
});
</script>
@endpush



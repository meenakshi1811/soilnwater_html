@extends('backend.layouts.app')

@section('title', 'Approval Center')

@push('styles')
<style>
    .approval-filter-pill {
        border: 1px solid #dbe4ef;
        border-radius: 999px;
        color: #334155;
        display: inline-flex;
        gap: .4rem;
        padding: .45rem .85rem;
        text-decoration: none;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .approval-filter-pill.active,
    .approval-filter-pill:hover {
        background: #0f766e;
        border-color: #0f766e;
        color: #fff;
    }

    .approval-module-badge {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-weight: 700;
        gap: .4rem;
        padding: .35rem .7rem;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <h2 class="admin-title mb-1">Approval Center</h2>
            <p class="text-muted mb-0">Review every pending ad, offer, product, service, and public page request from one newest-first queue.</p>
        </div>
        <div class="chart-card px-4 py-3 text-lg-end">
            <div class="text-muted small text-uppercase fw-bold">Pending requests</div>
            <div class="display-6 fw-bold text-success mb-0">{{ $totalPendingApprovals }}</div>
        </div>
    </div>

    <div class="chart-card p-3 mb-4">
        <div class="d-flex flex-wrap gap-2">
            @foreach($moduleFilters as $key => $label)
                @php $count = $key === 'all' ? $totalPendingApprovals : ($moduleCounts->get($key, 0)); @endphp
                <a class="approval-filter-pill {{ $activeModule === $key ? 'active' : '' }}" href="{{ route('admin.approvals.index', $key === 'all' ? [] : ['module' => $key]) }}">
                    <span>{{ $label }}</span>
                    <strong>{{ $count }}</strong>
                </a>
            @endforeach
        </div>
    </div>

    <div class="chart-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Module</th>
                        <th>Request</th>
                        <th>Owner</th>
                        <th>Requested</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvals as $approval)
                        <tr data-approval-row>
                            <td>
                                <span class="approval-module-badge bg-success-subtle text-success">
                                    <i class="fa-solid {{ $approval['icon'] }}"></i>
                                    {{ $approval['module_label'] }}
                                </span>
                                <div class="small text-muted mt-1">{{ $approval['description'] }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $approval['title'] }}</div>
                                <div class="small text-muted">Request #{{ $approval['id'] }}</div>
                            </td>
                            <td>{{ $approval['owner'] }}</td>
                            <td>
                                <div>{{ $approval['requested_at']?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '-' }}</div>
                                <div class="small text-muted">Newest requests appear first</div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                    <a href="{{ $approval['view_url'] }}" class="btn btn-sm btn-outline-primary" title="View details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success js-review-approval" data-action-url="{{ $approval['approve_url'] }}" data-action-label="approve">
                                        Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger js-review-approval" data-action-url="{{ $approval['decline_url'] }}" data-action-label="decline">
                                        Decline
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fa-solid fa-circle-check text-success display-6 mb-3"></i>
                                <h5 class="mb-1">No approval requests found</h5>
                                <p class="text-muted mb-0">New pending requests will appear here automatically.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($approvals->hasPages())
        <div class="mt-3">
            {{ $approvals->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-review-approval').forEach((button) => {
            button.addEventListener('click', async () => {
                const actionLabel = button.dataset.actionLabel;
                const actionUrl = button.dataset.actionUrl;
                const row = button.closest('[data-approval-row]');

                const result = await Swal.fire({
                    title: `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} this request?`,
                    text: 'This will update the original module record.',
                    icon: actionLabel === 'approve' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: actionLabel === 'approve' ? 'Yes, approve' : 'Yes, decline',
                    confirmButtonColor: actionLabel === 'approve' ? '#198754' : '#dc3545'
                });

                if (! result.isConfirmed) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });

                    const payload = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        throw new Error(payload.message || 'Unable to update this approval request.');
                    }

                    row?.remove();

                    Swal.fire({
                        title: 'Updated',
                        text: payload.message || 'Approval request updated successfully.',
                        icon: 'success',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } catch (error) {
                    button.disabled = false;
                    Swal.fire('Action failed', error.message, 'error');
                }
            });
        });
    });
</script>
@endpush

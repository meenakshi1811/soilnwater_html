@extends('backend.layouts.app')

@section('title', 'Approval Center')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
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
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-1">Approval Center</h2>
            <p class="text-muted mb-0">Review every pending ad, offer, product, service, public page, and community post request from one newest-first queue.</p>
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

    <div class="chart-card p-3 p-lg-4">
        <div class="table-responsive">
            <table id="approvalCenterTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Request</th>
                        <th>Owner</th>
                        <th>Requested</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    if (!window.jQuery) {
        return;
    }

    const moduleFilter = new URLSearchParams(window.location.search).get('module') || 'all';

    const table = jQuery('#approvalCenterTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[3, 'desc']],
        ajax: {
            url: @json(route('admin.approvals.data')),
            data: function (params) {
                params.module = moduleFilter;
            }
        },
        columns: [
            { data: 'module_display', name: 'module_label', orderable: false, searchable: false },
            { data: 'request_display', name: 'title', orderable: false },
            { data: 'owner', name: 'owner' },
            { data: 'requested_at', name: 'requested_at' },
            { data: 'actions', orderable: false, searchable: false, className: 'text-end' }
        ]
    });

    jQuery(document).on('click', '.js-review-approval', async function () {
        const button = this;
        const actionLabel = button.dataset.actionLabel;
        const actionUrl = button.dataset.actionUrl;

        const result = await Swal.fire({
            title: `${actionLabel.charAt(0).toUpperCase() + actionLabel.slice(1)} this request?`,
            text: 'This will update the original module record.',
            icon: actionLabel === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonText: actionLabel === 'approve' ? 'Yes, approve' : 'Yes, decline',
            confirmButtonColor: actionLabel === 'approve' ? '#198754' : '#dc3545'
        });

        if (!result.isConfirmed) {
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

            if (!response.ok) {
                throw new Error(payload.message || 'Unable to update this approval request.');
            }

            table.ajax.reload(null, false);

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
})();
</script>
@endpush

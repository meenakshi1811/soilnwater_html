@extends('backend.layouts.app')

@section('title', 'Premium Payment Review')

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="ems-kicker mb-1">Admin Portal</p>
            <h2 class="admin-title mb-1">Premium Payment Review</h2>
            <p class="text-muted mb-0">Verify the payment screenshot and activate premium for this profile.</p>
        </div>
        <div>
            <a href="{{ route('admin.premium-payments.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Payments
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="chart-card p-3 p-lg-4 h-100">
                <h5 class="mb-3">Submission details</h5>
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($submission->isPending())
                            <span class="badge text-bg-warning">Pending</span>
                        @elseif($submission->status === 'approved')
                            <span class="badge text-bg-success">Approved</span>
                        @else
                            <span class="badge text-bg-danger">Rejected</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Profile type</dt>
                    <dd class="col-sm-8">{{ $submission->profileTypeLabel() }}</dd>

                    <dt class="col-sm-4">Expected amount</dt>
                    <dd class="col-sm-8 fw-semibold">
                        {{ $submission->expected_amount !== null
                            ? \App\Models\PremiumPrice::formatAmount($submission->expected_amount)
                            : \App\Models\PremiumPrice::formatAmount(\App\Models\PremiumPrice::amountFor($submission->profile_type)) }}
                    </dd>

                    <dt class="col-sm-4">Profile name</dt>
                    <dd class="col-sm-8">{{ $submission->profileDisplayName() }}</dd>

                    <dt class="col-sm-4">User</dt>
                    <dd class="col-sm-8">
                        {{ $submission->user?->full_name ?: $submission->user?->name }}<br>
                        <span class="text-muted">{{ $submission->user?->email }}</span>
                    </dd>

                    @if($submission->transaction_reference)
                        <dt class="col-sm-4">Transaction ref.</dt>
                        <dd class="col-sm-8">{{ $submission->transaction_reference }}</dd>
                    @endif

                    @if($submission->user_note)
                        <dt class="col-sm-4">User note</dt>
                        <dd class="col-sm-8">{{ $submission->user_note }}</dd>
                    @endif

                    <dt class="col-sm-4">Submitted</dt>
                    <dd class="col-sm-8">{{ $submission->submitted_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '-' }}</dd>

                    @if($submission->reviewed_at)
                        <dt class="col-sm-4">Reviewed</dt>
                        <dd class="col-sm-8">
                            {{ $submission->reviewed_at->timezone(config('app.timezone'))->format('d M Y, h:i A') }}
                            @if($submission->reviewer)
                                <br><span class="text-muted">by {{ $submission->reviewer->name }}</span>
                            @endif
                        </dd>
                    @endif

                    @if($submission->admin_note)
                        <dt class="col-sm-4">Admin note</dt>
                        <dd class="col-sm-8">{{ $submission->admin_note }}</dd>
                    @endif
                </dl>

                @if($submission->isPending())
                    <hr>
                    <div class="mb-3">
                        <label for="adminNote" class="form-label">Admin note (optional)</label>
                        <textarea id="adminNote" class="form-control" rows="3" placeholder="Add a note for your records or for the user if declining."></textarea>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-success js-premium-payment-review" data-action="approve">
                            <i class="fa-solid fa-check me-1"></i> Verify &amp; Activate Premium
                        </button>
                        <button type="button" class="btn btn-outline-danger js-premium-payment-review" data-action="reject">
                            <i class="fa-solid fa-xmark me-1"></i> Decline
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            <div class="chart-card p-3 p-lg-4 h-100">
                <h5 class="mb-3">Payment screenshot</h5>
                <div class="text-center">
                    <a href="{{ $submission->screenshotUrl() }}" target="_blank" rel="noopener">
                        <img
                            src="{{ $submission->screenshotUrl() }}"
                            alt="Payment screenshot"
                            class="img-fluid rounded border"
                            style="max-height: 640px;"
                        >
                    </a>
                    <p class="small text-muted mt-2 mb-0">Click image to open full size in a new tab.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const reviewButtons = document.querySelectorAll('.js-premium-payment-review');
    if (!reviewButtons.length) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const approveUrl = @json(route('admin.premium-payments.approve', $submission));
    const rejectUrl = @json(route('admin.premium-payments.reject', $submission));

    reviewButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const action = button.dataset.action;
            const adminNote = document.getElementById('adminNote')?.value || '';

            const result = await Swal.fire({
                title: action === 'approve' ? 'Activate premium membership?' : 'Decline this payment proof?',
                text: action === 'approve'
                    ? 'This will mark the profile as premium immediately.'
                    : 'The user can submit a new screenshot after declining.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: action === 'approve' ? 'Yes, activate premium' : 'Yes, decline',
                confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
            });

            if (!result.isConfirmed) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(action === 'approve' ? approveUrl : rejectUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ admin_note: adminNote }),
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'Unable to process this request.');
                }

                await Swal.fire({
                    icon: 'success',
                    title: 'Done',
                    text: payload.message,
                });

                window.location.reload();
            } catch (error) {
                button.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Something went wrong.',
                });
            }
        });
    });
})();
</script>
@endpush

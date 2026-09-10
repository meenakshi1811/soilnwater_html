@extends('backend.layouts.app')

@section('title', 'Child Profile — '.$childProfile->full_name)

@section('content')
<div class="admin-panel ems-page">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="ems-kicker mb-1">Child Profile Review</p>
            <h2 class="admin-title mb-1">{{ $childProfile->full_name }}</h2>
            <span class="badge text-bg-{{ $childProfile->statusBadgeClass() }}">{{ ucfirst($childProfile->status) }}</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if(!$childProfile->isApproved())
                <button type="button" class="btn btn-success js-approve-child" data-id="{{ $childProfile->id }}">Approve</button>
            @endif
            @if(!$childProfile->isRejected())
                <button type="button" class="btn btn-outline-danger js-reject-child" data-id="{{ $childProfile->id }}">Decline</button>
            @endif
            <button type="button" class="btn btn-outline-danger js-delete-child" data-id="{{ $childProfile->id }}">Delete</button>
            <a href="{{ route('admin.child-profiles.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-card">
                <h5 class="mb-3">Child details</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Full name</dt><dd class="col-sm-8">{{ $childProfile->full_name }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $childProfile->email }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $childProfile->phone_number }}</dd>
                    <dt class="col-sm-4">Gender</dt><dd class="col-sm-8">{{ ucfirst($childProfile->gender ?: '—') }}</dd>
                    <dt class="col-sm-4">Class / Grade</dt><dd class="col-sm-8">{{ $childProfile->class_grade ?: '—' }}</dd>
                    <dt class="col-sm-4">Board</dt><dd class="col-sm-8">{{ $childProfile->board ?: '—' }}</dd>
                    <dt class="col-sm-4">School</dt><dd class="col-sm-8">{{ $childProfile->school_name ?: '—' }}</dd>
                    <dt class="col-sm-4">Subjects</dt>
                    <dd class="col-sm-8">
                        @forelse($childProfile->displaySubjects() as $subject)
                            <span class="badge text-bg-light">{{ $subject }}</span>
                        @empty
                            —
                        @endforelse
                    </dd>
                    <dt class="col-sm-4">Primary child</dt><dd class="col-sm-8">{{ $childProfile->is_primary ? 'Yes' : 'No' }}</dd>
                    <dt class="col-sm-4">Submitted</dt><dd class="col-sm-8">{{ $childProfile->created_at?->format('d M Y, h:i A') }}</dd>
                    @if($childProfile->isRejected() && $childProfile->rejection_reason)
                        <dt class="col-sm-4">Decline reason</dt><dd class="col-sm-8">{{ $childProfile->rejection_reason }}</dd>
                    @endif
                </dl>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-card mb-4">
                <h5 class="mb-3">Parent / Guardian</h5>
                <p class="mb-1 fw-semibold">{{ $childProfile->parentUser?->full_name ?: $childProfile->parentUser?->name }}</p>
                <p class="mb-1 small text-muted">{{ $childProfile->parentUser?->email }}</p>
                <p class="mb-0 small text-muted">{{ $childProfile->parentUser?->phone_number }}</p>
            </div>
            @if(filled($childProfile->profile_image))
                <div class="chart-card text-center">
                    <img src="{{ asset($childProfile->profile_image) }}" alt="{{ $childProfile->full_name }}" class="rounded-circle" width="120" height="120" style="object-fit:cover">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function ($) {
    if (!$) return;

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var approveUrl = @json(route('admin.child-profiles.approve', $childProfile));
    var rejectUrl = @json(route('admin.child-profiles.reject', $childProfile));
    var deleteUrl = @json(route('admin.child-profiles.destroy', $childProfile));
    var indexUrl = @json(route('admin.child-profiles.index'));

    var ajaxHeaders = {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
    };

    if (window.toastr) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000
        };
    }

    function toast(type, message) {
        if (window.FormHelper?.showToast) {
            FormHelper.showToast(type === 'success' ? 'success' : 'danger', message);
            return;
        }
        if (window.toastr) {
            toastr[type === 'success' ? 'success' : 'error'](message);
            return;
        }
        alert(message);
    }

    function errorMessage(xhr, fallback) {
        return xhr.responseJSON?.message
            || xhr.responseJSON?.errors?.reason?.[0]
            || fallback;
    }

    function postAction(url, data, $btn) {
        if ($btn) $btn.prop('disabled', true);

        return $.ajax({
            url: url,
            method: 'POST',
            data: $.extend({ _token: csrfToken }, data || {}),
            headers: ajaxHeaders
        }).always(function () {
            if ($btn) $btn.prop('disabled', false);
        });
    }

    $(document).on('click', '.js-approve-child', function () {
        var $btn = $(this);

        Swal.fire({
            title: 'Approve child profile?',
            text: 'The child will be able to sign in after approval.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve',
            confirmButtonColor: '#198754',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            postAction(approveUrl, {}, $btn)
                .done(function (response) {
                    toast('success', response.message || 'Child profile approved successfully.');
                    setTimeout(function () { window.location.reload(); }, 800);
                })
                .fail(function (xhr) {
                    toast('error', errorMessage(xhr, 'Unable to approve child profile.'));
                });
        });
    });

    $(document).on('click', '.js-reject-child', function () {
        var $btn = $(this);

        Swal.fire({
            title: 'Decline child profile?',
            input: 'textarea',
            inputLabel: 'Reason (optional)',
            inputPlaceholder: 'Enter reason for declining...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Decline',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            postAction(rejectUrl, { reason: result.value || '' }, $btn)
                .done(function (response) {
                    toast('success', response.message || 'Child profile declined successfully.');
                    setTimeout(function () { window.location.reload(); }, 800);
                })
                .fail(function (xhr) {
                    toast('error', errorMessage(xhr, 'Unable to decline child profile.'));
                });
        });
    });

    $(document).on('click', '.js-delete-child', function () {
        var $btn = $(this);

        Swal.fire({
            title: 'Delete child profile?',
            text: 'This will permanently remove the child profile and login account.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: { _token: csrfToken, _method: 'DELETE' },
                headers: ajaxHeaders
            }).done(function (response) {
                toast('success', response.message || 'Child profile deleted successfully.');
                setTimeout(function () { window.location.href = indexUrl; }, 800);
            }).fail(function (xhr) {
                toast('error', errorMessage(xhr, 'Unable to delete child profile.'));
                $btn.prop('disabled', false);
            });
        });
    });
})(window.jQuery);
</script>
@endpush

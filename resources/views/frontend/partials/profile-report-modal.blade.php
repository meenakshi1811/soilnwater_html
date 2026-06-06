@php
    $reportModalId = $reportModalId ?? 'profileReportModal';
    $reportFormId = $reportFormId ?? 'profileReportForm';
@endphp

<div class="modal fade" id="{{ $reportModalId }}" tabindex="-1" aria-labelledby="{{ $reportModalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $reportModalId }}Label">Report {{ $reportLabel }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="{{ $reportFormId }}" class="profile-report-form" action="{{ $reportAction }}" method="POST">
                @csrf
                <div class="modal-body">
                    <label for="{{ $reportFormId }}Reason" class="form-label">Reason for reporting</label>
                    <textarea id="{{ $reportFormId }}Reason" name="reason" class="form-control" rows="5" maxlength="1000" placeholder="Please explain why you are reporting this {{ strtolower($reportLabel) }}." required></textarea>
                    <div class="form-text">Maximum 1,000 characters.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Submit report</button>
                </div>
            </form>
        </div>
    </div>
</div>

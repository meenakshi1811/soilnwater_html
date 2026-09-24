@extends('backend.layouts.app')

@section('title', 'Jobs & Applications')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page institute-portal">
    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }}</p>
            <h2 class="admin-title mb-1">Jobs &amp; applications</h2>
            <p class="mb-0 text-secondary">Post openings on your public profile and manage applicants.</p>
        </div>
        @if($institute->isApproved())
            <a href="{{ $institute->publicSectionUrl('jobs') }}" target="_blank" class="btn btn-outline-primary">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View public jobs section
            </a>
        @endif
    </div>

    <div id="instJobsAlert" class="alert d-none" role="alert"></div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="chart-card h-100">
                <h3 class="h5 mb-3">Post a new job</h3>
                <form id="instJobCreateForm" class="sch-portal-form js-inst-job-form" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="job_title">Job title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="job_title" name="title" required maxlength="255">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="job_department">Department</label>
                            <input type="text" class="form-control" id="job_department" name="department" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="job_employment_type">Employment type</label>
                            <select class="form-select" id="job_employment_type" name="employment_type">
                                @foreach($employmentTypes as $type)
                                    <option value="{{ $type }}" @selected($type === 'full-time')>{{ ucfirst(str_replace('-', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="job_location">Location</label>
                            <input type="text" class="form-control" id="job_location" name="location" maxlength="255">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="job_deadline">Application deadline</label>
                            <input type="date" class="form-control" id="job_deadline" name="application_deadline">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="job_salary">Salary / pay (label)</label>
                            <input type="text" class="form-control" id="job_salary" name="salary_label" placeholder="e.g. ₹25,000 – ₹35,000/month">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="job_experience">Experience (label)</label>
                            <input type="text" class="form-control" id="job_experience" name="experience_label" placeholder="e.g. 2+ years">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="job_description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="job_description" name="description" rows="5" required maxlength="20000"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="job_requirements">Requirements</label>
                        <textarea class="form-control" id="job_requirements" name="requirements" rows="4" maxlength="20000"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary js-inst-job-submit">
                        <span class="btn-text">Publish job</span>
                        <span class="btn-loader d-none"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="chart-card">
                <h3 class="h5 mb-3">Your job listings</h3>
                <div id="instJobsList" class="sch-manage-list">
                    @forelse($jobs as $job)
                        @include('backend.institute.partials.job-item', ['job' => $job])
                    @empty
                        <p class="sch-manage-empty mb-0" id="instJobsEmpty">No jobs posted yet. Use the form to publish your first opening.</p>
                    @endforelse
                </div>
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
window.instJobRoutes = {
    store: @json($portalRoute('jobs.store')),
};
</script>
<script src="{{ asset('assets/js/institute-jobs.js') }}?v={{ now()->timestamp }}"></script>
@endpush

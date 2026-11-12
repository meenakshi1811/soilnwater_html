@extends('backend.layouts.app')

@section('title', 'Scholarships')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/institute-portal.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="admin-panel ems-page institute-portal">
    @includeWhen($managedInstitute, 'backend.institute.partials.admin-manage-bar')

    <div class="ems-hero mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <p class="ems-kicker mb-1">{{ $institute->roleLabel() }}</p>
            <h2 class="admin-title mb-1">Scholarships</h2>
            <p class="mb-0 text-secondary">Add the scholarships you offer, with who can apply and how. They show on your profile and in the Education Hub.</p>
        </div>
        @if($institute->isApproved())
            <a href="{{ $institute->publicSectionUrl('scholarships') }}" target="_blank" class="btn btn-outline-primary">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View public scholarships
            </a>
        @endif
    </div>

    <div id="instScholarshipsAlert" class="alert d-none" role="alert"></div>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="chart-card h-100">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <h3 class="h5 mb-0 js-scholarship-form-title">Add a scholarship</h3>
                    <button type="button" class="btn btn-sm btn-outline-secondary d-none js-scholarship-cancel">
                        <i class="fa-solid fa-xmark me-1" aria-hidden="true"></i> Cancel editing
                    </button>
                </div>

                <form id="instScholarshipForm" class="sch-portal-form" novalidate>
                    @csrf
                    <h4 class="sch-form-group-title">Basics</h4>
                    <div class="mb-3">
                        <label class="form-label" for="sch_title">Scholarship name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="sch_title" name="title" required maxlength="255" placeholder="e.g. Merit Scholarship for Class 11 Science">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="sch_type">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="sch_type" name="scholarship_type">
                                @foreach($types as $key => [$label])
                                    <option value="{{ $key }}" @selected($key === 'merit')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sch_amount">Amount / benefit</label>
                            <input type="text" class="form-control" id="sch_amount" name="amount_label" maxlength="120" placeholder="e.g. ₹25,000 a year or 50% fee waiver">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sch_for">For (classes / courses)</label>
                            <input type="text" class="form-control" id="sch_for" name="eligible_for" maxlength="160" placeholder="e.g. Class 11–12 or B.Sc first year">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sch_awards">Number of scholarships</label>
                            <input type="number" class="form-control" id="sch_awards" name="awards_count" min="1" max="100000" placeholder="e.g. 10">
                        </div>
                    </div>

                    <h4 class="sch-form-group-title">Who can apply</h4>
                    <div class="mb-3">
                        <label class="form-label" for="sch_eligibility">Eligibility criteria <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="sch_eligibility" name="eligibility" rows="4" required maxlength="5000" placeholder="One point per line, e.g.&#10;Indian citizen&#10;Studying in Class 11 in our school&#10;No other scholarship this year"></textarea>
                        <div class="form-text">Write one point per line; each shows as a tick on your page.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="sch_marks">Minimum marks</label>
                            <input type="text" class="form-control" id="sch_marks" name="min_marks" maxlength="120" placeholder="e.g. 85% in Class 10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sch_income">Family income limit</label>
                            <input type="text" class="form-control" id="sch_income" name="income_limit" maxlength="160" placeholder="e.g. Below ₹3 lakh a year">
                        </div>
                    </div>

                    <h4 class="sch-form-group-title">How to apply</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="sch_start">Applications open</label>
                            <input type="date" class="form-control" id="sch_start" name="application_start">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="sch_deadline">Last date to apply</label>
                            <input type="date" class="form-control" id="sch_deadline" name="application_deadline">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" for="sch_documents">Documents required</label>
                        <textarea class="form-control" id="sch_documents" name="documents" rows="3" maxlength="5000" placeholder="One per line, e.g.&#10;Class 10 marksheet&#10;Income certificate&#10;Aadhaar card"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sch_how">How to apply</label>
                        <textarea class="form-control" id="sch_how" name="how_to_apply" rows="3" maxlength="5000" placeholder="e.g. Collect the form from the school office and submit it with the documents."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sch_url">Online application link</label>
                        <input type="url" class="form-control" id="sch_url" name="apply_url" maxlength="500" placeholder="https://…">
                    </div>

                    <h4 class="sch-form-group-title">About</h4>
                    <div class="mb-3">
                        <label class="form-label" for="sch_description">Description</label>
                        <textarea class="form-control" id="sch_description" name="description" rows="3" maxlength="5000" placeholder="What the scholarship is for and anything else students should know."></textarea>
                    </div>
                    <div class="mb-3 d-none js-scholarship-status-wrap">
                        <label class="form-label" for="sch_status">Status</label>
                        <select class="form-select" id="sch_status" name="status">
                            <option value="open">Open (shown)</option>
                            <option value="closed">Closed (hidden)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary js-scholarship-submit">
                        <span class="btn-text">Publish scholarship</span>
                        <span class="btn-loader d-none"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="chart-card">
                <h3 class="h5 mb-3">Your scholarships</h3>
                <div id="instScholarshipsList" class="sch-manage-list">
                    @forelse($scholarships as $scholarship)
                        @include('backend.institute.partials.scholarship-item', ['scholarship' => $scholarship])
                    @empty
                        <p class="sch-manage-empty mb-0" id="instScholarshipsEmpty">No scholarships yet. Use the form to add your first one.</p>
                    @endforelse
                </div>
                {{ $scholarships->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<script>
window.instScholarshipRoutes = {
    store: @json($portalRoute('scholarships.store')),
};
</script>
<script src="{{ asset('assets/js/institute-scholarships.js') }}?v={{ now()->timestamp }}"></script>
@endpush

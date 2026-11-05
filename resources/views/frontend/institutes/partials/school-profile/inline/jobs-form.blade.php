@php
  $listingContext = $listingContext ?? 'schools';
  $portalPrefix = \App\Support\SchoolInstituteHelper::portalForListing($listingContext);
  $types = \App\Models\InstituteJob::EMPLOYMENT_TYPES;
  $jobs = $institute->jobs()
    ->withCount('applications')
    ->with(['applications' => fn ($q) => $q->with('user:id,name,email,phone_number')->latest()])
    ->latest('published_at')->latest('id')->get();
@endphp
<section>
  <form id="instJobForm" class="sch-portal-form mb-4" novalidate
        data-store-url="{{ route($portalPrefix.'.jobs.store') }}">
    @csrf
    <div class="alert d-none mb-3" role="alert"></div>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Job title *</label>
        <input type="text" name="title" class="form-control" maxlength="255">
      </div>
      <div class="col-md-6">
        <label class="form-label">Employment type *</label>
        <select name="employment_type" class="form-select">
          @foreach($types as $type)
            <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Department</label>
        <input type="text" name="department" class="form-control" maxlength="255">
      </div>
      <div class="col-md-6">
        <label class="form-label">Location</label>
        <input type="text" name="location" class="form-control" maxlength="255">
      </div>
      <div class="col-md-6">
        <label class="form-label">Salary (label)</label>
        <input type="text" name="salary_label" class="form-control" maxlength="255" placeholder="e.g. ₹30,000–40,000 / month">
      </div>
      <div class="col-md-6">
        <label class="form-label">Experience (label)</label>
        <input type="text" name="experience_label" class="form-control" maxlength="255" placeholder="e.g. 2+ years">
      </div>
      <div class="col-md-6">
        <label class="form-label">Application deadline</label>
        <input type="date" name="application_deadline" class="form-control" min="{{ now()->toDateString() }}">
      </div>
      <div class="col-12">
        <label class="form-label">Description *</label>
        <textarea name="description" class="form-control" rows="4" maxlength="20000"></textarea>
      </div>
      <div class="col-12">
        <label class="form-label">Requirements</label>
        <textarea name="requirements" class="form-control" rows="3" maxlength="20000"></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary js-inst-submit-btn">
          <span class="btn-text"><i class="fa-solid fa-plus me-1"></i> Post job</span>
          <span class="btn-loader d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </form>

  <div class="sch-manage-list" data-empty-text="No jobs posted yet.">
    @forelse($jobs as $job)
      @include('backend.institute.partials.job-item', ['job' => $job])
    @empty
      <p class="sch-manage-empty mb-0">No jobs posted yet.</p>
    @endforelse
  </div>
</section>

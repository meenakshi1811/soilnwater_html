<div class="sch-manage-item sch-job-manage-item mb-3" data-job-id="{{ $job->id }}">
    <div class="sch-manage-item__head">
        <div>
            <strong>{{ $job->title }}</strong>
            <div class="sch-manage-item__meta">
                <span>{{ $job->employmentTypeLabel() }}</span>
                @if($job->department)<span>{{ $job->department }}</span>@endif
                @if($job->location)<span>{{ $job->location }}</span>@endif
                <span>{{ $job->applications_count }} applicant{{ $job->applications_count === 1 ? '' : 's' }}</span>
                @if($job->status === 'closed')
                    <span class="text-danger">Closed</span>
                @else
                    <span class="text-success">Open</span>
                @endif
            </div>
            <p class="mb-0 mt-1 text-secondary small">{{ \Illuminate\Support\Str::limit($job->description, 180) }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($job->status === 'open')
                <button type="button" class="btn btn-outline-secondary btn-sm js-inst-job-close" data-url="{{ $portalRoute('jobs.update', $job) }}" data-status="closed">Close</button>
            @else
                <button type="button" class="btn btn-outline-success btn-sm js-inst-job-reopen" data-url="{{ $portalRoute('jobs.update', $job) }}" data-status="open">Reopen</button>
            @endif
            <button type="button" class="btn btn-outline-danger btn-sm js-inst-job-delete" data-url="{{ $portalRoute('jobs.destroy', $job) }}" aria-label="Delete job">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    @if($job->applications->isNotEmpty())
        <div class="sch-job-applications mt-3">
            <h4 class="h6 mb-2">Applications</h4>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($job->applications as $application)
                        @include('backend.institute.partials.job-application-row', ['application' => $application])
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

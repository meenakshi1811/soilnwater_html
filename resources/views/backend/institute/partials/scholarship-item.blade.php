{{-- A scholarship in the dashboard list: its name, type, benefit, who it's for, last date, open or
     closed, and Edit / Close (Reopen) / Delete --}}
<div class="sch-manage-item sch-scholarship-manage-item mb-3"
     data-scholarship-id="{{ $scholarship->id }}"
     data-update-url="{{ $portalRoute('scholarships.update', $scholarship) }}"
     data-scholarship='@json($scholarship->toFormPayload())'>
    <div class="sch-manage-item__head">
        <div>
            <strong>{{ $scholarship->title }}</strong>
            <div class="sch-manage-item__meta">
                <span><i class="fa-solid {{ $scholarship->typeIcon() }} me-1" aria-hidden="true"></i>{{ $scholarship->typeLabel() }}</span>
                @if($scholarship->amount_label)<span>{{ $scholarship->amount_label }}</span>@endif
                @if($scholarship->eligible_for)<span>{{ $scholarship->eligible_for }}</span>@endif
                @if($scholarship->application_deadline)
                    <span>Last date {{ $scholarship->application_deadline->format('j M Y') }}</span>
                @endif
                @if($scholarship->status === 'closed')
                    <span class="text-danger">Closed</span>
                @elseif(! $scholarship->isOpen())
                    <span class="text-warning">Last date passed</span>
                @else
                    <span class="text-success">Open</span>
                @endif
            </div>
            @if($scholarship->lines('eligibility') !== [])
                <p class="mb-0 mt-1 text-secondary small">{{ \Illuminate\Support\Str::limit(implode(' · ', $scholarship->lines('eligibility')), 180) }}</p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm js-scholarship-edit">
                <i class="fa-solid fa-pen me-1" aria-hidden="true"></i> Edit
            </button>
            @if($scholarship->status === 'open')
                <button type="button" class="btn btn-outline-secondary btn-sm js-scholarship-status" data-status="closed">Close</button>
            @else
                <button type="button" class="btn btn-outline-success btn-sm js-scholarship-status" data-status="open">Reopen</button>
            @endif
            <button type="button" class="btn btn-outline-danger btn-sm js-scholarship-delete" data-url="{{ $portalRoute('scholarships.destroy', $scholarship) }}" aria-label="Delete scholarship">
                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</div>

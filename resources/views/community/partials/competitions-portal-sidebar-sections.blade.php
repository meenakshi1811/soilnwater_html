@php
    $post = $post ?? null;
    if (! $post?->isCompetitionsPost()) {
        return;
    }

    $importantDates = array_filter([
        'Announcement' => data_get($post->meta, 'competitions_date_announcement'),
        'Registration opens' => data_get($post->meta, 'competitions_date_registration_opens'),
        'Registration closes' => data_get($post->meta, 'competitions_date_registration_closes'),
        'Submission deadline' => data_get($post->meta, 'competitions_date_submission_deadline'),
        'Evaluation period' => data_get($post->meta, 'competitions_date_evaluation_period'),
        'Result date' => data_get($post->meta, 'competitions_date_result'),
        'Award ceremony' => data_get($post->meta, 'competitions_date_award_ceremony'),
    ]);
    $organizerLogo = data_get($post->meta, 'competitions_organizer_logo');
    $hasOrganizer = filled(data_get($post->meta, 'competitions_organizer_name'))
        || filled(data_get($post->meta, 'competitions_organizer_organization'));
    $submissionDeadline = data_get($post->meta, 'competitions_date_submission_deadline');
@endphp

@if($submissionDeadline)
    <div class="community-news-sidebar__card community-news-sidebar__card--competition-deadline">
        <p class="community-news-sidebar__label">Submission deadline</p>
        <div class="border rounded p-3 bg-warning-subtle">
            <strong><i class="fa-solid fa-clock me-1" aria-hidden="true"></i>{{ $submissionDeadline }}</strong>
            @if(data_get($post->meta, 'competitions_registration_required'))
                <span class="d-block mt-2 badge bg-warning text-dark">Registration required</span>
            @endif
        </div>
    </div>
@endif

@if($importantDates !== [])
    <div class="community-news-sidebar__card community-news-sidebar__card--competition-dates">
        <p class="community-news-sidebar__label">Important dates</p>
        <ul class="list-unstyled small mb-0">
            @foreach($importantDates as $label => $value)
                <li class="border rounded p-2 mb-2 bg-light">
                    <strong class="d-block">{{ $label }}</strong>
                    <span>{{ $value }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif

@if($hasOrganizer)
    <div class="community-news-sidebar__card community-news-sidebar__card--competition-organizer">
        <p class="community-news-sidebar__label">Organizer</p>
        <div class="border rounded p-3 bg-light">
            @if(is_array($organizerLogo) && data_get($organizerLogo, 'url'))
                <img src="{{ data_get($organizerLogo, 'url') }}" alt="Organizer logo" class="rounded border mb-2" style="max-height:48px;">
            @endif
            @if(filled(data_get($post->meta, 'competitions_organizer_name')))
                <strong class="d-block">{{ data_get($post->meta, 'competitions_organizer_name') }}</strong>
            @endif
            @if(filled(data_get($post->meta, 'competitions_organizer_organization')))
                <span class="small text-muted">{{ data_get($post->meta, 'competitions_organizer_organization') }}</span>
            @endif
        </div>
    </div>
@endif

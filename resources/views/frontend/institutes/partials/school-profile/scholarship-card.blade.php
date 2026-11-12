{{-- A scholarship on an institution's page. $full: its Scholarships page (everything: criteria,
     documents, how to apply, the application link); else the profile's short card linking there. --}}
@php
  $full = $full ?? false;
  $eligibility = $scholarship->lines('eligibility');
  $documents = $scholarship->lines('documents');
  $facts = array_values(array_filter([
      $scholarship->eligible_for ? ['fa-user-graduate', 'For', $scholarship->eligible_for] : null,
      $scholarship->min_marks ? ['fa-chart-line', 'Minimum marks', $scholarship->min_marks] : null,
      $scholarship->income_limit ? ['fa-house-chimney-user', 'Family income', $scholarship->income_limit] : null,
      $scholarship->awards_count ? ['fa-users', 'Scholarships', number_format($scholarship->awards_count)] : null,
  ]));
  $detailsUrl = $institute->publicSectionUrl('scholarships').'#sch-scholarship-'.$scholarship->id;
@endphp
<{{ $full ? 'article' : 'a' }} @if($full) id="sch-scholarship-{{ $scholarship->id }}" @else href="{{ $detailsUrl }}" @endif class="sch-scholarship {{ $full ? 'sch-scholarship--full' : '' }}">
  <div class="sch-scholarship__top">
    <span class="sch-scholarship__type"><i class="fa-solid {{ $scholarship->typeIcon() }}" aria-hidden="true"></i> {{ $scholarship->typeLabel() }}</span>
    @if($scholarship->opensLater())
      <span class="sch-scholarship__when sch-scholarship__when--soon">Opens {{ $scholarship->application_start->format('j M Y') }}</span>
    @elseif($scholarship->application_deadline)
      <span class="sch-scholarship__when {{ $scholarship->application_deadline->diffInDays(now()->startOfDay(), true) <= 7 ? 'sch-scholarship__when--near' : '' }}">Apply by {{ $scholarship->application_deadline->format('j M Y') }}</span>
    @else
      <span class="sch-scholarship__when">Open</span>
    @endif
  </div>

  <h3 class="sch-scholarship__title">{{ $scholarship->title }}</h3>
  @if($scholarship->amount_label)
    <p class="sch-scholarship__amount"><i class="fa-solid fa-gift" aria-hidden="true"></i> {{ $scholarship->amount_label }}</p>
  @endif

  @if($facts !== [])
    <dl class="sch-scholarship__facts">
      @foreach($facts as [$icon, $label, $value])
        <div>
          <dt><i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }}</dt>
          <dd>{{ $value }}</dd>
        </div>
      @endforeach
    </dl>
  @endif

  @if($full)
    @if(filled($scholarship->description))
      <p class="sch-scholarship__text">{!! nl2br(e($scholarship->description)) !!}</p>
    @endif

    <div class="sch-scholarship__cols">
      @if($eligibility !== [])
        <div class="sch-scholarship__block">
          <h4><i class="fa-solid fa-list-check" aria-hidden="true"></i> Eligibility criteria</h4>
          <ul class="sch-scholarship__ticks">
            @foreach($eligibility as $point)
              <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
            @endforeach
          </ul>
        </div>
      @endif
      @if($documents !== [])
        <div class="sch-scholarship__block">
          <h4><i class="fa-solid fa-folder-open" aria-hidden="true"></i> Documents required</h4>
          <ul class="sch-scholarship__docs">
            @foreach($documents as $document)
              <li><i class="fa-regular fa-file-lines" aria-hidden="true"></i> {{ $document }}</li>
            @endforeach
          </ul>
        </div>
      @endif
    </div>

    @if(filled($scholarship->how_to_apply) || $scholarship->applyUrl() || $scholarship->application_start)
      <div class="sch-scholarship__apply">
        <div>
          <h4><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> How to apply</h4>
          @if(filled($scholarship->how_to_apply))
            <p class="mb-0">{!! nl2br(e($scholarship->how_to_apply)) !!}</p>
          @endif
          @if($scholarship->application_start || $scholarship->application_deadline)
            <p class="sch-scholarship__dates mb-0">
              @if($scholarship->application_start)
                <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> Opens {{ $scholarship->application_start->format('j M Y') }}</span>
              @endif
              @if($scholarship->application_deadline)
                <span><i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i> Last date {{ $scholarship->application_deadline->format('j M Y') }}</span>
              @endif
            </p>
          @endif
        </div>
        @if($scholarship->applyUrl())
          <a href="{{ $scholarship->applyUrl() }}" class="sch-btn sch-btn-primary sch-scholarship__apply-btn" target="_blank" rel="noopener nofollow">
            Apply online <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
          </a>
        @endif
      </div>
    @endif
  @else
    @if($eligibility !== [])
      <p class="sch-scholarship__lead">{{ \Illuminate\Support\Str::limit(implode(' · ', $eligibility), 110) }}</p>
    @endif
    <span class="sch-scholarship__more">View details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
  @endif
</{{ $full ? 'article' : 'a' }}>

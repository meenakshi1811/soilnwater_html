@php
  $hasApplied = in_array($job->id, $appliedJobIds ?? [], true);
  $applyUrl = route(($listingContext ?? 'schools').'.jobs.apply', ['slug' => $institute->slug, 'job' => $job->id]);
  $payload = $job->toPublicPayload();
@endphp
<button
  type="button"
  class="sch-job-card js-sch-job-open"
  data-job="{{ e(json_encode($payload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) }}"
  data-apply-url="{{ $applyUrl }}"
  data-applied="{{ $hasApplied ? '1' : '0' }}"
  aria-haspopup="dialog"
>
  <span class="sch-job-card__badge">{{ $job->employmentTypeLabel() }}</span>
  @if($hasApplied)
    <span class="sch-job-card__applied"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Applied</span>
  @endif
  <h3 class="sch-job-card__title">{{ $job->title }}</h3>
  @if($job->department)
    <p class="sch-job-card__dept">{{ $job->department }}</p>
  @endif
  <ul class="sch-job-card__meta">
    @if($job->location)
      <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $job->location }}</li>
    @endif
    @if($job->experience_label)
      <li><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> {{ $job->experience_label }}</li>
    @endif
    @if($job->salary_label)
      <li><i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> {{ $job->salary_label }}</li>
    @endif
  </ul>
  <p class="sch-job-card__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($job->description), 120) }}</p>
  <span class="sch-job-card__cta">View details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
</button>

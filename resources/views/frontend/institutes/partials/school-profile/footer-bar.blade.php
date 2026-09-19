@php
  $engagement = $engagement ?? [];
  $listingContext = $listingContext ?? 'schools';
  $helpfulVote = $engagement['helpful_vote'] ?? null;
  $canReport = ! empty($engagement['can_report']);
@endphp
<div class="sch-footer-bar">
  <div class="sch-footer-bar__left">
    <span>Find this profile helpful?</span>
    <button
      type="button"
      class="sch-footer-bar__vote js-sch-helpful {{ $helpfulVote === 'yes' ? 'is-selected' : '' }}"
      data-vote="yes"
    ><i class="fa-solid fa-thumbs-up"></i> Yes</button>
    <button
      type="button"
      class="sch-footer-bar__vote js-sch-helpful {{ $helpfulVote === 'no' ? 'is-selected' : '' }}"
      data-vote="no"
    ><i class="fa-solid fa-thumbs-down"></i> No</button>
  </div>
  @if($canReport)
    <button
      type="button"
      class="sch-footer-bar__report js-sch-report"
      data-bs-toggle="modal"
      data-bs-target="#schoolProfileReportModal"
    ><i class="fa-solid fa-flag"></i> Report Profile</button>
  @elseif(auth()->check())
    <button type="button" class="sch-footer-bar__report" disabled title="You cannot report your own profile">
      <i class="fa-solid fa-flag"></i> Report Profile
    </button>
  @else
    <a href="{{ route('login') }}" class="sch-footer-bar__report sch-footer-bar__report--link">
      <i class="fa-solid fa-flag"></i> Report Profile
    </a>
  @endif
</div>

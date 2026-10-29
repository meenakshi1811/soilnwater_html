@php
  $engagement = $engagement ?? [
    'is_following' => false,
    'is_bookmarked' => false,
    'in_compare' => false,
    'has_brochure' => false,
  ];
  $listingContext = $listingContext ?? 'schools';
@endphp
{{-- Phones: Enquire Now across, the rest as a row of small icon tiles with short labels --}}
<div class="sch-hero__actions">
  <button type="button" class="sch-btn sch-btn-primary sch-btn-block js-sch-open-enquiry">
    <i class="fa-solid fa-comment-dots" aria-hidden="true"></i> Enquire Now
  </button>
  <a href="{{ $institute->publicDiaryUrl() }}" class="sch-btn sch-btn-outline sch-btn-block">
    <i class="fa-solid fa-book-bookmark" aria-hidden="true"></i> <span><span class="sch-hero__action-extra">View </span>Diary</span>
  </a>
  <button
    type="button"
    class="sch-btn sch-btn-outline sch-btn-block js-sch-brochure {{ $engagement['has_brochure'] ? '' : 'is-disabled' }}"
    data-url="{{ route($listingContext.'.brochure', $institute->slug) }}"
    @if(! $engagement['has_brochure']) title="Brochure not uploaded yet" @endif
  >
    <i class="fa-solid fa-download" aria-hidden="true"></i> <span><span class="sch-hero__action-extra">Download </span>Brochure</span>
  </button>
  <button
    type="button"
    class="sch-btn sch-btn-outline sch-btn-block js-sch-compare {{ !empty($engagement['in_compare']) ? 'is-active' : '' }}"
    data-url="{{ route($listingContext.'.compare.toggle', $institute->slug) }}"
  >
    <i class="fa-solid fa-code-compare" aria-hidden="true"></i>
    <span class="js-sch-compare-label">
      @if(!empty($engagement['in_compare']))
        <span class="sch-hero__action-extra">In compare list</span><span class="sch-hero__action-short">Added</span>
      @else
        Compare
      @endif
    </span>
  </button>
  <button
    type="button"
    class="sch-btn sch-btn-outline sch-btn-block js-sch-follow {{ !empty($engagement['is_following']) ? 'is-following' : '' }}"
    data-url="{{ route($listingContext.'.follow', $institute->slug) }}"
  >
    <i class="{{ !empty($engagement['is_following']) ? 'fa-solid' : 'fa-regular' }} fa-heart" aria-hidden="true"></i>
    <span class="js-sch-follow-label">
      @if(!empty($engagement['is_following']))
        Following
      @else
        Follow<span class="sch-hero__action-extra"> School</span>
      @endif
    </span>
  </button>
  <button type="button" class="sch-btn sch-btn-outline sch-btn-block js-sch-share">
    <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> <span>Share<span class="sch-hero__action-extra"> Profile</span></span>
  </button>
</div>

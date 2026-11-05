<div class="sch-content-main">
    @if($aboutText)
      <section id="sch-about" class="sch-card sch-section">
        <h2 class="sch-section__title">About the {{ $entityLabel ?? 'School' }}@if($canEditInline ?? false)<button type="button" class="sch-inline-pencil js-sch-inline-edit" data-inline-edit="basics" title="Edit about"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>@endif</h2>
        <div class="sch-about">
          <div class="sch-about__text js-sch-about-text {{ $aboutNeedsToggle ? 'is-collapsed' : '' }}">{!! nl2br(e($aboutText)) !!}</div>
          @if($aboutNeedsToggle)
            <button type="button" class="sch-read-more js-sch-read-more">Read More <i class="fa-solid fa-chevron-down"></i></button>
          @endif
          @if($profile->aboutHighlights() !== [])
            <ul class="sch-check-list">
              @foreach($profile->aboutHighlights() as $point)
                <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </section>
    @endif

    @include('frontend.institutes.partials.school-profile.sections', [
      'onlySection' => null,
      // (Jobs & Careers comes under Admission Info, below)
      'skipSections' => ['jobs'],
      'isProfilePreview' => true,
      'profile' => $profile,
      'institute' => $institute,
      'entityLabel' => $entityLabel ?? 'School',
      'listingContext' => $listingContext ?? 'schools',
      'canEditInline' => $canEditInline ?? false,
    ])

    <section id="sch-admission" class="sch-card sch-section sch-admission-section">
      <h2 class="sch-section__title">Admission Info</h2>
      @if($profile->admissionLead() !== '')
        <p class="sch-section__lead">{{ $profile->admissionLead() }}</p>
      @endif
      @if($profile->admissionPreviewHighlights() !== [])
        <ul class="sch-check-list sch-admission-section__list">
          @foreach($profile->admissionPreviewHighlights() as $point)
            <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
          @endforeach
        </ul>
      @endif
      @if($profile->admissionHasMore() || $profile->feeStructure())
        <div class="sch-admission-section__links">
          @if($profile->admissionHasMore())
            <button type="button" class="sch-read-more js-sch-admission-read-more">
              Read more <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </button>
          @endif
          @if($profile->feeStructure())
            {{-- (also at the bottom of the Read more popup) --}}
            <a href="{{ $institute->publicFeeStructureUrl() }}" class="sch-read-more">
              <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> View fee structure <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
          @endif
        </div>
      @endif
    </section>

    @include('frontend.institutes.partials.school-profile.sections', [
      'onlySection' => 'jobs',
      'isProfilePreview' => true,
      'profile' => $profile,
      'institute' => $institute,
      'entityLabel' => $entityLabel ?? 'School',
      'listingContext' => $listingContext ?? 'schools',
      'canEditInline' => $canEditInline ?? false,
    ])
</div>

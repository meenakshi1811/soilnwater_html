{{-- Public notice board block, reused by show-school.blade.php and the inline-edit re-render --}}
@php
  $canEditInline = $canEditInline ?? false;
@endphp
@if($institute->activeNotices->isNotEmpty())
  @php
    $noticesPreviewLimit = \App\Support\SchoolProfilePresenter::PREVIEW_LIMITS['notices'];
  @endphp
  <section class="sch-notice-section" id="sch-notices" aria-label="Notice Board">
    @if($canEditInline)
      <div class="sch-inline-section-tools">
        <button type="button" class="sch-inline-pencil js-sch-inline-edit" data-inline-edit="notices" title="Edit notices"><i class="fa-solid fa-pen" aria-hidden="true"></i></button>
      </div>
    @endif
    <div class="sch-notice-section__frame sch-card">
      @include('frontend.institutes.partials.notice-board', [
        'notices' => $institute->activeNotices->take($noticesPreviewLimit),
        'featured' => true,
        'layout' => 'grid',
        'viewAllUrl' => $institute->publicSectionUrl('notices'),
        'viewAllLabel' => 'View all notices',
        'showViewAll' => true,
      ])
    </div>
  </section>
@endif

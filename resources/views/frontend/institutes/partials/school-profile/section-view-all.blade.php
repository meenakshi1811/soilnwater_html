@php
  $isProfilePreview = $isProfilePreview ?? true;
  $sectionKey = $sectionKey ?? '';
  $total = (int) ($total ?? 0);
  $limit = (int) ($limit ?? 0);
  $label = $label ?? 'items';
  $inHead = ! empty($inHead);
  $alwaysShow = ! empty($alwaysShow);
@endphp
@if($isProfilePreview && $sectionKey !== '' && ($alwaysShow || $total > $limit))
  @if($inHead)
    <a href="{{ $institute->publicSectionUrl($sectionKey) }}" class="sch-section__link">
      View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
    </a>
  @else
    <div class="sch-section__footer mt-3">
      <a href="{{ $institute->publicSectionUrl($sectionKey) }}" class="btn btn-outline-primary btn-sm">
        View all {{ number_format($total) }} {{ $label }}
      </a>
    </div>
  @endif
@endif

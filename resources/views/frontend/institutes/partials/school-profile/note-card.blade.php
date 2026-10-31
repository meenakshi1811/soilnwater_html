@php
  /** @var \App\Models\StudyMaterial $note */
  $fileMeta = $note->fileTypeMeta();
  $imageUrl = $note->cardImageUrl();
  $details = implode(' · ', array_filter([$note->subject, $note->class_course]));
@endphp
<a href="{{ $note->publicUrl() }}" class="sch-note-card">
  @if($imageUrl)
    <img src="{{ $imageUrl }}" alt="" class="sch-note-card__thumb sch-note-card__thumb--image" loading="lazy" decoding="async">
  @else
    <span class="sch-note-card__thumb sch-note-card__thumb--{{ $fileMeta['tone'] }}" aria-hidden="true">
      <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
    </span>
  @endif
  <div class="sch-note-card__body">
    <h3 class="sch-note-card__title">{{ $note->title }}</h3>
    @if($details !== '')
      <p class="sch-note-card__meta mb-0">{{ $details }}</p>
    @endif
    <p class="sch-note-card__stats mb-0">
      <span>{{ $note->materialTypeLabel() }}</span>
      <span title="Downloads"><i class="fa-solid fa-download" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($note->downloads_count) }}</span>
    </p>
  </div>
</a>

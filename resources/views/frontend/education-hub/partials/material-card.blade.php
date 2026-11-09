{{-- A trending study material: its cover image (or the file-type icon), the group's badge, title and details --}}
@php
  $fileMeta = $material->fileTypeMeta();
  $imageUrl = $material->cardImageUrl();
  $classYear = collect([$material->class_course, $material->academic_year])->filter()->implode(' · ');
  $rating = (float) $material->average_rating;
  // (a paper's board, or its school)
  $source = $material->material_type === 'question_papers' ? trim((string) ($material->board_university ?: $material->school_name)) : '';
@endphp
<a href="{{ $material->publicUrl() }}" class="eh-tm">
  <span class="eh-tm__media{{ $imageUrl ? '' : ' eh-tm__media--'.$fileMeta['tone'] }}">
    @if($imageUrl)
      <img src="{{ \App\Support\ImageThumbnail::url($imageUrl) }}" alt="" loading="lazy" decoding="async">
    @else
      <i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i>
    @endif
    <span class="eh-tm__type">{{ $badge ?? $material->materialTypeLabel() }}</span>
  </span>
  <span class="eh-tm__body">
    <strong class="eh-tm__title">{{ $material->title }}</strong>
    <span class="eh-tm__line"><i class="fa-solid fa-book" aria-hidden="true"></i><span>{{ $material->subject ?: $material->materialTypeLabel() }}</span></span>
    @if($source !== '')
      <span class="eh-tm__line"><i class="fa-solid {{ $sourceIcon ?? 'fa-building-columns' }}" aria-hidden="true"></i><span>{{ $source }}</span></span>
    @endif
    @if($classYear !== '')
      <span class="eh-tm__line"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i><span>{{ $classYear }}</span></span>
    @endif
    <span class="eh-tm__line"><i class="fa-solid fa-user" aria-hidden="true"></i><span>{{ $material->educator?->display_name ?: $material->user?->name ?: 'SoilnWater' }}</span></span>
    <span class="eh-tm__meta">
      <span title="Views"><i class="fa-regular fa-eye" aria-hidden="true"></i>{{ \App\Models\StudyMaterial::formatCompactCount($material->views_count) }}</span>
      <span title="Downloads"><i class="fa-solid fa-download" aria-hidden="true"></i>{{ \App\Models\StudyMaterial::formatCompactCount($material->downloads_count) }}</span>
      @if($rating > 0)
        <span title="Rating"><i class="fa-solid fa-star eh-tm__star" aria-hidden="true"></i>{{ number_format($rating, 1) }}</span>
      @endif
    </span>
  </span>
</a>

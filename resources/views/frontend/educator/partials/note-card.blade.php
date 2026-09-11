@php
    $fileMeta = $note->fileTypeMeta();
    $showActions = $showActions ?? false;
    $materialLabel = $materialLabel ?? ($note->material_type === 'question_papers' ? 'Question Paper' : 'Notes');
@endphp

<article class="edu-note-card {{ $showActions ? 'edu-note-card--interactive' : '' }}">
  <a href="{{ $note->publicUrl() }}" class="edu-note-card__link">
    <span class="edu-note-card__icon edu-note-card__icon--{{ $fileMeta['tone'] }}">
      <i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i>
    </span>
    <div class="edu-note-card__body">
      <h4>{{ $note->title }}</h4>
      <p>{{ collect([$note->class_course, $note->subject ?: $materialLabel])->filter()->implode(' · ') }} · {{ $fileMeta['label'] }}</p>
      <div class="edu-note-card__meta">
        @if($note->pages)
          <span><i class="fa-solid fa-file" aria-hidden="true"></i> {{ number_format($note->pages) }} pages</span>
        @endif
        <span><i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format((float) $note->average_rating, 1) }}</span>
        <span><i class="fa-solid fa-download" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($note->downloads_count) }}</span>
      </div>
    </div>
  </a>
  @if($showActions)
    <div class="edu-note-card__actions">
      <a href="{{ $note->publicUrl() }}" class="edu-note-card__action edu-note-card__action--primary">
        <i class="fa-solid fa-eye" aria-hidden="true"></i> View
      </a>
      @auth
        <button
          type="button"
          class="edu-note-card__action js-edu-note-bookmark {{ ! empty($note->is_bookmarked) ? 'is-active' : '' }}"
          data-url="{{ route('study-materials.bookmark', $note->slug) }}"
          data-bookmarked="{{ ! empty($note->is_bookmarked) ? '1' : '0' }}"
          aria-pressed="{{ ! empty($note->is_bookmarked) ? 'true' : 'false' }}"
        >
          <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
          {{ ! empty($note->is_bookmarked) ? 'Saved' : 'Save' }}
        </button>
      @else
        <a href="{{ route('login') }}" class="edu-note-card__action">
          <i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save
        </a>
      @endauth
      @if($note->is_free && auth()->check())
        <a href="{{ route('study-materials.download', $note->slug) }}" class="edu-note-card__action">
          <i class="fa-solid fa-download" aria-hidden="true"></i> Download
        </a>
      @elseif($note->is_free)
        <a href="{{ route('login') }}" class="edu-note-card__action">
          <i class="fa-solid fa-download" aria-hidden="true"></i> Download
        </a>
      @endif
    </div>
  @endif
</article>

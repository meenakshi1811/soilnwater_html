@php
    $typeMeta = \App\Models\StudyMaterial::materialTypeMeta($course->material_type);
    $showActions = $showActions ?? false;
@endphp

<article class="edu-course-card {{ $showActions ? 'edu-course-card--interactive' : '' }}">
  <a href="{{ $course->publicUrl() }}" class="edu-course-card__link">
    <div class="edu-course-card__thumb">
      <img src="{{ $course->thumbnailUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="">
      <span class="edu-course-card__badge">{{ $typeMeta['label'] }}</span>
    </div>
    <div class="edu-course-card__body">
      <h4>{{ $course->title }}</h4>
      @if($course->subject || $course->class_course)
        <p class="edu-course-card__subtitle">{{ collect([$course->class_course, $course->subject])->filter()->implode(' · ') }}</p>
      @endif
      <div class="edu-course-card__price">{{ $course->is_free ? 'Free' : 'Premium' }}</div>
      <div class="edu-course-card__meta">
        <span><i class="fa-solid fa-star" aria-hidden="true"></i> {{ number_format((float) $course->average_rating, 1) }}</span>
        <span><i class="fa-solid fa-users" aria-hidden="true"></i> {{ \App\Models\StudyMaterial::formatCompactCount($course->downloads_count) }}</span>
      </div>
    </div>
  </a>
  @if($showActions)
    <div class="edu-course-card__actions">
      <a href="{{ $course->publicUrl() }}" class="edu-course-card__action edu-course-card__action--primary">
        <i class="fa-solid fa-eye" aria-hidden="true"></i> View
      </a>
      @auth
        <button
          type="button"
          class="edu-course-card__action js-edu-course-bookmark {{ ! empty($course->is_bookmarked) ? 'is-active' : '' }}"
          data-url="{{ route('study-materials.bookmark', $course->slug) }}"
          data-bookmarked="{{ ! empty($course->is_bookmarked) ? '1' : '0' }}"
          aria-pressed="{{ ! empty($course->is_bookmarked) ? 'true' : 'false' }}"
        >
          <i class="fa-solid {{ ! empty($course->is_bookmarked) ? 'fa-bookmark' : 'fa-bookmark' }}" aria-hidden="true"></i>
          {{ ! empty($course->is_bookmarked) ? 'Saved' : 'Save' }}
        </button>
      @else
        <a href="{{ route('login') }}" class="edu-course-card__action">
          <i class="fa-regular fa-bookmark" aria-hidden="true"></i> Save
        </a>
      @endauth
      @if($course->is_free && auth()->check())
        <a href="{{ route('study-materials.download', $course->slug) }}" class="edu-course-card__action">
          <i class="fa-solid fa-download" aria-hidden="true"></i> Download
        </a>
      @elseif($course->is_free)
        <a href="{{ route('login') }}" class="edu-course-card__action">
          <i class="fa-solid fa-download" aria-hidden="true"></i> Download
        </a>
      @endif
    </div>
  @endif
</article>

@php
    $fileMeta = $item->fileTypeMeta();
    $tags = collect($item->tags ?? [])->filter()->take(4);
    if ($tags->isEmpty()) {
        $tags = collect(array_filter([$item->class_course, $item->board_university, $item->subject]));
    }
@endphp

<article class="sm-note-card">
    <a href="{{ $item->publicUrl() }}" class="sm-note-card__file sm-note-card__file--{{ $fileMeta['tone'] }}" aria-label="{{ $fileMeta['label'] }} file">
        <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
        <span>{{ $fileMeta['label'] }}</span>
    </a>

    <div class="sm-note-card__content">
        <h3 class="sm-note-card__title">
            <a href="{{ $item->publicUrl() }}">{{ $item->title }}</a>
        </h3>
        <div class="sm-note-card__crumbs">
            @if($item->class_course)
                <a href="{{ route('study-materials.notes', array_merge(request()->except('page'), ['class_course' => $item->class_course])) }}">{{ $item->class_course }}</a>
            @endif
            @if($item->subject)
                <span aria-hidden="true">·</span>
                <a href="{{ route('study-materials.notes', array_merge(request()->except('page'), ['subject' => $item->subject])) }}">{{ $item->subject }}</a>
            @endif
            @if($item->topic_chapter)
                <span aria-hidden="true">·</span>
                <a href="{{ route('study-materials.notes', array_merge(request()->except('page'), ['topic_chapter' => $item->topic_chapter])) }}">{{ $item->topic_chapter }}</a>
            @endif
        </div>

        @if($item->description)
            <p class="sm-note-card__desc">{{ \Illuminate\Support\Str::limit(strip_tags($item->description), 160) }}</p>
        @endif

        @if($tags->isNotEmpty())
            <div class="sm-note-card__tags">
                @foreach($tags as $tag)
                    <span class="sm-note-card__tag">{{ $tag }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="sm-note-card__author-col">
        <div class="sm-note-card__author">
            <a href="{{ $item->educator?->publicUrl() ?: '#' }}">
                <img src="{{ $item->educator?->photoUrl() ?: asset('assets/images/logo_soilnwater.webp') }}" alt="{{ $item->educator?->display_name ?: 'Contributor' }}" class="sm-note-card__avatar">
            </a>
            <div>
                <div class="sm-note-card__author-name">
                    By <a href="{{ $item->educator?->publicUrl() ?: '#' }}">{{ $item->educator?->display_name ?: 'Community Member' }}</a>
                </div>
                @if($item->educator?->isVerified())
                    <span class="sm-note-card__verified"><i class="fa-solid fa-circle-check"></i> Verified Teacher</span>
                @endif
                <div class="sm-note-card__stats">
                    <span class="sm-note-card__rating"><i class="fa-solid fa-star"></i> {{ number_format((float) $item->average_rating, 1) }} ({{ number_format($item->reviews_count) }})</span>
                    <span><i class="fa-solid fa-download"></i> {{ \App\Models\StudyMaterial::formatCompactCount($item->downloads_count) }}</span>
                    <span><i class="fa-solid fa-eye"></i> {{ \App\Models\StudyMaterial::formatCompactCount($item->views_count) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sm-note-card__actions-col">
        <time class="sm-note-card__date" datetime="{{ $item->created_at?->toDateString() }}">{{ $item->created_at?->format('j M Y') }}</time>
        @auth
            <button
                type="button"
                class="sm-note-card__save js-sm-bookmark {{ !empty($item->is_bookmarked) ? 'is-saved' : '' }}"
                data-url="{{ route('study-materials.bookmark', $item->slug) }}"
                title="{{ !empty($item->is_bookmarked) ? 'Saved' : 'Save' }}"
            >
                <i class="fa-{{ !empty($item->is_bookmarked) ? 'solid' : 'regular' }} fa-bookmark"></i> Save
            </button>
        @else
            <a href="{{ route('login') }}" class="sm-note-card__save"><i class="fa-regular fa-bookmark"></i> Save</a>
        @endauth
        <a href="{{ auth()->check() ? route('study-materials.download', $item->slug) : route('login') }}" class="sm-btn sm-btn-primary sm-note-card__download">
            <i class="fa-solid fa-download"></i> Download
        </a>
    </div>
</article>

@php
    $fileMeta = $item->fileTypeMeta();
@endphp

<a href="{{ $item->publicUrl() }}" class="sm-grid-card">
    <div class="sm-grid-card__icon sm-grid-card__icon--{{ $fileMeta['tone'] }}">
        <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
    </div>
    <div class="sm-grid-card__body">
        <h4>{{ $item->title }}</h4>
        <p>{{ $item->subject ?: $item->materialTypeLabel() }}</p>
        <div class="sm-grid-card__meta">
            <span><i class="fa-solid fa-star"></i> {{ number_format((float) $item->average_rating, 1) }}</span>
            <span><i class="fa-solid fa-download"></i> {{ \App\Models\StudyMaterial::formatCompactCount($item->downloads_count) }}</span>
        </div>
    </div>
</a>

@php
    $fileMeta = $item->fileTypeMeta();
    $imageUrl = $item->cardImageUrl();
    // Subject, school/institute (a board for board papers), class and year, whichever are filled
    $details = array_filter([
        ['icon' => 'fa-book', 'label' => 'Subject', 'value' => $item->subject, 'wide' => true],
        ['icon' => 'fa-school', 'label' => $item->isBoardQuestionPaper() ? 'Board' : 'School / Institute', 'value' => $item->board_university, 'wide' => true],
        ['icon' => 'fa-graduation-cap', 'label' => 'Class', 'value' => $item->class_course, 'wide' => false],
        ['icon' => 'fa-calendar', 'label' => 'Year', 'value' => $item->academic_year, 'wide' => false],
    ], fn (array $detail): bool => filled($detail['value']));
@endphp

<a href="{{ $item->publicUrl() }}" class="sm-grid-card">
    @if($imageUrl)
        <div class="sm-grid-card__icon sm-grid-card__icon--image">
            <img src="{{ $imageUrl }}" alt="" loading="lazy" decoding="async">
        </div>
    @else
        <div class="sm-grid-card__icon sm-grid-card__icon--{{ $fileMeta['tone'] }}">
            <i class="fa-solid {{ $fileMeta['icon'] }}"></i>
        </div>
    @endif
    <div class="sm-grid-card__body">
        <h4>{{ $item->title }}</h4>
        @if($details !== [])
            <dl class="sm-grid-card__details">
                @foreach($details as $detail)
                    <div class="sm-grid-card__detail{{ $detail['wide'] ? ' sm-grid-card__detail--wide' : '' }}" title="{{ $detail['label'] }}: {{ $detail['value'] }}">
                        <dt><i class="fa-solid {{ $detail['icon'] }}" aria-hidden="true"></i><span class="visually-hidden">{{ $detail['label'] }}</span></dt>
                        <dd>{{ $detail['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p>{{ $item->materialTypeLabel() }}</p>
        @endif
        <div class="sm-grid-card__meta">
            <span title="Rating"><i class="fa-solid fa-star"></i> {{ number_format((float) $item->average_rating, 1) }}</span>
            <span title="Downloads"><i class="fa-solid fa-download"></i> {{ \App\Models\StudyMaterial::formatCompactCount($item->downloads_count) }}</span>
            <span title="Views"><i class="fa-solid fa-eye"></i> {{ \App\Models\StudyMaterial::formatCompactCount($item->views_count) }}</span>
        </div>
    </div>
</a>

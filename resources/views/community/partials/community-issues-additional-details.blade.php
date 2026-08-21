@php
    $formFieldLabels = \App\Support\CommunityPostFormFields::labels();
    $railLayout = $railLayout ?? false;
    $skipKeys = array_merge(
        \App\Support\CommunityPostFormFields::communityIssueStructuredMetaKeys(),
        [
            'author_bio',
            'community_issue_photo_evidence',
            'community_issue_documents',
            'community_issue_private_link_token',
        ]
    );
    $additionalMeta = collect($post->meta ?? [])
        ->except($skipKeys)
        ->filter(function (mixed $value): bool {
            if (is_array($value)) {
                return array_filter($value, fn (mixed $item): bool => filled($item)) !== [];
            }

            if (is_object($value)) {
                return false;
            }

            return filled($value) || $value === false;
        });
@endphp

@if($post->isCommunityIssuesPost() && $additionalMeta->isNotEmpty())
    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--meta community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
                <div>
                    <h4 class="community-detail-card__title">{{ $heading ?? 'Additional details' }}</h4>
                </div>
            </div>
            <dl class="community-detail-list community-detail-list--rail mb-0">
                @foreach($additionalMeta as $key => $value)
                    @if(is_array($value))
                        @php
                            $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
                        @endphp
                    @endif
                    <div class="community-detail-list__row">
                        <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                        <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @else
        <div class="business-section-panel about-box mb-4">
            <div class="business-section-panel__header">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <h4 class="mb-0">{{ $heading ?? 'Additional details' }}</h4>
            </div>
            <dl class="community-detail-list mb-0">
                @foreach($additionalMeta as $key => $value)
                    @if(is_array($value))
                        @php
                            $value = implode(', ', array_values(array_filter($value, fn (mixed $item): bool => filled($item))));
                        @endphp
                    @endif
                    <div class="community-detail-list__row">
                        <dt>{{ $formFieldLabels[$key] ?? \Illuminate\Support\Str::headline($key) }}</dt>
                        <dd>{!! nl2br(e(is_bool($value) ? 'Yes' : $value)) !!}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endif
@endif

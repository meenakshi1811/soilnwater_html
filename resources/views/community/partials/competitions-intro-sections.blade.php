@if($post->isCompetitionsPost())
    @php
        $railLayout = $railLayout ?? false;
        $competitionType = data_get($post->meta, 'competitions_competition_type');
        $category = data_get($post->meta, 'competitions_category', $post->category);
        $chips = collect([
            $competitionType,
            $category,
            data_get($post->meta, 'competitions_level'),
            filled(data_get($post->meta, 'competitions_primary_origin_section'))
                ? 'From '.data_get($post->meta, 'competitions_primary_origin_section')
                : null,
        ])->filter(fn (mixed $value): bool => filled($value));
    @endphp

    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail community-detail-card--competition-intro">
            <div class="comp-show-overview comp-show-overview--rail mb-0">
                <div class="comp-show-overview__kicker">Competitions · SoilnWater community</div>
                <p class="comp-show-overview__tagline mb-0">Join this SoilnWater competition — review rules, dates, prizes, and how to participate.</p>
                @if($chips->isNotEmpty())
                    <div class="comp-show-overview__chips d-flex flex-wrap gap-2 mt-3">
                        @if(filled($competitionType))
                            <span class="comp-show-chip comp-show-chip--highlight">{{ $competitionType }}</span>
                        @endif
                        @if(filled($category))
                            <span class="comp-show-chip">{{ $category }}</span>
                        @endif
                        @if(filled(data_get($post->meta, 'competitions_level')))
                            <span class="comp-show-chip">{{ data_get($post->meta, 'competitions_level') }}</span>
                        @endif
                        @if(filled(data_get($post->meta, 'competitions_primary_origin_section')))
                            <span class="comp-show-chip">From {{ data_get($post->meta, 'competitions_primary_origin_section') }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="comp-show-overview">
            <div class="comp-show-overview__kicker">Competitions · SoilnWater community</div>
            <p class="comp-show-overview__tagline mb-0">Join this SoilnWater competition — review rules, dates, prizes, and how to participate.</p>
            @if($chips->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mt-3">
                    @if(filled($competitionType))
                        <span class="comp-show-chip comp-show-chip--highlight">{{ $competitionType }}</span>
                    @endif
                    @if(filled($category))
                        <span class="comp-show-chip">{{ $category }}</span>
                    @endif
                    @if(filled(data_get($post->meta, 'competitions_level')))
                        <span class="comp-show-chip">{{ data_get($post->meta, 'competitions_level') }}</span>
                    @endif
                    @if(filled(data_get($post->meta, 'competitions_primary_origin_section')))
                        <span class="comp-show-chip">From {{ data_get($post->meta, 'competitions_primary_origin_section') }}</span>
                    @endif
                </div>
            @endif
        </div>
    @endif
@endif

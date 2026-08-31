@if($post->isReligionSpiritualityPost())
    @php
        $postType = $post->religionSpiritualityPostTypeLabel();
        $category = $post->religionSpiritualityCategoryLabel();
        $tradition = data_get($post->meta, 'religion_spirituality_tradition');
        $uniqueFeatures = $post->religionSpiritualityUniqueFeatureLabels();
    @endphp

    <div class="rs-show-overview{{ ($detailLeadLayout ?? false) ? ' rs-show-overview--detail-lead' : '' }}">
        <div class="rs-show-overview__kicker">Religion &amp; Spirituality · SoilnWater community</div>
        <p class="rs-show-overview__objective mb-0">{{ \App\Support\CommunityContentTaxonomy::religionSpiritualityObjective() }}</p>
        @if(filled($postType) || filled($category) || filled($tradition) || $uniqueFeatures !== [])
            <div class="rs-show-overview__chips mt-3">
                @if(filled($postType))
                    <span class="rs-show-chip">{{ $postType }}</span>
                @endif
                @if(filled($category))
                    <span class="rs-show-chip">{{ $category }}</span>
                @endif
                @if(filled($tradition))
                    <span class="rs-show-chip">{{ $tradition }}</span>
                @endif
                @foreach($uniqueFeatures as $feature)
                    <span class="rs-show-chip rs-show-chip--flagship"><i class="fa-solid fa-star me-1" aria-hidden="true"></i>{{ $feature }}</span>
                @endforeach
            </div>
        @endif
    </div>
@endif

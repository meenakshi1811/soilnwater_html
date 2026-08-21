@if($post->isCreativeCornerPost())
    @php
        $railLayout = $railLayout ?? false;
        $postType = $post->creativeCornerPostTypeLabel();
        $category = $post->creativeCornerCategoryLabel();
        $availableForSale = (bool) data_get($post->meta, 'creative_corner_available_for_sale');
        $commissionOptions = (array) data_get($post->meta, 'creative_corner_commission_options', []);
        $capabilities = [
            ['label' => 'For sale', 'enabled' => $availableForSale, 'icon' => 'fa-store'],
            ['label' => 'Competition', 'enabled' => (bool) data_get($post->meta, 'creative_corner_submit_to_competition'), 'icon' => 'fa-trophy'],
            ['label' => 'Commissions', 'enabled' => $commissionOptions !== [], 'icon' => 'fa-handshake'],
            ['label' => 'Collaboration', 'enabled' => count((array) data_get($post->meta, 'creative_corner_collaboration_roles', [])) > 0, 'icon' => 'fa-people-group'],
            ['label' => 'Comments', 'enabled' => (bool) $post->allow_comments, 'icon' => 'fa-comments'],
            ['label' => 'Poll', 'enabled' => (bool) $post->allowsPoll(), 'icon' => 'fa-square-poll-vertical'],
            ['label' => 'Share', 'enabled' => (bool) $post->allow_sharing, 'icon' => 'fa-share-nodes'],
        ];
    @endphp

    @if($railLayout)
        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail community-detail-card--creative-intro">
            <div class="cc-show-overview cc-show-overview--rail mb-0">
                <div class="cc-show-overview__kicker">Creative Corner · SoilnWater community</div>
                <p class="cc-show-overview__tagline mb-0">Original creative work shared with the SoilnWater community — art, design, craft, music, and innovation.</p>
            </div>
        </div>

        <div class="community-news-rail__card community-news-rail__card--detail community-detail-card community-detail-card--rail">
            <div class="community-detail-card__head">
                <span class="community-detail-card__icon" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                <div>
                    <h4 class="community-detail-card__title">Post capabilities</h4>
                </div>
            </div>
            <div class="cc-capability-grid cc-capability-grid--rail">
                @foreach($capabilities as $capability)
                    <span class="cc-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                        <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                        {{ $capability['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    @else
        <div class="cc-show-overview">
            <div class="cc-show-overview__kicker">Creative Corner · SoilnWater community</div>
            <p class="cc-show-overview__tagline mb-0">Original creative work shared with the SoilnWater community — art, design, craft, music, and innovation.</p>
            <div class="cc-show-overview__chips mt-3">
                @if(filled($postType))
                    <span class="cc-show-chip cc-show-chip--highlight">{{ $postType }}</span>
                @endif
                @if(filled($category))
                    <span class="cc-show-chip">{{ $category }}</span>
                @endif
                @if(filled(data_get($post->meta, 'creative_corner_creation_type')))
                    <span class="cc-show-chip">{{ data_get($post->meta, 'creative_corner_creation_type') }}</span>
                @endif
                @if(filled(data_get($post->meta, 'creative_corner_difficulty_level')))
                    <span class="cc-show-chip">{{ data_get($post->meta, 'creative_corner_difficulty_level') }}</span>
                @endif
            </div>
        </div>

        <div class="cc-section-panel">
            <div class="cc-section-panel__label">Post capabilities</div>
            <div class="cc-capability-grid">
                @foreach($capabilities as $capability)
                    <span class="cc-capability-pill {{ $capability['enabled'] ? '' : 'is-disabled' }}">
                        <i class="fa-solid {{ $capability['icon'] }}" aria-hidden="true"></i>
                        {{ $capability['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
@endif

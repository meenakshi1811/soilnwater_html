@php
    $homepageDealBlocks = $homepageDealBlocks ?? [];
@endphp

@if(count($homepageDealBlocks) > 0)
    <section class="homepage-deal-blocks-section" aria-label="Deals and offers">
        <div class="homepage-deal-blocks-section__inner">
            @foreach($homepageDealBlocks as $block)
                <article
                    class="homepage-deal-block homepage-deal-block--tiles-{{ $block['tile_count'] }}"
                    data-deal-block="{{ $block['key'] }}"
                >
                    <header class="homepage-deal-block__head">
                        <h2 class="homepage-deal-block__title">{!! $block['title_html'] !!}</h2>
                        <a class="homepage-deal-block__view-all" href="{{ $block['view_all_url'] }}">See all deals</a>
                    </header>
                    <div class="homepage-deal-block__grid" role="list">
                        @foreach($block['tiles'] as $tile)
                            <a href="{{ $tile['url'] }}" class="homepage-deal-block__tile" role="listitem">
                                <div class="homepage-deal-block__media">
                                    <img
                                        src="{{ $tile['image'] }}"
                                        alt="{{ $tile['alt'] }}"
                                        loading="lazy"
                                        decoding="async"
                                        width="320"
                                        height="320"
                                    >
                                </div>
                                <span class="homepage-deal-block__badge">{{ $tile['badge'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif

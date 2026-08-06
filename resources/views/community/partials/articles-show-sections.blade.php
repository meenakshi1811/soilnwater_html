@if($post->content_type === 'articles')
    @php
        $scoreBadges = $post->articleScoreBadges();
    @endphp

    @if($scoreBadges !== [] || $post->adminPromotionLabels() !== [])
        <div class="community-article-score-row">
            @foreach($scoreBadges as $badge)
                <span class="badge bg-light text-dark community-score-badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            @endforeach
            @foreach($post->adminPromotionLabels() as $promotionLabel)
                <span class="badge bg-warning text-dark">{{ $promotionLabel }}</span>
            @endforeach
        </div>
    @endif

    @if($post->hasVideo())
        <div class="community-post-video mb-3">
            @if($post->youtubeEmbedUrl())
                <div class="ratio ratio-16x9 rounded overflow-hidden">
                    <iframe
                        src="{{ $post->youtubeEmbedUrl() }}"
                        title="Video for {{ $post->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                    ></iframe>
                </div>
            @elseif($post->videoFileUrl())
                <video controls class="w-100 rounded" preload="metadata">
                    <source src="{{ $post->videoFileUrl() }}">
                    Your browser does not support embedded video playback.
                </video>
            @endif
        </div>
    @endif

    @php
        $editorLanguage = data_get($post->meta, 'editor_language', 'en');
        $editorHtmlLang = \App\Support\CommunityContentTaxonomy::editorLanguageHtmlLang($editorLanguage);
    @endphp
    <div
        class="community-post-body community-post-body--article community-post-body--scalable"
        data-community-body-protected
        lang="{{ $editorHtmlLang }}"
        @if($editorLanguage === 'ur') dir="rtl" @endif
    >{!! $post->body !!}</div>
@endif

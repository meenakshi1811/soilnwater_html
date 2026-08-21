@php
    $placement = $placement ?? 'full';
@endphp

@if(in_array($placement, ['full', 'before'], true))
    @if($post->isAgriculturePost())
        @include('community.partials.agriculture-show-sections', ['post' => $post])
    @endif

    @if($post->isEnvironmentPost())
        @include('community.partials.environment-show-sections', ['post' => $post])
    @endif

    @if($post->isAwarenessPost())
        @include('community.partials.awareness-show-sections', [
            'post' => $post,
            'awarenessEngagement' => $awarenessEngagement ?? null,
            'awarenessPledgeCounts' => $awarenessPledgeCounts ?? [],
        ])
    @endif

    @if($post->isBusinessPost())
        @include('community.partials.business-show-sections', [
            'post' => $post,
            'businessEngagement' => $businessEngagement ?? null,
            'moveCareerBusinessExtrasToRail' => $moveCareerBusinessExtrasToRail ?? false,
        ])
    @endif

    @if($post->isReligionSpiritualityPost())
        @include('community.partials.religion-spirituality-show-sections', [
            'post' => $post,
            'moveCultureSpiritualityExtrasToRail' => $moveCultureSpiritualityExtrasToRail ?? false,
        ])
    @endif

    @if($post->isAstroConsultancyPost())
        @include('community.partials.astro-consultancy-show-sections', [
            'post' => $post,
            'moveCultureSpiritualityExtrasToRail' => $moveCultureSpiritualityExtrasToRail ?? false,
        ])
    @endif

    @if($post->isLocalVoicesPost())
        @include('community.partials.local-voices-show-sections', ['post' => $post])
    @endif

    @if($post->isCommunityIssuesPost())
        @include('community.partials.community-issues-show-sections', [
            'post' => $post,
            'reportEngagement' => $reportEngagement ?? null,
        ])
    @endif

    @if($post->isCreativeCornerPost())
        @include('community.partials.creative-corner-show-sections', ['post' => $post])
    @endif

    @if($post->isCompetitionsPost())
        @include('community.partials.competitions-show-sections', ['post' => $post])
    @endif
@endif

@if(in_array($placement, ['full', 'after'], true))
    @if($post->isAgriculturePost())
        @include('community.partials.agriculture-community-actions', ['post' => $post])
    @endif

    @if($post->isEnvironmentPost())
        @include('community.partials.environment-community-actions', [
            'post' => $post,
            'environmentEngagement' => $environmentEngagement ?? null,
        ])
    @endif

    @if($post->isAstroConsultancyPost())
        @include('community.partials.astro-consultancy-community-actions', [
            'post' => $post,
            'astroConsultancyEngagement' => $astroConsultancyEngagement ?? null,
        ])
    @endif

    @if($post->isLocalVoicesPost())
        @include('community.partials.local-voices-community-actions', [
            'post' => $post,
            'localVoiceEngagement' => $localVoiceEngagement ?? null,
        ])
    @endif

    @if($post->isCommunityIssuesPost())
        @include('community.partials.community-issues-community-actions', [
            'post' => $post,
            'reportEngagement' => $reportEngagement ?? null,
        ])
    @endif

    @if($post->isAwarenessPost())
        @include('community.partials.awareness-additional-details', ['post' => $post])
    @endif

    @if($post->isBusinessPost() && ! ($moveCareerBusinessExtrasToRail ?? false))
        @include('community.partials.business-meta-details', ['post' => $post])
    @endif
@endif

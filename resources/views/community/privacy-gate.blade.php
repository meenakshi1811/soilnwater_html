@extends('frontend.layouts.app')

@section('meta_title', $post->title.' · Privacy protected')
@section('meta_robots', 'noindex, nofollow')

@section('content')
@php
    $isWomensWorld = $post->isWomensWorldPost();
    $isChildrensCorner = $post->isChildrensCornerPost();
    $privacyLabel = $isWomensWorld
        ? $post->womensWorldVisibilityLabel()
        : $post->childrensCornerPrivacyLabel();
    $requiresPrivateLink = $isWomensWorld && $post->requiresWomensWorldPrivateLink();
    $requiresLogin = ! $requiresPrivateLink && $post->requiresAuthenticationForCommunityView();
@endphp
<div class="about-page">
    <section class="about-banner text-center">
        <div class="container">
            <span class="badge bg-warning text-dark mb-3">{{ $privacyLabel }}</span>
            <h1 class="h2">{{ $post->title }}</h1>
            <p class="lead mb-0">
                @if($requiresPrivateLink)
                    This Women's World post is only available through its private link.
                @elseif($isWomensWorld)
                    This Women's World post has restricted visibility.
                @else
                    This Children's Corner post has restricted visibility.
                @endif
            </p>
        </div>
    </section>

    <div class="about-inner">
        <section class="sec">
            <div class="about-box mx-auto" style="max-width:720px;">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-shield-halved fa-3x text-success mb-3" aria-hidden="true"></i>
                    @if($requiresPrivateLink)
                        <h2 class="h4 mb-2">Private link required</h2>
                        <p class="text-muted mb-0">Ask the author for the full private link to open this post.</p>
                    @else
                        <h2 class="h4 mb-2">Sign in to continue</h2>
                        @if($isChildrensCorner && $post->childrensCornerPrivacySetting() === 'school_community')
                            <p class="text-muted mb-0">
                                This submission is shared with the
                                @if(filled(data_get($post->meta, 'child_school_name')))
                                    <strong>{{ data_get($post->meta, 'child_school_name') }}</strong>
                                @else
                                    school
                                @endif
                                community and is available to registered SoilnWater members.
                            </p>
                        @elseif($isWomensWorld && $post->womensWorldVisibilitySetting() === 'women_community_only')
                            <p class="text-muted mb-0">This post is available to registered members of the Women's Community.</p>
                        @else
                            <p class="text-muted mb-0">This post is available to registered SoilnWater members only.</p>
                        @endif
                    @endif
                </div>

                @if(! $requiresPrivateLink)
                    <ul class="list-unstyled small text-muted mb-4">
                        @if($isWomensWorld)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Privacy settings protect sensitive Women's World stories.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Anonymous posting is available when authors choose it.</li>
                        @else
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Child safety and privacy settings are applied to this post.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Personal contact details are never shown on public pages.</li>
                        @endif
                        <li class="mb-0"><i class="fa-solid fa-check text-success me-2"></i>Login is required before you can read or participate.</li>
                    </ul>
                @endif

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    @if($requiresLogin)
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-success">Login to view</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-success">Create an account</a>
                    @endif
                    <a href="{{ route('community.index', ['type' => $isWomensWorld ? 'womens-world' : 'childrens-corner']) }}" class="btn btn-outline-secondary">
                        Back to {{ $isWomensWorld ? "Women's World" : "Children's Corner" }}
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

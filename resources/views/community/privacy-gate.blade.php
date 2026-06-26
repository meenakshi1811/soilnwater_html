@extends('frontend.layouts.app')

@section('meta_title', $post->title.' · Privacy protected')
@section('meta_robots', 'noindex, nofollow')

@section('content')
@php
    $isWomensWorld = $post->isWomensWorldPost();
    $isSeniorCitizensForum = $post->isSeniorCitizensForumPost();
    $isStudentCorner = $post->isStudentCornerPost();
    $isYouthCorner = $post->isYouthCornerPost();
    $isChildrensCorner = $post->isChildrensCornerPost();
    $isMyArea = $post->isMyAreaPost();
    $isLocalVoices = $post->isLocalVoicesPost();
    $privacyLabel = $isMyArea
        ? $post->myAreaVisibilityLabel()
        : ($isLocalVoices
            ? $post->localVoiceVisibilityLabel()
            : ($isWomensWorld
                ? $post->womensWorldVisibilityLabel()
                : ($isSeniorCitizensForum
                    ? $post->seniorCitizensForumVisibilityLabel()
                    : ($isStudentCorner
                        ? $post->studentCornerVisibilityLabel()
                        : ($isYouthCorner
                            ? $post->youthCornerVisibilityLabel()
                            : $post->childrensCornerPrivacyLabel())))));
    $requiresPrivateLink = ($isWomensWorld && $post->requiresWomensWorldPrivateLink())
        || ($isSeniorCitizensForum && $post->requiresSeniorCitizensForumPrivateLink())
        || ($isStudentCorner && $post->requiresStudentCornerPrivateLink())
        || ($isYouthCorner && $post->requiresYouthCornerPrivateLink())
        || ($isMyArea && $post->requiresMyAreaPrivateLink())
        || ($isLocalVoices && $post->requiresLocalVoicePrivateLink());
    $requiresLogin = ! $requiresPrivateLink && $post->requiresAuthenticationForCommunityView();
    $backType = match (true) {
        $isMyArea => 'my-area',
        $isLocalVoices => 'local-voices',
        $isWomensWorld => 'womens-world',
        $isSeniorCitizensForum => 'senior-citizens-forum',
        $isStudentCorner => 'student-corner',
        $isYouthCorner => 'youth-corner',
        default => 'childrens-corner',
    };
    $backLabel = match (true) {
        $isMyArea => 'My Area',
        $isLocalVoices => 'Local Voices',
        $isWomensWorld => "Women's World",
        $isSeniorCitizensForum => 'Senior Citizens Forum',
        $isStudentCorner => 'Student Corner',
        $isYouthCorner => 'Youth Corner',
        default => "Children's Corner",
    };
    $backRoute = $isMyArea ? route('community.my-area.index') : route('community.index', ['type' => $backType]);
@endphp
<div class="about-page">
    <section class="about-banner text-center">
        <div class="container">
            <span class="badge bg-warning text-dark mb-3">{{ $privacyLabel }}</span>
            <h1 class="h2">{{ $post->title }}</h1>
            <p class="lead mb-0">
                @if($requiresPrivateLink)
                    @if($isMyArea)
                        This My Area post is only available through its private link.
                    @elseif($isLocalVoices)
                        This Local Voices post is only available through its private link.
                    @elseif($isSeniorCitizensForum)
                        This Senior Citizens Forum post is only available through its private link.
                    @elseif($isStudentCorner)
                        This Student Corner post is only available through its private link.
                    @elseif($isYouthCorner)
                        This Youth Corner post is only available through its private link.
                    @else
                        This Women's World post is only available through its private link.
                    @endif
                @elseif($isMyArea)
                    This My Area post has restricted visibility for your local community.
                @elseif($isLocalVoices)
                    This Local Voices post has restricted visibility.
                @elseif($isSeniorCitizensForum)
                    This Senior Citizens Forum post has restricted visibility.
                @elseif($isStudentCorner)
                    This Student Corner post has restricted visibility.
                @elseif($isYouthCorner)
                    This Youth Corner post has restricted visibility.
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
                        @if($isMyArea && $post->myAreaVisibilitySetting() === 'local_community')
                            <p class="text-muted mb-0">This post is shared with your local community and is available to registered SoilnWater members.</p>
                        @elseif($isChildrensCorner && $post->childrensCornerPrivacySetting() === 'school_community')
                            <p class="text-muted mb-0">
                                This submission is shared with the
                                @if(filled(data_get($post->meta, 'child_school_name')))
                                    <strong>{{ data_get($post->meta, 'child_school_name') }}</strong>
                                @else
                                    school
                                @endif
                                community and is available to registered SoilnWater members.
                            </p>
                        @elseif($isSeniorCitizensForum && $post->seniorCitizensForumVisibilitySetting() === 'senior_citizens_community')
                            <p class="text-muted mb-0">This post is available to registered members of the Senior Citizens Community.</p>
                        @elseif($isWomensWorld && $post->womensWorldVisibilitySetting() === 'women_community_only')
                            <p class="text-muted mb-0">This post is available to registered members of the Women's Community.</p>
                        @elseif($isStudentCorner && $post->studentCornerVisibilitySetting() === 'students_only')
                            <p class="text-muted mb-0">This post is available to registered student members only.</p>
                        @elseif($isYouthCorner && $post->youthCornerVisibilitySetting() === 'youth_community')
                            <p class="text-muted mb-0">This post is available to registered members of the Youth Community.</p>
                        @else
                            <p class="text-muted mb-0">This post is available to registered SoilnWater members only.</p>
                        @endif
                    @endif
                </div>

                @if(! $requiresPrivateLink)
                    <ul class="list-unstyled small text-muted mb-4">
                        @if($isMyArea)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>My Area privacy settings protect sensitive local posts.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Authors can publish anonymously or with a pen name.</li>
                        @elseif($isLocalVoices)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Local Voices privacy settings protect sensitive community stories.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Authors can publish anonymously or with a pen name.</li>
                        @elseif($isWomensWorld)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Privacy settings protect sensitive Women's World stories.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Anonymous posting is available when authors choose it.</li>
                        @elseif($isStudentCorner)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Privacy settings protect student submissions and personal details.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Authors can publish with a pen name or first name only.</li>
                        @elseif($isYouthCorner)
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Privacy settings protect youth submissions and personal details.</li>
                            <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Authors can publish with a pen name or first name only.</li>
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
                    <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                        Back to {{ $backLabel }}
                    </a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

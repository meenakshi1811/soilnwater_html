@extends('frontend.layouts.app')

@section('meta_title', $post->title.' · Privacy protected')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="about-page">
    <section class="about-banner text-center">
        <div class="container">
            <span class="badge bg-warning text-dark mb-3">{{ $post->childrensCornerPrivacyLabel() }}</span>
            <h1 class="h2">{{ $post->title }}</h1>
            <p class="lead mb-0">This Children's Corner post has restricted visibility.</p>
        </div>
    </section>

    <div class="about-inner">
        <section class="sec">
            <div class="about-box mx-auto" style="max-width:720px;">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-shield-halved fa-3x text-success mb-3" aria-hidden="true"></i>
                    <h2 class="h4 mb-2">Sign in to continue</h2>
                    @if($post->childrensCornerPrivacySetting() === 'school_community')
                        <p class="text-muted mb-0">
                            This submission is shared with the
                            @if(filled(data_get($post->meta, 'child_school_name')))
                                <strong>{{ data_get($post->meta, 'child_school_name') }}</strong>
                            @else
                                school
                            @endif
                            community and is available to registered SoilnWater members.
                        </p>
                    @else
                        <p class="text-muted mb-0">This post is available to registered SoilnWater members only.</p>
                    @endif
                </div>

                <ul class="list-unstyled small text-muted mb-4">
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Child safety and privacy settings are applied to this post.</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Personal contact details are never shown on public pages.</li>
                    <li class="mb-0"><i class="fa-solid fa-check text-success me-2"></i>Login is required before you can read or participate.</li>
                </ul>

                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-success">Login to view</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-success">Create an account</a>
                    <a href="{{ route('community.index', ['type' => 'childrens-corner']) }}" class="btn btn-outline-secondary">Back to Children's Corner</a>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

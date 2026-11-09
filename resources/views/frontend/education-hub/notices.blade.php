@extends('frontend.layouts.app')

@section('meta_title', 'Announcements & Notices | Education Hub | SoilnWater')
@section('meta_description', 'The latest notices and announcements from schools, colleges, institutes and teachers on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="eh-page eh-notices-page">
    <div class="eh-container">
        <nav class="eh-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('education.hub') }}"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Education Hub</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Announcements &amp; Notices</span>
        </nav>

        <header class="eh-notices-head">
            <span class="eh-icon eh-icon--blue eh-notices-head__icon"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span>
            <div>
                <h1 class="eh-notices-head__title">Announcements &amp; Notices</h1>
                <p class="eh-notices-head__text">Every current notice from the schools, colleges, institutes and teachers on SoilnWater.</p>
            </div>
        </header>

        {{-- Who posted them --}}
        <nav class="eh-notice-tabs" aria-label="Notices by who posted them">
            <a href="{{ route('education.notices') }}" class="eh-notice-tab {{ $kind === null ? 'is-active' : '' }}" @if($kind === null) aria-current="page" @endif>
                All <span>{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($kinds as $key => [$label, $icon])
                <a href="{{ route('education.notices', ['kind' => $key]) }}" class="eh-notice-tab {{ $kind === $key ? 'is-active' : '' }}" @if($kind === $key) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if($notices->isNotEmpty())
            <div class="eh-notice-grid">
                @foreach($notices as $notice)
                    @php [, $kindIcon, $kindTone, $kindOne] = $kinds[$notice['kind']]; @endphp
                    <a href="{{ $notice['url'] }}" class="eh-notice-card">
                        <span class="eh-notice-card__media eh-notice-card__media--{{ $kindTone }}">
                            @if($notice['image'])
                                <img src="{{ \App\Support\ImageThumbnail::url($notice['image']) }}" alt="" loading="lazy" decoding="async">
                            @else
                                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                            @endif
                            <span class="eh-notice-card__kind"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i> {{ $kindOne }}</span>
                        </span>
                        <span class="eh-notice-card__body">
                            <strong class="eh-notice-card__title">{{ $notice['title'] }}</strong>
                            @if(filled($notice['excerpt']))
                                <span class="eh-notice-card__text">{{ $notice['excerpt'] }}</span>
                            @endif
                            <span class="eh-notice-card__source"><i class="fa-solid {{ $kindIcon }}" aria-hidden="true"></i> {{ $notice['source'] }}</span>
                            <span class="eh-notice-card__dates">
                                @if($notice['date'])
                                    <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $notice['date']->format('j M Y') }}</span>
                                @endif
                                @if($notice['expires'])
                                    <span>Valid till {{ $notice['expires']->format('j M Y') }}</span>
                                @endif
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>

            @if($notices->hasPages())
                <div class="eh-notice-pages">
                    {{ $notices->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <p class="eh-empty eh-notices-empty">
                {{ $kind ? 'No current notices from '.strtolower($kinds[$kind][0]).'.' : 'No announcements right now.' }}
                <a href="{{ route('education.hub') }}">Back to the Education Hub</a>
            </p>
        @endif
    </div>
</div>
@endsection

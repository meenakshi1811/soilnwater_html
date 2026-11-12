@extends('frontend.layouts.app')

@section('meta_title', 'Scholarships | Education Hub | SoilnWater')
@section('meta_description', 'Scholarships, fellowships and financial support offered by schools, colleges and institutes on SoilnWater.')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<div class="eh-page eh-notices-page eh-listpage">
    <div class="eh-container">
        <nav class="eh-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('education.hub') }}"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> Education Hub</a>
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Scholarships</span>
        </nav>

        @include('frontend.education-hub.partials.page-hero', [
            'tone' => 'green',
            'art' => 'scholarships',
            'title' => 'Scholarships',
            'text' => 'Scholarships, fellowships and financial support from schools, colleges and institutes. See who can apply, what each one gives and how to apply.',
            'action' => route('education.scholarships'),
            'placeholder' => 'Search scholarships, classes, institutions or places…',
            'stats' => array_values(array_filter([
                $totals['scholarships'] ? ['fa-award', $totals['scholarships'], $totals['scholarships'] === 1 ? 'open scholarship' : 'open scholarships'] : null,
                $totals['providers'] ? ['fa-building-columns', $totals['providers'], $totals['providers'] === 1 ? 'institution' : 'institutions'] : null,
            ])),
        ])

        {{-- Who offers them --}}
        <nav class="eh-notice-tabs eh-listtabs" aria-label="Scholarships by who offers them">
            <a href="{{ route('education.scholarships', array_filter(['q' => $search])) }}" class="eh-notice-tab {{ $kind === null ? 'is-active' : '' }}" @if($kind === null) aria-current="page" @endif>
                All <span>{{ $counts['all'] ?? 0 }}</span>
            </a>
            @foreach($kinds as $key => [$label, $icon])
                <a href="{{ route('education.scholarships', array_filter(['kind' => $key, 'q' => $search])) }}" class="eh-notice-tab {{ $kind === $key ? 'is-active' : '' }}" @if($kind === $key) aria-current="page" @endif>
                    <i class="fa-solid {{ $icon }}" aria-hidden="true"></i> {{ $label }} <span>{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </nav>

        @if($search !== '')
            <p class="eh-listnote">
                {{ number_format($scholarships->total()) }} {{ $scholarships->total() === 1 ? 'scholarship' : 'scholarships' }} for “{{ $search }}”
                <a href="{{ route('education.scholarships', array_filter(['kind' => $kind])) }}">Clear search <i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
            </p>
        @endif

        @if($scholarships->isNotEmpty())
            <div class="eh-scholarships">
                @foreach($scholarships as $item)
                    @include('frontend.education-hub.partials.scholarship-card', ['item' => $item])
                @endforeach
            </div>

            @if($scholarships->hasPages())
                <div class="eh-notice-pages">
                    {{ $scholarships->onEachSide(1)->links() }}
                </div>
            @endif
        @else
            <div class="eh-listempty">
                <img src="{{ asset('assets/images/education-hub/art-scholarships.webp') }}" alt="" width="70" height="54">
                <strong>
                    @if($search !== '')
                        No scholarships match “{{ $search }}”.
                    @elseif($kind)
                        No open scholarships from {{ strtolower($kinds[$kind][0]) }} right now.
                    @else
                        No open scholarships right now.
                    @endif
                </strong>
                <span>Schools, colleges and institutes add their scholarships from their dashboard; they will appear here.</span>
                <a href="{{ $search !== '' || $kind ? route('education.scholarships') : route('education.hub') }}" class="eh-listempty__btn">
                    {{ $search !== '' || $kind ? 'See all scholarships' : 'Back to the Education Hub' }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

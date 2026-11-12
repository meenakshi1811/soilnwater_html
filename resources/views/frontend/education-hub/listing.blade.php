@extends('frontend.layouts.app')

{{-- An Education Hub listing page (courses, jobs, scholarships) in the layout of the other listing
     pages: filters on the left, the header, sort and cards on the right. Search, filters and sort
     reload the cards without leaving the page; more load on scrolling (education-listing.js).
     $page: the page's texts and settings; $filters: its own filters; $cardsView: its cards. --}}

@section('meta_title', $page['meta_title'])
@section('meta_description', $page['meta_description'])

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/vendors-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/institutes-page.css') }}?v={{ now()->timestamp }}">
  <link rel="stylesheet" href="{{ asset('assets/css/education-hub.css') }}?v={{ now()->timestamp }}">
@endpush

@php $key = $page['key']; @endphp

@section('content')
<div
  class="vendors-page institutes-page hub-listing hub-listing--{{ $key }} hub-listing--{{ $page['tone'] }}"
  id="{{ $key }}PageRoot"
  data-listing-root
  data-index-url="{{ $page['url'] }}"
  data-default-sort="{{ array_key_first($sorts) }}"
  data-noun-one="{{ $page['noun'][0] }}"
  data-noun-many="{{ $page['noun'][1] }}"
>
  <div class="vendors-page__layout">
    <aside class="vendors-sidebar">
      <div class="vendors-sidebar__head">
        <div>
          <h2>Filters</h2>
          <p class="vendors-sidebar__lead">{{ $page['lead'] }}</p>
        </div>
        <button type="button" class="vendors-sidebar__reset" id="{{ $key }}ResetFilters" data-listing-reset>Reset All</button>
      </div>

      <div class="vendors-filter-group">
        <label for="{{ $key }}FilterSearch">Search</label>
        <div class="vendors-filter-search-wrap">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input id="{{ $key }}FilterSearch" class="form-control" type="search" placeholder="{{ $page['placeholder'] }}" value="{{ $search }}" autocomplete="off" data-filter="q">
        </div>
      </div>

      @foreach($filters as $name => $filter)
        <div class="vendors-filter-group">
          <label for="{{ $key }}Filter{{ ucfirst($name) }}">{{ $filter['label'] }}</label>
          <select id="{{ $key }}Filter{{ ucfirst($name) }}" class="form-select" data-filter="{{ $name }}">
            <option value="">{{ $filter['all'] }}</option>
            @foreach($filter['options'] as $value => $label)
              <option value="{{ $value }}" @selected($chosen[$name] === (string) $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      @endforeach

      @if($cities->isNotEmpty())
        <div class="vendors-filter-group">
          <label for="{{ $key }}FilterCity">City</label>
          <select id="{{ $key }}FilterCity" class="form-select" data-filter="city">
            <option value="">All cities</option>
            @foreach($cities as $name)
              <option value="{{ $name }}" @selected($city === $name)>{{ $name }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <button type="button" class="btn btn-primary w-100" id="{{ $key }}ApplyFilters" data-listing-apply>Apply filters</button>
    </aside>

    <main class="vendors-main">
      <section class="vendors-hero vendors-hero--compact">
        <div class="vendors-hero__intro">
          <a href="{{ route('education.hub') }}" class="vendors-hero__eyebrow hub-listing__eyebrow">
            <span class="vendors-hero__eyebrow-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
            Education Hub
          </a>
          <h1>{{ $page['title'] }}</h1>
          <p>{{ $page['text'] }}</p>
          @if(($page['stats'][0][1] ?? 0) > 0)
            <ul class="hub-listing__stats">
              @foreach($page['stats'] as [$icon, $number, $label])
                <li><i class="fa-solid {{ $icon }}" aria-hidden="true"></i> <strong>{{ number_format($number) }}</strong> {{ $label }}</li>
              @endforeach
            </ul>
          @endif
        </div>
        <img src="{{ asset('assets/images/education-hub/art-'.$page['art'].'.webp') }}" alt="" class="hub-listing__art" width="92" height="100">
      </section>

      <section class="vendors-section" id="{{ $key }}AllSection">
        <div class="vendors-section__head">
          <h2><i class="fa-solid fa-border-all" aria-hidden="true"></i> {{ $page['section'] }}</h2>
        </div>

        <div class="vendors-all__toolbar institutes-toolbar">
          <p class="institutes-toolbar__summary mb-0" data-listing-summary>
            @if($items->total())
              Showing 1 to {{ $items->lastItem() }} of {{ number_format($items->total()) }} {{ $items->total() === 1 ? $page['noun'][0] : $page['noun'][1] }}
            @else
              No {{ $page['noun'][1] }} to show
            @endif
          </p>
          <div class="vendors-all__controls">
            <select class="form-select" aria-label="Sort {{ $page['noun'][1] }}" data-filter="sort">
              @foreach($sorts as $value => $label)
                <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="vendors-grid institutes-grid hub-listing__grid" data-listing-grid data-next-page-url="{{ $items->nextPageUrl() }}" aria-live="polite">
          @include($cardsView, ['items' => $items, 'page' => $page])
        </div>

        <p class="hub-listing__loading d-none" role="status" data-listing-loading>
          <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Loading {{ $page['noun'][1] }}…
        </p>
        <div class="vendors-scroll-sentinel" aria-hidden="true" data-listing-sentinel></div>
      </section>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/education-listing.js') }}?v={{ now()->timestamp }}"></script>
@endpush

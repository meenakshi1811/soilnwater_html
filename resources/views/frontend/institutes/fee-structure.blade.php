@extends('frontend.layouts.app')

@php
  $feeTitle = $fee['title'] !== '' ? $fee['title'] : 'Fee Structure';
@endphp

@section('meta_title', $feeTitle.($fee['session'] !== '' ? ' '.$fee['session'] : '').' · '.$institute->displayName().' | SoilnWater')
@section('meta_description', \Illuminate\Support\Str::limit($feeTitle.($fee['session'] !== '' ? ' '.$fee['session'] : '').' of '.$institute->displayName().': fees by class, admission charges and payment details.', 160))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/school-profile-page.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
@php
  use App\Support\InstituteFeeStructure;

  $listingIndexRoute = $listingContext === 'schools' ? 'schools.index' : 'institutes.index';
  $listingLabel = $listingContext === 'schools' ? 'Schools' : 'Institutes';
  $profileUrl = $institute->publicUrl();
  $feeFileUrl = $fee['file'] ? asset($fee['file']) : null;
  $feeFileIsPdf = $fee['file'] && str_ends_with(strtolower($fee['file']), '.pdf');
@endphp

<div class="sch-page sch-diary-page sch-fee-page" id="schoolFeePage">
  <div class="container-fluid sch-container">
    <nav class="sch-breadcrumb" aria-label="Breadcrumb">
      <a href="{{ route('frontend.index') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Home</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ route($listingIndexRoute) }}">{{ $listingLabel }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <a href="{{ $profileUrl }}">{{ $institute->displayName() }}</a>
      <span class="sch-breadcrumb__sep" aria-hidden="true">›</span>
      <span class="sch-breadcrumb__current" aria-current="page">Fee structure</span>
    </nav>

    <div class="sch-grid {{ $navItems === [] ? 'sch-grid--no-nav' : '' }}">
      @if($navItems !== [])
        @include('frontend.institutes.partials.school-profile.nav', [
          'navItems' => $navItems,
          'institute' => $institute,
          'activeNavId' => $activeNavId,
          'sectionBaseUrl' => $profileUrl,
        ])
      @endif

      <div class="sch-body">
        <header class="sch-card sch-diary-hero">
          <div class="sch-diary-hero__main">
            @if($institute->logoUrl())
              <img src="{{ $institute->logoUrl() }}" alt="" class="sch-diary-hero__logo" width="64" height="64">
            @endif
            <div>
              <p class="sch-diary-hero__kicker">{{ $institute->displayName() }}</p>
              <h1 class="sch-diary-hero__title">{{ $feeTitle }}</h1>
              @if($fee['session'] !== '' || $fee['effective_from'])
                <p class="sch-fee-hero__meta mb-0">
                  @if($fee['session'] !== '')
                    <span><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Session {{ $fee['session'] }}</span>
                  @endif
                  @if($fee['effective_from'])
                    <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Effective from {{ \Illuminate\Support\Carbon::parse($fee['effective_from'])->format('j F Y') }}</span>
                  @endif
                </p>
              @endif
            </div>
          </div>
          <div class="sch-diary-hero__actions">
            <a href="{{ $profileUrl }}" class="sch-btn sch-btn-outline">
              <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to profile
            </a>
            @if($feeFileUrl)
              <a href="{{ $feeFileUrl }}" class="sch-btn sch-btn-outline" target="_blank" rel="noopener" download>
                <i class="fa-solid fa-download" aria-hidden="true"></i> Download {{ $feeFileIsPdf ? 'PDF' : 'fee chart' }}
              </a>
            @endif
            <a href="{{ $profileUrl }}#sch-contact" class="sch-btn sch-btn-primary">
              <i class="fa-solid fa-comment-dots" aria-hidden="true"></i> Enquire
            </a>
          </div>
        </header>

        @if($fee['intro'] !== '')
          <div class="sch-card sch-fee-intro">{!! nl2br(e($fee['intro'])) !!}</div>
        @endif

        @foreach($fee['tables'] as $table)
          <section class="sch-card sch-section sch-fee-card">
            @if($table['title'] !== '')
              <h2 class="sch-section__title">{{ $table['title'] }}</h2>
            @endif
            <div class="sch-fee-table-wrap">
              <table class="sch-fee-table">
                <thead>
                  <tr>
                    <th scope="col">{{ $table['row_heading'] }}</th>
                    @foreach($table['columns'] as $column)
                      <th scope="col">{{ $column }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach($table['rows'] as $row)
                    <tr>
                      <th scope="row">{{ $row['label'] }}</th>
                      @foreach($row['values'] as $value)
                        <td>{{ $value !== '' ? $value : '—' }}</td>
                      @endforeach
                    </tr>
                  @endforeach
                </tbody>
                @if($table['show_total'])
                  <tfoot>
                    <tr>
                      <th scope="row">Total</th>
                      @foreach(InstituteFeeStructure::totals($table) as $total)
                        <td>{{ $total ?? '' }}</td>
                      @endforeach
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
            @if($table['note'] !== '')
              <p class="sch-fee-card__note mb-0">{!! nl2br(e($table['note'])) !!}</p>
            @endif
          </section>
        @endforeach

        @foreach($fee['sections'] as $section)
          <section class="sch-card sch-section sch-fee-card">
            @if($section['heading'] !== '')
              <h2 class="sch-section__title">{{ $section['heading'] }}</h2>
            @endif
            <div class="sch-fee-card__text">{!! nl2br(e($section['text'])) !!}</div>
          </section>
        @endforeach

        @if($feeFileUrl)
          <section class="sch-card sch-section sch-fee-card">
            <div class="sch-section__head">
              <h2 class="sch-section__title mb-0">Fee structure document</h2>
              <a href="{{ $feeFileUrl }}" class="sch-section__link" target="_blank" rel="noopener">
                Open {{ $feeFileIsPdf ? 'PDF' : 'image' }} <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
              </a>
            </div>
            @if($feeFileIsPdf)
              <iframe src="{{ $feeFileUrl }}#view=FitH" class="sch-fee-file sch-fee-file--pdf" title="{{ $feeTitle }} (PDF)" loading="lazy"></iframe>
            @else
              <a href="{{ $feeFileUrl }}" target="_blank" rel="noopener">
                <img src="{{ $feeFileUrl }}" alt="{{ $feeTitle }} of {{ $institute->displayName() }}" class="sch-fee-file" loading="lazy">
              </a>
            @endif
          </section>
        @endif

        <p class="sch-fee-footnote">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
          Fees as published by {{ $institute->displayName() }}. Please confirm the latest fees with the school before paying.
        </p>
      </div>
    </div>
  </div>
</div>
@endsection

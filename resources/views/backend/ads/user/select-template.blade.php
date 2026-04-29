@extends('backend.layouts.app')

@section('title', 'Choose Template')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Choose a Design Template</h2>
            <p class="mb-0 text-secondary">Size: <strong>{{ $size['name'] }}</strong> ({{ $size['w'] }}×{{ $size['h'] }})</p>
            @if(($size['admin_only'] ?? false) === true)
                <p class="mb-0 mt-1"><span class="badge text-bg-warning">Admin Placement</span> <span class="text-secondary">This ad request will be submitted to admin for homepage placement review.</span></p>
            @endif
        </div>
    </div>

    <div class="chart-card">
        @if($templates->count() === 0)
            <div class="alert alert-warning mb-0">
                No templates available for this size yet. Please try again later.
            </div>
        @else
            @php
                $previewWidth = max((int) ($size['w'] ?? 0), 1);
                $previewRatio = ((int) ($size['h'] ?? 0) > 0)
                    ? ((int) ($size['w'] ?? 0) / (int) ($size['h'] ?? 1))
                    : 1;
                $isUltraWidePreview = $previewRatio >= 5;
            @endphp
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <p class="mb-0 text-secondary small">
                    {{ $templates->count() }} template{{ $templates->count() > 1 ? 's' : '' }} available · showing scaled previews for {{ $size['w'] }}×{{ $size['h'] }}
                </p>
            </div>

            <div class="row g-3 ads-template-grid">
                @foreach($templates as $template)
                    <div class="col-12 {{ $isUltraWidePreview ? '' : 'col-lg-6' }}">
                        <a
                            href="{{ route('ads.create.customize', ['sizeType' => $sizeType, 'template' => $template->id]) }}"
                            class="ads-template-card ads-template-card-actual d-block text-decoration-none"
                            style="--ads-preview-natural-width: {{ $previewWidth }}px;"
                        >
                            @include('backend.ads.partials.template-mini-preview', ['template' => $template, 'size' => $size])
                            <div class="mt-2">
                                <div class="fw-semibold text-dark">{{ $template->name }} <span class="text-secondary small">({{ $size['w'] }}×{{ $size['h'] }})</span></div>
                                @if($template->description)
                                    <div class="text-secondary small text-truncate">{{ $template->description }}</div>
                                @endif
                                <div class="text-secondary small mt-1">Preview ratio: {{ $size['w'] }}×{{ $size['h'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
            <a href="{{ route('ads.create.size') }}" class="btn btn-light px-4">Back</a>
        </div>
    </div>
</div>
@endsection

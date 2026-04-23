@extends('backend.layouts.app')

@section('title', 'Choose Template')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Choose a Design Template</h2>
            <p class="mb-0 text-secondary">Size: <strong>{{ $size['name'] }}</strong> ({{ $size['w'] }}×{{ $size['h'] }})</p>
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
            @endphp
            <div class="row g-3">
                @foreach($templates as $template)
                    <div class="col-12">
                        <a
                            href="{{ route('ads.create.customize', ['sizeType' => $sizeType, 'template' => $template->id]) }}"
                            class="ads-template-card ads-template-card-actual d-block text-decoration-none"
                            style="--ads-preview-natural-width: {{ $previewWidth }}px;"
                        >
                            @include('backend.ads.partials.template-mini-preview', ['template' => $template, 'size' => $size])
                            <div class="mt-3">
                                <div class="fw-semibold text-dark">{{ $template->name }}</div>
                                @if($template->description)
                                    <div class="text-secondary small">{{ $template->description }}</div>
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

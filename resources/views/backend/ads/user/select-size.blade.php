@extends('backend.layouts.app')

@section('title', 'Select Ad Size')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Select Ad Size</h2>
            <p class="mb-0 text-secondary">Choose the size that best fits where you want to run your ad.</p>
        </div>
    </div>

    <div class="chart-card">
        <div class="row g-3">
            @foreach($sizes as $sizeType => $size)
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="{{ route('ads.create.template', ['sizeType' => $sizeType]) }}" class="ads-size-card d-block text-decoration-none">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="fw-semibold text-dark">{{ $size['name'] }}</div>
                            @if(($size['admin_only'] ?? false) === true)
                                <span class="badge text-bg-warning">Admin Placement</span>
                            @endif
                        </div>
                        <div class="ads-size-shape" style="aspect-ratio: {{ $size['ratio'] }};">
                            <div class="ads-size-shape-inner">
                                <span class="ads-size-dim">{{ $size['w'] }}×{{ $size['h'] }}</span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="text-secondary small">Aspect ratio {{ $size['ratio'] }}</div>
                            @if(($size['admin_only'] ?? false) === true)
                                <div class="text-secondary small mt-1">Use this size to post a homepage-placement ad request to admin.</div>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
            <a href="{{ route('ads.index') }}" class="btn btn-light px-4">Back</a>
        </div>
    </div>
</div>
@endsection

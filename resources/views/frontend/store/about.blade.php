@extends('frontend.store.layout')

@section('title', $vendor->publicDisplayName().' – About Us')

@section('store_content')
<section class="vendor-about-page-hero">
    <div class="container text-center">
        <p class="vendor-store-eyebrow mb-2">Independent Page</p>
        <h1 class="mb-2">About {{ $vendor->publicDisplayName() }}</h1>
        <p class="mb-0 text-muted">This page is separate from the store home and focuses only on your company details.</p>
    </div>
</section>

<section class="vendor-store-section">
    <div class="container">
        <div class="vendor-about-page-card">
            @if(trim(strip_tags((string) $vendor->description)) !== '')
                <div class="content-body">{!! $vendor->description !!}</div>
            @else
                <div class="alert alert-info mb-0">
                    About Us content is not added yet. Vendor can add it from <strong>Vendor Panel → Manage Website → About Us Page Content</strong>.
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

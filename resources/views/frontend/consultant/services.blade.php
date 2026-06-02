@extends('frontend.consultant.layout')

@section('title', $consultant->publicDisplayName().' – Consultation Services')

@section('consultant_content')
<section class="vendor-hero-text-section">
    <div class="container">
        <p class="consultant-services-heading__eyebrow mb-1">Consultation Services</p>
        <h1 class="mb-0">All consultation services</h1>
    </div>
</section>

@include('frontend.consultant.partials.services-section', ['showViewAllServicesButton' => false])
@endsection

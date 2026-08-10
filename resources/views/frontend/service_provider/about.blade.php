@extends('frontend.service_provider.layout')

@section('title', 'About '. $service_provider->publicDisplayName())

@section('service_provider_content')
@include('frontend.partials.marketplace-store-about', [
    'profile' => $service_provider,
    'homeRoute' => route('service_provider.show', $service_provider->slug),
    'heroSubtitle' => 'Learn about our service background, capabilities, and how we support our clients.',
])
@endsection

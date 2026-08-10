@extends('frontend.consultant.layout')

@section('title', 'About '. $consultant->publicDisplayName())

@section('consultant_content')
@include('frontend.partials.marketplace-store-about', [
    'profile' => $consultant,
    'homeRoute' => route('consultant.show', $consultant->slug),
    'heroSubtitle' => 'Learn about our consulting background, approach, and the services we provide.',
])
@endsection

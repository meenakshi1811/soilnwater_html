@extends('frontend.store.layout')

@section('title', 'About '. $vendor->publicDisplayName())

@section('store_content')
@include('frontend.partials.marketplace-store-about', [
    'profile' => $vendor,
    'homeRoute' => route('store.show', $vendor->slug),
])
@endsection

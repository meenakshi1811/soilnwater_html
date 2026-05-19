@extends('backend.layouts.app')
@section('title','View Product')
@section('content')<div class="admin-panel ems-page"><div class="chart-card p-3"><h3>{{ $product->name }}</h3><p>{{ $product->description }}</p><a class="btn btn-outline-secondary" href="{{ route('vendor.products.index') }}">Back</a></div></div>@endsection

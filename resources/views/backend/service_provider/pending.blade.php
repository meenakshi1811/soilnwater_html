@extends('backend.layouts.app')

@section('title', 'Approval Pending')

@section('content')
@php
    $isRejected = $service_provider?->status === 'rejected';
@endphp
<div class="admin-panel ems-page">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="chart-card text-center py-5">
        <i class="fa-solid {{ $isRejected ? 'fa-circle-xmark text-danger' : 'fa-hourglass-half text-warning' }} fa-3x mb-3"></i>
        <h2 class="admin-title">{{ $isRejected ? 'Service Provider application rejected' : 'Account pending approval' }}</h2>
        <p class="text-secondary col-lg-8 mx-auto">
            @if ($isRejected)
                Your service provider application was rejected by the admin team. Please contact support for the next steps.
            @else
                Thank you for applying as a service_provider on SoilNWater. Our team is reviewing your application.
                You will be able to access the service provider portal once an administrator approves your account.
            @endif
        </p>
        @if($service_provider)
            <p class="small text-muted">Company: <strong>{{ $service_provider->company_name }}</strong></p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
    </div>
</div>
@endsection

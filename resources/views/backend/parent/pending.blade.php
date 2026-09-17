@extends('backend.layouts.app')

@section('title', 'Approval Pending')

@section('content')
@php
    $isRejected = $parentProfile?->status === 'rejected';
    $displayName = auth()->user()?->full_name ?: auth()->user()?->name;
@endphp
<div class="admin-panel ems-page">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="chart-card text-center py-5">
        <i class="fa-solid {{ $isRejected ? 'fa-circle-xmark text-danger' : 'fa-hourglass-half text-warning' }} fa-3x mb-3"></i>
        <h2 class="admin-title">{{ $isRejected ? 'Parent profile application rejected' : 'Parent profile pending approval' }}</h2>
        <p class="text-secondary col-lg-8 mx-auto">
            @if ($isRejected)
                Your parent profile application was rejected by the admin team. Your account has been restored as a general user. Please contact support if you need more information.
            @else
                Thank you for requesting a parent/guardian profile on SoilNWater. Our team is reviewing your application.
                You will be able to access the parent dashboard once an administrator approves your profile.
            @endif
        </p>
        @if($displayName)
            <p class="small text-muted">Applicant: <strong>{{ $displayName }}</strong></p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
    </div>
</div>
@endsection

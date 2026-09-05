@extends('backend.layouts.app')

@section('title', 'Approval Pending')

@section('content')
@php
    $isRejected = $educator?->status === 'rejected';
@endphp
<div class="admin-panel ems-page">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="chart-card text-center py-5">
        <i class="fa-solid {{ $isRejected ? 'fa-circle-xmark text-danger' : 'fa-hourglass-half text-warning' }} fa-3x mb-3"></i>
        <h2 class="admin-title">{{ $isRejected ? ($educator?->roleLabel() ?? 'Educator').' application rejected' : 'Account pending approval' }}</h2>
        <p class="text-secondary col-lg-8 mx-auto">
            @if ($isRejected)
                Your {{ strtolower($educator?->roleLabel() ?? 'educator') }} application was rejected by the admin team. Please contact support for the next steps.
            @else
                Thank you for applying as a {{ strtolower($educator?->roleLabel() ?? 'educator') }} on SoilNWater. Our team is reviewing your application.
                You will be able to access the educator portal once an administrator approves your account.
            @endif
        </p>
        @if($educator)
            <p class="small text-muted">Name: <strong>{{ $educator->display_name }}</strong></p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
    </div>
</div>
@endsection

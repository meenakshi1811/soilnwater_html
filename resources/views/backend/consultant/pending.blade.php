@extends('backend.layouts.app')

@section('title', 'Approval Pending')

@section('content')
@php
    $isRejected = $consultant?->status === 'rejected';
@endphp
<div class="admin-panel ems-page">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="chart-card text-center py-5">
        <i class="fa-solid {{ $isRejected ? 'fa-circle-xmark text-danger' : 'fa-hourglass-half text-warning' }} fa-3x mb-3"></i>
        <h2 class="admin-title">{{ $isRejected ? 'Consultant application rejected' : 'Account pending approval' }}</h2>
        <p class="text-secondary col-lg-8 mx-auto">
            @if ($isRejected)
                Your consultant application was rejected by the admin team. Please contact support for the next steps.
            @else
                Thank you for applying as a consultant on SoilNWater. Our team is reviewing your application.
                You will be able to access the consultant portal once an administrator approves your account.
            @endif
        </p>
        @if($consultant)
            <p class="small text-muted">Company: <strong>{{ $consultant->company_name }}</strong></p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
    </div>
</div>
@endsection

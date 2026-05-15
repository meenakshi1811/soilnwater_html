@extends('backend.layouts.app')

@section('title', 'Approval Pending')

@section('content')
<div class="admin-panel ems-page">
    <div class="chart-card text-center py-5">
        <i class="fa-solid fa-hourglass-half fa-3x text-warning mb-3"></i>
        <h2 class="admin-title">Account pending approval</h2>
        <p class="text-secondary col-lg-8 mx-auto">
            Thank you for registering as a vendor on SoilNWater. Our team is reviewing your application.
            You will be able to access the vendor portal once an administrator approves your account.
        </p>
        @if($vendor)
            <p class="small text-muted">Company: <strong>{{ $vendor->company_name }}</strong></p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Logout</button>
        </form>
    </div>
</div>
@endsection



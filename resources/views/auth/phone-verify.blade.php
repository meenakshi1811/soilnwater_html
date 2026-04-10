@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="auth-page-wrap otp-page-wrap">
    <div class="container">
        <div class="otp-layout justify-content-center">
            <div class="card auth-form-card otp-form-card">
                <div class="card-body">
                    <h2 class="auth-title otp-title">Verify Mobile Number</h2>
                    <p class="auth-subtitle mb-1">Your Google email <strong>{{ $email }}</strong> is verified.</p>
                    <p class="auth-subtitle mb-3">Add your mobile number and verify it with OTP to continue.</p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('phone_number'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('phone_number') }}</div>
                    @endif

                    @if ($errors->has('otp'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('otp') }}</div>
                    @endif

                    <form method="POST" action="{{ route('register.phone.verify.send') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Mobile Number</label>
                            <input id="phone_number" type="text" inputmode="numeric" class="form-control" name="phone_number" value="{{ $phoneNumber }}" required placeholder="Enter 10-15 digit mobile number">
                        </div>
                        <button type="submit" class="btn otp-btn w-100">Send OTP</button>
                    </form>

                    <hr class="my-3">

                    <form method="POST" action="{{ route('register.phone.verify') }}" novalidate>
                        @csrf
                        <input type="hidden" name="phone_number" value="{{ $phoneNumber }}">
                        <div class="mb-3">
                            <label for="otp" class="form-label">OTP Code</label>
                            <input id="otp" type="text" inputmode="numeric" maxlength="6" class="form-control" name="otp" required placeholder="Enter 6-digit OTP">
                        </div>
                        <button type="submit" class="btn otp-btn w-100">Verify Number</button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-link w-100 mt-2 otp-back-link">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

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
                    <h2 class="auth-title otp-title">Verify Your Account</h2>
                    <p class="auth-subtitle mb-1">We've sent separate 6-digit codes to <strong>{{ $email }}</strong> and <strong>{{ $phoneNumber }}</strong>.</p>
                    <p class="auth-subtitle mb-4">Expires in <span id="otp-timer" class="otp-timer" data-expires-at="{{ $expiresAt }}">05:00</span></p>

                    <div id="contactVerifyAlert" class="alert d-none" role="alert"></div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('email_otp'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('email_otp') }}</div>
                    @endif

                    @if ($errors->has('phone_otp'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('phone_otp') }}</div>
                    @endif

                    <form id="contactVerifyForm" method="POST" action="{{ route('register.contact.verify') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email_otp" class="form-label">Email Verification Code</label>
                            <input id="email_otp" type="text" inputmode="numeric" maxlength="6" class="form-control" name="email_otp" required placeholder="Enter 6-digit email code">
                        </div>

                        <div class="mb-3">
                            <label for="phone_otp" class="form-label">Phone Verification Code</label>
                            <input id="phone_otp" type="text" inputmode="numeric" maxlength="6" class="form-control" name="phone_otp" required placeholder="Enter 6-digit phone code">
                        </div>

                        <button id="contactVerifyBtn" type="submit" class="btn otp-btn w-100 js-auto-loader">
                            <span class="btn-text">Verify Account</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>
                    </form>

                    <form id="contactResendForm" method="POST" action="{{ route('register.contact.verify.resend') }}" class="mt-3" novalidate>
                        @csrf
                        <button id="contactResendBtn" type="submit" class="btn btn-auth-secondary auth-action-btn w-100 js-auto-loader">
                            <span class="btn-text">Resend Verification Code</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-link w-100 mt-2 otp-back-link">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@endpush

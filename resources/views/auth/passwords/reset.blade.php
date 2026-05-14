@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="auth-page-wrap">
    <div class="container">
        <div class="auth-layout">
            <aside class="auth-intro auth-intro-login d-none d-lg-block">
                <span class="intro-pill">SOILNWATER</span>
                <h1>Create a new password</h1>
                <p>Set a strong password to complete your secure account recovery.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Password reset is verified via email link</li>
                    <li><i class="fa-solid fa-circle-check"></i> Protect your account with a fresh password</li>
                    <li><i class="fa-solid fa-circle-check"></i> Sign in again after password update</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Reset password</h2>
                    <p class="auth-subtitle">Enter your email and choose a new password.</p>

                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter your email address" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter new password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password-confirm" class="form-label">Confirm Password</label>
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth auth-action-btn w-100 js-auto-loader">
                            <span class="btn-text">Reset Password</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="signin-copy mt-3 mb-0">Remembered your password? <a href="{{ route('login') }}">Back to Sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@endpush

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
                <h1>Reset your password</h1>
                <p>We’ll send a secure password reset link to your email address.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Reset link is sent only to email</li>
                    <li><i class="fa-solid fa-circle-check"></i> Fast and secure account recovery</li>
                    <li><i class="fa-solid fa-circle-check"></i> Continue login once password is updated</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Forgot password</h2>
                    <p class="auth-subtitle">Enter your email to receive a reset password link.</p>

                    @if (session('status'))
                        <div class="login-alert-floating alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autocomplete="email" autofocus>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth auth-action-btn w-100 js-auto-loader">
                            <span class="btn-text">Send Reset Password Email</span>
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

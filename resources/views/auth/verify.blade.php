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
                <h1>Verify your email address</h1>
                <p>Before continuing, please verify your email account to keep your profile secure.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Secure account confirmation</li>
                    <li><i class="fa-solid fa-circle-check"></i> Protection against unauthorized access</li>
                    <li><i class="fa-solid fa-circle-check"></i> Fast resend if you missed the email</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Email verification</h2>
                    <p class="auth-subtitle">Check your inbox for the verification link we sent.</p>

                    @if (session('resent') || session('status'))
                        <div class="alert alert-success" role="alert">
                            A fresh verification link has been sent to your email address.
                        </div>
                    @endif

                    <div class="alert alert-info" role="alert">
                        Before proceeding, please check your email for a verification link.
                    </div>

                    @php
                        $verificationResendRoute = Route::has('verification.send') ? 'verification.send' : 'verification.resend';
                    @endphp

                    <form method="POST" action="{{ route($verificationResendRoute) }}" novalidate>
                        @csrf
                        <button type="submit" class="btn btn-primary btn-auth auth-action-btn w-100 js-auto-loader">
                            <span class="btn-text">Resend Verification Email</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="signin-copy mt-3 mb-0">Already verified? <a href="{{ route('login') }}">Sign in</a></p>
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

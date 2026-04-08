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
                <h1>Welcome back to SoilnWater</h1>
                <p>Secure, professional access to your account with password, OTP, or Google sign-in.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Secure email verification checks</li>
                    <li><i class="fa-solid fa-circle-check"></i> One-time password login via email</li>
                    <li><i class="fa-solid fa-circle-check"></i> 5-minute OTP expiration protection</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Sign in</h2>
                    <p class="auth-subtitle">Choose your preferred login method.</p>

                    <!-- <div id="loginAlert" class="alert d-none" role="alert"></div> -->
                    <div id="loginAlert" class="login-alert-floating d-none"></div>
                    @if (session('status'))
                        <div class="login-alert-floating alert-success" >{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('email'))
                        <div class="alert alert-warning" role="alert">{{ $errors->first('email') }}</div>
                        @if (str_contains(strtolower($errors->first('email')), 'not verified'))
                            <form id="resendVerificationForm" method="POST" action="{{ route('login.verification.resend') }}" class="mb-3">
                                @csrf
                                <input type="hidden" name="email" value="{{ old('email') }}">
                                <button id="resendVerificationBtn" type="submit" class="btn btn-auth-secondary auth-action-btn w-100">
                                    <span class="btn-text">Resend Verification Email</span>
                                    <span class="btn-loader d-none" aria-hidden="true"></span>
                                </button>
                            </form>
                        @endif
                    @endif

                    @if ($errors->has('contact_verification'))
                        <div class="alert alert-warning" role="alert">
                            {{ $errors->first('contact_verification') }}
                            <a href="{{ route('register.contact.verify.start', ['email' => old('email')]) }}" class="fw-semibold">
                                Click here to verify your contact details.
                            </a>
                        </div>
                    @endif

                    @if ($errors->has('google'))
                        <div class="alert alert-info" role="alert">{{ $errors->first('google') }}</div>
                    @endif

                    <ul class="nav nav-pills nav-fill mb-4 auth-pill" id="login-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="password-tab" data-bs-toggle="pill" data-bs-target="#password-pane" type="button" role="tab">Password</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="otp-tab" data-bs-toggle="pill" data-bs-target="#otp-pane" type="button" role="tab">OTP</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="login-tab-content">
                        <div class="tab-pane fade show active" id="password-pane" role="tabpanel">
                            <form id="passwordLoginForm" method="POST" action="{{ route('login') }}" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a class="small" href="{{ route('password.request') }}">Forgot password?</a>
                                    @endif
                                </div>

                                <button id="passwordSubmitBtn" type="submit" class="btn btn-primary btn-auth auth-action-btn w-100 js-auto-loader">
                                    <span class="btn-text">Login with Password</span>
                                    <span class="btn-loader d-none" aria-hidden="true"></span>
                                </button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="otp-pane" role="tabpanel">
                            <form id="otpSendForm" method="POST" action="{{ route('login.otp.send') }}" novalidate>
                                @csrf
                                <div class="mb-3">
                                    <label for="otp_email" class="form-label">Email Address</label>
                                    <input id="otp_email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email">
                                </div>
                                <button id="otpSendBtn" type="submit" class="btn btn-primary btn-auth auth-action-btn w-100 js-auto-loader">
                                    <span class="btn-text">Send OTP to Email</span>
                                    <span class="btn-loader d-none" aria-hidden="true"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="auth-divider"><span>or</span></div>

                    <a href="{{ route('login.google') }}" class="btn btn-google d-flex justify-content-center align-items-center gap-2">
                        <span class="fw-semibold">Sign in with Google</span>
                    </a>

                    <p class="signin-copy mt-3 mb-0">Don’t have an account? <a href="{{ route('register') }}">Sign up</a></p>
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

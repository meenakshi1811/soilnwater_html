@extends('layouts.app')

@section('content')
<div class="container py-5">
    <style>
        :root {
            --primary: #1976d2;
            --secondary: #2e7d32;
        }
        .auth-split {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 460px;
        }
        .auth-pill .nav-link.active {
            background-color: var(--primary);
        }
        .btn-auth-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .btn-auth-secondary {
            border-color: var(--secondary);
            color: var(--secondary);
        }
        .btn-auth-secondary:hover {
            background-color: var(--secondary);
            color: #fff;
        }
        .btn-loader {
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .75s linear infinite;
            display: inline-block;
            vertical-align: middle;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <div class="row justify-content-center align-items-center g-4">
        <div class="col-lg-5 d-none d-lg-block">
            <div class="p-4 rounded-4 text-white auth-split">
                <h2 class="fw-bold mb-3">Welcome back to SoilNWater</h2>
                <p class="opacity-75 mb-4">Secure, professional access to your account with password, OTP, or Google sign-in.</p>
                <ul class="list-unstyled small opacity-75 mb-0">
                    <li class="mb-2">• Secure email verification checks</li>
                    <li class="mb-2">• One-time password login via email</li>
                    <li>• 5-minute OTP expiration protection</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-6 col-xl-5">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-2">Sign in</h3>
                    <p class="text-muted mb-4">Choose your preferred login method.</p>

                    <div id="loginAlert" class="alert d-none" role="alert"></div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('email'))
                        <div class="alert alert-warning" role="alert">{{ $errors->first('email') }}</div>
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

                                <button id="passwordSubmitBtn" type="submit" class="btn btn-auth-primary w-100 js-auto-loader">
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
                                <button id="otpSendBtn" type="submit" class="btn btn-auth-secondary w-100 js-auto-loader">
                                    <span class="btn-text">Send OTP to Email</span>
                                    <span class="btn-loader d-none" aria-hidden="true"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="position-relative text-center my-4">
                        <hr>
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">or</span>
                    </div>

                    <a href="{{ route('login.google') }}" class="btn btn-light border w-100 d-flex justify-content-center align-items-center gap-2">
                        <span class="fw-semibold">Sign in with Google</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/login.js') }}?v={{ now()->timestamp }}"></script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center align-items-center g-4">
        <div class="col-lg-5 d-none d-lg-block">
            <div class="p-4 rounded-4 text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); min-height: 460px;">
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

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->has('email'))
                        <div class="alert alert-warning" role="alert">
                            {{ $errors->first('email') }}
                        </div>
                    @endif

                    @if ($errors->has('google'))
                        <div class="alert alert-info" role="alert">
                            {{ $errors->first('google') }}
                        </div>
                    @endif

                    <ul class="nav nav-pills nav-fill mb-4" id="login-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="password-tab" data-bs-toggle="pill" data-bs-target="#password-pane" type="button" role="tab" aria-controls="password-pane" aria-selected="true">
                                Password
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="otp-tab" data-bs-toggle="pill" data-bs-target="#otp-pane" type="button" role="tab" aria-controls="otp-pane" aria-selected="false">
                                OTP
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="login-tab-content">
                        <div class="tab-pane fade show active" id="password-pane" role="tabpanel" aria-labelledby="password-tab">
                            <form method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    @error('password')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
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

                                <button type="submit" class="btn btn-primary w-100">Login with Password</button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="otp-pane" role="tabpanel" aria-labelledby="otp-tab">
                            <form method="POST" action="{{ route('login.otp.send') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="otp_email" class="form-label">Email Address</label>
                                    <input id="otp_email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email">
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">Send OTP to Email</button>
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
@endsection

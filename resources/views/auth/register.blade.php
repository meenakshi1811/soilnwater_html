@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="register-page-wrap">
    <div class="container">
        <div class="register-layout">
            <aside class="register-intro">
                <span class="intro-pill">SOILNWATER</span>
                <h1>Build your trusted marketplace profile.</h1>
                <p>Create your account to connect with verified agricultural buyers and sellers across local and national markets.</p>

                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Verified user onboarding</li>
                    <li><i class="fa-solid fa-circle-check"></i> Faster order and inquiry management</li>
                    <li><i class="fa-solid fa-circle-check"></i> Secure account access controls</li>
                </ul>
            </aside>

            <div class="card auth-card register-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Create Account</h2>
                    <p class="auth-subtitle">It only takes a minute to get started.</p>

                    <div id="registerAlert" class="alert d-none" role="alert"></div>

                    <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input id="fullname" type="text" class="form-control @error('fullname') is-invalid @enderror" name="fullname" value="{{ old('fullname') }}" required autocomplete="name" autofocus>
                            @error('fullname')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel">
                            @error('phone_number')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Select Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                                <option value="">Choose your role</option>
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                                <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="builder" {{ old('role') === 'builder' ? 'selected' : '' }}>Builder</option>
                                <option value="developer" {{ old('role') === 'developer' ? 'selected' : '' }}>Developer</option>
                                <option value="consultant" {{ old('role') === 'consultant' ? 'selected' : '' }}>Consultant</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <button id="registerSubmitBtn" type="submit" class="btn btn-primary btn-create auth-action-btn js-auto-loader">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>

                        <div class="auth-divider"><span>or</span></div>

                        <button type="button" class="btn btn-google">
                            <i class="fa-brands fa-google me-2"></i> Continue with Google
                        </button>

                        <p class="signin-copy">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                    </form>
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

@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="auth-page-wrap">
    <div class="container">
        <div class="auth-layout">
            <aside class="auth-intro d-none d-lg-block">
                <span class="intro-pill">EMPLOYEE SIGN UP</span>
                <h1>Create a staff account</h1>
                <p>Employee accounts live on a separate table from users, so this email can also belong to a regular portal user.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Does not replace your user login</li>
                    <li><i class="fa-solid fa-circle-check"></i> Admin assigns a role before access</li>
                    <li><i class="fa-solid fa-circle-check"></i> You only see permitted modules</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Employee sign up</h2>
                    <p class="auth-subtitle">After you register, an admin must activate your account and assign a role.</p>

                    @if ($errors->any())
                        <div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('employee.register') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">This can be the same email as a user account.</small>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required inputmode="numeric">
                            @error('phone_number')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth auth-action-btn w-100">
                            Create employee account
                        </button>
                    </form>

                    <p class="signin-copy mt-3 mb-0">Already have an employee account? <a href="{{ route('employee.login') }}">Employee sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

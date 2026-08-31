@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="auth-page-wrap">
    <div class="container">
        <div class="auth-layout">
            <aside class="auth-intro auth-intro-login d-none d-lg-block">
                <span class="intro-pill">EMPLOYEE PORTAL</span>
                <h1>Staff access for SoilnWater</h1>
                <p>Sign in with your employee account. This is separate from a regular user, vendor, or consultant login — the same email can be used for both.</p>
                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Independent employee table and session</li>
                    <li><i class="fa-solid fa-circle-check"></i> Access only the modules your role allows</li>
                    <li><i class="fa-solid fa-circle-check"></i> Admin assigns role and activation</li>
                </ul>
            </aside>

            <div class="card auth-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Employee sign in</h2>
                    <p class="auth-subtitle">Use the employee credentials issued by an admin, or from employee sign up.</p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-warning" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('employee.login') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Enter your employee email" required autofocus>
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
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth auth-action-btn w-100">
                            Sign in to employee portal
                        </button>
                    </form>

                    <p class="signin-copy mt-3 mb-1">Need an employee account? <a href="{{ route('employee.register') }}">Employee sign up</a></p>
                    <p class="signin-copy mb-0">Looking for a user or vendor account? <a href="{{ route('login') }}">Regular sign in</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

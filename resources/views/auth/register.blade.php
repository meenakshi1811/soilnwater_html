@extends('frontend.layouts.app')

@push('styles')
<style>
    .auth-shell { padding: 48px 12px 72px; background: #f6f8fb; }
    .auth-card { max-width: 540px; margin: 0 auto; border: 0; border-radius: 18px; box-shadow: 0 20px 45px rgba(16, 24, 40, .08); }
    .auth-card .card-body { padding: 32px; }
    .auth-title { font-size: 32px; font-weight: 800; color: #1a3a5c; margin-bottom: 6px; }
    .auth-subtitle { font-size: 15px; color: #667085; margin-bottom: 24px; }
    .form-label { font-weight: 600; color: #344054; }
    .btn-create { width: 100%; border-radius: 10px; padding: 11px 16px; font-weight: 700; background: #1a73e8; border: none; }
    .btn-google { width: 100%; border-radius: 10px; padding: 11px 16px; font-weight: 600; border: 1px solid #d0d5dd; background: #fff; color: #344054; }
    .divider { display: flex; align-items: center; gap: 12px; margin: 14px 0; color: #98a2b3; font-size: 13px; }
    .divider::before, .divider::after { content: ''; height: 1px; flex: 1; background: #eaecf0; }
    label.error { color: #d92d20; font-size: 12px; margin-top: 6px; display: block; }
</style>
@endpush

@section('content')
<section class="auth-shell">
    <div class="card auth-card">
        <div class="card-body">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join SoilNWater to start your journey</p>

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
                    <label for="email" class="form-label">Email</label>
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

                <button type="submit" class="btn btn-primary btn-create">Create Account</button>

                <div class="divider">or</div>

                <button type="button" class="btn-google">
                    <i class="fa-brands fa-google me-2"></i> Continue with Google
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script>
    $(function () {
        $('#registerForm').validate({
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback d-block');
                error.insertAfter(element);
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            rules: {
                fullname: {
                    required: true,
                    minlength: 3,
                    maxlength: 255
                },
                email: {
                    required: true,
                    email: true
                },
                phone_number: {
                    required: true,
                    digits: true,
                    minlength: 10,
                    maxlength: 15
                },
                password: {
                    required: true,
                    minlength: 8
                },
                password_confirmation: {
                    required: true,
                    equalTo: '#password'
                }
            },
            messages: {
                fullname: {
                    required: 'Please enter your full name.',
                    minlength: 'Full name must be at least 3 characters.'
                },
                email: {
                    required: 'Please enter your email address.',
                    email: 'Please enter a valid email address.'
                },
                phone_number: {
                    required: 'Please enter your phone number.',
                    digits: 'Phone number should contain only digits.',
                    minlength: 'Phone number must be at least 10 digits.',
                    maxlength: 'Phone number cannot exceed 15 digits.'
                },
                password: {
                    required: 'Please create a password.',
                    minlength: 'Password must be at least 8 characters long.'
                },
                password_confirmation: {
                    required: 'Please confirm your password.',
                    equalTo: 'Password confirmation does not match.'
                }
            }
        });
    });
</script>
@endpush

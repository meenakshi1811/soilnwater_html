@extends('layouts.app')

@section('content')
<div class="container py-5">
    <style>
        :root {
            --primary: #1976d2;
            --secondary: #2e7d32;
        }
        .otp-btn {
            background-color: var(--primary);
            border-color: var(--primary);
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

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-2" style="color: var(--primary);">Enter OTP</h4>
                    <p class="text-muted mb-1">We've sent a 6-digit code to <strong>{{ $email }}</strong>.</p>
                    <p class="text-muted mb-4">Expires in <span id="otp-timer" class="fw-semibold" style="color: var(--secondary);">05:00</span></p>

                    <div id="otpAlert" class="alert d-none" role="alert"></div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('otp'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('otp') }}</div>
                    @endif

                    <form id="otpVerifyForm" method="POST" action="{{ route('login.otp.verify') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label for="otp" class="form-label">One-Time Password</label>
                            <input id="otp" type="text" inputmode="numeric" maxlength="6" class="form-control" name="otp" required placeholder="Enter 6-digit OTP">
                        </div>

                        <button id="otpVerifyBtn" type="submit" class="btn otp-btn w-100 js-auto-loader">
                            <span class="btn-text">Verify & Login</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-link w-100 mt-2" style="color: var(--secondary);">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const expiresAt = new Date(@json($expiresAt)).getTime();
        const timer = document.getElementById('otp-timer');

        function updateCountdown() {
            const now = Date.now();
            const distance = expiresAt - now;

            if (distance <= 0) {
                timer.textContent = '00:00 (Expired)';
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script src="{{ asset('assets/js/otp-login.js') }}?v={{ now()->timestamp }}"></script>
@endsection

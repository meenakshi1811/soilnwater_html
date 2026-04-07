@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-2">Enter OTP</h4>
                    <p class="text-muted mb-1">We've sent a 6-digit code to <strong>{{ $email }}</strong>.</p>
                    <p class="text-muted mb-4">Expires in <span id="otp-timer" class="fw-semibold text-danger">05:00</span></p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->has('otp'))
                        <div class="alert alert-danger" role="alert">
                            {{ $errors->first('otp') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.otp.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="otp" class="form-label">One-Time Password</label>
                            <input id="otp" type="text" inputmode="numeric" maxlength="6" class="form-control" name="otp" required placeholder="Enter 6-digit OTP">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify & Login</button>
                    </form>

                    <a href="{{ route('login') }}" class="btn btn-link w-100 mt-2">Back to login</a>
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
@endsection

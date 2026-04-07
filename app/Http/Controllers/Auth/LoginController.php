<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Ensure unverified users do not proceed after password login.
     */
    protected function authenticated(Request $request, $user): RedirectResponse|JsonResponse|null
    {
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is not verified yet. Please verify your email before signing in.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withInput($request->only('email', 'remember'))
                ->withErrors([
                    'email' => 'Your account is not verified yet. Please verify your email before signing in.',
                ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => $this->redirectPath(),
            ]);
        }

        return null;
    }

    public function sendOtp(Request $request): RedirectResponse|JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.',
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Your account is not verified yet. Please verify your email before signing in.',
            ]);
        }

        $otpCode = (string) random_int(100000, 999999);
        $cacheKey = $this->otpCacheKey($user->email);
        $expiresAt = now()->addMinutes(5);

        Cache::put($cacheKey, [
            'otp' => $otpCode,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        Mail::raw("Your SoilNWater login OTP is {$otpCode}. It expires in 5 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your SoilNWater Login OTP');
        });

        $request->session()->put('otp_login_email', $user->email);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'We sent a one-time password (OTP) to your email address.',
                'redirect' => route('login.otp.form'),
            ]);
        }

        return redirect()
            ->route('login.otp.form')
            ->with('status', 'We sent a one-time password (OTP) to your email address.');
    }

    public function showOtpForm(Request $request)
    {
        $email = $request->session()->get('otp_login_email');

        if (! $email) {
            return redirect()->route('login');
        }

        $otpData = Cache::get($this->otpCacheKey($email));

        if (! $otpData) {
            return redirect()->route('login')->withErrors([
                'otp' => 'OTP has expired. Please request a new code.',
            ]);
        }

        return view('auth.otp-login', [
            'email' => $email,
            'expiresAt' => $otpData['expires_at'],
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('otp_login_email');

        if (! $email) {
            $message = 'Your OTP session has expired. Please login again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('login')->withErrors([
                'otp' => $message,
            ]);
        }

        $otpData = Cache::get($this->otpCacheKey($email));

        if (! $otpData || now()->isAfter($otpData['expires_at'])) {
            Cache::forget($this->otpCacheKey($email));
            $message = 'OTP has expired. Please request a new code.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('login')->withErrors([
                'otp' => $message,
            ]);
        }

        if (! hash_equals((string) $otpData['otp'], (string) $request->string('otp'))) {
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP. Please try again.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! $user->hasVerifiedEmail()) {
            Cache::forget($this->otpCacheKey($email));
            $request->session()->forget('otp_login_email');
            $message = 'Your account is not verified yet. Please verify your email before signing in.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        Cache::forget($this->otpCacheKey($email));
        $request->session()->forget('otp_login_email');

        Auth::login($user, true);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => $this->redirectPath(),
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

    public function googleLogin(): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'google' => 'Google sign in is not configured yet. Please use password or OTP login.',
        ]);
    }

    private function otpCacheKey(string $email): string
    {
        return 'login_otp_'.Str::lower($email);
    }
}

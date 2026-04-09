<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

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

    protected function redirectTo(): string
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            return route('admin.dashboard');
        }

        return '/home';
    }

    /**
     * Ensure unverified users do not proceed after password login.
     */
    protected function authenticated(Request $request, $user): RedirectResponse|JsonResponse|null
    {
        if ($user->isGeneralUser() && ! $user->hasVerifiedContact()) {
            Auth::logout();

            $message = 'Your email and phone number are not verified yet. Please verify your account first.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'verification_redirect' => route('register.contact.verify.start', ['email' => $user->email]),
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withInput([
                    'login' => $request->input('login'),
                    'remember' => $request->boolean('remember'),
                    'verification_email' => $user->email,
                ])
                ->withErrors([
                    'contact_verification' => $message,
                ]);
        }

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is not verified yet. Please verify your email before signing in.',
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withInput([
                    'login' => $request->input('login'),
                    'remember' => $request->boolean('remember'),
                    'verification_email' => $user->email,
                ])
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
            'login_contact' => ['required', 'string'],
        ]);

        $user = $this->findUserByLogin($credentials['login_contact']);

        if (! $user) {
            throw ValidationException::withMessages([
                'login_contact' => 'No account found with this email address or phone number.',
            ]);
        }

        if ($user->isGeneralUser() && ! $user->hasVerifiedContact()) {
            $message = 'Your email and phone number are not verified yet. Please verify your account first.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'verification_redirect' => route('register.contact.verify.start', ['email' => $user->email]),
                ], 403);
            }

            return redirect()
                ->route('login')
                ->withInput([
                    'login' => $credentials['login_contact'],
                    'verification_email' => $user->email,
                ])
                ->withErrors([
                    'contact_verification' => $message,
                ]);
        }

        $otpCode = (string) random_int(100000, 999999);
        $cacheKey = $this->otpCacheKey($user->id);
        $expiresAt = now()->addMinutes(5);

        Cache::put($cacheKey, [
            'otp' => $otpCode,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        $isPhoneLogin = $this->looksLikePhone($credentials['login_contact']);
        if ($isPhoneLogin) {
            $this->sendLoginOtpToPhone($user->phone_number, $otpCode);
        } else {
            Mail::to($user->email)->send(new OtpMail(
                otpCode: $otpCode,
                subjectLine: 'Your SoilNWater Login OTP',
                headline: 'Confirm your sign in',
                contextLine: 'Use the OTP below to securely complete your login to your SoilNWater account.',
            ));
        }

        $request->session()->put('otp_login_user_id', $user->id);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $isPhoneLogin
                    ? 'We sent a one-time password (OTP) to your phone number.'
                    : 'We sent a one-time password (OTP) to your email address.',
                'redirect' => route('login.otp.form'),
            ]);
        }

        return redirect()
            ->route('login.otp.form')
            ->with('status', $isPhoneLogin
                ? 'We sent a one-time password (OTP) to your phone number.'
                : 'We sent a one-time password (OTP) to your email address.');
    }

    public function showOtpForm(Request $request)
    {
        $userId = $request->session()->get('otp_login_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        $otpData = Cache::get($this->otpCacheKey($user->id));

        if (! $otpData) {
            return redirect()->route('login')->withErrors([
                'otp' => 'OTP has expired. Please request a new code.',
            ]);
        }

        return view('auth.otp-login', [
            'email' => $user->email,
            'expiresAt' => $otpData['expires_at'],
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $userId = $request->session()->get('otp_login_user_id');

        if (! $userId) {
            $message = 'Your OTP session has expired. Please login again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('login')->withErrors([
                'otp' => $message,
            ]);
        }

        $otpData = Cache::get($this->otpCacheKey($userId));

        if (! $otpData || now()->isAfter($otpData['expires_at'])) {
            Cache::forget($this->otpCacheKey($userId));
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

        $user = User::find($userId);

        if (! $user) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');
            $message = 'Your account could not be found. Please login again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        if ($user->isGeneralUser() && ! $user->hasVerifiedContact()) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');
            $message = 'Your email and phone number are not verified yet. Please verify your account first.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'verification_redirect' => route('register.contact.verify.start', ['email' => $user->email]),
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'contact_verification' => $message,
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');
            $message = 'Your account is not verified yet. Please verify your email before signing in.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'verification_email' => $user->email,
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        Cache::forget($this->otpCacheKey($userId));
        $request->session()->forget('otp_login_user_id');

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

    public function resendVerification(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', Rule::exists('users', 'email')],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'No account found with this email address.',
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            $message = 'Your email is already verified. Please login with your password or OTP.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('login')->with('status', $message);
        }

        $user->sendEmailVerificationNotification();

        $message = 'A new verification email has been sent. Please check your inbox.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('login')->with('status', $message);
    }

    public function username(): string
    {
        return 'login';
    }

    protected function validateLogin(Request $request): void
    {
        $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);
    }

    protected function credentials(Request $request): array
    {
        $login = $this->normalizeLoginIdentifier((string) $request->input('login'));
        $field = $this->looksLikePhone($login) ? 'phone_number' : 'email';

        return [
            $field => $login,
            'password' => $request->input('password'),
        ];
    }

    private function findUserByLogin(string $login): ?User
    {
        $login = $this->normalizeLoginIdentifier($login);
        $field = $this->looksLikePhone($login) ? 'phone_number' : 'email';

        return User::where($field, $login)->first();
    }

    private function looksLikePhone(string $value): bool
    {
        return (bool) preg_match('/^[0-9]{10,15}$/', $this->normalizeLoginIdentifier($value));
    }

    private function normalizeLoginIdentifier(string $value): string
    {
        $value = trim($value);
        if (str_contains($value, '@')) {
            return strtolower($value);
        }

        return preg_replace('/\D+/', '', $value) ?? $value;
    }

    private function sendLoginOtpToPhone(string $phoneNumber, string $otpCode): void
    {
       try {
            $apikey   = config('services.message.api_key');
            $username = config('services.message.username');
            $sender   = config('services.message.sender');
            $smstype  = config('services.message.smstype');
            $peid     = config('services.message.peid');

            $message = "Verification OTP Your login verification code is {$phoneOtpCode} This code is valid for 5 minutes. Do not share it with anyone. – Annuvedant Team";

            $url = 'http://sms.messageindia.in/v2/sendSMS?' . http_build_query([
                'username'   => $username,
                'message'    => $message, // let http_build_query encode it
                'sendername' => $sender,
                'smstype'    => $smstype,
                'numbers'    => $phoneNumber,
                'apikey'     => $apikey,
                'peid'       => $peid,
                'templateid' => 1707177571854887443,
            ]);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }

            curl_close($curl);
            Log::info('SMS sent successfully', [
                'phone' => $phoneNumber,
                'response' => $response,
            ]);

        } catch (\Throwable $e) {
            Log::error('SMS sending failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function otpCacheKey(int $userId): string
    {
        return 'login_otp_'.$userId;
    }
}

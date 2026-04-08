<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/', 'unique:users,phone_number'],
            'role' => ['required', 'in:user,vendor,builder,developer,consultant'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['fullname'],
            'full_name' => $data['fullname'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        if ($user->isGeneralUser()) {
            $otpCode = $this->sendContactVerificationOtp($user);
            $request->session()->put('contact_verification_user_id', $user->id);

            $message = 'Registration successful. We sent a 6-digit verification code to your email and phone number.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => route('register.contact.verify.form'),
                    'debug_otp' => app()->isLocal() ? $otpCode : null,
                ]);
            }

            return redirect()->route('register.contact.verify.form')->with('status', $message);
        }

        $user->sendEmailVerificationNotification();

        $message = 'Thank you for registering. Please verify your account, we have sent you the verification mail on your registered email.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('status', $message);
    }

    public function showContactVerificationForm(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $user = $this->verificationUserFromSession($request);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Verification session expired. Please login and request verification again.',
            ]);
        }

        $otpData = Cache::get($this->contactOtpCacheKey($user->id));

        if (! $otpData) {
            return redirect()->route('login')->withErrors([
                'email' => 'Verification code has expired. Please request a new code.',
            ]);
        }

        return view('auth.contact-verify', [
            'email' => $user->email,
            'phoneNumber' => $user->phone_number,
            'expiresAt' => $otpData['expires_at'],
        ]);
    }

    public function verifyContactOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $this->verificationUserFromSession($request);

        if (! $user) {
            return $this->contactVerificationErrorResponse($request, 'Verification session expired. Please register or login again.');
        }

        $cacheKey = $this->contactOtpCacheKey($user->id);
        $otpData = Cache::get($cacheKey);

        if (! $otpData || now()->isAfter($otpData['expires_at'])) {
            Cache::forget($cacheKey);

            return $this->contactVerificationErrorResponse($request, 'Verification code has expired. Please resend the code.');
        }

        if (! hash_equals((string) $otpData['otp'], (string) $request->string('otp'))) {
            return $this->contactVerificationErrorResponse($request, 'Invalid OTP. Please try again.', 422, 'otp');
        }

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'phone_verified_at' => now(),
        ])->save();

        Cache::forget($cacheKey);
        $request->session()->forget('contact_verification_user_id');

        $message = 'Your email and phone number are verified successfully. Please login to continue.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('status', $message);
    }

    public function resendContactOtp(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->verificationUserFromSession($request);

        if (! $user) {
            return $this->contactVerificationErrorResponse($request, 'Verification session expired. Please login and request verification again.');
        }

        $otpCode = $this->sendContactVerificationOtp($user);

        $message = 'A new verification code was sent to your email and phone number.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'debug_otp' => app()->isLocal() ? $otpCode : null,
            ]);
        }

        return back()->with('status', $message);
    }

    public function startContactVerificationFromLogin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->isGeneralUser()) {
            return redirect()->route('login')->withErrors([
                'email' => 'No user account found for contact verification.',
            ]);
        }

        if ($user->hasVerifiedContact()) {
            return redirect()->route('login')->with('status', 'Your contact details are already verified. Please login.');
        }

        $request->session()->put('contact_verification_user_id', $user->id);
        $this->sendContactVerificationOtp($user);

        return redirect()->route('register.contact.verify.form')->with('status', 'We sent a verification code to your email and phone number.');
    }

    private function sendContactVerificationOtp(User $user): string
    {
        $otpCode = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        Cache::put($this->contactOtpCacheKey($user->id), [
            'otp' => $otpCode,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        Mail::raw("Your SoilNWater verification OTP is {$otpCode}. It expires in 5 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your SoilNWater Verification OTP');
        });

        $this->sendOtpToPhone($user->phone_number, $otpCode);

        return $otpCode;
    }

    private function sendOtpToPhone(string $phoneNumber, string $otpCode): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token || ! $from) {
            Log::warning('Twilio SMS configuration is missing. Skipping SMS OTP send.', ['phone_number' => $phoneNumber]);

            return;
        }

        try {
            Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $phoneNumber,
                    'Body' => "Your SoilNWater verification OTP is {$otpCode}. It expires in 5 minutes.",
                ]);
        } catch (\Throwable $exception) {
            Log::error('Twilio SMS OTP send failed.', [
                'phone_number' => $phoneNumber,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function verificationUserFromSession(Request $request): ?User
    {
        $userId = $request->session()->get('contact_verification_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function contactVerificationErrorResponse(Request $request, string $message, int $status = 422, string $field = 'email'): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withErrors([$field => $message]);
    }

    private function contactOtpCacheKey(int $userId): string
    {
        return 'contact_verification_otp_'.$userId;
    }
}

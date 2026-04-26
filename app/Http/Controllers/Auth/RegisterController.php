<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
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
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;

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
            'accept_terms' => ['accepted'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
            'accept_terms.accepted' => 'Please accept the terms and conditions to continue.',
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
            $otpPayload = $this->sendContactVerificationOtp($user);
            $request->session()->put('contact_verification_user_id', $user->id);

            $message = 'Registration successful. We sent separate 6-digit verification codes to your email and phone number.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect' => route('register.contact.verify.form'),
                    'expires_at' => $otpPayload['expires_at'],
                    'debug_otp' => app()->isLocal() ? $otpPayload['debug'] : null,
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
            'email_otp' => ['required', 'digits:6'],
            'phone_otp' => ['required', 'digits:6'],
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

        if (! hash_equals((string) $otpData['email_otp'], (string) $request->string('email_otp'))) {
            return $this->contactVerificationErrorResponse($request, 'Invalid email OTP. Please try again.', 422, 'email_otp');
        }

        if (! hash_equals((string) $otpData['phone_otp'], (string) $request->string('phone_otp'))) {
            return $this->contactVerificationErrorResponse($request, 'Invalid phone OTP. Please try again.', 422, 'phone_otp');
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

        $otpPayload = $this->sendContactVerificationOtp($user);

        $message = 'New email and phone verification codes were sent successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'expires_at' => $otpPayload['expires_at'],
                'debug_otp' => app()->isLocal() ? $otpPayload['debug'] : null,
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

        return redirect()->route('register.contact.verify.form')->with('status', 'We sent new email and phone verification codes.');
    }

    public function startPhoneVerification(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->isGeneralUser()) {
            return redirect()->route('login')->withErrors([
                'email' => 'No user account found for phone verification.',
            ]);
        }

        if ($user->phone_verified_at) {
            return redirect()->route('login')->with('status', 'Your mobile number is already verified. Please login.');
        }

        $request->session()->put('phone_verification_user_id', $user->id);

        return redirect()->route('register.phone.verify.form');
    }

    public function showPhoneVerificationForm(Request $request): RedirectResponse|\Illuminate\View\View
    {
        $user = $this->phoneVerificationUserFromSession($request);

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'email' => 'Phone verification session expired. Please start again from login.',
            ]);
        }

        if ($user->phone_verified_at) {
            $request->session()->forget('phone_verification_user_id');

            return redirect()->route('login')->with('status', 'Your mobile number is already verified. Please login.');
        }

        $otpData = Cache::get($this->phoneOtpCacheKey($user->id));

        return view('auth.phone-verify', [
            'email' => $user->email,
            'phoneNumber' => old('phone_number', $user->phone_number),
            'expiresAt' => $otpData['expires_at'] ?? null,
        ]);
    }

    public function sendPhoneVerificationOtp(Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->phoneVerificationUserFromSession($request);

        if (! $user) {
            return $this->contactVerificationErrorResponse($request, 'Phone verification session expired. Please start again from login.');
        }

        $data = $request->validate([
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'phone_number')->ignore($user->id)],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
        ]);

        $phoneOtpCode = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        $user->forceFill([
            'phone_number' => $data['phone_number'],
            'phone_verified_at' => null,
        ])->save();

        Cache::put($this->phoneOtpCacheKey($user->id), [
            'otp' => $phoneOtpCode,
            'phone_number' => $data['phone_number'],
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        $this->sendOtpToPhone($data['phone_number'], $phoneOtpCode);

        $message = 'OTP sent to your mobile number. Please enter the 6-digit code to verify.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'expires_at' => $expiresAt->toIso8601String(),
                'debug_otp' => app()->isLocal() ? $phoneOtpCode : null,
            ]);
        }

        return back()->with('status', $message);
    }

    public function verifyPhoneOtp(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
            'phone_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
        ], [
            'phone_number.regex' => 'Phone number must contain only digits and be between 10 and 15 characters.',
        ]);

        $user = $this->phoneVerificationUserFromSession($request);

        if (! $user) {
            return $this->contactVerificationErrorResponse($request, 'Phone verification session expired. Please start again from login.');
        }

        $cacheKey = $this->phoneOtpCacheKey($user->id);
        $otpData = Cache::get($cacheKey);

        if (! $otpData || now()->isAfter($otpData['expires_at'])) {
            Cache::forget($cacheKey);

            return $this->contactVerificationErrorResponse($request, 'Verification code has expired. Please request a new OTP.', 422, 'otp');
        }

        if (! hash_equals((string) $otpData['phone_number'], (string) $request->string('phone_number'))) {
            return $this->contactVerificationErrorResponse($request, 'Phone number mismatch. Please request OTP again.', 422, 'phone_number');
        }

        if (! hash_equals((string) $otpData['otp'], (string) $request->string('otp'))) {
            return $this->contactVerificationErrorResponse($request, 'Invalid OTP. Please try again.', 422, 'otp');
        }

        $user->forceFill([
            'phone_number' => $otpData['phone_number'],
            'email_verified_at' => $user->email_verified_at ?? now(),
            'phone_verified_at' => now(),
        ])->save();

        Cache::forget($cacheKey);
        $request->session()->forget('phone_verification_user_id');

        $message = 'Your mobile number has been verified successfully. Please login to continue.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('login'),
            ]);
        }

        return redirect()->route('login')->with('status', $message);
    }

    private function sendContactVerificationOtp(User $user): array
    {
        $emailOtpCode = (string) random_int(100000, 999999);
        $phoneOtpCode = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        Cache::put($this->contactOtpCacheKey($user->id), [
            'email_otp' => $emailOtpCode,
            'phone_otp' => $phoneOtpCode,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        Mail::to($user->email)->send(new OtpMail(
            otpCode: $emailOtpCode,
            subjectLine: 'Your SoilNWater Email Verification OTP',
            headline: 'Verify your email address',
            contextLine: 'Use the OTP below to complete your SoilNWater registration and verify your email address.',
        ));

        $this->sendOtpToPhone($user->phone_number, $phoneOtpCode);

        return [
            'expires_at' => $expiresAt->toIso8601String(),
            'debug' => "email:{$emailOtpCode}|phone:{$phoneOtpCode}",
        ];
    }

    private function sendOtpToPhone(string $phoneNumber, string $phoneOtpCode): void
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

    private function verificationUserFromSession(Request $request): ?User
    {
        $userId = $request->session()->get('contact_verification_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function phoneVerificationUserFromSession(Request $request): ?User
    {
        $userId = $request->session()->get('phone_verification_user_id');

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

    private function phoneOtpCacheKey(int $userId): string
    {
        return 'phone_verification_otp_'.$userId;
    }
}

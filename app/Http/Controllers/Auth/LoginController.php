<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PremiumPromptService;
use App\Support\GoogleGeocoder;
use App\Mail\ConsultantStatusMail;
use App\Mail\ServiceProviderStatusMail;
use App\Mail\OtpMail;
use App\Mail\VendorStatusMail;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

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

        if ($user && $user->role === 'employee') {
            $slug = $user->firstReadableModuleSlug();
            if ($slug) {
                return route('modules.show', ['module' => $slug]);
            }
        }

        if ($user && $user->isGeneralUser()) {
            return route('user.dashboard');
        }

        if ($user && $user->isVendor()) {
            if ($user->vendor?->isApproved()) {
                return route('vendor.dashboard');
            }

            return route('vendor.pending');
        }

        if ($user && $user->isConsultant()) {
            if ($user->consultant?->isApproved()) {
                return route('consultant.dashboard');
            }

            return route('consultant.pending');
        }

        if ($user && $user->isServiceProvider()) {
            if ($user->serviceProvider?->isApproved()) {
                return route('service_provider.dashboard');
            }

            return route('service_provider.pending');
        }

        return '/home';
    }

    /**
     * Ensure unverified users do not proceed after password login.
     */
    protected function authenticated(Request $request, $user): RedirectResponse|JsonResponse|null
    {
        $blockedResponse = $this->ensureAccountNotBlocked($request, $user, true);
        if ($blockedResponse) {
            return $blockedResponse;
        }

        if ($user->isGeneralUser() && ! $user->hasVerifiedContact()) {
            Auth::logout();

            return $this->contactVerificationRequiredResponse(
                $request,
                $user,
                'Your email and phone number are not verified yet. Please verify your account before signing in.'
            );
        }

        $approvalResponse = $this->ensureApprovedMarketplaceAccount($request, $user, true);
        if ($approvalResponse) {
            return $approvalResponse;
        }

        if ($this->isMarketplaceUser($user) && ! $user->hasVerifiedContact()) {
            Auth::logout();

            return $this->contactVerificationRequiredResponse(
                $request,
                $user,
                'Your account is approved. Please verify your email and phone number before signing in.'
            );
        }

        if (! $this->isMarketplaceUser($user) && ! $user->isGeneralUser() && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your account is not verified yet. Please verify your email before signing in.',
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
                    'email' => 'Your account is not verified yet. Please verify your email before signing in.',
                ]);
        }

        if ($request->expectsJson()) {
            PremiumPromptService::flashForUser($user);

            return response()->json([
                'message' => 'Login successful.',
                'redirect' => $this->redirectPath(),
            ]);
        }

        PremiumPromptService::flashForUser($user);

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

        $blockedResponse = $this->ensureAccountNotBlocked($request, $user);
        if ($blockedResponse) {
            return $blockedResponse;
        }

        if ($user->isGeneralUser() && ! $user->hasVerifiedContact()) {
            return $this->contactVerificationRequiredResponse(
                $request,
                $user,
                'Your email and phone number are not verified yet. Please verify your account first.',
                $credentials['login_contact']
            );
        }

        $approvalResponse = $this->ensureApprovedMarketplaceAccount($request, $user);
        if ($approvalResponse) {
            return $approvalResponse;
        }

        if ($this->isMarketplaceUser($user) && ! $user->hasVerifiedContact()) {
            return $this->contactVerificationRequiredResponse(
                $request,
                $user,
                'Your account is approved. Please verify your email and phone number before signing in.',
                $credentials['login_contact']
            );
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

        $blockedResponse = $this->ensureAccountNotBlocked($request, $user);
        if ($blockedResponse) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');

            return $blockedResponse;
        }

        $approvalResponse = $this->ensureApprovedMarketplaceAccount($request, $user);
        if ($approvalResponse) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');

            return $approvalResponse;
        }

        if (($user->isGeneralUser() || $this->isMarketplaceUser($user)) && ! $user->hasVerifiedContact()) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');

            return $this->contactVerificationRequiredResponse(
                $request,
                $user,
                $this->isMarketplaceUser($user)
                    ? 'Your account is approved. Please verify your email and phone number before signing in.'
                    : 'Your email and phone number are not verified yet. Please verify your account first.'
            );
        }

        if (! $this->isMarketplaceUser($user) && ! $user->isGeneralUser() && ! $user->hasVerifiedEmail()) {
            Cache::forget($this->otpCacheKey($userId));
            $request->session()->forget('otp_login_user_id');
            $message = 'Your account is not verified yet. Please verify your email before signing in.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'verification_redirect' => route('register.contact.verify.start', ['email' => $user->email]),
                ], 403);
            }

            return redirect()->route('login')->withInput([
                'verification_email' => $user->email,
            ])->withErrors([
                'email' => $message,
            ]);
        }

        Cache::forget($this->otpCacheKey($userId));
        $request->session()->forget('otp_login_user_id');

        Auth::login($user, true);

        PremiumPromptService::flashForUser($user);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Login successful.',
                'redirect' => $this->redirectPath(),
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

    public function googleLogin(Request $request): RedirectResponse
    {
        $request->session()->put('google_auth.intent', 'login');
        $request->session()->forget(['google_auth.role', 'google_auth.registration']);

        $stateToken = $this->storeGoogleAuthState([
            'intent' => 'login',
        ]);

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $stateToken])
            ->redirect();
    }

    public function googleRegister(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:user,vendor,builder,developer,consultant,service_provider'],
            'whatsapp_number' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'regex:/^[0-9]{4,10}$/'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
        ], [
            'role.required' => 'Please select a role before continuing with Google.',
            'whatsapp_number.regex' => 'WhatsApp number must contain only digits and be between 10 and 15 characters.',
            'pincode.regex' => 'Pincode must contain only digits and be between 4 and 10 characters.',
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old to register.',
        ]);

        $registrationPayload = [
            'whatsapp_number' => $data['whatsapp_number'],
            'address' => $data['address'],
            'city' => $data['city'],
            'pincode' => $data['pincode'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
        ];

        $request->session()->put('google_auth.intent', 'register');
        $request->session()->put('google_auth.role', $data['role']);
        $request->session()->put('google_auth.registration', $registrationPayload);

        $stateToken = $this->storeGoogleAuthState([
            'intent' => 'register',
            'role' => $data['role'],
            'registration' => $registrationPayload,
        ]);

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $stateToken])
            ->redirect();
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $exception) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google authentication failed. Please try again.',
            ]);
        }

        $authState = $this->resolveGoogleAuthState($request);
        $intent = (string) ($authState['intent'] ?? 'login');
        $roleFromRegisterFlow = $authState['role'] ?? null;
        $registrationFromRegisterFlow = is_array($authState['registration'] ?? null)
            ? $authState['registration']
            : [];
        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'google' => 'Google account email is missing. Please use another login method.',
            ]);
        }

        $user = User::where('email', $email)->first();

        if ($intent === 'register' && ! $user) {
            $missingRegistrationDetails = $registrationFromRegisterFlow === []
                || ! isset(
                    $registrationFromRegisterFlow['whatsapp_number'],
                    $registrationFromRegisterFlow['address'],
                    $registrationFromRegisterFlow['city'],
                    $registrationFromRegisterFlow['pincode'],
                    $registrationFromRegisterFlow['date_of_birth']
                );

            if (! in_array($roleFromRegisterFlow, ['user', 'vendor', 'builder', 'developer', 'consultant', 'service_provider'], true) || $missingRegistrationDetails) {
                return redirect()->route('register')->withErrors([
                    'role' => 'Please complete the Google registration popup before continuing.',
                ]);
            }
        }

        if ($intent === 'register' && $registrationFromRegisterFlow !== []) {
            $registrationFromRegisterFlow = $this->resolveGoogleRegistrationCoordinates($registrationFromRegisterFlow);
        }

        $createdUser = false;
        if (! $user) {
            $displayName = trim((string) ($googleUser->getName() ?: 'Google User'));
            $role = $intent === 'register' ? $roleFromRegisterFlow : 'user';

            $user = User::create([
                'name' => $displayName,
                'full_name' => $displayName,
                'email' => $email,
                'whatsapp_number' => $registrationFromRegisterFlow['whatsapp_number'] ?? null,
                'address' => $registrationFromRegisterFlow['address'] ?? null,
                'city' => $registrationFromRegisterFlow['city'] ?? null,
                'pincode' => $registrationFromRegisterFlow['pincode'] ?? null,
                'latitude' => $registrationFromRegisterFlow['latitude'] ?? null,
                'longitude' => $registrationFromRegisterFlow['longitude'] ?? null,
                'role' => $role,
                'date_of_birth' => $registrationFromRegisterFlow['date_of_birth'] ?? null,
                'password' => Hash::make(str()->random(40)),
                'email_verified_at' => now(),
            ]);
            $createdUser = true;
        } elseif ($intent === 'register') {
            $this->applyGoogleRegistrationDetails($user, $roleFromRegisterFlow, $registrationFromRegisterFlow);
        }

        $blockedResponse = $this->ensureAccountNotBlocked($request, $user);
        if ($blockedResponse) {
            return $blockedResponse;
        }

        if ($user->isGeneralUser() && ! $user->phone_verified_at) {
            if ($intent === 'login') {
                return redirect()
                    ->route('login')
                    ->withInput([
                        'verification_email' => $user->email,
                    ])
                    ->withErrors([
                        'contact_verification' => 'Your mobile number is not verified yet. Please verify your number to continue.',
                    ]);
            }

            $request->session()->put('phone_verification_user_id', $user->id);

            return redirect()
                ->route('register.phone.verify.form')
                ->with('status', 'Email is verified via Google. Please add and verify your mobile number to complete registration.');
        }

        if ($this->isMarketplaceUser($user)) {
            $this->ensureMarketplaceProfile($user);

            if ($createdUser && $intent === 'register') {
                if ($user->isVendor() && $user->vendor) {
                    Mail::to($user->email)->send(VendorStatusMail::forVendor($user->vendor, 'pending'));
                } elseif ($user->isConsultant() && $user->consultant) {
                    Mail::to($user->email)->send(ConsultantStatusMail::forConsultant($user->consultant, 'pending'));
                } elseif ($user->isServiceProvider() && $user->serviceProvider) {
                    Mail::to($user->email)->send(ServiceProviderStatusMail::forServiceProvider($user->serviceProvider, 'pending'));
                }
            }

            $approvalResponse = $this->ensureApprovedMarketplaceAccount($request, $user, true);
            if ($approvalResponse) {
                return $approvalResponse;
            }

            if (! $user->hasVerifiedContact()) {
                return $this->contactVerificationRequiredResponse(
                    $request,
                    $user,
                    'Your account is approved. Please verify your email and phone number before signing in.'
                );
            }
        }

        Auth::login($user, true);

        PremiumPromptService::flashForUser($user);

        return redirect()->intended($this->redirectPath());
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

            $message = "Verification OTP Your login verification code is {$otpCode} This code is valid for 5 minutes. Do not share it with anyone. – Annuvedant Team";

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

    private function contactVerificationRequiredResponse(Request $request, User $user, string $message, ?string $login = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'verification_redirect' => route('register.contact.verify.start', ['email' => $user->email]),
            ], 403);
        }

        return redirect()
            ->route('register.contact.verify.start', ['email' => $user->email])
            ->withInput([
                'login' => $login ?? $user->email,
                'verification_email' => $user->email,
            ])
            ->with('status', $message);
    }

    private function ensureAccountNotBlocked(Request $request, User $user, bool $logout = false): RedirectResponse|JsonResponse|null
    {
        if (! $user->isBlocked()) {
            return null;
        }

        if ($logout) {
            Auth::logout();
        }

        $message = 'Your account has been blocked by the admin. Please contact support for assistance.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    private function ensureApprovedMarketplaceAccount(Request $request, User $user, bool $logout = false): RedirectResponse|JsonResponse|null
    {
        if (! $this->isMarketplaceUser($user)) {
            return null;
        }

        $this->ensureMarketplaceProfile($user);

        $message = null;
        if ($user->isVendor() && ! $user->vendor?->isApproved()) {
            $message = $user->vendor?->isRejected()
                ? 'Your vendor account has been rejected by the admin. Please contact support for more information.'
                : 'Your vendor account is pending admin approval. You will be able to log in once approved.';
        } elseif ($user->isConsultant() && ! $user->consultant?->isApproved()) {
            $message = $user->consultant?->isRejected()
                ? 'Your consultant account has been rejected by the admin. Please contact support for more information.'
                : 'Your consultant account is pending admin approval. You will be able to log in once approved.';
        } elseif ($user->isServiceProvider() && ! $user->serviceProvider?->isApproved()) {
            $message = $user->serviceProvider?->isRejected()
                ? 'Your service account has been rejected by the admin. Please contact support for more information.'
                : 'Your service account is pending admin approval. You will be able to log in once approved.';
        }

        if (! $message) {
            return null;
        }

        if ($logout) {
            Auth::logout();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    private function ensureMarketplaceProfile(User $user): void
    {
        if ($user->isVendor() && ! $user->vendor) {
            \App\Services\VendorRegistrationService::createProfileForUser($user);
            $user->load('vendor');
        }

        if ($user->isConsultant() && ! $user->consultant) {
            \App\Services\ConsultantRegistrationService::createProfileForUser($user);
            $user->load('consultant');
        }

        if ($user->isServiceProvider() && ! $user->serviceProvider) {
            \App\Services\ServiceProviderRegistrationService::createProfileForUser($user);
            $user->load('serviceProvider');
        }
    }

    private function isMarketplaceUser(User $user): bool
    {
        return $user->isVendor() || $user->isConsultant() || $user->isServiceProvider();
    }

    private function otpCacheKey(int $userId): string
    {
        return 'login_otp_'.$userId;
    }

    private function googleAuthCacheKey(string $token): string
    {
        return 'google_auth_state_'.$token;
    }

    private function storeGoogleAuthState(array $payload): string
    {
        $stateToken = str()->random(64);
        Cache::put($this->googleAuthCacheKey($stateToken), $payload, now()->addMinutes(20));

        return $stateToken;
    }

    private function resolveGoogleAuthState(Request $request): array
    {
        $stateToken = (string) $request->query('state', '');

        if ($stateToken !== '') {
            $cached = Cache::pull($this->googleAuthCacheKey($stateToken));
            if (is_array($cached) && $cached !== []) {
                return $cached;
            }
        }

        return [
            'intent' => $request->session()->pull('google_auth.intent', 'login'),
            'role' => $request->session()->pull('google_auth.role'),
            'registration' => $request->session()->pull('google_auth.registration', []),
        ];
    }

    private function applyGoogleRegistrationDetails(User $user, ?string $role, array $details): void
    {
        if ($details === [] || ! isset($details['whatsapp_number'], $details['address'], $details['city'], $details['pincode'], $details['date_of_birth'])) {
            return;
        }

        $details = $this->resolveGoogleRegistrationCoordinates($details);

        $fill = [
            'whatsapp_number' => $details['whatsapp_number'],
            'address' => $details['address'],
            'city' => $details['city'],
            'pincode' => $details['pincode'],
            'date_of_birth' => $details['date_of_birth'],
        ];

        if (isset($details['latitude'], $details['longitude'])) {
            $fill['latitude'] = $details['latitude'];
            $fill['longitude'] = $details['longitude'];
        }

        if (in_array($role, ['user', 'vendor', 'builder', 'developer', 'consultant', 'service_provider'], true)) {
            $fill['role'] = $role;
        }

        $user->forceFill($fill)->save();
    }

    private function resolveGoogleRegistrationCoordinates(array $details): array
    {
        if (filled($details['latitude'] ?? null) && filled($details['longitude'] ?? null)) {
            return $details;
        }

        $coordinates = GoogleGeocoder::coordinatesForAddress(
            (string) ($details['address'] ?? ''),
            isset($details['city']) ? (string) $details['city'] : null,
            isset($details['pincode']) ? (string) $details['pincode'] : null,
        );

        if ($coordinates['latitude'] !== null && $coordinates['longitude'] !== null) {
            $details['latitude'] = $coordinates['latitude'];
            $details['longitude'] = $coordinates['longitude'];
        }

        return $details;
    }
}

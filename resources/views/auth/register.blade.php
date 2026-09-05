@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
<section class="register-page-wrap">
    <div class="container">
        <div class="register-layout">
            <aside class="register-intro">
                <span class="intro-pill">SOILNWATER</span>
                <h1>Build your trusted marketplace profile.</h1>
                <p>Create your account to connect with verified agricultural buyers and sellers across local and national markets.</p>

                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Verified user onboarding</li>
                    <li><i class="fa-solid fa-circle-check"></i> Faster order and inquiry management</li>
                    <li><i class="fa-solid fa-circle-check"></i> Secure account access controls</li>
                </ul>
            </aside>

            <div class="card auth-card register-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Create Account</h2>
                    <p class="auth-subtitle">It only takes a minute to get started.</p>

                    <div id="registerAlert" class="alert d-none" role="alert"></div>

                    <form id="registerForm" method="POST" action="{{ route('register') }}" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input id="fullname" type="text" class="form-control @error('fullname') is-invalid @enderror" name="fullname" value="{{ old('fullname') }}" required autocomplete="name" autofocus>
                            @error('fullname')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input id="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" inputmode="numeric">
                            @error('phone_number')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="whatsapp_same_as_phone" name="whatsapp_same_as_phone" value="1" {{ old('whatsapp_same_as_phone', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsapp_same_as_phone">WhatsApp number is same as phone number</label>
                        </div>

                        <div class="mb-3 {{ old('whatsapp_same_as_phone', '1') ? 'd-none' : '' }}" id="whatsappNumberWrap">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input id="whatsapp_number" type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" name="whatsapp_number" value="{{ old('whatsapp_number') }}" autocomplete="tel" inputmode="numeric">
                            @error('whatsapp_number')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}" required autocomplete="street-address" placeholder="Search and select your address">
                            <small class="text-muted">Start typing and choose a Google address to auto-fill city and pincode. You can edit both fields.</small>
                            @error('address')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" required autocomplete="address-level2">
                                    @error('city')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input id="pincode" type="text" class="form-control @error('pincode') is-invalid @enderror" name="pincode" value="{{ old('pincode') }}" required autocomplete="postal-code">
                                    @error('pincode')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Select Role</label>
                            <select id="role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                                <option value="">Choose your role</option>
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                                <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <!-- <option value="builder" {{ old('role') === 'builder' ? 'selected' : '' }}>Builder</option>
                                <option value="developer" {{ old('role') === 'developer' ? 'selected' : '' }}>Developer</option> -->
                                <option value="consultant" {{ old('role') === 'consultant' ? 'selected' : '' }}>Consultant</option>
                                <option value="service_provider" {{ old('role') === 'service_provider' ? 'selected' : '' }}>Service</option>
                                <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                <option value="tutor" {{ old('role') === 'tutor' ? 'selected' : '' }}>Tutor</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div id="profileImageWrap" class="mb-3 {{ in_array(old('role'), ['user', 'vendor', 'consultant', 'service_provider', 'teacher', 'tutor'], true) ? '' : 'd-none' }}">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input id="profile_image" type="file" class="form-control @error('profile_image') is-invalid @enderror" name="profile_image" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Upload a JPG, PNG, or WebP image up to 2 MB. For vendors, consultants, services, teachers, and tutors, this image will also appear on the public profile.</small>
                            @error('profile_image')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div id="businessRegistrationFields" class="d-none">
                            <div class="mb-3">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input id="pan_number" type="text" class="form-control @error('pan_number') is-invalid @enderror" name="pan_number" value="{{ old('pan_number') }}">
                                @error('pan_number')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Do you have a GST number?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="has_gst" id="has_gst_no" value="0" {{ old('has_gst', '0') !== '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_gst_no">No</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="has_gst" id="has_gst_yes" value="1" {{ old('has_gst') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_gst_yes">Yes</label>
                                </div>
                                @error('has_gst')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3 d-none" id="gstNumberWrap">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input id="gst_number" type="text" class="form-control @error('gst_number') is-invalid @enderror" name="gst_number" value="{{ old('gst_number') }}">
                                @error('gst_number')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="government_certificate_number" class="form-label">Any Other Government Certificate Number</label>
                                <input id="government_certificate_number" type="text" class="form-control @error('government_certificate_number') is-invalid @enderror" name="government_certificate_number" value="{{ old('government_certificate_number') }}">
                                @error('government_certificate_number')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input id="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subYears(18)->toDateString() }}" required>
                            @error('date_of_birth')
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

                        <div class="form-check mb-3">
                            <input class="form-check-input @error('accept_terms') is-invalid @enderror" type="checkbox" value="1" id="accept_terms" name="accept_terms" required {{ old('accept_terms') ? 'checked' : '' }}>
                            <label class="form-check-label" for="accept_terms">
                                I accept the
                                <a href="{{ route('frontend.terms.show', ['moduleKey' => 'register']) }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                            </label>
                            @error('accept_terms')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button id="registerSubmitBtn" type="submit" class="btn btn-primary btn-create auth-action-btn js-auto-loader">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loader d-none" aria-hidden="true"></span>
                        </button>

                        <p class="signin-copy">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                        <p class="signin-copy mt-2 mb-0">Joining as staff? <a href="{{ route('employee.register') }}">Employee sign up</a></p>
                    </form>
                    <div class="auth-divider"><span>or</span></div>
                    <button type="button" class="btn btn-google w-100" id="googleRegisterTrigger">
                        <i class="fa-brands fa-google me-2"></i> Continue with Google
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="modal fade role-picker-modal" id="googleRoleModal" tabindex="-1" aria-labelledby="googleRoleModalLabel" aria-hidden="true" data-bs-focus="false" data-open-on-error="{{ old('google_registration') === '1' && ($errors->has('role') || $errors->has('phone_number') || $errors->has('whatsapp_number') || $errors->has('address') || $errors->has('city') || $errors->has('pincode') || $errors->has('date_of_birth')) ? 'true' : 'false' }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="googleRoleModalLabel">Continue with Google</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="role-picker-subtitle">Choose your role and add the required details before continuing with Google.</p>

                <form id="googleRegisterRoleForm" method="GET" action="{{ route('register.google') }}">
                    <input type="hidden" name="google_registration" value="1">
                    <div class="mb-3">
                        <label for="google_role" class="form-label">Select Role</label>
                        <select id="google_role" class="form-select @error('role') is-invalid @enderror" name="role" required>
                            <option value="">Choose your role</option>
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                            <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                            <option value="builder" {{ old('role') === 'builder' ? 'selected' : '' }}>Builder</option>
                            <option value="developer" {{ old('role') === 'developer' ? 'selected' : '' }}>Developer</option>
                            <option value="consultant" {{ old('role') === 'consultant' ? 'selected' : '' }}>Consultant</option>
                            <option value="service_provider" {{ old('role') === 'service_provider' ? 'selected' : '' }}>Service</option>
                            <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="tutor" {{ old('role') === 'tutor' ? 'selected' : '' }}>Tutor</option>
                        </select>
                        @error('role')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="google_phone_number" class="form-label">Phone Number</label>
                        <input id="google_phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" inputmode="numeric">
                        @error('phone_number')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="google_whatsapp_same_as_phone" name="whatsapp_same_as_phone" value="1" {{ old('whatsapp_same_as_phone', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="google_whatsapp_same_as_phone">WhatsApp number is same as phone number</label>
                    </div>

                    <div class="mb-3 {{ old('whatsapp_same_as_phone', '1') ? 'd-none' : '' }}" id="googleWhatsappNumberWrap">
                        <label for="google_whatsapp_number" class="form-label">WhatsApp Number</label>
                        <input id="google_whatsapp_number" type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" name="whatsapp_number" value="{{ old('whatsapp_number') }}" autocomplete="tel" inputmode="numeric">
                        @error('whatsapp_number')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="google_address" class="form-label">Address</label>
                        <input id="google_address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address') }}" required autocomplete="street-address" placeholder="Search and select your address">
                        <small class="text-muted">Start typing and choose a Google address to auto-fill city and pincode, or type your address manually. You can edit city and pincode.</small>
                        <input type="hidden" id="google_latitude" name="latitude" value="{{ old('latitude') }}">
                        <input type="hidden" id="google_longitude" name="longitude" value="{{ old('longitude') }}">
                        @error('address')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="google_city" class="form-label">City</label>
                                <input id="google_city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" required autocomplete="address-level2">
                                @error('city')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="google_pincode" class="form-label">Pincode</label>
                                <input id="google_pincode" type="text" class="form-control @error('pincode') is-invalid @enderror" name="pincode" value="{{ old('pincode') }}" required autocomplete="postal-code">
                                @error('pincode')
                                    <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="google_date_of_birth" class="form-label">Date of Birth</label>
                        <input id="google_date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ old('date_of_birth') }}" max="{{ now()->subYears(18)->toDateString() }}" required>
                        @error('date_of_birth')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-auth-secondary flex-fill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="googleRoleContinueBtn" class="btn btn-google role-continue-btn flex-fill" disabled>
                            <i class="fa-brands fa-google me-2"></i> Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@endpush

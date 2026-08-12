@extends('frontend.layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ now()->timestamp }}">
@endpush

@section('content')
<section class="register-page-wrap">
    <div class="container">
        <div class="register-layout">
            <aside class="register-intro">
                <span class="intro-pill">SOILNWATER</span>
                <h1>Almost there — complete your profile.</h1>
                <p>Your Google email is verified. Add the required account details below to finish creating your SoilnWater profile.</p>

                <ul class="intro-points">
                    <li><i class="fa-solid fa-circle-check"></i> Secure Google sign-in</li>
                    <li><i class="fa-solid fa-circle-check"></i> Verified marketplace onboarding</li>
                    <li><i class="fa-solid fa-circle-check"></i> Email verified through Google</li>
                </ul>
            </aside>

            <div class="card auth-card register-form-card">
                <div class="card-body">
                    <h2 class="auth-title">Complete Your Profile</h2>
                    <p class="auth-subtitle">Signed in with Google as <strong>{{ $email }}</strong></p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
                    @endif

                    @if ($errors->has('google'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('google') }}</div>
                    @endif

                    <form id="googleCompleteForm" method="POST" action="{{ route('register.google.complete.store') }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input id="fullname" type="text" class="form-control @error('fullname') is-invalid @enderror" name="fullname" value="{{ $fullname }}" required autocomplete="name" autofocus>
                            @error('fullname')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="{{ $email }}" readonly disabled>
                            <small class="text-muted">This email is verified through Google and cannot be changed here.</small>
                        </div>

                        <div class="mb-3">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input id="whatsapp_number" type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" name="whatsapp_number" value="{{ $whatsappNumber }}" required autocomplete="tel">
                            @error('whatsapp_number')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input id="address" type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ $address }}" required autocomplete="street-address" placeholder="Search and select your address">
                            <input type="hidden" id="complete_latitude" name="latitude" value="{{ $latitude }}">
                            <input type="hidden" id="complete_longitude" name="longitude" value="{{ $longitude }}">
                            <small class="text-muted">Start typing and choose a Google address to auto-fill city and pincode. You can edit both fields.</small>
                            @error('address')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ $city }}" required autocomplete="address-level2">
                                    @error('city')
                                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pincode" class="form-label">Pincode</label>
                                    <input id="pincode" type="text" class="form-control @error('pincode') is-invalid @enderror" name="pincode" value="{{ $pincode }}" required autocomplete="postal-code">
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
                                <option value="user" {{ $role === 'user' ? 'selected' : '' }}>User</option>
                                <option value="vendor" {{ $role === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="consultant" {{ $role === 'consultant' ? 'selected' : '' }}>Consultant</option>
                                <option value="service_provider" {{ $role === 'service_provider' ? 'selected' : '' }}>Service</option>
                            </select>
                            @error('role')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div id="profileImageWrap" class="mb-3 {{ in_array($role, ['user', 'vendor', 'consultant', 'service_provider'], true) ? '' : 'd-none' }}">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input id="profile_image" type="file" class="form-control @error('profile_image') is-invalid @enderror" name="profile_image" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">Upload a JPG, PNG, or WebP image up to 2 MB.</small>
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
                            <input id="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" name="date_of_birth" value="{{ $dateOfBirth }}" max="{{ now()->subYears(18)->toDateString() }}" required>
                            @error('date_of_birth')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
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

                        <button type="submit" class="btn btn-primary btn-create auth-action-btn w-100">
                            Create Account
                        </button>

                        <p class="signin-copy mt-3 mb-0">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@endpush

@php
    $profile = $profile ?? null;
    $showMarketplaceFields = $showMarketplaceFields ?? false;
    $hasGstValue = old('has_gst', $profile?->gst_number ? '1' : '0');
@endphp

<div class="col-md-6">
    <label for="name" class="form-label">Full Name</label>
    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label for="email" class="form-label">Email Address</label>
    <input id="email" name="email" type="email" class="form-control" value="{{ $user->email }}" readonly>
    <small class="text-muted">Email cannot be changed from this profile page.</small>
</div>

<div class="col-md-6">
    <label for="phone_number" class="form-label">Phone Number</label>
    <input id="phone_number" name="phone_number" type="tel" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}" required autocomplete="tel">
    <small class="text-muted">Changing this number will require phone verification on your next login.</small>
    @error('phone_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
    <input id="whatsapp_number" name="whatsapp_number" type="tel" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $user->whatsapp_number) }}" required autocomplete="tel">
    @error('whatsapp_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-12">
    <label for="address" class="form-label">Address</label>
    <input id="address" name="address" type="text" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address) }}" required autocomplete="street-address">
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="city" class="form-label">City</label>
    <input id="city" name="city" type="text" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $user->city) }}" required autocomplete="address-level2">
    @error('city')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="pincode" class="form-label">Pincode</label>
    <input id="pincode" name="pincode" type="text" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $user->pincode) }}" required autocomplete="postal-code">
    @error('pincode')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-4">
    <label for="date_of_birth" class="form-label">Date of Birth</label>
    <input id="date_of_birth" name="date_of_birth" type="date" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" max="{{ now()->subYears(18)->toDateString() }}" required>
    @error('date_of_birth')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if ($showMarketplaceFields)
    <div class="col-md-6">
        <label for="pan_number" class="form-label">PAN Number</label>
        <input id="pan_number" name="pan_number" type="text" class="form-control @error('pan_number') is-invalid @enderror" value="{{ old('pan_number', $profile?->pan_number) }}" required>
        @error('pan_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label d-block">Do you have GST?</label>
        <div class="d-flex gap-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="has_gst" id="has_gst_yes" value="1" {{ $hasGstValue === '1' ? 'checked' : '' }} required>
                <label class="form-check-label" for="has_gst_yes">Yes</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="has_gst" id="has_gst_no" value="0" {{ $hasGstValue !== '1' ? 'checked' : '' }} required>
                <label class="form-check-label" for="has_gst_no">No</label>
            </div>
        </div>
        @error('has_gst')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 js-gst-number-field">
        <label for="gst_number" class="form-label">GST Number</label>
        <input id="gst_number" name="gst_number" type="text" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $profile?->gst_number) }}">
        @error('gst_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="government_certificate_number" class="form-label">Other Government Certificate Number</label>
        <input id="government_certificate_number" name="government_certificate_number" type="text" class="form-control @error('government_certificate_number') is-invalid @enderror" value="{{ old('government_certificate_number', $profile?->government_certificate_number) }}">
        @error('government_certificate_number')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endif

<div class="col-md-6">
    <label for="password" class="form-label">Password</label>
    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-6">
    <label for="password_confirmation" class="form-label">Confirm Password</label>
    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
</div>

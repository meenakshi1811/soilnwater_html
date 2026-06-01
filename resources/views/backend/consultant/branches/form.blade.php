@extends('backend.layouts.app')

@section('title', $branch->exists ? 'Manage Branch' : 'Add Branch')

@section('content')
<div class="admin-panel ems-page consultant-branch-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="ems-kicker mb-1">Consultant Panel</p>
            <h2 class="admin-title mb-0">{{ $branch->exists ? 'Manage Branch' : 'Add Branch' }}</h2>
            <p class="text-secondary small mb-0">Update details for your branch / company location</p>
        </div>
        <a href="{{ route('consultant.branches.index') }}" class="btn btn-light">Back to list</a>
    </div>

    <form id="consultant-branch-form" method="POST" action="{{ $branch->exists ? route('consultant.branches.update', $branch) : route('consultant.branches.store') }}">
        @csrf
        @if($branch->exists) @method('PUT') @endif

        <div class="consultant-form-card mb-4">
            <h5 class="consultant-form-card-title"><span>1</span> Basic Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Branch / Company Name</label>
                    <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $branch->branch_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $branch->contact_person) }}">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="isPrimary" {{ old('is_primary', $branch->is_primary) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isPrimary">Primary branch</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="consultant-form-card mb-4">
            <h5 class="consultant-form-card-title"><span>2</span> Contact &amp; Location</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}" placeholder="+91">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Alternative Mobile Number</label>
                    <input type="text" name="alt_mobile_number" class="form-control" value="{{ old('alt_mobile_number', $branch->alt_mobile_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $branch->whatsapp) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $branch->email) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Full Address *</label>
                    <input type="text" id="address" name="address" class="form-control" value="{{ old('address', $branch->address) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">City *</label>
                    <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $branch->city) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">State *</label>
                    <input type="text" id="state" name="state" class="form-control" value="{{ old('state', $branch->state) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode *</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" value="{{ old('pincode', $branch->pincode) }}" required>
                </div>
            </div>
        </div>

        <div class="consultant-form-card mb-4">
            <h5 class="consultant-form-card-title"><span>3</span> Legal &amp; Info</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">PAN Number *</label>
                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $branch->pan_number) }}" required>
                </div>
                @php($hasGst = old('has_gst', filled($branch->gst_number) ? '1' : '0'))
                <div class="col-md-6">
                    <label class="form-label d-block">Do you have a GST number? *</label>
                    <div class="d-flex gap-4 pt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_gst" id="hasGstYes" value="1" {{ $hasGst === '1' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="hasGstYes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="has_gst" id="hasGstNo" value="0" {{ $hasGst === '0' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="hasGstNo">No</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6" id="gstNumberField">
                    <label class="form-label">GST Number *</label>
                    <input type="text" name="gst_number" id="gstNumber" class="form-control" value="{{ old('gst_number', $branch->gst_number) }}">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-3">{{ $branch->exists ? 'Update Branch Details' : 'Create Branch' }}</button>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };

function getAddressPart(components, type) {
    const found = (components || []).find((c) => (c.types || []).includes(type));
    return found ? found.long_name : '';
}

function initBranchAddressAutocomplete() {
    const address = document.getElementById('address');
    if (!address || !window.google?.maps?.places) return;
    const autocomplete = new google.maps.places.Autocomplete(address, {
        fields: ['formatted_address', 'address_components'],
    });

    autocomplete.addListener('place_changed', function () {
        const place = autocomplete.getPlace();
        const components = place.address_components || [];
        document.getElementById('city').value = getAddressPart(components, 'locality') || getAddressPart(components, 'administrative_area_level_2');
        document.getElementById('state').value = getAddressPart(components, 'administrative_area_level_1');
        document.getElementById('pincode').value = getAddressPart(components, 'postal_code');
    });
}

$(function () {
    const $form = $('#consultant-branch-form');
    const $gstField = $('#gstNumberField');
    const $gstInput = $('#gstNumber');

    function toggleGstField() {
        const hasGst = $('input[name="has_gst"]:checked').val() === '1';
        $gstField.toggleClass('d-none', !hasGst);
        $gstInput.prop('required', hasGst);
        if (!hasGst) {
            $gstInput.val('');
        }
    }

    $('input[name="has_gst"]').on('change', toggleGstField);
    toggleGstField();
    $form.on('submit', function (e) {
        e.preventDefault();
        const $btn = $form.find('button[type="submit"]');
        const original = $btn.text();
        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: $form.attr('action'),
            method: '{{ $branch->exists ? 'PUT' : 'POST' }}',
            data: $form.serialize(),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (resp) {
                toastr.success(resp.message || 'Saved successfully.');
                setTimeout(() => window.location.href = resp.redirect || '{{ route('consultant.branches.index') }}', 900);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Unable to save branch.';
                toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false).text(original);
            }
        });
    });
});
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initBranchAddressAutocomplete"></script>
@endpush


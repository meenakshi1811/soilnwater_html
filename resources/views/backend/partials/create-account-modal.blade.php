@php
    use App\Support\AccountCreation;
    use App\Support\AuthActor;

    $portalActor = AuthActor::user();
    $portalIsAdmin = AccountCreation::portalActorIsAdmin($portalActor);
    $allowedCreateRoles = [];

    foreach ([
        'user' => 'User',
        'vendor' => 'Vendor',
        'consultant' => 'Consultant',
        'service_provider' => 'Service Provider',
        'teacher' => 'Teacher / Tutor',
    ] as $roleKey => $roleLabel) {
        if (AccountCreation::canCreateRole($portalActor, $portalIsAdmin, $roleKey)) {
            $allowedCreateRoles[$roleKey] = $roleLabel;
        }
    }
@endphp

<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content ems-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalTitle">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createUserForm" class="create-user-form" method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" novalidate>
                @csrf
                <input type="hidden" name="role" id="createRoleHidden" value="" disabled>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">Create accounts with the same fields as public registration. Email and phone are verified automatically and no notification email is sent.</p>
                    <div id="createUserAlert" class="alert d-none" role="alert"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="createFullname">Full Name</label>
                            <input type="text" name="fullname" id="createFullname" class="form-control" autocomplete="name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="createEmail">Email Address</label>
                            <input type="email" name="email" id="createEmail" class="form-control" autocomplete="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="createPhone">Phone Number</label>
                            <input type="tel" name="phone_number" id="createPhone" class="form-control" autocomplete="tel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="createWhatsapp">WhatsApp Number</label>
                            <input type="tel" name="whatsapp_number" id="createWhatsapp" class="form-control" autocomplete="tel">
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="createAddress">Address</label>
                            <input type="text" name="address" id="createAddress" class="form-control" autocomplete="off" placeholder="Search and select your address">
                            <input type="hidden" name="latitude" id="createLatitude" value="">
                            <input type="hidden" name="longitude" id="createLongitude" value="">
                            <small class="text-muted">Start typing and choose a Google address to auto-fill city and pincode. You can edit both fields.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="createCity">City</label>
                            <input type="text" name="city" id="createCity" class="form-control" autocomplete="address-level2">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="createPincode">Pincode</label>
                            <input type="text" name="pincode" id="createPincode" class="form-control" autocomplete="postal-code">
                        </div>
                        <div class="col-md-4" id="createRoleWrap">
                            <label class="form-label" for="createRole">Role</label>
                            <select id="createRole" class="form-select">
                                <option value="">Choose role</option>
                                @foreach($allowedCreateRoles as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}">{{ $roleLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="createProfileImageWrap">
                            <label class="form-label" for="createProfileImage">Profile Image <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="file" name="profile_image" id="createProfileImage" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG, or WebP up to 2 MB. Leave empty to skip.</small>
                        </div>
                        <div class="col-md-6" id="createDateOfBirthWrap">
                            <label class="form-label" for="createDateOfBirth">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="createDateOfBirth" class="form-control" max="{{ now()->subYears(18)->toDateString() }}">
                        </div>
                        <div class="col-12 d-none" id="createBusinessFields">
                            <div class="row g-3">
                                <div class="col-md-6" id="createDateOfIncorporationWrap">
                                    <label class="form-label" for="createDateOfIncorporation">Date of Incorporation</label>
                                    <input type="date" name="date_of_incorporation" id="createDateOfIncorporation" class="form-control" max="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="createPanNumber">PAN Number</label>
                                    <input type="text" name="pan_number" id="createPanNumber" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label d-block">Do you have a GST number?</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="has_gst" id="createHasGstNo" value="0" checked>
                                        <label class="form-check-label" for="createHasGstNo">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="has_gst" id="createHasGstYes" value="1">
                                        <label class="form-check-label" for="createHasGstYes">Yes</label>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none" id="createGstNumberWrap">
                                    <label class="form-label" for="createGstNumber">GST Number</label>
                                    <input type="text" name="gst_number" id="createGstNumber" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="createCertificateNumber">Government Certificate Number</label>
                                    <input type="text" name="government_certificate_number" id="createCertificateNumber" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="createPassword">Password</label>
                            <input type="password" name="password" id="createPassword" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="createPasswordConfirmation">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="createPasswordConfirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="createUserSubmitBtn" class="btn btn-primary ems-btn-primary">
                        <span class="btn-text">Create User</span>
                        <span class="btn-loader d-none" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

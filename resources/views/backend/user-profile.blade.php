@extends('backend.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="admin-panel admin-profile-wrap">
    <h1 class="admin-title mb-3">Update Profile</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card admin-table-card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <h5 class="mb-1">Grow your presence on SoilNWater</h5>
                <p class="mb-0 text-secondary">Become a vendor, consultant, or service provider. Your request will be sent to the admin team for approval before the portal is unlocked.</p>
            </div>
            <div class="user-profile-become-actions">
                <form method="POST" action="{{ route('user.convert-to-vendor') }}" class="js-become-account-form"
                    data-title="Become a vendor?"
                    data-text="Become a vendor with your user profile and send it to admin for approval?"
                    data-success="Your vendor request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Vendor</button>
                </form>
                <form method="POST" action="{{ route('user.convert-to-consultant') }}" class="js-become-account-form"
                    data-title="Become a consultant?"
                    data-text="Become a consultant with your user profile and send it to admin for approval?"
                    data-success="Your consultant request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Consultant</button>
                </form>
                <form method="POST" action="{{ route('user.convert-to-service-provider') }}" class="js-become-account-form"
                    data-title="Become a service provider?"
                    data-text="Become a service provider with your user profile and send it to admin for approval?"
                    data-success="Your service provider request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Service Provider</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card admin-table-card">
        <div class="card-body">
            <div id="userProfileAlert" class="alert d-none" role="alert"></div>

            <form id="userProfileForm" method="POST" action="{{ route('user.profile.update') }}" class="row g-3" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="col-12">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input id="profile_image" type="file" name="profile_image" class="form-control @error('profile_image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Upload a JPG, PNG, or WebP image up to 2 MB.</small>
                    @if($user->profile_image)
                        <div class="mt-2">
                            <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}" width="80" height="80" class="rounded-circle object-fit-cover">
                        </div>
                    @endif
                    @error('profile_image')
                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                @include('backend.partials.registration-profile-fields', ['showMarketplaceFields' => false])

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">Back</a>
                    <button id="userProfileSubmitBtn" type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .user-profile-become-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .user-profile-become-actions form {
        margin: 0;
        display: flex;
        flex: 0 0 auto;
    }

    .user-profile-become-actions .btn {
        width: auto;
        white-space: nowrap;
        font-size: 0.84rem;
        padding: 0.5rem 0.85rem;
    }

    @media (max-width: 575.98px) {
        .user-profile-become-actions {
            flex-direction: column;
        }

        .user-profile-become-actions form,
        .user-profile-become-actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
    $(function () {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3500
        };

        $('.js-become-account-form').on('submit', function (event) {
            event.preventDefault();

            var form = this;
            var $form = $(form);
            var $submitButton = $form.find('button[type="submit"]');

            Swal.fire({
                title: $form.data('title'),
                text: $form.data('text'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, become',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $submitButton.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        Accept: 'application/json'
                    }
                }).done(function (response) {
                    toastr.success(response.message || $form.data('success'));

                    if (response.redirect) {
                        setTimeout(function () {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                }).fail(function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Unable to submit your request right now. Please try again.';

                    toastr.error(message);
                    $submitButton.prop('disabled', false);
                });
            });
        });
    });
</script>
@endpush

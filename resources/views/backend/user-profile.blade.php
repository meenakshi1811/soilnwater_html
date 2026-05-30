@extends('backend.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="admin-panel admin-profile-wrap">
    <h1 class="admin-title mb-3">Update Profile</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card admin-table-card mb-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Want to sell on SoilNWater?</h5>
                <p class="mb-0 text-secondary">Convert your profile to a vendor account. Your vendor profile will be sent to the admin team for approval before the vendor portal is unlocked.</p>
            </div>
            <form method="POST" action="{{ route('user.convert-to-vendor') }}" class="js-convert-vendor-form">
                @csrf
                <button type="submit" class="btn btn-outline-primary">Convert to Vendor</button>
            </form>
        </div>
    </div>

    <div class="card admin-table-card">
        <div class="card-body">
            <div id="userProfileAlert" class="alert d-none" role="alert"></div>

            <form id="userProfileForm" method="POST" action="{{ route('user.profile.update') }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input id="phone_number" name="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}">
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" class="form-control" value="{{ $user->email }}" disabled>
                </div>

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
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
<script>
    $(function () {
        $('.js-convert-vendor-form').on('submit', function (event) {
            event.preventDefault();

            var form = this;

            Swal.fire({
                title: 'Convert to vendor?',
                text: 'Convert your user profile to a vendor account and send it to admin for approval?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, convert',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush

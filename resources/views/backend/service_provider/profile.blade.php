@extends('backend.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="admin-panel admin-profile-wrap">
    <h1 class="admin-title mb-3">Update Profile</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card admin-table-card">
        <div class="card-body">
            <div id="service_providerProfileAlert" class="alert d-none" role="alert"></div>

            <form id="service_providerProfileForm" method="POST" action="{{ route('service_provider.profile.update') }}" class="row g-3">
                @csrf
                @method('PUT')

                @include('backend.partials.registration-profile-fields', ['profile' => $service_provider, 'showMarketplaceFields' => true])

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('service_provider.dashboard') }}" class="btn btn-outline-secondary">Back</a>
                    <button id="service_providerProfileSubmitBtn" type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('assets/js/form.js') }}?v={{ now()->timestamp }}"></script>
@endpush

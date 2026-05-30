@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel dashboard-modern user-dashboard-panel">
    <div class="dashboard-hero mb-4">
        <div>
            <h2 class="admin-title mb-1">User Dashboard</h2>
            <p class="mb-0">Your listings and activity at a glance.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.profile.edit') }}" class="btn btn-primary">Update Profile</a>
            <form method="POST" action="{{ route('user.convert-to-vendor') }}" class="js-convert-vendor-form">
                @csrf
                <button type="submit" class="btn btn-outline-primary">Become a Vendor</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="section-label">Overview</div>
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card ads">
                <span>Total Ads</span>
                <h3>{{ number_format($totalAds) }}</h3>
                <small>Your posted advertisements</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card offers">
                <span>Total Offers</span>
                <h3>{{ number_format($totalOffers) }}</h3>
                <small>Active offers you have shared</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="modern-stat-card products">
                <span>Total Products</span>
                <h3>{{ number_format($totalProducts) }}</h3>
                <small>Items in your catalog</small>
            </div>
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

@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="admin-panel dashboard-modern user-dashboard-panel">
    <div class="dashboard-hero mb-4 user-dashboard-hero">
        <div class="user-dashboard-hero__intro">
            <h2 class="admin-title mb-1">User Dashboard</h2>
            <p class="mb-0">Your listings and activity at a glance.</p>
        </div>
        <div class="user-dashboard-hero__actions">
            <a href="{{ route('user.profile.edit') }}" class="btn btn-primary user-dashboard-hero__profile-btn">Update Profile</a>
            <div class="user-dashboard-hero__convert-actions">
                <form method="POST" action="{{ route('user.convert-to-vendor') }}" class="js-convert-account-form"
                    data-title="Convert to vendor?"
                    data-text="Convert your user profile to a vendor account and send it to admin for approval?"
                    data-success="Your vendor conversion request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Vendor</button>
                </form>
                <form method="POST" action="{{ route('user.convert-to-consultant') }}" class="js-convert-account-form"
                    data-title="Convert to consultant?"
                    data-text="Convert your user profile to a consultant account and send it to admin for approval?"
                    data-success="Your consultant conversion request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Consultant</button>
                </form>
                <form method="POST" action="{{ route('user.convert-to-service-provider') }}" class="js-convert-account-form"
                    data-title="Convert to service?"
                    data-text="Convert your user profile to a service account and send it to admin for approval?"
                    data-success="Your service conversion request has been sent for approval.">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Become a Service</button>
                </form>
            </div>
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .user-dashboard-hero {
        align-items: flex-start;
        gap: 1rem;
    }

    .user-dashboard-hero__intro {
        flex: 1 1 240px;
        min-width: 0;
    }

    .user-dashboard-hero__actions {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 0.65rem;
        flex: 0 1 520px;
        width: min(100%, 520px);
    }

    .user-dashboard-hero__profile-btn {
        align-self: flex-end;
        min-width: 160px;
    }

    .user-dashboard-hero__convert-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
    }

    .user-dashboard-hero__convert-actions form {
        margin: 0;
        display: flex;
        min-width: 0;
    }

    .user-dashboard-hero__convert-actions .btn {
        width: 100%;
        white-space: nowrap;
        font-size: 0.84rem;
        padding: 0.5rem 0.7rem;
    }

    @media (max-width: 991.98px) {
        .user-dashboard-hero__actions {
            width: 100%;
            flex-basis: 100%;
        }

        .user-dashboard-hero__profile-btn {
            align-self: stretch;
        }
    }

    @media (max-width: 575.98px) {
        .user-dashboard-hero__convert-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(function () {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 3500
        };

        $('.js-convert-account-form').on('submit', function (event) {
            event.preventDefault();

            var form = this;
            var $form = $(form);
            var $submitButton = $form.find('button[type="submit"]');

            Swal.fire({
                title: $form.data('title'),
                text: $form.data('text'),
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, convert',
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
                        : 'Unable to convert your profile right now. Please try again.';

                    toastr.error(message);
                    $submitButton.prop('disabled', false);
                });
            });
        });
    });
</script>
@endpush

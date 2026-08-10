@extends('frontend.layouts.app')

@hasSection('title')
    @section('meta_title', trim($__env->yieldContent('title')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="{{ asset('assets/css/premium-page.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $consultantCategories = $consultantCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your consultant is published.</div>
    @endif

    @include('frontend.consultant.partials.store-header', [
        'consultant' => $consultant,
        'consultantCategories' => $consultantCategories,
        'activeNav' => $activeNav ?? '',
    ])

    @include('frontend.partials.marketplace-store-quicknav', [
        'storeHomeUrl' => route('consultant.show', $consultant->slug),
        'storeHomeLabel' => 'Consultant Home',
        'activeNav' => $activeNav ?? '',
    ])

    @include('frontend.premium.partials.profile-status', ['profile' => $consultant, 'type' => 'consultant'])

    @yield('consultant_content')

    @auth
        @if((int) auth()->id() !== (int) $consultant->user_id)
            @include('frontend.partials.profile-report-modal', [
                'reportModalId' => 'consultantReportModal',
                'reportFormId' => 'consultantReportForm',
                'reportLabel' => 'Consultant',
                'reportAction' => route('consultant.report', $consultant->slug),
            ])
        @endif
    @endauth

    @include('frontend.consultant.partials.store-footer', ['consultant' => $consultant])
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>window.jQuery||document.write('<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"><\/script>');</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>if(window.toastr){window.toastr.options={closeButton:true,progressBar:true,positionClass:'toast-top-right',timeOut:4000,extendedTimeOut:2000};}</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }
        alert(message);
    }

    document.querySelectorAll('.profile-report-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok) {
                            const errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
                            throw new Error(errors || payload.message || 'Unable to submit report.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    const modalEl = form.closest('.modal');
                    form.reset();
                    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    notify('success', payload.message || 'Report submitted successfully.');
                })
                .catch(function (error) {
                    notify('error', error.message || 'Unable to submit report.');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
        });
    });

    document.querySelectorAll('.consultant-service-enquiry-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok) {
                            const errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
                            throw new Error(errors || payload.message || 'Unable to submit enquiry.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    const successMessage = payload.message || 'Enquiry submitted successfully.';
                    const modalEl = form.closest('.modal');

                    form.reset();

                    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                        let toastShown = false;
                        const showSuccessToast = function () {
                            if (toastShown) return;
                            toastShown = true;
                            modalEl.removeEventListener('hidden.bs.modal', showSuccessToast);
                            notify('success', successMessage);
                        };

                        modalEl.addEventListener('hidden.bs.modal', showSuccessToast);
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                        window.setTimeout(showSuccessToast, 700);
                    } else {
                        notify('success', successMessage);
                    }
                })
                .catch(function (error) {
                    notify('error', error.message || 'Unable to submit enquiry.');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
        });
    });
});
</script>
<script src="{{ asset('assets/js/vendor-store.js') }}?v={{ now()->timestamp }}" defer></script>
@stack('consultant_scripts')
@endpush

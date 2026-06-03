@extends('frontend.layouts.app')

@hasSection('title')
    @section('meta_title', trim($__env->yieldContent('title')))
@endif

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-store.css') }}?v={{ now()->timestamp }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@php
    $service_providerCategories = $service_providerCategories ?? collect();
@endphp
<div class="vendor-store-page">
    @if(!empty($preview))
        <div class="vendor-preview-banner">Preview mode — only you can see this until your service_provider is published.</div>
    @endif

    @include('frontend.service_provider.partials.store-header', [
        'service_provider' => $service_provider,
        'service_providerCategories' => $service_providerCategories,
        'activeNav' => $activeNav ?? '',
    ])

    @yield('service_provider_content')

    @include('frontend.service_provider.partials.store-footer', ['service_provider' => $service_provider])
</div>
@endsection

@push('scripts')
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

    document.querySelectorAll('.service_provider-service-enquiry-form').forEach(function (form) {
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
                    notify('success', payload.message || 'Enquiry submitted successfully.');
                    form.reset();
                    const modal = bootstrap.Modal.getInstance(form.closest('.modal'));
                    if (modal) modal.hide();
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
@stack('service_provider_scripts')
@endpush

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>window.jQuery||document.write('<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"><\/script>');</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>if(window.toastr){window.toastr.options={closeButton:true,progressBar:true,positionClass:'toast-top-right',timeOut:4000,extendedTimeOut:2000};}</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function notify(type, message) {
        const toastType = type === 'danger' ? 'error' : type;

        if (window.toastr && typeof window.toastr[toastType] === 'function') {
            window.toastr[toastType](message);
            return;
        }

        let container = document.getElementById('serviceProviderToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'serviceProviderToastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '11000';
            document.body.appendChild(container);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast align-items-center border-0 text-bg-' + (toastType === 'error' ? 'danger' : toastType);
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        toastEl.querySelector('.toast-body').textContent = message;
        container.appendChild(toastEl);

        if (window.bootstrap && window.bootstrap.Toast) {
            window.bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 4000 }).show();
            toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
        } else {
            toastEl.classList.add('show');
            window.setTimeout(function () { toastEl.remove(); }, 4000);
        }
    }

    function closeModalsThenNotify(sourceModal, message) {
        const openModals = Array.from(document.querySelectorAll('.modal.show'));
        if (sourceModal && !openModals.includes(sourceModal)) {
            openModals.push(sourceModal);
        }

        if (!openModals.length || !window.bootstrap || !window.bootstrap.Modal) {
            notify('success', message);
            return;
        }

        let pending = openModals.length;
        let notified = false;
        const finish = function () {
            if (notified) return;
            notified = true;
            document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) { backdrop.remove(); });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            notify('success', message);
        };

        openModals.forEach(function (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                pending -= 1;
                if (pending <= 0) finish();
            }, { once: true });

            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        });

        window.setTimeout(finish, 900);
    }

    document.addEventListener('submit', function (event) {
        const form = event.target.closest('.service_provider-service-enquiry-form');
        if (!form) return;

        event.preventDefault();
        if (form.dataset.submitting === '1') return;
        form.dataset.submitting = '1';

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
                        throw new Error(errors || payload.message || 'Unable to submit enquiry.');
                    }
                    return payload;
                });
            })
            .then(function (payload) {
                const successMessage = payload.message || 'Enquiry submitted successfully.';
                const modalEl = form.closest('.modal');

                form.reset();
                closeModalsThenNotify(modalEl, successMessage);
            })
            .catch(function (error) {
                notify('error', error.message || 'Unable to submit enquiry.');
            })
            .finally(function () {
                form.dataset.submitting = '0';
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
    });
});
</script>
<script src="{{ asset('assets/js/vendor-store.js') }}?v={{ now()->timestamp }}" defer></script>
@stack('service_provider_scripts')
@endpush

@extends('backend.layouts.app')

@section('title', 'Page Settings')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <h2 class="admin-title mb-1">Page Settings</h2>
        <p class="mb-0 text-secondary">Manage homepage, offers market, and ads market banners from one place.</p>
    </div>

    <div class="col-12">
        <ul class="nav nav-tabs" id="pageSettingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage-settings" type="button" role="tab">Homepage Settings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers-settings" type="button" role="tab">Offer Page Settings</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads-settings" type="button" role="tab">Ads Page Settings</button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-3 p-md-4" id="pageSettingsTabsContent">
            <div class="tab-pane fade show active" id="homepage-settings" role="tabpanel">
                <form class="ajax-page-settings-form row g-3" method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="setting_type" value="homepage">
                    <div class="col-12">
                        <label class="form-label">Hero Banner Image</label>
                        <input type="file" name="hero_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                        @if($setting->hero_banner_image)
                            <img src="{{ asset($setting->hero_banner_image) }}" alt="Hero banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Button Text <span class="text-danger">*</span></label>
                        <input type="text" name="hero_button_text" class="form-control" value="{{ $setting->hero_button_text ?? 'Advertise Now' }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero Button Link <span class="text-danger">*</span></label>
                        <input type="text" name="hero_button_link" class="form-control" value="{{ $setting->hero_button_link ?? '#' }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Enable/Disable Homepage Sections</label>
                        <div class="row g-2">
                            @foreach($sections as $key => $label)
                                <div class="col-md-4">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sections[]" value="{{ $key }}" {{ data_get($setting->section_toggles, $key, true) ? 'checked' : '' }}>
                                        <span class="form-check-label">{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Send Enquiry To <span class="text-danger">*</span></label>
                        <select name="vendor_enquiry_send_to" class="form-select" required>
                            <option value="all" {{ ($setting->vendor_enquiry_send_to ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                            <option value="non_premium" {{ ($setting->vendor_enquiry_send_to ?? 'all') === 'non_premium' ? 'selected' : '' }}>Non Premium</option>
                            <option value="premium" {{ ($setting->vendor_enquiry_send_to ?? 'all') === 'premium' ? 'selected' : '' }}>Premium</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">Save Homepage Settings</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="offers-settings" role="tabpanel">
                <form class="ajax-page-settings-form row g-3" method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="setting_type" value="offers">
                    <div class="col-12">
                        <label class="form-label">Offers Market Banner Image <span class="text-danger">*</span></label>
                        <input type="file" name="offers_market_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                        @if($setting->offers_market_banner_image)
                            <img src="{{ asset($setting->offers_market_banner_image) }}" alt="Offers market banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                        @endif
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">Save Offer Settings</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="ads-settings" role="tabpanel">
                <form class="ajax-page-settings-form row g-3" method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="setting_type" value="ads">
                    <div class="col-12">
                        <label class="form-label">Ads Market Banner Image <span class="text-danger">*</span></label>
                        <input type="file" name="ads_market_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp" required>
                        @if($setting->ads_market_banner_image)
                            <img src="{{ asset($setting->ads_market_banner_image) }}" alt="Ads market banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                        @endif
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">Save Ads Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.ajax-page-settings-form');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : 'Save';
            const formData = new FormData(form);

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData,
            })
                .then(async function (response) {
                    const payload = await response.json().catch(function () { return {}; });

                    if (!response.ok) {
                        let message = payload.message || 'Unable to update page settings.';
                        if (payload.errors) {
                            const firstKey = Object.keys(payload.errors)[0];
                            if (firstKey && Array.isArray(payload.errors[firstKey]) && payload.errors[firstKey].length) {
                                message = payload.errors[firstKey][0];
                            }
                        }
                        throw new Error(message);
                    }

                    const message = payload.message || 'Page settings updated successfully.';
                    toastr.success(message);
    

                    setTimeout(function () {
                        window.location.reload();
                    }, 600);
                })
                .catch(function (error) {
                    const message = error && error.message ? error.message : 'Unable to update page settings.';
                    if (window.toastr && typeof window.toastr.error === 'function') {
                        window.toastr.error(message);
                    } else if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                        window.FormHelper.showToast('danger', message);
                    }
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
@endpush

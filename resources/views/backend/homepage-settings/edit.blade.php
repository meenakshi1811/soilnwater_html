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
<script>
$(function () {
    $('.ajax-page-settings-form').on('submit', function (e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const $btn = $form.find('button[type="submit"]');
        const originalText = $btn.text();
        const formData = new FormData(form);

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (response) {
                const message = response?.message || 'Page settings updated successfully.';
                if (window.toastr?.success) {
                    window.toastr.success(message);
                } else if (window.FormHelper?.showToast) {
                    window.FormHelper.showToast('success', message);
                }
                setTimeout(function () { window.location.reload(); }, 600);
            },
            error: function (xhr) {
                let message = xhr?.responseJSON?.message || 'Unable to update page settings.';
                if (xhr?.responseJSON?.errors) {
                    const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                    if (firstKey && xhr.responseJSON.errors[firstKey]?.length) {
                        message = xhr.responseJSON.errors[firstKey][0];
                    }
                }
                if (window.toastr?.error) {
                    window.toastr.error(message);
                } else if (window.FormHelper?.showToast) {
                    window.FormHelper.showToast('danger', message);
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
</script>
@endpush

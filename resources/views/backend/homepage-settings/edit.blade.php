@extends('backend.layouts.app')

@section('title', 'Page Settings')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <h2 class="admin-title mb-1">Page Settings</h2>
        <p class="mb-0 text-secondary">Manage homepage, offers market, and ads market banners from one place.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="pageSettingsForm" method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-12">
            <ul class="nav nav-tabs" id="pageSettingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="homepage-tab" data-bs-toggle="tab" data-bs-target="#homepage-settings" type="button" role="tab" aria-controls="homepage-settings" aria-selected="true">Homepage Settings</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers-settings" type="button" role="tab" aria-controls="offers-settings" aria-selected="false">Offer Page Settings</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ads-tab" data-bs-toggle="tab" data-bs-target="#ads-settings" type="button" role="tab" aria-controls="ads-settings" aria-selected="false">Ads Page Settings</button>
                </li>
            </ul>

            <div class="tab-content border border-top-0 rounded-bottom p-3 p-md-4" id="pageSettingsTabsContent">
                <div class="tab-pane fade show active" id="homepage-settings" role="tabpanel" aria-labelledby="homepage-tab">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Hero Banner Image <span class="text-danger">*</span></label>
                            <input type="file" name="hero_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            @if($setting->hero_banner_image)
                                <img src="{{ asset($setting->hero_banner_image) }}" alt="Hero banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hero Button Text <span class="text-danger">*</span></label>
                            <input type="text" name="hero_button_text" class="form-control" value="{{ old('hero_button_text', $setting->hero_button_text ?? 'Advertise Now') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hero Button Link <span class="text-danger">*</span></label>
                            <input type="text" name="hero_button_link" class="form-control" value="{{ old('hero_button_link', $setting->hero_button_link ?? '#') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Enable/Disable Homepage Sections</label>
                            <div class="row g-2">
                                @foreach($sections as $key => $label)
                                    <div class="col-md-4">
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" name="sections[]" value="{{ $key }}"
                                                {{ data_get($setting->section_toggles, $key, true) ? 'checked' : '' }}>
                                            <span class="form-check-label">{{ $label }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="offers-settings" role="tabpanel" aria-labelledby="offers-tab">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Offers Market Banner Image <span class="text-danger">*</span></label>
                            <input type="file" name="offers_market_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            @if($setting->offers_market_banner_image)
                                <img src="{{ asset($setting->offers_market_banner_image) }}" alt="Offers market banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="ads-settings" role="tabpanel" aria-labelledby="ads-tab">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Ads Market Banner Image <span class="text-danger">*</span></label>
                            <input type="file" name="ads_market_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            @if($setting->ads_market_banner_image)
                                <img src="{{ asset($setting->ads_market_banner_image) }}" alt="Ads market banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Save Settings</button>
        </div>
    </form>
</div>
@endsection


@push('scripts')
<script>
$(function () {
    $('#pageSettingsForm').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        const $btn = $(form).find('button[type="submit"]');
        const originalText = $btn.text();
        const formData = new FormData(form);

        $btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const message = response && response.message ? response.message : 'Page settings updated successfully.';
                if (window.toastr && typeof window.toastr.success === 'function') {
                    window.toastr.success(message);
                } else if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                    window.FormHelper.showToast('success', message);
                } else {
                    alert(message);
                }
                window.location.reload();
            },
            error: function (xhr) {
                let message = 'Unable to update page settings.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (window.toastr && typeof window.toastr.error === 'function') {
                    window.toastr.error(message);
                } else if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
                    window.FormHelper.showToast('danger', message);
                } else {
                    alert(message);
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

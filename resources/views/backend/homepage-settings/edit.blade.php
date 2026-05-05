@extends('backend.layouts.app')

@section('title', 'Homepage Settings')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <h2 class="admin-title mb-1">Homepage Settings</h2>
        <p class="mb-0 text-secondary">Manage homepage hero banner and section visibility.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form id="homepage-settings-form" method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-12">
            <label class="form-label">Hero Banner Image</label>
            <input type="file" name="hero_banner_image" class="form-control" accept="image/png,image/jpeg,image/webp">
            @if($setting->hero_banner_image)
                <img src="{{ asset($setting->hero_banner_image) }}" alt="Hero banner" style="max-width:320px;margin-top:10px;border-radius:8px;">
            @endif
        </div>

        <div class="col-md-6">
            <label class="form-label">Hero Button Text</label>
            <input type="text" name="hero_button_text" class="form-control" value="{{ old('hero_button_text', $setting->hero_button_text ?? 'Advertise Now') }}">
        </div>

        <div class="col-md-6">
            <label class="form-label">Hero Button Link</label>
            <input type="text" name="hero_button_link" class="form-control" value="{{ old('hero_button_link', $setting->hero_button_link ?? '#') }}">
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

        <div class="col-12 d-flex justify-content-end">
            <button id="homepage-settings-submit" class="btn btn-primary" type="submit">Save Settings</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(function () {
            const $form = $('#homepage-settings-form');
            const $submitButton = $('#homepage-settings-submit');

            $form.on('submit', function (event) {
                event.preventDefault();

                const formData = new FormData(this);
                const originalButtonText = $submitButton.text();

                $submitButton.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    toastr.success(response.message || 'Homepage settings updated successfully.');
                }).fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        $.each(xhr.responseJSON.errors, function (_, messages) {
                            toastr.error(messages[0]);
                        });
                        return;
                    }

                    toastr.error('Something went wrong. Please try again.');
                }).always(function () {
                    $submitButton.prop('disabled', false).text(originalButtonText);
                });
            });
        });
    </script>
@endpush

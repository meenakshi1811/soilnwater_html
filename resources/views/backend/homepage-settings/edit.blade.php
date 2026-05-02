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

    <form method="POST" action="{{ route('admin.homepage-settings.update') }}" enctype="multipart/form-data" class="row g-3">
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
            <button class="btn btn-primary" type="submit">Save Settings</button>
        </div>
    </form>
</div>
@endsection

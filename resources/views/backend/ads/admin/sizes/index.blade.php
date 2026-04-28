@extends('backend.layouts.app')

@section('title', 'Ad Sizes')

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <div>
            <p class="ems-kicker mb-1">Ads</p>
            <h2 class="admin-title mb-1">Ad Sizes</h2>
            <p class="mb-0 text-secondary">Add ad sizes for user placements and admin placements.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach($sizes as $size)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="ads-size-card d-block text-decoration-none">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div class="fw-semibold text-dark">{{ $size['name'] }}</div>
                        @if(($size['admin_only'] ?? false) === true)
                            <span class="badge text-bg-warning">Admin Placement</span>
                        @else
                            <span class="badge text-bg-success">User Placement</span>
                        @endif
                    </div>
                    <div class="ads-size-shape" style="aspect-ratio: {{ $size['ratio'] }};">
                        <div class="ads-size-shape-inner">
                            <span class="ads-size-dim">{{ $size['w'] }}×{{ $size['h'] }}</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-secondary small">Aspect ratio {{ $size['ratio'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chart-card mb-4">
        <h5 class="mb-3">Add New Size</h5>
        <form method="POST" action="{{ route('admin.ads.sizes.store') }}" class="row g-3">
            @csrf
            <div class="col-12 col-md-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="120">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Width</label>
                <input type="number" min="1" max="5000" name="width" class="form-control @error('width') is-invalid @enderror" value="{{ old('width') }}" required>
                @error('width')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Height</label>
                <input type="number" min="1" max="5000" name="height" class="form-control @error('height') is-invalid @enderror" value="{{ old('height') }}" required>
                @error('height')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="adminOnly" name="admin_only" value="1" {{ old('admin_only') ? 'checked' : '' }}>
                    <label class="form-check-label" for="adminOnly">Admin only</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary ems-btn-primary">Add Size</button>
            </div>
        </form>
    </div>

    <div class="chart-card">
        <h5 class="mb-3">Custom Sizes</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Dimensions</th>
                        <th>Placement</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customSizes as $custom)
                        <tr>
                            <td>{{ $custom->name }}</td>
                            <td><code>{{ $custom->size_key }}</code></td>
                            <td>{{ $custom->width }}×{{ $custom->height }}</td>
                            <td>{{ $custom->admin_only ? 'Admin' : 'User' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary">No custom sizes added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

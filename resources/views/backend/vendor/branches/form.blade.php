@extends('backend.layouts.app')

@section('title', $branch->exists ? 'Manage Branch' : 'Add Branch')

@section('content')
<div class="admin-panel ems-page vendor-branch-form">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="ems-kicker mb-1">Vendor Panel</p>
            <h2 class="admin-title mb-0">{{ $branch->exists ? 'Manage Branch' : 'Add Branch' }}</h2>
            <p class="text-secondary small mb-0">Update details for your branch / company location</p>
        </div>
        <a href="{{ route('vendor.branches.index') }}" class="btn btn-light">Back to list</a>
    </div>

    <form method="POST" action="{{ $branch->exists ? route('vendor.branches.update', $branch) : route('vendor.branches.store') }}" enctype="multipart/form-data">
        @csrf
        @if($branch->exists) @method('PUT') @endif

        <div class="vendor-form-card mb-4">
            <h5 class="vendor-form-card-title"><span>1</span> Basic Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Branch / Company Name</label>
                    <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $branch->branch_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="{{ old('contact_person', $branch->contact_person) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    @if($branch->logo)
                        <div class="mb-2"><img src="{{ asset($branch->logo) }}" alt="" class="vendor-logo-preview rounded-circle"></div>
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="isPrimary" {{ old('is_primary', $branch->is_primary) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isPrimary">Primary branch</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="vendor-form-card mb-4">
            <h5 class="vendor-form-card-title"><span>2</span> Contact &amp; Location</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}" placeholder="+91">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $branch->whatsapp) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $branch->email) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Full Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $branch->address) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $branch->city) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $branch->state) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $branch->pincode) }}">
                </div>
            </div>
        </div>

        <div class="vendor-form-card mb-4">
            <h5 class="vendor-form-card-title"><span>3</span> Legal &amp; Info</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $branch->pan_number) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $branch->gst_number) }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $branch->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="vendor-form-card mb-4">
            <h5 class="vendor-form-card-title"><span>4</span> Gallery</h5>
            @if(is_array($branch->gallery) && count($branch->gallery))
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach($branch->gallery as $img)
                        <img src="{{ asset($img) }}" alt="" class="rounded" style="width:80px;height:80px;object-fit:cover">
                    @endforeach
                </div>
            @endif
            <label class="form-label">Add new images</label>
            <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
        </div>

        <button type="submit" class="btn btn-dark w-100 py-3">{{ $branch->exists ? 'Update Branch Details' : 'Create Branch' }}</button>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-portal.css') }}?v={{ now()->timestamp }}">
@endpush



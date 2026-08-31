@extends('backend.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="admin-panel admin-profile-wrap">
    <h1 class="admin-title mb-3">Update Profile</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card admin-table-card">
        <div class="card-body">
            <form method="POST" action="{{ route('employee.profile.update') }}" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label for="name" class="form-label">Full Name</label>
                    <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input id="phone_number" name="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $employee->phone_number) }}" required>
                    @error('phone_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="email" class="form-label">Email</label>
                    <input id="email" type="email" class="form-control" value="{{ $employee->email }}" disabled>
                    <small class="text-muted">Email cannot be changed here. Ask an admin if you need it updated.</small>
                </div>

                <div class="col-md-6">
                    <label for="password" class="form-label">New Password</label>
                    <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

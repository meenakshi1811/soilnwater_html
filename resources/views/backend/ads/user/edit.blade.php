@extends('backend.layouts.app')

@section('title', 'Edit Ad')

@section('content')
<div class="admin-panel ems-page"><div class="chart-card">
<h4 class="mb-3">Edit Ad</h4>
<form method="POST" action="{{ route('ads.update', $ad) }}">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title',$ad->title) }}" required></div>
<div class="col-md-6"><label class="form-label">Valid Until</label><input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', optional($ad->valid_until)->format('Y-m-d')) }}" required></div>
<div class="col-12"><label class="form-label">Short Description</label><textarea name="short_description" class="form-control" rows="4" maxlength="1000" placeholder="Write a short summary for this ad (max 1000 characters)...">{{ old('short_description',$ad->short_description) }}</textarea></div>
<div class="col-md-6"><label class="form-label">Category</label><select name="category_id" class="form-select" required>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$ad->category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Subcategory</label><select name="subcategory_id" class="form-select" required>@foreach($subcategories as $s)<option value="{{ $s->id }}" @selected(old('subcategory_id',$ad->subcategory_id)==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location',$ad->location) }}" required></div>
<div class="col-md-4"><label class="form-label">Lat</label><input name="location_lat" class="form-control" value="{{ old('location_lat',$ad->location_lat) }}" required></div>
<div class="col-md-4"><label class="form-label">Lng</label><input name="location_lng" class="form-control" value="{{ old('location_lng',$ad->location_lng) }}" required></div>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary">Update Ad</button><a href="{{ route('ads.index') }}" class="btn btn-light">Cancel</a></div>
</div></form></div></div>
@endsection

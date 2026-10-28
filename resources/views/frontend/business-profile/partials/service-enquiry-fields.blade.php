{{-- The fields of a consultant's or service provider's enquiry form (the visitor's details and question). --}}
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Client name *</label>
        <input type="text" name="client_name" class="form-control" value="{{ auth()->user()?->full_name ?: auth()->user()?->name }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Number *</label>
        <input type="text" name="phone_number" class="form-control" value="{{ auth()->user()?->phone_number }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" value="{{ auth()->user()?->email }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Occupation</label>
        <input type="text" name="occupation" class="form-control" placeholder="Your occupation">
    </div>
    <div class="col-md-6">
        <label class="form-label">DOB</label>
        <input type="date" name="date_of_birth" class="form-control" value="{{ auth()->user()?->date_of_birth?->format('Y-m-d') }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Upload image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <div class="col-12">
        <label class="form-label">Question *</label>
        <textarea name="question" class="form-control" rows="4" placeholder="{{ $questionPlaceholder }}" required></textarea>
    </div>
</div>

@php
    use App\Support\ProfileEmails;

    // Several contact emails, like the phone numbers (profile-emails.js adds and removes rows)
    $emails = ProfileEmails::forForm($entity ?? null, $user ?? null);
    $inputIdPrefix = $inputIdPrefix ?? 'profile_email';
    $wrapperClass = $wrapperClass ?? 'col-md-6';
@endphp

<div class="{{ $wrapperClass }} js-profile-email-field">
    <label class="form-label d-flex align-items-center justify-content-between gap-2">
        <span>Email *</span>
        <button type="button" class="btn btn-sm btn-outline-secondary js-profile-email-add">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add email
        </button>
    </label>
    <small class="text-muted d-block mb-2">The first email is your main contact. Your login email doesn't change.</small>
    <div class="js-profile-email-list d-flex flex-column gap-2" data-max="{{ ProfileEmails::MAX_ITEMS }}">
        @foreach($emails as $index => $email)
            <div class="input-group js-profile-email-row">
                <span class="input-group-text text-muted js-profile-email-index">{{ $index + 1 }}</span>
                <input
                    type="email"
                    name="emails[]"
                    class="form-control js-profile-email-input @error('emails.'.$index) is-invalid @enderror @error('emails') is-invalid @enderror"
                    value="{{ $email }}"
                    autocomplete="email"
                    maxlength="255"
                    @if($index === 0) required @endif
                    id="{{ $inputIdPrefix }}_{{ $index }}"
                >
                @if($index > 0)
                    <button type="button" class="btn btn-outline-danger js-profile-email-remove" aria-label="Remove email">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        @endforeach
    </div>
    @error('emails')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('emails.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/js/profile-emails.js') }}?v={{ now()->timestamp }}"></script>
    @endpush
@endonce

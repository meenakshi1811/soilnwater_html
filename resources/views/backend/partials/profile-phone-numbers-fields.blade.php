@php
    use App\Support\ProfilePhoneNumbers;

    $phoneNumbers = ProfilePhoneNumbers::forForm($entity ?? null, $user ?? null);
    $inputIdPrefix = $inputIdPrefix ?? 'profile_phone';
    $wrapperClass = $wrapperClass ?? 'col-md-6';
    $required = $required ?? true;
    $showPrimaryHint = $showPrimaryHint ?? true;
@endphp

<div class="{{ $wrapperClass }} js-profile-phone-field">
    <label class="form-label d-flex align-items-center justify-content-between gap-2">
        <span>Phone {{ $required ? '*' : '' }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary js-profile-phone-add">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i>Add number
        </button>
    </label>
    @if($showPrimaryHint)
        <small class="text-muted d-block mb-2">The first number is your primary contact and used for login verification.</small>
    @endif
    <div class="js-profile-phone-list d-flex flex-column gap-2">
        @foreach($phoneNumbers as $index => $number)
            <div class="input-group js-profile-phone-row">
                <span class="input-group-text text-muted js-profile-phone-index">{{ $index + 1 }}</span>
                <input
                    type="tel"
                    name="phone_numbers[]"
                    class="form-control js-profile-phone-input @error('phone_numbers.'.$index) is-invalid @enderror @error('phone_numbers') is-invalid @enderror"
                    value="{{ $number }}"
                    inputmode="numeric"
                    autocomplete="tel"
                    @if($required && $index === 0) required @endif
                    id="{{ $inputIdPrefix }}_{{ $index }}"
                >
                @if($index > 0)
                    <button type="button" class="btn btn-outline-danger js-profile-phone-remove" aria-label="Remove phone number">
                        <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        @endforeach
    </div>
    @error('phone_numbers')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('phone_numbers.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
    @push('scripts')
        <script src="{{ asset('assets/js/profile-phone-numbers.js') }}?v={{ now()->timestamp }}"></script>
    @endpush
@endonce

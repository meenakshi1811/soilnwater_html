@php
    /** @var \Illuminate\Database\Eloquent\Model|null $model */
    $phones = $phones ?? (isset($model) && method_exists($model, 'phoneNumbersList') ? $model->phoneNumbersList() : []);
    $phones = is_array($phones) ? array_values(array_filter($phones)) : [];
    $linkClass = $linkClass ?? '';
    $separator = $separator ?? ', ';
    $asList = $asList ?? false;
@endphp

@if($phones !== [])
    @if($asList)
        <ul class="list-unstyled mb-0 profile-phone-links">
            @foreach($phones as $phone)
                <li>
                    <a href="tel:{{ $phone }}" @if($linkClass !== '') class="{{ $linkClass }}" @endif>{{ $phone }}</a>
                </li>
            @endforeach
        </ul>
    @else
        @foreach($phones as $index => $phone)
            @if($index > 0)<span class="profile-phone-links__sep">{{ $separator }}</span>@endif
            <a href="tel:{{ $phone }}" @if($linkClass !== '') class="{{ $linkClass }}" @endif>{{ $phone }}</a>
        @endforeach
    @endif
@endif

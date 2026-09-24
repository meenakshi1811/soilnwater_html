@php
  $institute = $affiliation->institute;
@endphp
<div class="edu-affiliation-item" data-affiliation-id="{{ $affiliation->id }}">
  <div class="edu-affiliation-item__main">
    @if($institute?->logoUrl())
      <img src="{{ $institute->logoUrl() }}" alt="" class="edu-affiliation-item__logo">
    @else
      <span class="edu-affiliation-item__logo edu-affiliation-item__logo--placeholder"><i class="fa-solid fa-school" aria-hidden="true"></i></span>
    @endif
    <div>
      <strong>{{ $institute?->displayName() ?? 'Institution' }}</strong>
      <div class="text-secondary small">
        {{ $institute?->user?->isSchool() ? 'School' : 'Institute' }}
        @if($institute?->city)
          · {{ $institute->city }}
        @endif
      </div>
      @if($affiliation->role_title || $affiliation->subject)
        <div class="small mt-1">
          @if($affiliation->role_title)<span>{{ $affiliation->role_title }}</span>@endif
          @if($affiliation->role_title && $affiliation->subject)<span> · </span>@endif
          @if($affiliation->subject)<span>{{ $affiliation->subject }}</span>@endif
        </div>
      @endif
      @if($institute?->isApproved())
        <a href="{{ $institute->publicUrl() }}" target="_blank" rel="noopener noreferrer" class="small">View public page</a>
      @endif
    </div>
  </div>
  <button type="button" class="btn btn-outline-danger btn-sm js-edu-affiliation-remove" data-url="{{ route('educator.affiliations.destroy', $affiliation) }}" aria-label="Remove association">
    <i class="fa-solid fa-link-slash" aria-hidden="true"></i>
  </button>
</div>

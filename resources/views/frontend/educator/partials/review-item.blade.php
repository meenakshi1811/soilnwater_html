@php
  $body = trim((string) ($item->body ?? ''));
@endphp
<div class="edu-review" data-review-id="{{ $item->id }}">
  <div class="edu-review__head">
    <div>
      <strong>{{ $item->author }}</strong>
      @if(! empty($item->meta))
        <div class="small text-muted">{{ $item->meta }}</div>
      @endif
      @if(! empty($item->material_title) && ! empty($item->material_url))
        <div class="small">
          On <a href="{{ $item->material_url }}">{{ $item->material_title }}</a>
        </div>
      @elseif(($item->source ?? '') === 'profile')
        <div class="small text-muted">Profile review</div>
      @endif
    </div>
    <div class="text-end">
      <span class="edu-stars" aria-label="{{ $item->rating }} out of 5">
        @foreach (range(1, 5) as $star)
          <i class="fa-{{ $star <= (int) $item->rating ? 'solid' : 'regular' }} fa-star"></i>
        @endforeach
      </span>
      @if(! empty($item->date))
        <div class="small text-muted">{{ $item->date->diffForHumans() }}</div>
      @endif
    </div>
  </div>
  @if($body !== '')
    <p class="mb-0 mt-2">{{ $body }}</p>
  @endif
</div>

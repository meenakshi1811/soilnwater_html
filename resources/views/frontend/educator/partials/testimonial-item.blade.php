<blockquote class="edu-testimonial" data-review-id="{{ $item->id }}">
  <div class="edu-testimonial__stars" aria-label="{{ $item->rating }} out of 5">
    @for($s = 1; $s <= 5; $s++)
      <i class="fa-{{ $s <= (int) $item->rating ? 'solid' : 'regular' }} fa-star" aria-hidden="true"></i>
    @endfor
  </div>
  <p class="edu-testimonial__text">&ldquo;{{ $item->body }}&rdquo;</p>
  <footer>
    <div class="edu-testimonial__author">{{ $item->author }}</div>
    @if(!empty($item->meta))
      <div class="edu-testimonial__meta">{{ $item->meta }}</div>
    @endif
  </footer>
</blockquote>

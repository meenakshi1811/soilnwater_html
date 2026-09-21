@php
    $fileMeta = $material->fileTypeMeta();
    $showActions = $showActions ?? false;
    $authUser = auth()->user();
    $canAccess = $material->canAccessContent($authUser);
    $isPaid = $material->isPaidMaterial();
    $paymentState = $material->resolvePaymentStateFor($authUser);
    $description = \Illuminate\Support\Str::limit(strip_tags((string) $material->description), 120);
    $uploadedAt = $material->approved_at ?? $material->created_at;
@endphp

<article class="edu-sm-card {{ $showActions ? 'edu-sm-card--interactive' : '' }}">
  <div class="edu-sm-card__top">
    <span class="edu-sm-card__icon edu-sm-card__icon--{{ $fileMeta['tone'] }}">
      <i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i>
    </span>
    <div class="edu-sm-card__head">
      <div class="edu-sm-card__badges">
        <span class="edu-sm-card__type">{{ $material->materialTypeLabel() }}</span>
        @if($isPaid)
          <span class="edu-sm-card__price-badge edu-sm-card__price-badge--paid">Paid · {{ $material->formattedPrice() }}</span>
        @else
          <span class="edu-sm-card__price-badge edu-sm-card__price-badge--free">Free</span>
        @endif
      </div>
      <h3 class="edu-sm-card__title">
        <a href="{{ $material->publicUrl() }}">{{ $material->title }}</a>
      </h3>
      @if($description !== '')
        <p class="edu-sm-card__desc">{{ $description }}</p>
      @endif
    </div>
  </div>

  <ul class="edu-sm-card__meta">
    <li><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $uploadedAt?->timezone(config('app.timezone'))->format('d M Y') ?? '—' }}</li>
    <li><i class="fa-solid {{ $fileMeta['icon'] }}" aria-hidden="true"></i> {{ $fileMeta['label'] }}</li>
    <li><i class="fa-solid fa-weight-hanging" aria-hidden="true"></i> {{ $material->fileSizeLabel() }}</li>
    @if($material->subject)
      <li><i class="fa-solid fa-book" aria-hidden="true"></i> {{ $material->subject }}</li>
    @endif
  </ul>

  @if($showActions)
    <div class="edu-sm-card__actions">
      @if($paymentState['mode'] === 'pending')
        <span class="edu-sm-card__pending"><i class="fa-solid fa-clock" aria-hidden="true"></i> Payment verification pending</span>
      @elseif($canAccess)
        <a href="{{ $material->publicUrl() }}" class="edu-sm-card__action edu-sm-card__action--primary">
          <i class="fa-solid fa-eye" aria-hidden="true"></i> View
        </a>
        @auth
          <a href="{{ route('study-materials.download', $material->slug) }}" class="edu-sm-card__action js-edu-sm-download">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Download
          </a>
        @else
          <a href="{{ route('login') }}" class="edu-sm-card__action">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Download
          </a>
        @endauth
      @elseif($isPaid)
        @guest
          <a href="{{ route('login') }}" class="edu-sm-card__action edu-sm-card__action--primary">
            <i class="fa-solid fa-lock" aria-hidden="true"></i> Login to purchase
          </a>
        @else
          <button
            type="button"
            class="edu-sm-card__action edu-sm-card__action--primary js-edu-sm-purchase"
            data-material-id="{{ $material->id }}"
            data-material-title="{{ $material->title }}"
            data-material-amount="{{ $material->price }}"
          >
            <i class="fa-solid fa-indian-rupee-sign" aria-hidden="true"></i> Purchase · {{ $material->formattedPrice() }}
          </button>
        @endguest
      @else
        <a href="{{ $material->publicUrl() }}" class="edu-sm-card__action edu-sm-card__action--primary">
          <i class="fa-solid fa-eye" aria-hidden="true"></i> Open
        </a>
        @auth
          <a href="{{ route('study-materials.download', $material->slug) }}" class="edu-sm-card__action js-edu-sm-download">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Download
          </a>
        @else
          <a href="{{ route('login') }}" class="edu-sm-card__action">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Download
          </a>
        @endauth
      @endif

      @auth
        <button
          type="button"
          class="edu-sm-card__action js-edu-sm-bookmark {{ ! empty($material->is_bookmarked) ? 'is-active' : '' }}"
          data-url="{{ route('study-materials.bookmark', $material->slug) }}"
          data-bookmarked="{{ ! empty($material->is_bookmarked) ? '1' : '0' }}"
          aria-pressed="{{ ! empty($material->is_bookmarked) ? 'true' : 'false' }}"
        >
          <i class="fa-solid fa-bookmark" aria-hidden="true"></i>
          {{ ! empty($material->is_bookmarked) ? 'Saved' : 'Save' }}
        </button>
      @endauth
    </div>
  @endif
</article>

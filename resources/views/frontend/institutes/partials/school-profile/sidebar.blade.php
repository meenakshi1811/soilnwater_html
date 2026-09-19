<aside class="sch-sidebar" aria-label="School contact and highlights">
  <div class="sch-card sch-glance-card">
    <h2>At a Glance</h2>
    <ul class="sch-glance-list">
      @foreach($profile->atAGlance() as $item)
        <li>
          <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
          <div>
            <span>{{ $item['label'] }}</span>
            <strong>{{ $item['value'] }}</strong>
          </div>
        </li>
      @endforeach
    </ul>
  </div>

  @if($profile->mapEmbedUrl())
    <div class="sch-card sch-map-card">
      <h2>Location Map</h2>
      <div class="sch-map-wrap">
        <iframe src="{{ $profile->mapEmbedUrl() }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="School location map"></iframe>
      </div>
      @if($profile->directionsUrl())
        <a href="{{ $profile->directionsUrl() }}" target="_blank" rel="noopener" class="sch-btn sch-btn-outline sch-btn-block">
          <i class="fa-solid fa-diamond-turn-right" aria-hidden="true"></i> Get Directions
        </a>
      @endif
    </div>
  @endif

  <div class="sch-card sch-contact-card">
    <h2>Contact Information</h2>
    @if($institute->formattedAddress())
      <div class="sch-contact-item">
        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
        <div>
          <span>Address</span>
          <p>{{ $institute->formattedAddress() }}</p>
        </div>
      </div>
    @endif
    @if($institute->phone)
      <div class="sch-contact-item">
        <i class="fa-solid fa-phone" aria-hidden="true"></i>
        <div>
          <span>Phone</span>
          <p><a href="tel:{{ $institute->phone }}">{{ $institute->phone }}</a></p>
        </div>
      </div>
    @endif
    @if($institute->whatsapp ?: $institute->phone)
      <div class="sch-contact-item">
        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
        <div>
          <span>WhatsApp</span>
          <p><a href="https://wa.me/91{{ preg_replace('/\D/', '', $institute->whatsapp ?: $institute->phone) }}" target="_blank" rel="noopener">{{ $institute->whatsapp ?: $institute->phone }}</a></p>
        </div>
      </div>
    @endif
    @if($institute->email)
      <div class="sch-contact-item">
        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
        <div>
          <span>Email</span>
          <p><a href="mailto:{{ $institute->email }}">{{ $institute->email }}</a></p>
        </div>
      </div>
    @endif
    @if($institute->website_url)
      <div class="sch-contact-item">
        <i class="fa-solid fa-globe" aria-hidden="true"></i>
        <div>
          <span>Website</span>
          <p><a href="{{ $institute->website_url }}" target="_blank" rel="noopener">{{ parse_url($institute->website_url, PHP_URL_HOST) ?: $institute->website_url }}</a></p>
        </div>
      </div>
    @endif
    <div class="sch-contact-item">
      <i class="fa-solid fa-clock" aria-hidden="true"></i>
      <div>
        <span>Operating Hours</span>
        <p>{{ $profile->operatingHours() }}</p>
      </div>
    </div>
  </div>

  @if($profile->highlights())
    <div class="sch-card sch-highlights-card">
      <h2>Highlights</h2>
      <ul class="sch-highlights">
        @foreach($profile->highlights() as $index => $highlight)
          <li><i class="fa-solid fa-{{ ['certificate', 'chalkboard', 'heart-pulse', 'bus', 'shield', 'leaf', 'music', 'flask'][$index % 8] }}" aria-hidden="true"></i> {{ $highlight }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($profile->whyChooseUs())
    <div class="sch-card sch-why-card">
      <h2>Why Choose Us?</h2>
      <ul class="sch-why-list">
        @foreach($profile->whyChooseUs() as $point)
          <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ $point }}</li>
        @endforeach
      </ul>
    </div>
  @endif
</aside>

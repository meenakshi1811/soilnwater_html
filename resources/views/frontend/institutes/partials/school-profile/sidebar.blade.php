<aside class="sch-sidebar" aria-label="School actions and contact">
  <div class="sch-sidebar__stack">
    <a href="#sch-contact" class="sch-btn sch-btn-primary sch-btn-block js-sch-nav-link">Enquire Now</a>
    <button type="button" class="sch-btn sch-btn-outline sch-btn-block js-sch-brochure">Download Brochure</button>
    <button type="button" class="sch-btn sch-btn-outline sch-btn-block js-sch-compare">Compare</button>
    <button type="button" class="sch-btn sch-btn-outline sch-btn-block js-sch-follow">Follow School</button>
    <button type="button" class="sch-btn sch-btn-outline sch-btn-block js-sch-share">Share Profile</button>
  </div>

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

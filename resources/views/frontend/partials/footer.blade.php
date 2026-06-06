<footer class="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">
        <img class="footer-logo-icon" src="{{ asset('assets/images/logo_soilnwater.webp') }}" alt="SoilnWater logo">
      </div>
      <p class="footer-tagline">India's trusted local &amp; national marketplace connecting buyers, sellers, and services across every category.</p>
      {{--
      <div class="footer-socials">
        <a href="https://www.facebook.com" target="_blank" rel="noopener" class="social-btn" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
        <a href="https://www.instagram.com" target="_blank" rel="noopener" class="social-btn" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
        <a href="https://x.com" target="_blank" rel="noopener" class="social-btn" aria-label="X"><i class="fab fa-x-twitter" aria-hidden="true"></i></a>
        <a href="https://www.linkedin.com" target="_blank" rel="noopener" class="social-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
      </div>
      --}}
    </div>

    <div class="footer-links-grid">
      <div class="footer-col footer-panel">
        <h4 class="footer-col-title">Marketplace</h4>
        <ul class="footer-list">
          <li><a href="{{ route('frontend.ads.index') }}"><i class="fa-solid fa-chevron-right"></i> Ads</a></li>
          <li><a href="{{ route('frontend.offers.index') }}"><i class="fa-solid fa-chevron-right"></i> Offers</a></li>
          <li><a href="{{ route('frontend.vendors.index') }}"><i class="fa-solid fa-chevron-right"></i> Vendors</a></li>
          <li><a href="{{ route('frontend.consultants.index') }}"><i class="fa-solid fa-chevron-right"></i> Consultants</a></li>
          <li><a href="{{ route('frontend.service_providers.index') }}"><i class="fa-solid fa-chevron-right"></i> Services</a></li>
          {{--
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> E-Commerce</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Properties</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Services</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Builders</a></li>
          <li><a href="https://www.facebook.com" target="_blank" rel="noopener"><i class="fa-solid fa-chevron-right"></i> Social Media</a></li>
          --}}
        </ul>
      </div>

      {{--
      <div class="footer-col footer-panel">
        <h4 class="footer-col-title">For Business</h4>
        <ul class="footer-list">
          <li><a href="{{ auth()->check() ? route('ads.create.size') : route('login') }}"><i class="fa-solid fa-chevron-right"></i> Post an Ad</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Sponsored Listings</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Premium Packages</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Seller Dashboard</a></li>
          <li><a href="#"><i class="fa-solid fa-chevron-right"></i> Partner with Us</a></li>
        </ul>
      </div>
      --}}

      <div class="footer-col footer-panel">
        <h4 class="footer-col-title">Contact Us</h4>
        <ul class="footer-list footer-contact-list">
          <li><i class="fa-solid fa-phone"></i> +91 7055533011</li>
          <li><i class="fa-solid fa-envelope"></i> soilnwaterworld@gmail.com</li>
          <li><i class="fa-solid fa-clock"></i> Mon–Sat, 9 AM – 6 PM IST</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="footer-bottom-inner">
      <span>© 2021 - {{ date('Y') }} SoilnWater. All rights reserved.</span>
      <div class="footer-bottom-links">
        <a href="{{ route('frontend.about-us') }}">About Us</a>
        <a href="{{ route('frontend.privacy-policy') }}">Privacy Policy</a>
        <a href="{{ route('frontend.terms.show', ['moduleKey' => 'main']) }}">Terms of Service</a>
        <a href="{{ route('frontend.cookie-policy') }}">Cookie Policy</a>
        <a href="#">Help Center</a>
      </div>
    </div>
  </div>
</footer>

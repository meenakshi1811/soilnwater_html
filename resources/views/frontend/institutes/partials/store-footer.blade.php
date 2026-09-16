<footer class="vendor-store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="text-white">{{ $institute->displayName() }}</h5>
                <p class="small mb-2 mt-2">{{ $institute->formattedAddress() ?: 'Address details are not available yet.' }}</p>
                @if($institute->tagline)
                    <p class="small mb-0 opacity-75">{{ $institute->tagline }}</p>
                @endif
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Contact</h6>
                @if($institute->phone)
                    <p class="small mb-1"><i class="fa-solid fa-phone me-1"></i> <a href="tel:{{ $institute->phone }}" class="text-white-50 text-decoration-none">{{ $institute->phone }}</a></p>
                @endif
                @if($institute->email)
                    <p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> <a href="mailto:{{ $institute->email }}" class="text-white-50 text-decoration-none">{{ $institute->email }}</a></p>
                @endif
                @if($institute->contact_person)
                    <p class="small mb-1"><i class="fa-solid fa-user me-1"></i> {{ $institute->contact_person }}</p>
                @endif
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Quick links</h6>
                <a href="#sch-overview" class="small d-block mb-1 text-white-50 js-sch-nav-link">Profile home</a>
                @if(filled($institute->about ?: $institute->description))
                    <a href="#sch-about" class="small d-block mb-1 text-white-50 js-sch-nav-link">About us</a>
                @endif
                <a href="#sch-contact" class="small d-block mb-1 text-white-50 js-sch-nav-link">Send enquiry</a>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $institute->displayName() }} · Powered by SoilNWater</p>
    </div>
</footer>

@if($institute->whatsapp)
    <a href="https://wa.me/91{{ preg_replace('/\D/', '', $institute->whatsapp) }}" class="vendor-whatsapp-float" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
@endif

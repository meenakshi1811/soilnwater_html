<footer id="contact" class="vendor-store-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h5 class="text-white">{{ $consultant->publicDisplayName() }}</h5>
                <p class="small mb-0 mt-2">
                    {{ $consultant->formattedAddress() ?: 'Address details are not available yet.' }}
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Contact</h6>
                @if($consultant->phone)<p class="small mb-1"><i class="fa-solid fa-phone me-1"></i> {{ $consultant->phone }}</p>@endif
                @if($consultant->email)<p class="small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $consultant->email }}</p>@endif
            </div>
            <div class="col-md-3">
                <h6 class="text-white">Quick links</h6>
                <a href="{{ route('consultant.show', $consultant->slug) }}" class="small d-block mb-1">Consultant home</a>
                <a href="{{ route('consultant.about', $consultant->slug) }}" class="small d-block mb-1">About Us</a>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <p class="small text-center mb-0">&copy; {{ date('Y') }} {{ $consultant->publicDisplayName() }} · Powered by SoilNWater</p>
    </div>
</footer>

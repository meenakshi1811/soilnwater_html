<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | SoilnWater</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ now()->timestamp }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v={{ now()->timestamp }}">
    <link rel="stylesheet" href="{{ asset('assets/css/password-toggle.css') }}?v={{ now()->timestamp }}">
    @if (session('premium_upgrade_prompt'))
        <link rel="stylesheet" href="{{ asset('assets/css/premium-upgrade-modal.css') }}?v={{ now()->timestamp }}">
    @endif
    @auth
        @if (
            (auth()->user()->isVendor() && auth()->user()->vendor?->is_premium)
            || (auth()->user()->isConsultant() && auth()->user()->consultant?->is_premium)
            || (auth()->user()->isServiceProvider() && auth()->user()->serviceProvider?->is_premium)
        )
            <link rel="stylesheet" href="{{ asset('assets/css/marketplace-portal-dashboard.css') }}?v={{ now()->timestamp }}">
        @endif
    @endauth
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "tc65hkaj5f");
    </script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CJ8QTYE9ED"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-CJ8QTYE9ED');
    </script>
    @stack('styles')
</head>
@php
    $authUser = auth()->user();
    $isMarketplacePremium = $authUser && (
        ($authUser->isVendor() && $authUser->vendor?->is_premium)
        || ($authUser->isConsultant() && $authUser->consultant?->is_premium)
        || ($authUser->isServiceProvider() && $authUser->serviceProvider?->is_premium)
    );
@endphp
<body class="admin-body{{ $isMarketplacePremium ? ' marketplace-portal-premium' : '' }}">
    @include('backend.partials.header')

    <div class="admin-shell">
        @include('backend.partials.sidebar')

        <main class="admin-main container-fluid py-4">
            @yield('content')
        </main>
    </div>

    @include('backend.partials.footer')

    @include('backend.partials.premium-upgrade-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @if (session('premium_upgrade_prompt'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalElement = document.getElementById('premiumUpgradeModal');

                if (!modalElement || typeof bootstrap === 'undefined') {
                    return;
                }

                bootstrap.Modal.getOrCreateInstance(modalElement, {
                    backdrop: 'static',
                    keyboard: true
                }).show();
            });
        </script>
    @endif
    <script src="{{ asset('assets/js/google-places-autocomplete.js') }}?v={{ now()->timestamp }}"></script>
    <script src="{{ asset('assets/js/main.js') }}?v={{ now()->timestamp }}" defer></script>
    <script src="{{ asset('assets/js/password-toggle.js') }}?v={{ now()->timestamp }}" defer></script>
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('meta_title', 'SoilnWater – Local & National Marketplace')</title>
  <meta name="description" content="@yield('meta_description', 'Discover local and national deals on SoilnWater.')">
  @hasSection('meta_keywords')
    <meta name="keywords" content="@yield('meta_keywords')">
  @endif
  @hasSection('meta_robots')
    <meta name="robots" content="@yield('meta_robots')">
  @endif
  <link rel="canonical" href="@yield('meta_canonical', url()->current())">
  <meta property="og:type" content="@yield('meta_type', 'website')">
  <meta property="og:title" content="@yield('meta_title', 'SoilnWater – Local & National Marketplace')">
  <meta property="og:description" content="@yield('meta_description', 'Discover local and national deals on SoilnWater.')">
  <meta property="og:url" content="@yield('meta_url', url()->current())">
  <meta property="og:image" content="@yield('meta_image', asset('assets/images/logo_soilnwater.webp'))">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="@yield('meta_title', 'SoilnWater – Local & National Marketplace')">
  <meta name="twitter:description" content="@yield('meta_description', 'Discover local and national deals on SoilnWater.')">
  <meta name="twitter:image" content="@yield('meta_image', asset('assets/images/logo_soilnwater.webp'))">
  @stack('head')
  <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}?v={{ now()->timestamp }}" id="mainStylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/password-toggle.css') }}?v={{ now()->timestamp }}">
  <script>
    window.soilnwaterNormalizeShareUrl = window.soilnwaterNormalizeShareUrl || function soilnwaterNormalizeShareUrl(url) {
      try {
        const parsedUrl = new URL(url || window.location.href, window.location.href);

        if (parsedUrl.hostname === window.location.hostname && window.location.protocol === 'https:') {
          parsedUrl.protocol = 'https:';
        }

        parsedUrl.hash = '';

        return parsedUrl.href;
      } catch (error) {
        return window.location.href;
      }
    };

    window.soilnwaterFacebookShareUrl = window.soilnwaterFacebookShareUrl || function soilnwaterFacebookShareUrl(url) {
      const shareUrl = window.soilnwaterNormalizeShareUrl(url);

      return 'https://www.facebook.com/sharer/sharer.php?display=popup&u=' + encodeURIComponent(shareUrl);
    };
  </script>
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
<body>
  @include('frontend.partials.header')

  @yield('content')

  @if(request()->routeIs('community.*', 'frontend.community-posting-policy'))
    @include('frontend.partials.content-disclaimer')
  @endif

  @include('frontend.partials.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>window.initHeaderLocationAutocomplete = window.initHeaderLocationAutocomplete || function initHeaderLocationAutocomplete() {};</script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initHeaderLocationAutocomplete"></script>
  <script src="{{ asset('assets/js/main.js') }}?v={{ now()->timestamp }}" defer></script>
  <script src="{{ asset('assets/js/password-toggle.js') }}?v={{ now()->timestamp }}" defer></script>
  @stack('scripts')
</body>
</html>

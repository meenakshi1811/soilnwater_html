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
        const host = (parsedUrl.hostname || '').toLowerCase();
        const isLocalHost = host === 'localhost'
          || host === '127.0.0.1'
          || host.endsWith('.local')
          || host.endsWith('.test');

        if (!isLocalHost) {
          parsedUrl.protocol = 'https:';
        } else if (window.location.protocol === 'https:') {
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

    window.soilnwaterWhatsappShareUrl = window.soilnwaterWhatsappShareUrl || function soilnwaterWhatsappShareUrl(url, suffix) {
      const normalized = window.soilnwaterNormalizeShareUrl(url);

      return 'https://wa.me/?text=' + encodeURIComponent(normalized + '\n\n' + (suffix || 'Check this on SoilnWater'));
    };

    window.soilnwaterCopyShareLink = window.soilnwaterCopyShareLink || async function soilnwaterCopyShareLink(inputOrId, buttonEl) {
      const input = typeof inputOrId === 'string' ? document.getElementById(inputOrId) : inputOrId;

      if (!input || !input.value) {
        return;
      }

      try {
        await navigator.clipboard.writeText(input.value);
      } catch (error) {
        input.select();
        document.execCommand('copy');
      }

      if (!buttonEl) {
        return;
      }

      const original = buttonEl.textContent;
      buttonEl.textContent = 'Copied';
      window.setTimeout(function () {
        buttonEl.textContent = original;
      }, 1600);
    };

    window.soilnwaterBindShareCopyButton = window.soilnwaterBindShareCopyButton || function soilnwaterBindShareCopyButton(buttonOrId, inputOrId) {
      const button = typeof buttonOrId === 'string' ? document.getElementById(buttonOrId) : buttonOrId;

      if (!button) {
        return;
      }

      button.addEventListener('click', function () {
        window.soilnwaterCopyShareLink(inputOrId, button);
      });
    };

    window.soilnwaterSetShareButtonFeedback = window.soilnwaterSetShareButtonFeedback || function soilnwaterSetShareButtonFeedback(buttonEl, message, resetMs) {
      if (!buttonEl) {
        return;
      }

      if (!buttonEl.dataset.originalHtml) {
        buttonEl.dataset.originalHtml = buttonEl.innerHTML;
      }

      buttonEl.textContent = message;
      window.setTimeout(function () {
        buttonEl.innerHTML = buttonEl.dataset.originalHtml;
      }, resetMs || 2200);
    };

    window.SOILNWATER_SHARE_JS_VERSION = '20260731c';

    window.soilnwaterIsAndroid = function soilnwaterIsAndroid() {
      return /Android/i.test(navigator.userAgent);
    };

    window.soilnwaterLaunchAndroidShareIntent = function soilnwaterLaunchAndroidShareIntent(text) {
      const intentUrl = 'intent:#Intent;action=android.intent.action.SEND;type=text/plain;S.android.intent.extra.TEXT='
        + encodeURIComponent(text) + ';end';
      const link = document.createElement('a');
      link.href = intentUrl;
      link.setAttribute('aria-hidden', 'true');
      link.style.cssText = 'position:fixed;left:-9999px;top:-9999px;opacity:0;pointer-events:none;';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    };

    window.soilnwaterOpenInstagramShare = async function soilnwaterOpenInstagramShare(url, text) {
      const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
      const shareText = text || 'Check this out on SoilnWater';
      const fullText = shareText + '\n' + url;

      if (!isMobile) {
        window.open('https://www.instagram.com/', '_blank', 'noopener');
        return 'desktop';
      }

      if (window.soilnwaterIsAndroid()) {
        if (navigator.share) {
          try {
            await navigator.share({ url: url });
            return 'shared';
          } catch (error) {
            if (error && error.name === 'AbortError') {
              return 'cancelled';
            }
          }

          try {
            await navigator.share({ text: fullText });
            return 'shared';
          } catch (error) {
            if (error && error.name === 'AbortError') {
              return 'cancelled';
            }
          }
        }

        window.soilnwaterLaunchAndroidShareIntent(url);
        return 'android-chooser';
      }

      if (navigator.share) {
        const sharePayload = { url: url, text: shareText };
        const canUsePayload = !navigator.canShare || navigator.canShare(sharePayload);

        try {
          if (canUsePayload) {
            await navigator.share(sharePayload);
          } else {
            await navigator.share({ url: url });
          }
          return 'shared';
        } catch (error) {
          if (error && error.name === 'AbortError') {
            return 'cancelled';
          }
        }
      }

      return 'copied-only';
    };

    window.soilnwaterShareToInstagram = async function soilnwaterShareToInstagram(options) {
      options = options || {};

      function resolve(target) {
        return typeof target === 'string' ? document.getElementById(target) : target;
      }

      const buttonEl = resolve(options.button);
      const inputEl = resolve(options.input);
      const url = window.soilnwaterNormalizeShareUrl(options.url || inputEl?.value || window.location.href);
      const shareText = options.text || 'Check this out on SoilnWater';

      if (inputEl) {
        inputEl.value = url;
      }

      await window.soilnwaterCopyShareLink(inputEl || { value: url }, null);

      const result = await window.soilnwaterOpenInstagramShare(url, shareText);

      if (result === 'cancelled') {
        return;
      }

      if (result === 'shared' || result === 'android-chooser') {
        window.soilnwaterSetShareButtonFeedback(buttonEl, 'Choose Instagram');
        return;
      }

      window.soilnwaterSetShareButtonFeedback(buttonEl, 'Link copied — paste in IG');
    };

    window.soilnwaterBindInstagramShareButton = function soilnwaterBindInstagramShareButton(buttonOrId, inputOrId, options) {
      const button = typeof buttonOrId === 'string' ? document.getElementById(buttonOrId) : buttonOrId;

      if (!button) {
        return;
      }

      button.addEventListener('click', function () {
        window.soilnwaterShareToInstagram(Object.assign({}, options || {}, {
          button: button,
          input: inputOrId,
        }));
      });
    };

    window.soilnwaterPopulateShareLinks = window.soilnwaterPopulateShareLinks || function soilnwaterPopulateShareLinks(options) {
      options = options || {};
      const url = window.soilnwaterNormalizeShareUrl(options.url || window.location.href);
      const whatsappSuffix = options.whatsappSuffix || 'Check this on SoilnWater';
      const qrSize = options.qrSize || 220;

      function resolve(target) {
        return typeof target === 'string' ? document.getElementById(target) : target;
      }

      const linkInput = resolve(options.linkInput);
      const qrImage = resolve(options.qrImage);
      const whatsappLink = resolve(options.whatsappLink);
      const facebookLink = resolve(options.facebookLink);

      if (linkInput) {
        linkInput.value = url;
      }

      if (qrImage) {
        qrImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=' + qrSize + 'x' + qrSize + '&data=' + encodeURIComponent(url);
      }

      if (whatsappLink) {
        whatsappLink.href = window.soilnwaterWhatsappShareUrl(url, whatsappSuffix);
      }

      if (facebookLink) {
        facebookLink.href = window.soilnwaterFacebookShareUrl(url);
      }

      return url;
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
  <div class="header-sticky-shell" id="headerStickyShell">
    @include('frontend.partials.header')
  </div>

  @yield('content')

  @if(request()->routeIs('community.*', 'frontend.community-posting-policy'))
    @include('frontend.partials.content-disclaimer')
  @endif

  @include('frontend.partials.footer')

  @include('discussions.partials.fab')

  <link rel="stylesheet" href="{{ asset('assets/css/discussion.css') }}?v={{ now()->timestamp }}">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script>window.initHeaderLocationAutocomplete = window.initHeaderLocationAutocomplete || function initHeaderLocationAutocomplete() {};</script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initHeaderLocationAutocomplete"></script>
  <script src="{{ asset('assets/js/main.js') }}?v={{ now()->timestamp }}" defer></script>
  <script src="{{ asset('assets/js/password-toggle.js') }}?v={{ now()->timestamp }}" defer></script>
  @stack('scripts')
</body>
</html>

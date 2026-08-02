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

window.SOILNWATER_SHARE_JS_VERSION = '20260802a';

window.soilnwaterIsAndroid = window.soilnwaterIsAndroid || function soilnwaterIsAndroid() {
  return /Android/i.test(navigator.userAgent);
};

window.soilnwaterLaunchAndroidShareIntent = window.soilnwaterLaunchAndroidShareIntent || function soilnwaterLaunchAndroidShareIntent(text) {
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

window.soilnwaterOpenInstagramShare = window.soilnwaterOpenInstagramShare || async function soilnwaterOpenInstagramShare(url, text) {
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

window.soilnwaterShareToInstagram = window.soilnwaterShareToInstagram || async function soilnwaterShareToInstagram(options) {
  options = options || {};

  function resolve(target) {
    return typeof target === 'string' ? document.getElementById(target) : target;
  }

  const buttonEl = resolve(options.button);
  const inputEl = resolve(options.input);
  const inputValue = inputEl && inputEl.value ? inputEl.value : '';
  const url = window.soilnwaterNormalizeShareUrl(options.url || inputValue || window.location.href);
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

window.soilnwaterBindInstagramShareButton = window.soilnwaterBindInstagramShareButton || function soilnwaterBindInstagramShareButton(buttonOrId, inputOrId, options) {
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

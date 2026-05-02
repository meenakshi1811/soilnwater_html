window.initHeaderLocationAutocomplete = window.initHeaderLocationAutocomplete || function initHeaderLocationAutocomplete() {};

(function(){
  const scroller = document.getElementById('catScroller');
  const btnL = document.getElementById('catLeft');
  const btnR = document.getElementById('catRight');

  if (!scroller || !btnL || !btnR) return;

  const getStep = () => scroller.clientWidth;

  btnL.addEventListener('click', () => {
    scroller.scrollBy({ left: -getStep(), behavior: 'smooth' });
  });

  btnR.addEventListener('click', () => {
    scroller.scrollBy({ left: getStep(), behavior: 'smooth' });
  });

  let autoDir = 1;
  let autoTimer;

  function autoScroll(){
    const maxScroll = scroller.scrollWidth - scroller.clientWidth;
    const step = getStep();

    if (scroller.scrollLeft >= maxScroll - 5) autoDir = -1;
    if (scroller.scrollLeft <= 5) autoDir = 1;

    scroller.scrollBy({ left: step * autoDir, behavior: 'smooth' });
  }

  function startAuto(){
    stopAuto();
    autoTimer = setInterval(autoScroll, 3500);
  }

  function stopAuto(){
    if (autoTimer) clearInterval(autoTimer);
  }

  scroller.addEventListener('mouseenter', stopAuto);
  scroller.addEventListener('mouseleave', startAuto);

  startAuto();
})();

(function(){
  const sliders = Array.from(document.querySelectorAll('.ad-slider, .auto-ad-slider'))
    .filter((element, index, all) => all.indexOf(element) === index);

  if (!sliders.length) return;

  sliders.forEach((slider) => {
    if (slider.dataset.sliderReady === 'true') return;

    const slides = Array.from(slider.children).filter((child) =>
      child.classList.contains('ad-slide') ||
      child.classList.contains('side-card') ||
      child.classList.contains('side-ad-promo') ||
      child.classList.contains('ad-wide-content') ||
      child.classList.contains('vendor-top-ad') ||
      child.classList.contains('adv-strip')
    );

    if (slides.length <= 1) {
      if (slides[0]) {
        slides[0].classList.add('ad-slide', 'is-active');
        slides[0].hidden = false;
      }
      slider.dataset.sliderReady = 'true';
      return;
    }

    slider.dataset.sliderReady = 'true';

    slides.forEach((slide, index) => {
      slide.classList.add('ad-slide');
      const isActive = index === 0;
      slide.classList.toggle('is-active', isActive);
      slide.hidden = !isActive;
    });

    let activeIndex = 0;
    let autoTimer;
    let dots = [];

    const updateDots = (index) => {
      if (!dots.length) return;
      dots.forEach((dot, dotIndex) => {
        const isActive = dotIndex === index;
        dot.classList.toggle('is-active', isActive);
        dot.setAttribute('aria-selected', String(isActive));
      });
    };

    const showSlide = (index) => {
      slides.forEach((slide, slideIndex) => {
        const isActive = slideIndex === index;
        slide.classList.toggle('is-active', isActive);
        slide.hidden = !isActive;
      });
      updateDots(index);
    };

    const goTo = (nextIndex) => {
      activeIndex = (nextIndex + slides.length) % slides.length;
      showSlide(activeIndex);
    };

    const showArrows = slider.dataset.showArrows !== 'false';

    if (showArrows) {
      const controlsWrap = document.createElement('div');
      controlsWrap.className = 'ad-slider-arrows';

      const prevBtn = document.createElement('button');
      prevBtn.className = 'ad-slider-arrow ad-slider-arrow-prev';
      prevBtn.type = 'button';
      prevBtn.setAttribute('aria-label', 'Previous ad');
      prevBtn.innerHTML = '&#10094;';

      const nextBtn = document.createElement('button');
      nextBtn.className = 'ad-slider-arrow ad-slider-arrow-next';
      nextBtn.type = 'button';
      nextBtn.setAttribute('aria-label', 'Next ad');
      nextBtn.innerHTML = '&#10095;';

      prevBtn.addEventListener('click', () => {
        goTo(activeIndex - 1);
        restartAuto();
      });

      nextBtn.addEventListener('click', () => {
        goTo(activeIndex + 1);
        restartAuto();
      });

      controlsWrap.appendChild(prevBtn);
      controlsWrap.appendChild(nextBtn);
      slider.appendChild(controlsWrap);
    }

    const showDots = slider.dataset.showDots === 'true';
    if (showDots) {
      const dotsWrap = document.createElement('div');
      dotsWrap.className = 'ad-slider-dots';
      dotsWrap.setAttribute('role', 'tablist');
      dotsWrap.setAttribute('aria-label', 'Slider pagination');

      dots = slides.map((_, index) => {
        const dotBtn = document.createElement('button');
        dotBtn.className = `ad-slider-dot${index === 0 ? ' is-active' : ''}`;
        dotBtn.type = 'button';
        dotBtn.setAttribute('role', 'tab');
        dotBtn.setAttribute('aria-label', `Go to slide ${index + 1}`);
        dotBtn.setAttribute('aria-selected', String(index === 0));
        dotBtn.addEventListener('click', () => {
          goTo(index);
          restartAuto();
        });
        dotsWrap.appendChild(dotBtn);
        return dotBtn;
      });

      slider.appendChild(dotsWrap);
    }

    const stopAuto = () => {
      if (autoTimer) clearInterval(autoTimer);
    };

    const startAuto = () => {
      stopAuto();
      autoTimer = setInterval(() => {
        goTo(activeIndex + 1);
      }, 3500);
    };

    const restartAuto = () => {
      startAuto();
    };

    const pauseOnHover = slider.dataset.pauseOnHover !== 'false';

    if (pauseOnHover) {
      slider.addEventListener('mouseenter', stopAuto);
      slider.addEventListener('mouseleave', startAuto);
    }

    slider.addEventListener('focusin', stopAuto);
    slider.addEventListener('focusout', startAuto);

    startAuto();
  });
})();

(function(){
  const topSidebarAds = document.querySelector('.top-sidebar-ads');
  const topFoldMain = document.querySelector('.top-fold-main');

  if (!topSidebarAds || !topFoldMain) return;

  const desktopMedia = window.matchMedia('(min-width: 992px)');

  const resetInlineHeights = () => {
    const adSlots = topSidebarAds.querySelectorAll('.ad-slider, .auto-ad-slider');
    topSidebarAds.style.height = '';
    topSidebarAds.style.minHeight = '';
    adSlots.forEach((slot) => {
      slot.style.height = '';
      slot.style.minHeight = '';
      slot.style.flex = '';
      slot.style.overflow = '';
    });
  };

  const applySidebarLayout = () => {
    if (!desktopMedia.matches) {
      resetInlineHeights();
      return;
    }

    const adSlots = Array.from(topSidebarAds.querySelectorAll('.ad-slider, .auto-ad-slider'));
    if (!adSlots.length) {
      resetInlineHeights();
      return;
    }

    const computedStyle = window.getComputedStyle(topSidebarAds);
    const gap = parseFloat(computedStyle.rowGap || computedStyle.gap || '0') || 0;
    const sponsoredSections = Array.from(topFoldMain.querySelectorAll('.sec'));
    const sectionHeights = sponsoredSections
      .map((section) => Math.ceil(section.getBoundingClientRect().height))
      .filter((height) => height > 0);

    if (!sectionHeights.length) return;

    const resolvedSlotHeights = adSlots.map((slot, index) => {
      const matchedSectionHeight = sectionHeights[index] || sectionHeights[sectionHeights.length - 1];
      return matchedSectionHeight;
    });

    const totalSidebarHeight = resolvedSlotHeights.reduce((sum, height) => sum + height, 0) + (gap * Math.max(0, adSlots.length - 1));

    topSidebarAds.style.height = `${totalSidebarHeight}px`;
    topSidebarAds.style.minHeight = `${totalSidebarHeight}px`;

    adSlots.forEach((slot, index) => {
      const slotHeight = resolvedSlotHeights[index];
      slot.style.height = `${slotHeight}px`;
      slot.style.minHeight = `${slotHeight}px`;
      slot.style.flex = `0 0 ${slotHeight}px`;
      slot.style.overflow = 'hidden';
    });
  };

  window.addEventListener('load', applySidebarLayout);
  window.addEventListener('resize', applySidebarLayout);
  window.setTimeout(applySidebarLayout, 60);
  window.setInterval(applySidebarLayout, 1200);

  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(applySidebarLayout);
    observer.observe(topSidebarAds);
    observer.observe(topFoldMain);
  }
})();



(function(){
  const topVendorSection = document.querySelector('.top-vendors-featured-slider');
  const topVendorSideSlider = document.querySelector('.top-vendor-side-slider');
  const vendorCards = Array.from(document.querySelectorAll('.vendor-grid .vendor-card, .vendor-grid .ad-slot-card'));
  const desktopMedia = window.matchMedia('(min-width: 992px)');

  if (!topVendorSection || !topVendorSideSlider || !vendorCards.length) return;

  const getActiveSideCard = () =>
    topVendorSideSlider.querySelector('.side-card.is-active') || topVendorSideSlider.querySelector('.side-card');

  const clearCardHeight = () => {
    vendorCards.forEach((card) => {
      card.style.minHeight = '';
      card.style.height = '';
    });
  };

  const syncVendorCardHeight = () => {
    if (!desktopMedia.matches) {
      clearCardHeight();
      return;
    }

    const activeSideCard = getActiveSideCard();
    if (!activeSideCard) return;

    const sideCardHeight = Math.ceil(activeSideCard.getBoundingClientRect().height);
    if (!sideCardHeight) return;

    vendorCards.forEach((card) => {
      card.style.minHeight = `${sideCardHeight}px`;
      card.style.height = `${sideCardHeight}px`;
    });
  };

  window.addEventListener('load', syncVendorCardHeight);
  window.addEventListener('resize', syncVendorCardHeight);
  window.setInterval(syncVendorCardHeight, 1200);

  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(syncVendorCardHeight);
    observer.observe(topVendorSideSlider);
    observer.observe(topVendorSection);
  }
})();

(function(){
  const ppngSideSlider = document.querySelector('.ppng-side-slider');
  const ppngCards = Array.from(document.querySelectorAll('.ppng-listings .listing-card'));
  const desktopMedia = window.matchMedia('(min-width: 992px)');

  if (!ppngSideSlider || !ppngCards.length) return;

  const getActiveSideCard = () =>
    ppngSideSlider.querySelector('.side-card.is-active') || ppngSideSlider.querySelector('.side-card');

  const clearCardHeight = () => {
    ppngCards.forEach((card) => {
      card.style.minHeight = '';
      card.style.height = '';
    });
  };

  const syncPpngCardHeight = () => {
    if (!desktopMedia.matches) {
      clearCardHeight();
      return;
    }

    const activeSideCard = getActiveSideCard();
    if (!activeSideCard) return;

    const sideCardHeight = Math.ceil(activeSideCard.getBoundingClientRect().height);
    if (!sideCardHeight) return;

    ppngCards.forEach((card) => {
      card.style.minHeight = `${sideCardHeight}px`;
      card.style.height = `${sideCardHeight}px`;
    });
  };

  window.addEventListener('load', syncPpngCardHeight);
  window.addEventListener('resize', syncPpngCardHeight);
  window.setInterval(syncPpngCardHeight, 1200);

  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(syncPpngCardHeight);
    observer.observe(ppngSideSlider);
  }
})();


(function(){
  const buildersSection = document.querySelector('.builders-developers-sec');
  const buildersSideStack = document.querySelector('.builders-side-ads-stack');
  const buildersSideSliders = Array.from(document.querySelectorAll('.builders-side-ads-stack .builders-side-slider'));
  const desktopMedia = window.matchMedia('(min-width: 992px)');

  if (!buildersSection || !buildersSideStack || !buildersSideSliders.length) return;

  const clearHeights = () => {
    buildersSideStack.style.height = '';
    buildersSideStack.style.minHeight = '';
    buildersSideSliders.forEach((slider) => {
      slider.style.height = '';
      slider.style.minHeight = '';
    });
  };

  const syncBuildersHeights = () => {
    if (!desktopMedia.matches) {
      clearHeights();
      return;
    }

    const sectionHeight = Math.ceil(buildersSection.getBoundingClientRect().height);
    if (!sectionHeight) return;

    buildersSideStack.style.height = `${sectionHeight}px`;
    buildersSideStack.style.minHeight = `${sectionHeight}px`;
  };

  window.addEventListener('load', syncBuildersHeights);
  window.addEventListener('resize', syncBuildersHeights);
  window.setTimeout(syncBuildersHeights, 100);
  window.setInterval(syncBuildersHeights, 1200);

  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(syncBuildersHeights);
    observer.observe(buildersSection);
    observer.observe(buildersSideStack);
  }
})();

(function(){
  const promoLayout = document.querySelector('.promo-layout');
  if (!promoLayout) return;

  const leftColumn = promoLayout.querySelector('.offer-coupon-wrap');
  const rightSlider = promoLayout.querySelector('.promo-side-slider');
  const desktopMedia = window.matchMedia('(min-width: 1200px)');

  if (!leftColumn || !rightSlider) return;

  const syncHeights = () => {
    rightSlider.style.height = '';
    rightSlider.style.minHeight = '';

    if (!desktopMedia.matches) return;

    const leftHeight = Math.ceil(leftColumn.getBoundingClientRect().height);
    if (leftHeight > 0) {
      rightSlider.style.height = `${leftHeight}px`;
      rightSlider.style.minHeight = `${leftHeight}px`;
    }
  };

  window.addEventListener('load', syncHeights);
  window.addEventListener('resize', syncHeights);
  window.setTimeout(syncHeights, 80);
  window.setInterval(syncHeights, 1200);
})();

(function () {
  const trigger = document.getElementById('googleRegisterTrigger');
  const modalElement = document.getElementById('googleRoleModal');
  const roleSelect = document.getElementById('google_role');
  const continueBtn = document.getElementById('googleRoleContinueBtn');
  const registerRoleSelect = document.getElementById('role');

  if (!trigger || !modalElement || !roleSelect || !continueBtn) return;
  if (typeof bootstrap === 'undefined' || !bootstrap.Modal) return;

  const roleModal = new bootstrap.Modal(modalElement);

  const syncContinueState = () => {
    continueBtn.disabled = roleSelect.value.trim() === '';
  };

  if (registerRoleSelect && registerRoleSelect.value) {
    roleSelect.value = registerRoleSelect.value;
  }

  syncContinueState();

  trigger.addEventListener('click', () => {
    if (registerRoleSelect && registerRoleSelect.value) {
      roleSelect.value = registerRoleSelect.value;
    }

    syncContinueState();
    roleModal.show();
  });

  roleSelect.addEventListener('change', () => {
    if (registerRoleSelect) {
      registerRoleSelect.value = roleSelect.value;
    }
    syncContinueState();
  });

  modalElement.addEventListener('shown.bs.modal', () => {
    roleSelect.focus();
  });
})();

(function () {
  if (document.body.classList.contains('admin-body')) {
    return;
  }

  const hash = window.location.hash;
  if (hash !== '#post-ad' && hash !== '#post-offer') {
    return;
  }

  const target = document.querySelector(hash);
  if (!target) {
    return;
  }

  window.requestAnimationFrame(() => {
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();


(function () {
  const locationInput = document.getElementById('headerCurrentLocation');
  const eligiblePaths = ['/', '/offers-market', '/ads-market'];
  const isEligiblePath = eligiblePaths.includes(window.location.pathname);

  function updateLocationLabel(value) {
    const resolved = value || (locationInput && locationInput.dataset.defaultLocation) || 'Your Location';
    if (!locationInput) return;
    locationInput.value = resolved;
    locationInput.setAttribute('title', resolved);
  }

  function showSearchOnlyLocationField() {
    if (!locationInput) return;
    const locationWrapElement = locationInput.closest('.loc-wrap');
    const locationPin = locationWrapElement ? locationWrapElement.querySelector('.loc-pin') : null;
    const locationCaretElement = locationWrapElement ? locationWrapElement.querySelector('.loc-caret') : null;

    locationInput.value = '';
    locationInput.placeholder = 'Search location';
    locationInput.setAttribute('title', 'Search location');

    if (locationPin) locationPin.style.display = 'none';
    if (locationCaretElement) locationCaretElement.style.display = 'none';

    if (locationWrapElement) {
      locationWrapElement.removeAttribute('role');
      locationWrapElement.removeAttribute('tabindex');
      locationWrapElement.removeAttribute('aria-haspopup');
      locationWrapElement.setAttribute('aria-expanded', 'false');
    }
  }

  function syncLocationToSession(lat, lng) {
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return Promise.resolve();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    return fetch('/frontend/location', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ lat, lng })
    }).catch(() => null);
  }

  async function fetchLocationName(lat, lng) {
    try {
      const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(String(lat))}&lon=${encodeURIComponent(String(lng))}`);
      if (!response.ok) return null;
      const payload = await response.json();
      const address = payload && payload.address ? payload.address : {};
      return address.city || address.town || address.village || address.suburb || payload.display_name || null;
    } catch (error) {
      return null;
    }
  }


  const locationWrap = document.querySelector('.loc-wrap');
  const locationCaret = locationWrap ? locationWrap.querySelector('.loc-caret') : null;

  if (locationWrap && locationInput) {
    locationWrap.addEventListener('click', (event) => {
      if (event.target === locationInput) return;
      locationInput.focus();
    });
  }

  if (locationCaret && locationInput) {
    locationCaret.addEventListener('click', () => {
      locationInput.focus();
    });
  }
  window.initHeaderLocationAutocomplete = function initHeaderLocationAutocomplete() {
    if (!locationInput || !window.google || !google.maps || !google.maps.places) return;

    const autocomplete = new google.maps.places.Autocomplete(locationInput, {
      fields: ['formatted_address', 'geometry', 'name'],
      componentRestrictions: { country: 'in' }
    });

    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace();
      const lat = place && place.geometry && place.geometry.location ? place.geometry.location.lat() : null;
      const lng = place && place.geometry && place.geometry.location ? place.geometry.location.lng() : null;
      const selectedLocation = (place && (place.formatted_address || place.name)) ? (place.formatted_address || place.name) : '';

      if (!selectedLocation || typeof lat !== 'number' || typeof lng !== 'number') return;

      updateLocationLabel(selectedLocation);
      localStorage.setItem('frontendLocationName', selectedLocation);

      syncLocationToSession(lat, lng).finally(() => {
        sessionStorage.setItem('frontendLocationSynced', '1');
        const refreshUrl = new URL(window.location.href);
        refreshUrl.searchParams.delete('lat');
        refreshUrl.searchParams.delete('lng');
        window.location.replace(refreshUrl.toString());
      });
    });
  };

  const searchParams = new URLSearchParams(window.location.search);
  const rawLat = searchParams.get('lat');
  const rawLng = searchParams.get('lng');
  const hasCoordinatesInUrl = rawLat !== null && rawLat !== '' && rawLng !== null && rawLng !== '';

  if (hasCoordinatesInUrl) {
    sessionStorage.removeItem('frontendLocationSynced');
    const currentLat = Number(rawLat);
    const currentLng = Number(rawLng);

    if (Number.isFinite(currentLat) && Number.isFinite(currentLng)) {
      fetchLocationName(currentLat, currentLng).then((name) => {
        const resolvedName = name || 'Current location';
        updateLocationLabel(resolvedName);
        localStorage.setItem('frontendLocationName', resolvedName);
      });
      return;
    }
  }

  if (navigator.permissions && navigator.permissions.query) {
    navigator.permissions.query({ name: 'geolocation' }).then((permissionStatus) => {
      permissionStatus.onchange = () => {
        if (permissionStatus.state === 'granted') {
          window.location.reload();
        }
      };
    }).catch(() => null);
  }

  const hasSessionSyncMarker = sessionStorage.getItem('frontendLocationSynced') === '1';
  const cachedLocationName = localStorage.getItem('frontendLocationName');

  if (hasSessionSyncMarker) {
    if (cachedLocationName) updateLocationLabel(cachedLocationName);
    return;
  }

  if (!navigator.geolocation || !isEligiblePath) {
    showSearchOnlyLocationField();
    return;
  }

  navigator.geolocation.getCurrentPosition(async (position) => {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;

    const name = await fetchLocationName(lat, lng);
    const resolvedName = name || 'Current location';
    updateLocationLabel(resolvedName);
    localStorage.setItem('frontendLocationName', resolvedName);

    syncLocationToSession(lat, lng).finally(() => {
      sessionStorage.setItem('frontendLocationSynced', '1');
      const refreshUrl = new URL(window.location.href);
      refreshUrl.searchParams.delete('lat');
      refreshUrl.searchParams.delete('lng');
      window.location.replace(refreshUrl.toString());
    });
  }, () => {
    showSearchOnlyLocationField();
  }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 300000 });
})();

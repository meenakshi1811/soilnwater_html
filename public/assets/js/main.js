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
      const slotContentHeight = Array.from(slot.querySelectorAll('.ad-slide')).reduce((maxHeight, slide) => {
        return Math.max(maxHeight, Math.ceil(slide.scrollHeight));
      }, 0);
      return Math.max(matchedSectionHeight, slotContentHeight);
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
  const form = document.querySelector('#create-new-lead, form[data-form="create-new-lead"]');
  if (!form) return;

  const categorySelect = form.querySelector('[name="subscriber_category"], [name="category"], #subscriber_category, #category');
  const subcategorySelect = form.querySelector('[name="subscriber_sub_category"], [name="sub_category"], [name="subcategory"], #subscriber_sub_category, #sub_category, #subcategory');
  const countrySelect = form.querySelector('[name="country"], [name="countries"], [name="countries[]"], #country, #countries');
  const preferenceSelect = form.querySelector('[name="preferences"], [name="preferences[]"], [name="preference_countries"], [name="preference_countries[]"], #preferences, #preference_countries');

  if (!categorySelect || !subcategorySelect || !countrySelect) return;

  const initialCountryOptions = Array.from(countrySelect.options).map((option) => ({
    value: option.value,
    label: option.textContent.trim(),
    disabled: option.disabled,
    selected: option.selected
  }));
  let baseCountryOptions = initialCountryOptions.slice();

  const normalize = (value) => String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
  const uniqueByValue = (items) => {
    const seen = new Set();
    return items.filter((item) => {
      const key = normalize(item.value) + '|' + normalize(item.label);
      if (seen.has(key)) return false;
      seen.add(key);
      return true;
    });
  };

  const countryAliases = {
    uk: ['uk', 'u k', 'united kingdom', 'great britain', 'britain', 'england'],
    usa: ['us', 'u s', 'usa', 'u s a', 'united states', 'united states of america', 'america'],
    uae: ['uae', 'u a e', 'united arab emirates']
  };

  const countryMatches = (option, countryName) => {
    const needle = normalize(countryName);
    const haystack = normalize(option.label + ' ' + option.value);

    if (needle === 'us') {
      return countryAliases.usa.some((alias) => haystack.includes(normalize(alias)));
    }
    if (needle === 'uk') {
      return countryAliases.uk.some((alias) => haystack.includes(normalize(alias)));
    }
    if (needle === 'uae') {
      return countryAliases.uae.some((alias) => haystack.includes(normalize(alias)));
    }

    return haystack.includes(needle);
  };

  const prioritizedThenRest = (prioritizedCountries) => {
    const placeholders = baseCountryOptions.filter((option) => option.value === '' || option.disabled);
    const normalOptions = baseCountryOptions.filter((option) => option.value !== '' && !option.disabled);

    const prioritized = [];
    prioritizedCountries.forEach((countryName) => {
      const found = normalOptions.find((option) => countryMatches(option, countryName));
      if (found) prioritized.push(found);
    });

    const prioritizedSet = new Set(prioritized.map((item) => normalize(item.value) + '|' + normalize(item.label)));
    const rest = normalOptions.filter((option) => !prioritizedSet.has(normalize(option.value) + '|' + normalize(option.label)));

    return uniqueByValue(placeholders.concat(prioritized, rest));
  };

  const onlyCountries = (countries) => {
    const placeholders = baseCountryOptions.filter((option) => option.value === '' || option.disabled);
    const normalOptions = baseCountryOptions.filter((option) => option.value !== '' && !option.disabled);

    const filtered = normalOptions.filter((option) => countries.some((country) => countryMatches(option, country)));
    return uniqueByValue(placeholders.concat(filtered));
  };

  const rebuildSelectOptions = (selectEl, options) => {
    if (!selectEl) return;
    const previousValues = new Set(Array.from(selectEl.selectedOptions).map((option) => option.value));
    selectEl.innerHTML = '';

    options.forEach((option) => {
      const nextOption = document.createElement('option');
      nextOption.value = option.value;
      nextOption.textContent = option.label;
      nextOption.disabled = !!option.disabled;
      if (previousValues.has(option.value)) {
        nextOption.selected = true;
      }
      selectEl.appendChild(nextOption);
    });

    const hasSelectedValue = Array.from(selectEl.selectedOptions).some((option) => option.value !== '');
    if (!hasSelectedValue && selectEl.options.length) {
      selectEl.selectedIndex = 0;
    }

    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const visaRules = {
    'general visa consultants': { type: 'all' },
    'study abroad consultants': { type: 'all' },
    'oisc iaa advisors': { type: 'only', countries: ['UK'] },
    'iccrc advisors': { type: 'only', countries: ['Canada'] },
    'mara advisors': { type: 'only', countries: ['Australia'] },
    'work visa business visas': { type: 'all' },
    'pr settlement visas': { type: 'prioritized', countries: ['Canada', 'Australia', 'New Zealand'] },
    'us immigration attorney': { type: 'only', countries: ['US'] },
    'immigration law firm': { type: 'all' },
    'mbbs study for foreign students': { type: 'only', countries: ['China', 'Philippines', 'Dominica', 'Russia', 'Georgia'] }
  };

  const cbiCountries = ['US', 'Portugal', 'Turkey', 'Grenada', 'Dominica', 'UAE'];

  const getSubcategoryLabel = () => {
    const selectedOption = subcategorySelect.options[subcategorySelect.selectedIndex];
    return normalize(selectedOption ? selectedOption.textContent : subcategorySelect.value);
  };

  const getCategoryLabel = () => {
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    return normalize(selectedOption ? selectedOption.textContent : categorySelect.value);
  };

  const resolveCountryOptions = () => {
    const category = getCategoryLabel();
    const subcategory = getSubcategoryLabel();

    if (subcategory.includes('cbi')) {
      return onlyCountries(cbiCountries);
    }

    if (!category.includes('visa') || !category.includes('consult')) {
      return baseCountryOptions;
    }

    const mappedKey = Object.keys(visaRules).find((ruleKey) => subcategory.includes(ruleKey));
    if (!mappedKey) {
      return baseCountryOptions;
    }

    const rule = visaRules[mappedKey];
    if (rule.type === 'only') {
      return onlyCountries(rule.countries || []);
    }

    if (rule.type === 'prioritized') {
      return prioritizedThenRest(rule.countries || []);
    }

    return baseCountryOptions;
  };

  const syncCountries = () => {
    const nextOptions = resolveCountryOptions();
    rebuildSelectOptions(countrySelect, nextOptions);
    if (preferenceSelect) {
      rebuildSelectOptions(preferenceSelect, nextOptions);
    }
  };

  const normalizeCountryApiItem = (item) => {
    if (!item || typeof item !== 'object') return null;
    const label = String(item.name || item.label || '').trim();
    const value = String(item.id || item.iso2 || item.code || label).trim();
    if (!label || !value) return null;
    return {
      value: value,
      label: label,
      disabled: false,
      selected: false
    };
  };

  const hydrateCountriesFromTable = async () => {
    const endpoint = form.dataset.countriesUrl || '/countries/options';
    if (!endpoint) return;

    try {
      const response = await fetch(endpoint, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      if (!response.ok) return;
      const payload = await response.json();
      const countries = Array.isArray(payload)
        ? payload
        : (Array.isArray(payload.data) ? payload.data : []);

      const normalizedCountries = countries
        .map(normalizeCountryApiItem)
        .filter(Boolean);

      if (!normalizedCountries.length) return;

      const placeholders = initialCountryOptions.filter((option) => option.value === '' || option.disabled);
      baseCountryOptions = uniqueByValue(placeholders.concat(normalizedCountries));
      syncCountries();
    } catch (error) {
      // Keep current in-DOM country options as fallback when countries API is unavailable.
    }
  };

  categorySelect.addEventListener('change', syncCountries);
  subcategorySelect.addEventListener('change', syncCountries);
  syncCountries();
  hydrateCountriesFromTable();
})();

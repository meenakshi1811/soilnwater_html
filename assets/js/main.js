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

    const resolvedSlotHeights = adSlots.map((_, index) => {
      if (sectionHeights[index]) return sectionHeights[index];
      return sectionHeights[sectionHeights.length - 1];
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

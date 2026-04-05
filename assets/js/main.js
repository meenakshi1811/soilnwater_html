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

    const showSlide = (index) => {
      slides.forEach((slide, slideIndex) => {
        const isActive = slideIndex === index;
        slide.classList.toggle('is-active', isActive);
        slide.hidden = !isActive;
      });
    };

    const goTo = (nextIndex) => {
      activeIndex = (nextIndex + slides.length) % slides.length;
      showSlide(activeIndex);
    };

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
  const topFoldMain = document.querySelector('.top-fold-main');
  const topSidebarAds = document.querySelector('.top-sidebar-ads');

  if (!topFoldMain || !topSidebarAds) return;

  const desktopMedia = window.matchMedia('(min-width: 992px)');
  const leftSections = Array.from(topFoldMain.children).filter((child) => child.classList.contains('sec'));
  const rightSlots = Array.from(topSidebarAds.children).filter((child) => child.classList.contains('ad-slider') || child.classList.contains('auto-ad-slider'));

  if (!leftSections.length || !rightSlots.length) return;

  const clearHeights = () => {
    rightSlots.forEach((slot) => {
      slot.style.height = '';
      slot.style.minHeight = '';
      slot.style.flex = '';
    });
  };

  const syncHeights = () => {
    if (!desktopMedia.matches) {
      clearHeights();
      return;
    }

    leftSections.forEach((section, index) => {
      const targetSlot = rightSlots[index];
      if (!targetSlot) return;
      const sectionHeight = Math.ceil(section.getBoundingClientRect().height);
      targetSlot.style.setProperty('height', `${sectionHeight}px`, 'important');
      targetSlot.style.setProperty('min-height', `${sectionHeight}px`, 'important');
      targetSlot.style.flex = '0 0 auto';
    });
  };

  window.addEventListener('load', syncHeights);
  window.addEventListener('resize', syncHeights);
  window.setTimeout(syncHeights, 80);
  window.setTimeout(syncHeights, 450);

  if (typeof ResizeObserver !== 'undefined') {
    const observer = new ResizeObserver(syncHeights);
    leftSections.forEach((section) => observer.observe(section));
  }
})();

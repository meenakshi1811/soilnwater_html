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
  const sliderCandidates = Array.from(document.querySelectorAll('.ad-slider, .auto-ad-slider, [data-ad-slider]'));
  const sliders = sliderCandidates.filter((element, index) => sliderCandidates.indexOf(element) === index);
  if (!sliders.length) return;

  const dummyAds = [
    {
      title: 'Premium Farm Supplies',
      sub: 'Boost visibility for seeds, tools, and agri services.',
      cta: 'View Offer',
      image: 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=600&q=70'
    },
    {
      title: 'Local Business Spotlight',
      sub: 'Run geo-targeted ads for high-intent local buyers.',
      cta: 'Promote Now',
      image: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=600&q=70'
    },
    {
      title: 'Festival Campaign Deals',
      sub: 'Showcase seasonal offers across top marketplace sections.',
      cta: 'Book Slot',
      image: 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=600&q=70'
    }
  ];

  const slideSelectors = [
    ':scope > .ad-slide',
    ':scope > .side-card',
    ':scope > .side-ad-promo',
    ':scope > .ad-wide-content',
    ':scope > .vendor-top-ad',
    ':scope > .adv-strip'
  ];

  const hydrateSlide = (slide, data) => {
    const img = slide.querySelector('.side-card-img');
    if (img) {
      img.src = data.image;
      img.alt = data.title;
    }
    const heading = slide.querySelector('.side-card-body h3, .side-ad-title, .vendor-top-ad-title, h3');
    if (heading) heading.textContent = data.title;

    const subText = slide.querySelector('.side-card-body p, .side-ad-sub, .vendor-top-ad-sub, p');
    if (subText) subText.textContent = data.sub;

    const cta = slide.querySelector('.btn-learn, .side-ad-btn, .vendor-top-ad-btn, .ad-slot-btn');
    if (cta) cta.textContent = data.cta;

    if (slide.classList.contains('adv-strip')) {
      slide.textContent = data.title.toUpperCase();
    }
  };

  sliders.forEach((slider) => {
    slider.classList.add('ad-slider');
    const seedCards = slideSelectors
      .flatMap((selector) => Array.from(slider.querySelectorAll(selector)))
      .filter((element, index, arr) => arr.indexOf(element) === index);
    if (!seedCards.length) return;

    seedCards.forEach((card) => card.classList.add('ad-slide'));

    if (seedCards.length < 3) {
      const originalCards = [...seedCards];
      let cloneIndex = 0;
      while (slider.querySelectorAll(':scope > .ad-slide').length < 3) {
        const clone = originalCards[cloneIndex % originalCards.length].cloneNode(true);
        hydrateSlide(clone, dummyAds[(cloneIndex + 1) % dummyAds.length]);
        clone.classList.add('ad-slide');
        slider.appendChild(clone);
        cloneIndex += 1;
      }
    }

    const slides = Array.from(slider.querySelectorAll(':scope > .ad-slide')).slice(0, 3);
    slides.forEach((slide, index) => {
      if (index > 0) hydrateSlide(slide, dummyAds[index % dummyAds.length]);
    });

    Array.from(slider.querySelectorAll(':scope > .ad-slide')).slice(3).forEach((extra) => {
      extra.hidden = true;
      extra.classList.remove('is-active');
    });

    let activeIndex = 0;
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

    controlsWrap.appendChild(prevBtn);
    controlsWrap.appendChild(nextBtn);
    slider.appendChild(controlsWrap);

    const showSlide = (index) => {
      slides.forEach((slide, slideIndex) => {
        slide.hidden = slideIndex !== index;
        slide.classList.toggle('is-active', slideIndex === index);
      });
    };

    prevBtn.addEventListener('click', () => {
      activeIndex = (activeIndex - 1 + slides.length) % slides.length;
      showSlide(activeIndex);
    });

    nextBtn.addEventListener('click', () => {
      activeIndex = (activeIndex + 1) % slides.length;
      showSlide(activeIndex);
    });

    showSlide(activeIndex);

    setInterval(() => {
      activeIndex = (activeIndex + 1) % slides.length;
      showSlide(activeIndex);
    }, 3500);
  });
})();

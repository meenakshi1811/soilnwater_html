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
  const sliders = document.querySelectorAll('.auto-ad-slider');
  if (!sliders.length) return;

  sliders.forEach((slider) => {
    const seedCards = Array.from(slider.querySelectorAll(':scope > .side-card'));
    if (!seedCards.length) return;

    if (seedCards.length < 3) {
      const originalCards = [...seedCards];
      let cloneIndex = 0;
      while (slider.querySelectorAll(':scope > .side-card').length < 3) {
        const clone = originalCards[cloneIndex % originalCards.length].cloneNode(true);
        slider.appendChild(clone);
        cloneIndex += 1;
      }
    }

    const slides = Array.from(slider.querySelectorAll(':scope > .side-card')).slice(0, 3);
    Array.from(slider.querySelectorAll(':scope > .side-card')).slice(3).forEach((extra) => {
      extra.style.display = 'none';
    });

    let activeIndex = 0;
    const showSlide = (index) => {
      slides.forEach((slide, slideIndex) => {
        slide.classList.toggle('is-active', slideIndex === index);
      });
    };

    showSlide(activeIndex);

    setInterval(() => {
      activeIndex = (activeIndex + 1) % slides.length;
      showSlide(activeIndex);
    }, 3500);
  });
})();

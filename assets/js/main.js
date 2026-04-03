(function(){
  const scroller = document.getElementById('catScroller');
  const btnL = document.getElementById('catLeft');
  const btnR = document.getElementById('catRight');
  const STEP = 294;

  if (!scroller || !btnL || !btnR) return;

  btnL.addEventListener('click', () => { scroller.scrollLeft -= STEP; });
  btnR.addEventListener('click', () => { scroller.scrollLeft += STEP; });

  let autoDir = 1;
  let autoTimer;

  function autoScroll(){
    const maxScroll = scroller.scrollWidth - scroller.clientWidth;
    if (scroller.scrollLeft >= maxScroll - 5) autoDir = -1;
    if (scroller.scrollLeft <= 5) autoDir = 1;
    scroller.scrollLeft += STEP * autoDir;
  }

  function startAuto(){
    stopAuto();
    autoTimer = setInterval(autoScroll, 3000);
  }

  function stopAuto(){
    if (autoTimer) clearInterval(autoTimer);
  }

  scroller.addEventListener('mouseenter', stopAuto);
  scroller.addEventListener('mouseleave', startAuto);

  startAuto();
})();

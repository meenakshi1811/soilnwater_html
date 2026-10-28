// Listing pages' Top categories: one row that scrolls sideways, with arrow buttons at its ends
// (shown while there is more to scroll to; phones swipe instead, see vendors-page.css).
(function () {
    'use strict';

    document.querySelectorAll('.vendors-categories-strip:not(.vendors-categories-strip--all)').forEach(function (strip) {
        var wrap = document.createElement('div');
        wrap.className = 'vendors-categories-scroller';
        strip.parentNode.insertBefore(wrap, strip);
        wrap.appendChild(strip);

        [['prev', -1, 'fa-chevron-left', 'Scroll categories left'], ['next', 1, 'fa-chevron-right', 'Scroll categories right']].forEach(function (arrow) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'vendors-categories-scroller__arrow vendors-categories-scroller__arrow--' + arrow[0];
            button.setAttribute('aria-label', arrow[3]);
            button.innerHTML = '<i class="fa-solid ' + arrow[2] + '" aria-hidden="true"></i>';
            button.addEventListener('click', function () {
                strip.scrollBy({ left: arrow[1] * Math.max(160, strip.clientWidth * 0.8), behavior: 'smooth' });
            });
            wrap.appendChild(button);
        });

        function sync() {
            var max = strip.scrollWidth - strip.clientWidth;
            wrap.classList.toggle('can-prev', strip.scrollLeft > 4);
            wrap.classList.toggle('can-next', strip.scrollLeft < max - 4);
        }

        strip.addEventListener('scroll', sync, { passive: true });
        window.addEventListener('resize', sync);
        window.addEventListener('load', sync);
        sync();
    });
})();

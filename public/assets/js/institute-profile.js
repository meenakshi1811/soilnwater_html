document.addEventListener('DOMContentLoaded', function () {
    var pageRoot = document.getElementById('instituteProfilePage');
    if (!pageRoot) {
        return;
    }

    if (typeof window.initProfileSectionNav === 'function') {
        window.initProfileSectionNav('.js-sch-nav-link');
    }

    document.querySelectorAll('.js-sch-read-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = document.querySelector('.js-sch-about-text');
            if (!text) {
                return;
            }

            var collapsed = text.classList.toggle('is-collapsed');
            btn.innerHTML = collapsed
                ? 'Read More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>'
                : 'Read Less <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>';
        });
    });

    var noticeCarouselTimer = null;

    function initNoticeCarousel() {
        var carousel = document.querySelector('.js-sch-notice-carousel');
        if (!carousel) {
            return;
        }

        var track = carousel.querySelector('.js-sch-notice-track');
        var viewport = carousel.querySelector('.sch-notices__viewport');
        var slides = carousel.querySelectorAll('.sch-notice');
        var prevBtn = carousel.querySelector('.js-sch-notice-prev');
        var nextBtn = carousel.querySelector('.js-sch-notice-next');
        var currentIndex = 0;

        if (noticeCarouselTimer) {
            clearInterval(noticeCarouselTimer);
            noticeCarouselTimer = null;
        }

        function slideOffset() {
            if (!viewport) {
                return slides[0] ? slides[0].getBoundingClientRect().width : 0;
            }

            return viewport.clientWidth;
        }

        function showSlide(index) {
            if (!slides.length || !track) {
                return;
            }

            currentIndex = (index + slides.length) % slides.length;
            carousel.dataset.slideIndex = String(currentIndex);
            track.style.transform = 'translateX(-' + (currentIndex * slideOffset()) + 'px)';
        }

        if (prevBtn) {
            prevBtn.onclick = function () {
                showSlide(currentIndex - 1);
            };
        }

        if (nextBtn) {
            nextBtn.onclick = function () {
                showSlide(currentIndex + 1);
            };
        }

        showSlide(0);

        if (slides.length > 1) {
            noticeCarouselTimer = setInterval(function () {
                showSlide(currentIndex + 1);
            }, 7000);
        }

        if (!carousel.dataset.resizeBound) {
            carousel.dataset.resizeBound = '1';
            window.addEventListener('resize', function () {
                var carouselEl = document.getElementById('schNoticeCarousel');
                if (!carouselEl) {
                    return;
                }

                var trackEl = carouselEl.querySelector('.js-sch-notice-track');
                var viewportEl = carouselEl.querySelector('.sch-notices__viewport');
                var idx = parseInt(carouselEl.dataset.slideIndex || '0', 10);

                if (!trackEl || !viewportEl) {
                    return;
                }

                trackEl.style.transform = 'translateX(-' + (idx * viewportEl.clientWidth) + 'px)';
            });
        }
    }

    initNoticeCarousel();

    document.querySelectorAll('.js-sch-notice-read-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var notice = btn.closest('.sch-notice');
            var modalEl = document.getElementById('schNoticeModal');
            if (!notice || !modalEl || !window.bootstrap) {
                return;
            }

            var titleEl = modalEl.querySelector('#schNoticeModalLabel');
            var bodyEl = modalEl.querySelector('#schNoticeModalBody');
            var expiryEl = modalEl.querySelector('#schNoticeModalExpiry');

            if (titleEl) {
                titleEl.textContent = notice.dataset.noticeTitle || 'Notice';
            }

            if (bodyEl) {
                bodyEl.textContent = notice.dataset.noticeMessage || '';
            }

            if (expiryEl) {
                expiryEl.textContent = notice.dataset.noticeExpires
                    ? 'Valid until ' + notice.dataset.noticeExpires
                    : '';
            }

            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });

    var shareBtn = document.querySelector('.js-sch-share');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            var shareData = {
                title: pageRoot.dataset.shareTitle || document.title,
                url: pageRoot.dataset.shareUrl || window.location.href,
            };

            if (navigator.share) {
                navigator.share(shareData).catch(function () {});
                return;
            }

            if (navigator.clipboard && shareData.url) {
                navigator.clipboard.writeText(shareData.url).then(function () {
                    alert('Profile link copied to clipboard.');
                });
            }
        });
    }

    if (typeof window.initInstituteEnquiryForm === 'function') {
        window.initInstituteEnquiryForm({
            form: document.getElementById('instituteEnquiryForm'),
            enquiryUrl: pageRoot.dataset.enquiryUrl,
            loginUrl: pageRoot.dataset.loginUrl,
            feedbackEl: document.getElementById('instituteEnquiryFeedback'),
            submitBtn: document.querySelector('.js-institute-enquiry-submit'),
        });
    }
});

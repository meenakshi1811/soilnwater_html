document.addEventListener('DOMContentLoaded', function () {
    var pageRoot = document.getElementById('schoolProfilePage');
    if (!pageRoot) {
        return;
    }

    var navLinks = document.querySelectorAll('.js-sch-nav-link');
    var sections = [];

    navLinks.forEach(function (link) {
        var targetId = link.getAttribute('href')?.replace('#', '');
        if (!targetId) {
            return;
        }

        var section = document.getElementById(targetId);
        if (section && !sections.some(function (item) { return item.id === targetId; })) {
            sections.push({ id: targetId, el: section });
        }

        link.addEventListener('click', function (event) {
            event.preventDefault();
            section?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            setActiveNav(targetId);
        });
    });

    function setActiveNav(activeId) {
        document.querySelectorAll('.js-sch-nav-link').forEach(function (link) {
            var href = link.getAttribute('href')?.replace('#', '');
            link.classList.toggle('is-active', href === activeId);
        });
    }

    if (sections.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    setActiveNav(entry.target.id);
                }
            });
        }, {
            rootMargin: '-30% 0px -55% 0px',
            threshold: 0,
        });

        sections.forEach(function (item) {
            observer.observe(item.el);
        });
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

    document.querySelectorAll('.js-sch-gallery-open').forEach(function (button) {
        button.addEventListener('click', function () {
            var modalEl = document.getElementById('schoolGalleryModal');
            var img = document.getElementById('schoolGalleryModalImg');
            if (!modalEl || !img || !window.bootstrap) {
                return;
            }

            img.src = button.getAttribute('data-gallery-src') || '';
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });

    function openShareModal() {
        var modalEl = document.getElementById('schoolShareModal');
        if (modalEl && window.bootstrap) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            return;
        }

        var url = pageRoot.dataset.shareUrl || window.location.href;
        if (navigator.share) {
            navigator.share({
                title: pageRoot.dataset.shareTitle || document.title,
                url: url,
            }).catch(function () {});
            return;
        }

        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function () {
                alert('Profile link copied to clipboard.');
            });
        }
    }

    document.querySelectorAll('.js-sch-share').forEach(function (btn) {
        btn.addEventListener('click', openShareModal);
    });

    document.querySelectorAll('.js-sch-copy-url').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById('schoolShareUrl');
            var url = input?.value || pageRoot.dataset.shareUrl || window.location.href;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    btn.textContent = 'Copied';
                    window.setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
                });
            }
        });
    });

    document.querySelectorAll('.js-sch-helpful').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.js-sch-helpful').forEach(function (item) {
                item.classList.remove('is-selected');
            });
            btn.classList.add('is-selected');
        });
    });

    document.querySelectorAll('.js-sch-report').forEach(function (btn) {
        btn.addEventListener('click', function () {
            alert('Thank you. Our team will review this profile.');
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

    if (typeof window.initInstituteEnquiryForm === 'function') {
        window.initInstituteEnquiryForm({
            form: document.getElementById('schoolEnquiryForm'),
            enquiryUrl: pageRoot.dataset.enquiryUrl,
            loginUrl: pageRoot.dataset.loginUrl,
            feedbackEl: document.getElementById('schoolEnquiryFeedback'),
            submitBtn: document.querySelector('.js-school-enquiry-submit'),
        });
    }
});

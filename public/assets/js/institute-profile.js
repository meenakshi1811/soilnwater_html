document.addEventListener('DOMContentLoaded', function () {
    var pageRoot = document.getElementById('instituteProfilePage');
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
        if (section) {
            sections.push({ id: targetId, el: section, link: link });
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

    var form = document.getElementById('instituteEnquiryForm');
    if (!form) {
        return;
    }

    var enquiryUrl = pageRoot.dataset.enquiryUrl;
    var feedback = document.getElementById('instituteEnquiryFeedback');
    var submitBtn = form.querySelector('.js-institute-enquiry-submit');
    var btnText = submitBtn?.querySelector('.js-enquiry-btn-text');
    var btnSending = submitBtn?.querySelector('.js-enquiry-btn-sending');

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!enquiryUrl || !submitBtn) {
            return;
        }

        submitBtn.disabled = true;
        if (btnText) btnText.classList.add('d-none');
        if (btnSending) btnSending.classList.remove('d-none');
        if (feedback) {
            feedback.classList.add('d-none');
            feedback.classList.remove('alert-success', 'alert-danger');
        }

        try {
            var response = await fetch(enquiryUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            var payload = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                throw new Error(payload.message || 'Unable to send enquiry.');
            }

            if (feedback) {
                feedback.textContent = payload.message || 'Enquiry sent successfully.';
                feedback.classList.remove('d-none');
                feedback.classList.add('alert-success');
            }

            form.reset();
        } catch (error) {
            if (feedback) {
                feedback.textContent = error.message || 'Unable to send enquiry.';
                feedback.classList.remove('d-none');
                feedback.classList.add('alert-danger');
            }
        } finally {
            submitBtn.disabled = false;
            if (btnText) btnText.classList.remove('d-none');
            if (btnSending) btnSending.classList.add('d-none');
        }
    });
});

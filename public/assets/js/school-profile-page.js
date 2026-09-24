document.addEventListener('DOMContentLoaded', function () {
    var pageRoot = document.getElementById('schoolProfilePage');
    var sectionPageRoot = document.getElementById('schoolSectionPage');

    function openNoticeModal(notice) {
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
    }

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
        var isGrid = carousel.dataset.noticeLayout === 'grid';

        if (!track || !viewport || !slides.length) {
            return;
        }

        if (isGrid) {
            track.style.transform = 'none';

            function scrollStep(direction) {
                var firstSlide = slides[0];
                if (!firstSlide) {
                    return;
                }

                var styles = window.getComputedStyle(track);
                var gap = parseFloat(styles.columnGap || styles.gap) || 0;
                var step = firstSlide.getBoundingClientRect().width + gap;
                var maxScroll = viewport.scrollWidth - viewport.clientWidth;

                if (direction > 0 && viewport.scrollLeft >= maxScroll - 2) {
                    viewport.scrollTo({ left: 0, behavior: 'smooth' });
                    return;
                }

                if (direction < 0 && viewport.scrollLeft <= 2) {
                    viewport.scrollTo({ left: maxScroll, behavior: 'smooth' });
                    return;
                }

                viewport.scrollBy({ left: direction * step, behavior: 'smooth' });
            }

            if (prevBtn) {
                prevBtn.onclick = function () {
                    scrollStep(-1);
                };
            }

            if (nextBtn) {
                nextBtn.onclick = function () {
                    scrollStep(1);
                };
            }

            return;
        }

        var currentIndex = 0;

        function slideOffset() {
            return viewport.clientWidth;
        }

        function showSlide(index) {
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
    }

    initNoticeCarousel();

    document.addEventListener('click', function (event) {
        var readMoreBtn = event.target.closest('.js-sch-notice-read-more');
        if (!readMoreBtn) {
            return;
        }

        event.preventDefault();
        openNoticeModal(readMoreBtn.closest('.sch-notice'));
    });

    if (!pageRoot) {
        if (!sectionPageRoot) {
            return;
        }

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
                if (typeof window.schoolProfileNotify === 'function') {
                    window.schoolProfileNotify('success', 'Profile link copied to clipboard.');
                }
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
                    schNotify('success', 'Link copied to clipboard.');
                    window.setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
                });
            }
        });
    });

    function schNotify(type, message) {
        if (typeof window.schoolProfileNotify === 'function') {
            window.schoolProfileNotify(type, message);
        }
    }

    document.querySelectorAll('.js-sch-helpful').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var helpfulUrl = pageRoot.dataset.helpfulUrl;
            var isAuth = pageRoot.dataset.isAuth === '1';
            var loginUrl = pageRoot.dataset.loginUrl;

            if (!isAuth) {
                if (loginUrl) {
                    window.location.href = loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'redirect=' + encodeURIComponent(window.location.href);
                }
                return;
            }

            if (!helpfulUrl) {
                return;
            }

            var vote = btn.getAttribute('data-vote');
            btn.disabled = true;

            try {
                var response = await fetch(helpfulUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ vote: vote }),
                });

                var payload = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok || payload.ok === false) {
                    throw new Error(payload.message || 'Unable to save your feedback.');
                }

                document.querySelectorAll('.js-sch-helpful').forEach(function (item) {
                    item.classList.remove('is-selected');
                });
                btn.classList.add('is-selected');
                schNotify('success', payload.message || 'Thanks for your feedback!');
            } catch (error) {
                schNotify('error', error.message || 'Unable to save your feedback.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('.profile-report-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn ? submitBtn.textContent : '';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; }).then(function (payload) {
                        if (!response.ok) {
                            var errors = payload.errors ? Object.values(payload.errors).flat().join(' ') : '';
                            throw new Error(errors || payload.message || 'Unable to submit report.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    var modalEl = form.closest('.modal');
                    form.reset();
                    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                    }
                    schNotify('success', payload.message || 'Report submitted successfully.');
                })
                .catch(function (error) {
                    schNotify('error', error.message || 'Unable to submit report.');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                });
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

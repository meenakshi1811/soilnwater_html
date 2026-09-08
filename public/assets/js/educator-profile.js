document.addEventListener('DOMContentLoaded', function () {
    var page = document.getElementById('educatorProfilePage');
    if (!page) {
        return;
    }

    var csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var shareUrl = page.dataset.shareUrl || window.location.href;
    var loginUrl = page.dataset.loginUrl || '/login';
    var enquiryUrl = page.dataset.enquiryUrl || '';
    var isAuth = page.dataset.isAuth === '1';
    var carouselTimer = null;

    function notify(type, message) {
        if (!message) {
            return;
        }

        var toastType = type === 'danger' ? 'error' : type;

        try {
            if (window.toastr && window.jQuery && typeof window.toastr[toastType] === 'function') {
                window.toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 3500,
                    extendedTimeOut: 2000,
                };
                window.toastr[toastType](message);
                return;
            }
        } catch (error) {
            console.warn('Toastr unavailable.', error);
        }

        alert(message);
    }

    function requireAuth(actionLabel) {
        notify('warning', 'Please login to ' + (actionLabel || 'continue') + '.');
        return false;
    }

    function updateRatingDisplay(averageRating, reviewsCount) {
        document.querySelectorAll('.js-edu-avg-rating').forEach(function (el) {
            el.textContent = averageRating;
        });

        document.querySelectorAll('.js-edu-reviews-count').forEach(function (el) {
            el.textContent = Number(reviewsCount || 0).toLocaleString();
        });

        var ratingWrap = document.querySelector('.edu-overview__rating');
        if (!ratingWrap) {
            return;
        }

        var stars = ratingWrap.querySelectorAll('i.fa-star');
        var filled = Math.round(parseFloat(averageRating) || 0);

        stars.forEach(function (star, index) {
            star.className = 'fa-' + (index + 1 <= filled ? 'solid' : 'regular') + ' fa-star';
            star.setAttribute('aria-hidden', 'true');
        });
    }

    function openEnquiryModal() {
        if (!isAuth) {
            return requireAuth('send an enquiry');
        }

        var modalEl = document.getElementById('enquiryModal');
        if (modalEl && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function submitEnquiry(form, submitBtn, btnText) {
        if (!isAuth) {
            return requireAuth('send an enquiry');
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (btnText) {
            btnText.textContent = 'Sending...';
        }

        try {
            var formData = new FormData(form);
            var response = await fetch(form.action || enquiryUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: formData,
            });

            var data = await response.json().catch(function () {
                return {};
            });

            if (response.status === 401) {
                return requireAuth('send an enquiry');
            }

            if (!response.ok || data.ok === false) {
                var firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(firstError || data.message || 'Unable to send enquiry.');
            }

            notify('success', data.message || 'Enquiry sent successfully.');

            var subjectField = form.querySelector('[name="subject"]');
            var messageField = form.querySelector('[name="message"]');
            if (subjectField) {
                subjectField.value = '';
            }
            if (messageField) {
                messageField.value = '';
            }

            var modalEl = document.getElementById('enquiryModal');
            if (modalEl && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
        } catch (error) {
            notify('error', error.message || 'Unable to send enquiry.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (btnText) {
                if (form.id === 'eduQuickQuestionForm') {
                    btnText.innerHTML = '<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Send Question';
                } else {
                    btnText.textContent = 'Send';
                }
            }
        }
    }

    /* Nav scroll spy */
    var navLinks = document.querySelectorAll('.js-edu-nav-link');
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
        document.querySelectorAll('.js-edu-nav-link').forEach(function (link) {
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

    document.querySelectorAll('.js-edu-read-more').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = document.querySelector('.js-edu-about-text');
            if (!text) {
                return;
            }

            var collapsed = text.classList.toggle('is-collapsed');
            btn.innerHTML = collapsed
                ? 'Read More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>'
                : 'Read Less <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>';
        });
    });

    var carousel = document.querySelector('.js-edu-testimonial-carousel');

    function getCarouselSlides() {
        return carousel ? carousel.querySelectorAll('.edu-testimonial') : [];
    }

    function initTestimonialCarousel() {
        if (!carousel) {
            return;
        }

        var track = carousel.querySelector('.js-edu-testimonial-track');
        var slides = getCarouselSlides();
        var prevBtn = carousel.querySelector('.js-edu-testimonial-prev');
        var nextBtn = carousel.querySelector('.js-edu-testimonial-next');
        var currentIndex = 0;

        if (carouselTimer) {
            clearInterval(carouselTimer);
            carouselTimer = null;
        }

        function showSlide(index) {
            if (!slides.length || !track) {
                return;
            }

            currentIndex = (index + slides.length) % slides.length;
            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
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
            carouselTimer = setInterval(function () {
                showSlide(currentIndex + 1);
            }, 7000);
        }
    }

    function prependTestimonial(html, reviewKey) {
        if (!html) {
            return;
        }

        var carouselEl = document.getElementById('eduTestimonialCarousel');
        var track = document.getElementById('eduTestimonialTrack');

        if (!carouselEl || !track) {
            return;
        }

        carouselEl.classList.remove('is-empty');

        if (reviewKey) {
            var duplicate = track.querySelector('[data-review-id="' + reviewKey + '"]');
            if (duplicate) {
                duplicate.remove();
            }
        }

        track.insertAdjacentHTML('afterbegin', html);
        initTestimonialCarousel();
    }

    initTestimonialCarousel();

    document.querySelectorAll('.js-edu-share-profile').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var title = page.dataset.shareTitle || document.title;
            var text = page.dataset.shareText || 'Check out this teacher profile';

            try {
                if (navigator.share) {
                    await navigator.share({ title: title, text: text, url: shareUrl });
                    return;
                }
            } catch (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
            }

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(shareUrl);
                } else {
                    var input = document.createElement('input');
                    input.value = shareUrl;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                }

                notify('success', 'Profile link copied to clipboard.');
            } catch (error) {
                notify('error', 'Unable to share profile.');
            }
        });
    });

    document.querySelectorAll('.js-edu-open-enquiry').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            openEnquiryModal();
        });
    });

    document.querySelectorAll('.js-edu-guest-action').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var action = btn.dataset.action || 'continue';
            var labels = {
                follow: 'follow this educator',
                review: 'leave a review',
                question: 'ask a question',
            };
            requireAuth(labels[action] || 'continue');
        });
    });

    document.querySelectorAll('.js-edu-follow').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!isAuth) {
                return requireAuth('follow this educator');
            }

            var url = btn.dataset.url;
            if (!url) {
                return;
            }

            btn.disabled = true;

            try {
                var response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: new URLSearchParams({ _token: csrf }),
                });

                var data = await response.json().catch(function () {
                    return {};
                });

                if (response.status === 401) {
                    return requireAuth('follow this educator');
                }

                if (!response.ok || data.ok === false) {
                    throw new Error(data.message || 'Unable to update follow.');
                }

                var following = Boolean(data.following);
                btn.classList.toggle('is-following', following);

                var label = btn.querySelector('.js-edu-follow-label');
                if (label) {
                    label.textContent = following
                        ? (btn.dataset.labelFollowing || 'Following')
                        : (btn.dataset.labelFollow || 'Follow');
                }

                if (typeof data.followers_count !== 'undefined') {
                    document.querySelectorAll('.js-edu-followers-count').forEach(function (el) {
                        el.textContent = Number(data.followers_count).toLocaleString();
                    });
                }

                notify('success', data.message || 'Updated.');
            } catch (error) {
                notify('error', error.message || 'Unable to update follow.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    var enquiryForm = document.getElementById('educatorEnquiryForm');
    if (enquiryForm && document.getElementById('educatorEnquirySubmitBtn')) {
        enquiryForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitEnquiry(
                enquiryForm,
                document.getElementById('educatorEnquirySubmitBtn'),
                document.querySelector('#educatorEnquirySubmitBtn .btn-text')
            );
        });
    }

    var quickQuestionForm = document.getElementById('eduQuickQuestionForm');
    if (quickQuestionForm) {
        quickQuestionForm.addEventListener('submit', function (event) {
            event.preventDefault();
            submitEnquiry(
                quickQuestionForm,
                document.getElementById('eduQuickQuestionSubmit'),
                quickQuestionForm.querySelector('#eduQuickQuestionSubmit .btn-text')
            );
        });
    }

    var reviewSection = document.getElementById('educatorReviewsSection');
    if (!reviewSection) {
        return;
    }

    var reviewUrl = reviewSection.dataset.reviewUrl;
    var ratingInput = document.getElementById('educatorReviewRating');
    var starButtons = document.querySelectorAll('.edu-star-picker__btn');

    function paintStars(value) {
        starButtons.forEach(function (btn) {
            btn.classList.toggle('is-active', Number(btn.dataset.rating) <= Number(value));
        });
    }

    starButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (ratingInput) {
                ratingInput.value = btn.dataset.rating;
            }
            paintStars(btn.dataset.rating);
        });
        btn.addEventListener('mouseenter', function () {
            paintStars(btn.dataset.rating);
        });
    });

    document.querySelector('.edu-star-picker')?.addEventListener('mouseleave', function () {
        paintStars(ratingInput?.value || 5);
    });

    var reviewForm = document.getElementById('educatorReviewForm');
    if (!reviewForm || !reviewUrl) {
        return;
    }

    reviewForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!isAuth) {
            return requireAuth('leave a review');
        }

        var submitBtn = document.getElementById('educatorReviewSubmitBtn');
        var btnText = submitBtn?.querySelector('.btn-text');

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (btnText) {
            btnText.textContent = 'Saving...';
        }

        try {
            var response = await fetch(reviewUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    _token: csrf,
                    rating: Number(ratingInput?.value || 5),
                    student_class: document.getElementById('educatorStudentClass')?.value || '',
                    review: document.getElementById('educatorReviewText')?.value || '',
                }),
            });

            var data = await response.json().catch(function () {
                return {};
            });

            if (response.status === 401) {
                return requireAuth('leave a review');
            }

            if (!response.ok || data.ok === false) {
                var firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(firstError || data.message || 'Unable to save review.');
            }

            updateRatingDisplay(data.average_rating, data.reviews_count);

            var list = document.getElementById('educatorReviewsList');
            document.getElementById('educatorReviewsEmpty')?.remove();

            if (list && data.review_html) {
                var existing = data.review_key
                    ? list.querySelector('[data-review-id="' + data.review_key + '"]')
                    : null;
                if (existing) {
                    existing.remove();
                }
                list.insertAdjacentHTML('afterbegin', data.review_html);
            }

            if (data.testimonial_html) {
                prependTestimonial(data.testimonial_html, data.review_key);
            }

            if (btnText) {
                btnText.textContent = 'Update review';
            }

            var title = reviewForm.querySelector('.edu-review-form__title');
            if (title) {
                title.textContent = 'Update your review';
            }

            notify('success', data.message || 'Review submitted.');
        } catch (error) {
            notify('error', error.message || 'Unable to save review.');
            if (btnText) {
                btnText.textContent = 'Submit review';
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });
});

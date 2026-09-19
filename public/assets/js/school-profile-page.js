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

    document.querySelectorAll('.js-sch-brochure, .js-sch-compare, .js-sch-follow, .js-sch-bookmark').forEach(function (btn) {
        btn.addEventListener('click', function () {
            alert('This feature will be available soon.');
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

    var form = document.getElementById('schoolEnquiryForm');
    if (!form) {
        return;
    }

    var enquiryUrl = pageRoot.dataset.enquiryUrl;
    var feedback = document.getElementById('schoolEnquiryFeedback');
    var submitBtn = form.querySelector('.js-school-enquiry-submit');
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

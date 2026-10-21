document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('studyMaterialShowPage');
    if (!page) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const bookmarkUrl = page.dataset.bookmarkUrl || '';
    const reviewUrl = page.dataset.reviewUrl || '';
    const shareUrl = page.dataset.shareUrl || window.location.href;
    let totalPages = parseInt(page.dataset.totalPages || '1', 10);
    const canPreview = page.dataset.canPreview === '1';
    const fileUrl = page.dataset.fileUrl || '';

    let currentPage = 1;
    let zoomLevel = 1;

    function notify(type, message) {
        if (!message) {
            return;
        }

        const toastType = type === 'danger' ? 'error' : type;

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

    function updateSaveButtons(saved) {
        document.querySelectorAll('.js-sm-save').forEach(function (btn) {
            btn.classList.toggle('is-saved', saved);

            const icon = btn.querySelector('i[class*="fa-bookmark"]');
            if (icon) {
                icon.className = (saved ? 'fa-solid' : 'fa-regular') + ' fa-bookmark';
                icon.setAttribute('aria-hidden', 'true');
            }

            const label = btn.querySelector('.js-sm-save-label');
            const text = saved ? (btn.dataset.labelSaved || 'Saved') : (btn.dataset.labelUnsaved || 'Save');
            if (label) {
                label.textContent = text;
            }
        });
    }

    document.querySelectorAll('.js-sm-save').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            if (!bookmarkUrl) {
                return;
            }

            btn.disabled = true;

            try {
                const response = await fetch(bookmarkUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: new URLSearchParams({ _token: csrf }),
                });

                const data = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Unable to update save.');
                }

                updateSaveButtons(Boolean(data.saved || data.bookmarked));
                notify('success', data.message || 'Updated.');
            } catch (error) {
                notify('error', error.message || 'Unable to update save.');
            } finally {
                btn.disabled = false;
            }
        });
    });

    document.querySelectorAll('#materialTabs .sm-show-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('#materialTabs .sm-show-tab').forEach(function (item) {
                item.classList.remove('is-active');
            });
            document.querySelectorAll('.sm-show-tab-panel').forEach(function (panel) {
                panel.classList.remove('is-active');
            });

            tab.classList.add('is-active');
            document.querySelector('[data-panel="' + tab.dataset.tab + '"]')?.classList.add('is-active');
        });
    });

    document.querySelector('.js-sm-scroll-reviews')?.addEventListener('click', function () {
        const reviewsTab = document.querySelector('#materialTabs .sm-show-tab[data-tab="reviews"]');
        reviewsTab?.click();
        document.getElementById('smReviewsPanel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.querySelector('.js-sm-open-solution-tab')?.addEventListener('click', function () {
        const solutionTab = document.querySelector('#materialTabs .sm-show-tab[data-tab="solution"]');
        solutionTab?.click();
        document.querySelector('.sm-show-tabs-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    document.querySelector('.js-sm-desc-toggle')?.addEventListener('click', function () {
        const text = document.querySelector('.js-sm-desc-text');
        if (!text) {
            return;
        }

        const collapsed = text.classList.toggle('is-collapsed');
        this.innerHTML = collapsed
            ? 'Show More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>'
            : 'Show Less <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>';
    });

    document.querySelector('.js-sm-copy-link')?.addEventListener('click', async function () {
        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(shareUrl);
            } else {
                const input = document.createElement('input');
                input.value = shareUrl;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                input.remove();
            }

            notify('success', 'Link copied to clipboard.');
        } catch (error) {
            notify('error', 'Unable to copy link.');
        }
    });

    const pageLabel = document.querySelector('.js-sm-viewer-page-label');
    const canvas = document.querySelector('.js-sm-viewer-canvas');
    const content = document.querySelector('.js-sm-viewer-content');
    const pdfFrame = document.querySelector('.js-sm-viewer-frame');
    // Phones, tablets and apps built from the website (Android WebView) don't show a PDF inside a
    // page (the box stays blank): there its pages are drawn with PDF.js instead (createPdfPages).
    // Desktop browsers, which say they have a PDF viewer, keep it.
    const drawsPdfPages = !!pdfFrame && canPreview && !!fileUrl
        && (navigator.pdfViewerEnabled !== true || window.matchMedia('(pointer: coarse)').matches);
    const frame = drawsPdfPages ? null : pdfFrame;
    const previewImage = document.querySelector('.js-sm-viewer-image');
    const placeholderTitle = document.querySelector('.js-sm-viewer-section-title');
    const pageMeta = document.querySelector('.js-sm-viewer-page-meta');
    const contentItems = document.querySelectorAll('.js-sm-content-item');
    const viewerCard = document.querySelector('.sm-show-viewer-card');
    const fitSelect = document.querySelector('.js-sm-viewer-fit-select');
    const prevBtn = document.querySelector('.js-sm-viewer-prev');
    const nextBtn = document.querySelector('.js-sm-viewer-next');
    const fullscreenBtn = document.querySelector('.js-sm-viewer-fullscreen');
    let pageMetaSuffix = '';

    if (pageMeta) {
        const metaMatch = pageMeta.textContent.match(/Page \d+ of \d+ · (.+)/);
        pageMetaSuffix = metaMatch ? metaMatch[1] : '';
    }

    let fitMode = fitSelect?.value || 'width';
    const pdfPages = drawsPdfPages ? createPdfPages(pdfFrame) : null;

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = function () {
                reject(new Error('Unable to load ' + src));
            };
            document.head.appendChild(script);
        });
    }

    /**
     * The PDF's pages drawn into a scrolling box with PDF.js (loaded only here), in place of the
     * frame: sharp for the screen, only the pages near view kept drawn (phones have little
     * memory), and the page counter follows the scroll.
     */
    function createPdfPages(iframe) {
        // The legacy build is for older browsers (e.g. an app's WebView) the modern one doesn't run in
        const PDFJS_BUILDS = [
            { lib: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js', worker: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js' },
            { lib: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/legacy/build/pdf.min.js', worker: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/legacy/build/pdf.worker.min.js' },
        ];

        const box = document.createElement('div');
        // sm-show-viewer-frame: the same size and place as the frame it replaces
        box.className = 'sm-show-viewer-frame sm-show-pdf-pages';
        box.setAttribute('role', 'document');
        box.setAttribute('aria-label', iframe.getAttribute('title') || 'Document');
        box.innerHTML = '<p class="sm-show-pdf-pages__status">Loading document…</p>';
        iframe.replaceWith(box);

        const pages = [];
        let sizedFor = null;

        function setContent(el, node) {
            while (el.firstChild) {
                el.removeChild(el.firstChild);
            }
            if (node) {
                el.appendChild(node);
            }
        }

        function pdfjsLibrary() {
            return window.pdfjsLib || window['pdfjs-dist/build/pdf'] || null;
        }

        // The modern build, else the legacy one
        function loadPdfjs(index) {
            const build = PDFJS_BUILDS[index || 0];
            if (!build) {
                return Promise.reject(new Error('PDF.js did not load.'));
            }

            return loadScript(build.lib).then(function () {
                const lib = pdfjsLibrary();
                if (!lib) {
                    throw new Error('PDF.js did not run.');
                }
                lib.GlobalWorkerOptions.workerSrc = build.worker;

                return lib;
            }).catch(function () {
                return loadPdfjs((index || 0) + 1);
            });
        }

        function showError() {
            const message = document.createElement('p');
            message.className = 'sm-show-pdf-pages__status';
            message.textContent = 'This document can’t be shown here. ';
            const link = document.createElement('a');
            link.href = fileUrl;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'Open PDF';
            message.appendChild(link);
            setContent(box, message);
        }

        // Fit Width / Fit Page, times the zoom
        function pageWidth(viewport) {
            let width = Math.max(box.clientWidth - 16, 120);
            if (fitMode === 'page') {
                width = Math.min(width, Math.max(box.clientHeight - 16, 120) * viewport.width / viewport.height);
            }

            return Math.round(width * zoomLevel);
        }

        function draw(item) {
            const width = parseInt(item.el.style.width, 10);
            if (item.drawnWidth === width) {
                return;
            }
            if (item.renderTask) {
                item.renderTask.cancel();
            }

            const outputScale = Math.min(window.devicePixelRatio || 1, 2);
            const viewport = item.page.getViewport({ scale: (width / item.viewport.width) * outputScale });
            const canvasEl = document.createElement('canvas');
            canvasEl.width = Math.floor(viewport.width);
            canvasEl.height = Math.floor(viewport.height);

            const task = item.page.render({ canvasContext: canvasEl.getContext('2d'), viewport: viewport });
            item.renderTask = task;
            item.drawnWidth = width;

            task.promise.then(function () {
                if (item.renderTask === task) {
                    item.renderTask = null;
                    // Swapped in once drawn, so a redraw (zoom) doesn't flash blank
                    setContent(item.el, canvasEl);
                }
            }).catch(function () {
                if (item.renderTask === task) {
                    item.renderTask = null;
                    item.drawnWidth = null;
                }
            });
        }

        function release(item) {
            if (item.renderTask) {
                item.renderTask.cancel();
                item.renderTask = null;
            }
            setContent(item.el, null);
            item.drawnWidth = null;
        }

        function show(pageNumber) {
            const item = pages[pageNumber - 1];
            if (item) {
                box.scrollTop = item.el.offsetTop - 8;
            }
        }

        function refit() {
            // Phones resize while scrolling (the address bar): only redraw for a real change
            const key = box.clientWidth + '|' + zoomLevel + '|' + fitMode + (fitMode === 'page' ? '|' + box.clientHeight : '');
            if (!pages.length || key === sizedFor) {
                return;
            }
            sizedFor = key;

            const keepPage = currentPage;
            pages.forEach(function (item) {
                const width = pageWidth(item.viewport);
                item.el.style.width = width + 'px';
                item.el.style.height = Math.round(width * item.viewport.height / item.viewport.width) + 'px';
            });
            pages.forEach(function (item) {
                if (item.visible) {
                    draw(item);
                }
            });
            show(keepPage);
        }

        let tracking = false;
        box.addEventListener('scroll', function () {
            if (tracking) {
                return;
            }
            tracking = true;
            window.requestAnimationFrame(function () {
                tracking = false;
                const probe = box.scrollTop + box.clientHeight / 3;
                let inView = 1;
                for (let i = 0; i < pages.length; i++) {
                    if (pages[i].el.offsetTop > probe) {
                        break;
                    }
                    inView = i + 1;
                }
                if (inView !== currentPage) {
                    currentPage = inView;
                    updatePageLabel();
                }
            });
        }, { passive: true });

        loadPdfjs(0).then(function (lib) {
            return lib.getDocument({ url: fileUrl }).promise;
        }).then(function (doc) {
            const loading = [];
            for (let i = 1; i <= doc.numPages; i++) {
                loading.push(doc.getPage(i));
            }

            return Promise.all(loading);
        }).then(function (pdfPageList) {
            setContent(box, null);
            pdfPageList.forEach(function (pdfPage, index) {
                const el = document.createElement('div');
                el.className = 'sm-show-pdf-page';
                el.dataset.page = String(index + 1);
                box.appendChild(el);
                pages.push({ el: el, page: pdfPage, viewport: pdfPage.getViewport({ scale: 1 }), visible: false });
            });

            totalPages = pages.length;
            updatePageLabel();
            refit();

            // Draw pages as they come near view, free them when far away
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    const item = pages[Number(entry.target.dataset.page) - 1];
                    item.visible = entry.isIntersecting;
                    if (item.visible) {
                        draw(item);
                    } else {
                        release(item);
                    }
                });
            }, { root: box, rootMargin: '800px 0px' });
            pages.forEach(function (item) {
                observer.observe(item.el);
            });
        }).catch(function (error) {
            console.error('Unable to show the PDF.', error);
            showError();
        });

        return { refit: refit, show: show };
    }

    function getPreviewElement() {
        return frame || previewImage || document.querySelector('.js-sm-viewer-placeholder');
    }

    function setActiveContentItem(index) {
        contentItems.forEach(function (item) {
            item.classList.toggle('is-active', parseInt(item.dataset.index || '0', 10) === index);
        });

        const activeItem = contentItems[index];
        if (activeItem && placeholderTitle) {
            placeholderTitle.textContent = activeItem.dataset.title || placeholderTitle.textContent;
        }
    }

    function updatePageMeta() {
        if (pageMeta) {
            pageMeta.textContent = 'Page ' + currentPage + ' of ' + totalPages + (pageMetaSuffix ? ' · ' + pageMetaSuffix : '');
        }
    }

    function updateNavButtons() {
        const atStart = currentPage <= 1;
        const atEnd = currentPage >= totalPages;

        [prevBtn, nextBtn].forEach(function (button) {
            if (!button) {
                return;
            }
            button.disabled = button.classList.contains('js-sm-viewer-prev') ? atStart : atEnd;
            button.classList.toggle('is-disabled', button.disabled);
        });
    }

    function updatePageLabel() {
        if (pageLabel) {
            pageLabel.textContent = currentPage + ' / ' + totalPages;
        }

        if (frame && canPreview && fileUrl) {
            refreshPdfView();
        }

        updatePageMeta();
        updateNavButtons();
    }

    function goToPage(pageNumber) {
        currentPage = Math.max(1, Math.min(totalPages, pageNumber));
        updatePageLabel();
        if (pdfPages) {
            pdfPages.show(currentPage);
        }

        contentItems.forEach(function (item, index) {
            const start = parseInt(item.dataset.pageStart || '1', 10);
            const end = parseInt(item.dataset.pageEnd || String(start), 10);
            if (currentPage >= start && currentPage <= end) {
                setActiveContentItem(index);
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }
        });
    }

    // The PDF viewer zooms the PDF itself (scaling the iframe would blur it): fitted at the default
    // level, else a percentage of that fit
    function pdfViewParams() {
        if (zoomLevel === 1) {
            return '&view=' + (fitMode === 'page' ? 'Fit' : 'FitH');
        }

        const pageWidth = 816; // a page at 100%, in CSS pixels
        const pageHeight = 1056;
        let fit = (frame.clientWidth - 40) / pageWidth;
        if (fitMode === 'page') {
            fit = Math.min(fit, (frame.clientHeight - 40) / pageHeight);
        }

        return '&zoom=' + Math.max(25, Math.round(fit * zoomLevel * 100));
    }

    function refreshPdfView() {
        // navpanes=0: no thumbnail panel, so the page gets the full width (the viewer's menu still opens it)
        const next = new URL(fileUrl.split('#')[0] + '#page=' + currentPage + '&navpanes=0' + pdfViewParams(), window.location.href).href;
        // Setting the same address again would reload the PDF
        if (frame.src !== next) {
            frame.src = next;
        }
    }

    function applyFit() {
        if (pdfPages) {
            pdfPages.refit();
            return;
        }

        if (!canvas || !content) {
            return;
        }

        const preview = getPreviewElement();
        if (!preview) {
            return;
        }

        if (preview.tagName === 'IFRAME') {
            content.style.transform = '';
            if (canPreview && fileUrl) {
                refreshPdfView();
            }
            return;
        }

        content.style.transform = 'scale(1)';

        const padding = 24;
        const availW = Math.max(canvas.clientWidth - padding, 120);
        const availH = Math.max(canvas.clientHeight - padding, 120);

        let naturalW = preview.offsetWidth;
        let naturalH = preview.offsetHeight;

        if (!naturalW || !naturalH) {
            naturalW = 640;
            naturalH = 420;
        }

        let baseScale = 1;
        if (fitMode === 'page') {
            baseScale = Math.min(availW / naturalW, availH / naturalH);
        } else {
            baseScale = availW / naturalW;
        }

        const scale = Math.min(2.5, Math.max(0.45, baseScale * zoomLevel));
        content.style.transform = 'scale(' + scale + ')';
    }

    function changeZoom(delta) {
        zoomLevel = Math.min(2.5, Math.max(0.5, Math.round((zoomLevel + delta) * 10) / 10));
        applyFit();
    }

    function resetZoom() {
        zoomLevel = 1;
        applyFit();
    }

    function updateFullscreenButton() {
        if (!fullscreenBtn) {
            return;
        }

        const isFullscreen = document.fullscreenElement === viewerCard;
        fullscreenBtn.innerHTML = isFullscreen
            ? '<i class="fa-solid fa-compress" aria-hidden="true"></i> Exit Full Screen'
            : '<i class="fa-solid fa-expand" aria-hidden="true"></i> Full Screen';
        viewerCard?.classList.toggle('is-fullscreen', isFullscreen);
    }

    prevBtn?.addEventListener('click', function () {
        goToPage(currentPage - 1);
    });

    nextBtn?.addEventListener('click', function () {
        goToPage(currentPage + 1);
    });

    contentItems.forEach(function (item, index) {
        item.addEventListener('click', function () {
            const start = parseInt(item.dataset.pageStart || '1', 10);
            setActiveContentItem(index);
            goToPage(start);
        });
    });

    document.querySelector('.js-sm-viewer-zoom-in')?.addEventListener('click', function () {
        changeZoom(0.1);
    });

    document.querySelector('.js-sm-viewer-zoom-out')?.addEventListener('click', function () {
        changeZoom(-0.1);
    });

    document.querySelector('.js-sm-viewer-fit')?.addEventListener('click', function () {
        resetZoom();
    });

    fitSelect?.addEventListener('change', function () {
        fitMode = fitSelect.value || 'width';
        applyFit();
    });

    fullscreenBtn?.addEventListener('click', function () {
        if (!viewerCard) {
            return;
        }

        if (document.fullscreenElement) {
            document.exitFullscreen?.();
            return;
        }

        viewerCard.requestFullscreen?.().catch(function () {
            notify('error', 'Full screen is not supported in this browser.');
        });
    });

    document.addEventListener('fullscreenchange', function () {
        updateFullscreenButton();
        window.requestAnimationFrame(applyFit);
    });

    window.addEventListener('resize', function () {
        window.requestAnimationFrame(applyFit);
    });

    document.addEventListener('keydown', function (event) {
        if (!viewerCard || !document.body.contains(viewerCard)) {
            return;
        }

        if (event.target.closest('input, textarea, select, [contenteditable="true"]')) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goToPage(currentPage - 1);
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goToPage(currentPage + 1);
        }

        if (event.key === '+' || event.key === '=') {
            event.preventDefault();
            changeZoom(0.1);
        }

        if (event.key === '-') {
            event.preventDefault();
            changeZoom(-0.1);
        }
    });

    if (previewImage) {
        if (previewImage.complete) {
            applyFit();
        } else {
            previewImage.addEventListener('load', applyFit);
        }
    }

    if (frame) {
        frame.addEventListener('load', applyFit);
    }

    goToPage(1);
    applyFit();

    const ratingInput = document.getElementById('smReviewRating');
    const starButtons = document.querySelectorAll('.sm-star-picker__btn');

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

    document.querySelector('.sm-star-picker')?.addEventListener('mouseleave', function () {
        paintStars(ratingInput?.value || 5);
    });

    const reviewForm = document.getElementById('smReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitBtn = document.getElementById('smReviewSubmitBtn');
            const btnText = submitBtn?.querySelector('.btn-text');
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (btnText) {
                btnText.textContent = 'Saving...';
            }

            try {
                const response = await fetch(reviewUrl, {
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
                        review: document.getElementById('smReviewText')?.value || '',
                    }),
                });

                const data = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(firstError || data.message || 'Unable to save review.');
                }

                document.querySelectorAll('.js-sm-avg-rating').forEach(function (el) {
                    el.textContent = data.average_rating;
                });
                document.querySelectorAll('.js-sm-reviews-count').forEach(function (el) {
                    el.textContent = Number(data.reviews_count || 0).toLocaleString();
                });

                const list = document.getElementById('smReviewsList');
                const empty = document.getElementById('smReviewsEmpty');
                if (empty) {
                    empty.remove();
                }

                if (list && data.review_html) {
                    const userId = list.dataset.currentUser || '';
                    const existing = userId
                        ? list.querySelector('[data-review-user="' + userId + '"]')
                        : null;
                    if (existing) {
                        existing.remove();
                    }
                    list.insertAdjacentHTML('afterbegin', data.review_html);
                }

                if (btnText) {
                    btnText.textContent = 'Update review';
                }

                const title = reviewForm.querySelector('.sm-review-form__title');
                if (title) {
                    title.textContent = 'Update your review';
                }

                notify('success', data.message || 'Review submitted.');
            } catch (error) {
                notify('error', error.message || 'Unable to save review.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
                if (btnText && btnText.textContent === 'Saving...') {
                    btnText.textContent = 'Submit review';
                }
            }
        });
    }
});

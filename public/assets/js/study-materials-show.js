document.addEventListener('DOMContentLoaded', function () {
    const page = document.getElementById('studyMaterialShowPage');
    if (!page) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const bookmarkUrl = page.dataset.bookmarkUrl || '';
    const reviewUrl = page.dataset.reviewUrl || '';
    const shareUrl = page.dataset.shareUrl || window.location.href;
    const totalPages = parseInt(page.dataset.totalPages || '1', 10);
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
    const frame = document.querySelector('.js-sm-viewer-frame');
    const placeholderTitle = document.querySelector('.js-sm-viewer-section-title');
    const contentItems = document.querySelectorAll('.js-sm-content-item');

    function setActiveContentItem(index) {
        contentItems.forEach(function (item) {
            item.classList.toggle('is-active', parseInt(item.dataset.index || '0', 10) === index);
        });

        const activeItem = contentItems[index];
        if (activeItem && placeholderTitle) {
            placeholderTitle.textContent = activeItem.dataset.title || placeholderTitle.textContent;
        }
    }

    function updatePageLabel() {
        if (pageLabel) {
            pageLabel.textContent = currentPage + ' / ' + totalPages;
        }

        if (frame && canPreview && fileUrl) {
            const base = fileUrl.split('#')[0];
            frame.src = base + '#page=' + currentPage;
        }
    }

    function goToPage(pageNumber) {
        currentPage = Math.max(1, Math.min(totalPages, pageNumber));
        updatePageLabel();

        contentItems.forEach(function (item, index) {
            const start = parseInt(item.dataset.pageStart || '1', 10);
            const end = parseInt(item.dataset.pageEnd || String(start), 10);
            if (currentPage >= start && currentPage <= end) {
                setActiveContentItem(index);
            }
        });
    }

    document.querySelector('.js-sm-viewer-prev')?.addEventListener('click', function () {
        goToPage(currentPage - 1);
    });

    document.querySelector('.js-sm-viewer-next')?.addEventListener('click', function () {
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
        zoomLevel = Math.min(2, Math.round((zoomLevel + 0.1) * 10) / 10);
        if (canvas) {
            canvas.style.transform = 'scale(' + zoomLevel + ')';
        }
    });

    document.querySelector('.js-sm-viewer-zoom-out')?.addEventListener('click', function () {
        zoomLevel = Math.max(0.6, Math.round((zoomLevel - 0.1) * 10) / 10);
        if (canvas) {
            canvas.style.transform = 'scale(' + zoomLevel + ')';
        }
    });

    document.querySelector('.js-sm-viewer-fit')?.addEventListener('click', function () {
        zoomLevel = 1;
        if (canvas) {
            canvas.style.transform = 'scale(1)';
        }
    });

    document.querySelector('.js-sm-viewer-fullscreen')?.addEventListener('click', function () {
        const stage = document.getElementById('smViewerStage');
        if (!stage) {
            return;
        }

        if (document.fullscreenElement) {
            document.exitFullscreen?.();
            return;
        }

        stage.requestFullscreen?.();
    });

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

    updatePageLabel();
});

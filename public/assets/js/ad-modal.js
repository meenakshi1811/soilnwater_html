(function () {
    const config = window.soilnwaterAdModal || {};
    const isLoggedIn = !!config.isLoggedIn;
    const reportBase = config.reportBase || '/ads-market';

    function getEl(id) {
        return document.getElementById(id);
    }

    function syncAdModalSize(trigger) {
        const adModalDialog = getEl('adDetailsModalDialog');
        const adModalImageWrap = getEl('adDetailsModalImageWrap');

        if (!adModalDialog || !trigger) {
            return;
        }

        const adWidth = Number(trigger.dataset.adW || 0);
        const adHeight = Number(trigger.dataset.adH || 0);
        const aspectRatio = adWidth > 0 && adHeight > 0 ? adWidth / adHeight : 1;
        const isWideAd = adWidth >= 900 || aspectRatio >= 2.4;
        const isTallAd = adHeight >= 400 || aspectRatio <= 0.75;
        let modalMax = 640;

        adModalDialog.classList.remove('modal-lg', 'modal-xl', 'is-wide-ad', 'is-tall-ad');
        adModalDialog.style.removeProperty('--offer-modal-w');
        adModalDialog.style.removeProperty('width');
        adModalDialog.style.removeProperty('max-width');

        if (adModalImageWrap) {
            if (adWidth > 0 && adHeight > 0) {
                adModalImageWrap.style.setProperty('--ad-modal-w', String(adWidth));
                adModalImageWrap.style.setProperty('--ad-modal-h', String(adHeight));
            } else {
                adModalImageWrap.style.removeProperty('--ad-modal-w');
                adModalImageWrap.style.removeProperty('--ad-modal-h');
            }
        }

        if (isWideAd) {
            adModalDialog.classList.add('modal-xl', 'is-wide-ad');
            modalMax = Math.min(1140, window.innerWidth - 24);
        } else if (adWidth >= 458 || adHeight >= 450) {
            adModalDialog.classList.add('modal-lg');
            modalMax = Math.min(760, window.innerWidth - 24);
        } else {
            modalMax = Math.min(640, window.innerWidth - 24);
        }

        if (isTallAd) {
            adModalDialog.classList.add('is-tall-ad');
        }

        adModalDialog.style.setProperty('--offer-modal-w', modalMax + 'px');
        adModalDialog.style.width = 'min(100% - 1.5rem, ' + modalMax + 'px)';
        adModalDialog.style.maxWidth = modalMax + 'px';
    }

    function setAdModalImage(src) {
        const imgEl = getEl('adDetailsModalImage');
        const adModalImageWrap = getEl('adDetailsModalImageWrap');
        const adEnlargeBtn = getEl('adDetailsEnlargeBtn');

        if (!imgEl) {
            return;
        }

        if (src) {
            imgEl.src = src;
            imgEl.classList.remove('d-none');
            if (adModalImageWrap) {
                adModalImageWrap.classList.remove('d-none');
            }
            if (adEnlargeBtn) {
                adEnlargeBtn.classList.remove('d-none');
            }
            return;
        }

        imgEl.src = '';
        imgEl.classList.add('d-none');
        if (adModalImageWrap) {
            adModalImageWrap.classList.add('d-none');
        }
        if (adEnlargeBtn) {
            adEnlargeBtn.classList.add('d-none');
        }
    }

    function populateAdShareLinks(url) {
        if (typeof window.soilnwaterPopulateShareLinks !== 'function') {
            return url;
        }

        return window.soilnwaterPopulateShareLinks({
            url: url,
            linkInput: 'adShareLink',
            qrImage: 'adShareQr',
            whatsappLink: 'adShareWhatsapp',
            facebookLink: 'adShareFacebook',
            whatsappSuffix: 'Check this ad on SoilnWater',
        });
    }

    function clearAdShareLinks() {
        const shareLinkEl = getEl('adShareLink');
        const shareQrEl = getEl('adShareQr');
        const shareWhatsappEl = getEl('adShareWhatsapp');
        const shareFacebookEl = getEl('adShareFacebook');

        if (shareLinkEl) shareLinkEl.value = '';
        if (shareQrEl) shareQrEl.src = '';
        if (shareWhatsappEl) shareWhatsappEl.href = '#';
        if (shareFacebookEl) shareFacebookEl.href = '#';
    }

    function populateAdModal(trigger) {
        if (!trigger) {
            return;
        }

        syncAdModalSize(trigger);

        const titleEl = getEl('adDetailsModalTitle');
        const metaEl = getEl('adDetailsModalMeta');
        const descriptionEl = getEl('adDetailsModalDescription');
        const adLoginMessageBox = getEl('adLoginMessageBox');
        const adSharePanel = getEl('adSharePanel');
        const adReportActions = getEl('adReportActions');
        const adReportForm = getEl('adReportForm');
        const adReportPopupWrap = getEl('adReportPopupWrap');

        if (!isLoggedIn) {
            if (adLoginMessageBox) adLoginMessageBox.classList.remove('d-none');
            if (adSharePanel) adSharePanel.classList.add('d-none');
            if (adReportActions) adReportActions.classList.add('d-none');
            if (titleEl) titleEl.textContent = 'You are not logged in';
            if (metaEl) metaEl.textContent = '';
            if (descriptionEl) descriptionEl.textContent = '';
            setAdModalImage('');
            clearAdShareLinks();
            if (adReportPopupWrap) adReportPopupWrap.classList.add('d-none');
            return;
        }

        if (titleEl) titleEl.textContent = trigger.dataset.adTitle || 'Ad Details';
        if (metaEl) metaEl.textContent = trigger.dataset.adMeta || '';
        if (descriptionEl) descriptionEl.textContent = trigger.dataset.adDescription || '';
        setAdModalImage(trigger.dataset.adImage || '');
        populateAdShareLinks(trigger.dataset.adUrl || window.location.href);

        if (adLoginMessageBox) adLoginMessageBox.classList.add('d-none');
        if (adSharePanel) adSharePanel.classList.remove('d-none');
        if (adReportActions) adReportActions.classList.remove('d-none');
        if (adReportForm && trigger.dataset.adId) {
            adReportForm.action = reportBase.replace(/\/$/, '') + '/' + trigger.dataset.adId + '/report';
        }
        if (adReportPopupWrap) adReportPopupWrap.classList.add('d-none');
    }

    function resolveLegacyAdTrigger(target) {
        const img = target.closest('.ad-slider img, .recent-ad-card img, .vendor-store-ad-card img, .sponsored-listings-ad-card img');
        if (!img || img.closest('.offer-coupon-card, .con-card')) {
            return null;
        }

        const card = img.closest('.recent-ad-card, .vendor-store-ad-card, [data-ad-id]');
        const context = img.dataset.adId ? img : (card || img);
        const adCard = img.closest('.recent-ad-card');
        const categoriesMeta = adCard?.dataset.adMeta || context.dataset.adMeta || '';
        const servicesMeta = adCard?.dataset.adServices || context.dataset.adServices || '';
        const adMeta = categoriesMeta || servicesMeta || 'Home Page Advertisement';

        return {
            dataset: {
                adId: context.dataset.adId || '',
                adTitle: context.dataset.adTitle || img.getAttribute('alt') || 'Ad Details',
                adMeta: adMeta,
                adDescription: context.dataset.adDescription || 'Special marketplace ad available now.',
                adImage: context.dataset.adImage || img.getAttribute('src') || '',
                adUrl: context.dataset.adUrl || adCard?.dataset.adUrl || window.location.href,
                adW: context.dataset.adW || '',
                adH: context.dataset.adH || '',
            },
        };
    }

    function initAdModal() {
        const adModal = getEl('adDetailsModal');
        if (!adModal) {
            return;
        }

        document.addEventListener('keydown', function (event) {
            const trigger = event.target.closest('.js-ad-modal-trigger');
            if (!trigger) {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                trigger.click();
            }
        });

        adModal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            if (!trigger || !trigger.classList.contains('js-ad-modal-trigger')) {
                return;
            }

            populateAdModal(trigger);
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('.js-ad-modal-trigger')) {
                return;
            }

            const legacyTrigger = resolveLegacyAdTrigger(event.target);
            if (!legacyTrigger) {
                return;
            }

            event.preventDefault();
            populateAdModal(legacyTrigger);
            bootstrap.Modal.getOrCreateInstance(adModal).show();
        });

        const adShareCopyBtn = getEl('adShareCopyBtn');
        const adShareInstagramBtn = getEl('adShareInstagram');
        const openAdReportPopupBtn = getEl('openAdReportPopupBtn');
        const closeAdReportPopupBtn = getEl('closeAdReportPopupBtn');
        const adReportPopupWrap = getEl('adReportPopupWrap');
        const adEnlargeBtn = getEl('adDetailsEnlargeBtn');
        const adImageEnlargePreview = getEl('adImageEnlargePreview');

        if (typeof window.soilnwaterBindShareCopyButton === 'function') {
            window.soilnwaterBindShareCopyButton(adShareCopyBtn, 'adShareLink');
        }

        if (typeof window.soilnwaterBindInstagramShareButton === 'function') {
            window.soilnwaterBindInstagramShareButton(adShareInstagramBtn, 'adShareLink', {
                title: 'SoilnWater Ad',
                text: 'Check this ad on SoilnWater',
            });
        }

        if (openAdReportPopupBtn && adReportPopupWrap) {
            openAdReportPopupBtn.addEventListener('click', function () {
                adReportPopupWrap.classList.remove('d-none');
            });
        }

        if (closeAdReportPopupBtn && adReportPopupWrap) {
            closeAdReportPopupBtn.addEventListener('click', function () {
                adReportPopupWrap.classList.add('d-none');
            });
        }

        if (adEnlargeBtn) {
            adEnlargeBtn.addEventListener('click', function () {
                const imgEl = getEl('adDetailsModalImage');
                const enlargeModal = getEl('adImageEnlargeModal');
                if (!imgEl || !imgEl.src || !enlargeModal || !adImageEnlargePreview) {
                    return;
                }

                adImageEnlargePreview.src = imgEl.src;
                bootstrap.Modal.getOrCreateInstance(enlargeModal).show();
            });
        }
    }

    window.soilnwaterApplyAdModalTrigger = window.soilnwaterApplyAdModalTrigger || function soilnwaterApplyAdModalTrigger(card, item, width, height) {
        if (!card || !item || (!item.id && !item.url)) {
            return;
        }

        card.classList.add('js-ad-modal-trigger');
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', '0');
        card.setAttribute('data-bs-toggle', 'modal');
        card.setAttribute('data-bs-target', '#adDetailsModal');

        if (item.id) {
            card.dataset.adId = String(item.id);
        }

        card.dataset.adTitle = item.title || item.label || 'Sponsored Ad';
        card.dataset.adMeta = item.meta || item.label || 'Sponsored';
        card.dataset.adDescription = item.description || 'Special marketplace ad available now.';

        if (item.image) {
            card.dataset.adImage = item.image;
        }

        if (item.url) {
            card.dataset.adUrl = item.url;
        }

        card.dataset.adW = String(width || item.w || 0);
        card.dataset.adH = String(height || item.h || 0);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdModal);
    } else {
        initAdModal();
    }
})();

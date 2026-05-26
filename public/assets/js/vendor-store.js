document.addEventListener('DOMContentLoaded', function () {
    initProductsMegaMenu();
    initVendorShare();
});

function initVendorShare() {
    const copyBtn = document.getElementById('copyVendorStoreLink');
    const urlInput = document.getElementById('vendorStoreShareUrl');

    if (!copyBtn || !urlInput) {
        return;
    }

    copyBtn.addEventListener('click', async function () {
        const original = copyBtn.textContent;
        try {
            await navigator.clipboard.writeText(urlInput.value);
            copyBtn.textContent = 'Copied';
        } catch (_) {
            urlInput.select();
            document.execCommand('copy');
            copyBtn.textContent = 'Copied';
        }

        window.setTimeout(function () {
            copyBtn.textContent = original;
        }, 1300);
    });
}

function initProductsMegaMenu() {
    const mega = document.querySelector('.vendor-store-products-mega');
    if (!mega) {
        return;
    }

    const trigger = mega.querySelector('.vendor-store-products-trigger');
    const panel = mega.querySelector('.vendor-store-mega-panel');
    const catItems = mega.querySelectorAll('.vendor-store-mega-cat-item.has-children');
    const panels = mega.querySelectorAll('.vendor-store-mega-subpanel[data-mega-panel]');
    const placeholder = mega.querySelector('[data-mega-panel="placeholder"]');
    const CLOSE_DELAY = 300;
    let closeTimer = null;

    function clearCloseTimer() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
    }

    function openMega() {
        clearCloseTimer();
        mega.classList.add('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
    }

    function closeMega() {
        clearCloseTimer();
        mega.classList.remove('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        showSubpanel('placeholder');
    }

    function scheduleClose() {
        clearCloseTimer();
        closeTimer = window.setTimeout(closeMega, CLOSE_DELAY);
    }

    function showSubpanel(panelId) {
        panels.forEach(function (el) {
            const id = el.getAttribute('data-mega-panel');
            const visible = id === String(panelId);
            el.classList.toggle('is-visible', visible);
            el.hidden = !visible;
        });

        catItems.forEach(function (item) {
            item.classList.toggle('is-highlight', item.getAttribute('data-mega-cat') === String(panelId));
        });
    }

    mega.addEventListener('mouseenter', openMega);
    mega.addEventListener('mouseleave', scheduleClose);

    if (panel) {
        panel.addEventListener('mouseenter', clearCloseTimer);
    }

    if (trigger) {
        trigger.addEventListener('click', function (event) {
            if (window.matchMedia('(hover: hover)').matches) {
                return;
            }
            event.preventDefault();
            mega.classList.toggle('is-open');
            trigger.setAttribute('aria-expanded', mega.classList.contains('is-open') ? 'true' : 'false');
        });
    }

    catItems.forEach(function (item) {
        const catId = item.getAttribute('data-mega-cat');

        item.addEventListener('mouseenter', function () {
            clearCloseTimer();
            showSubpanel(catId);
        });

        item.addEventListener('focusin', function () {
            showSubpanel(catId);
        });
    });

    mega.querySelectorAll('.vendor-store-mega-cat-item:not(.has-children)').forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            if (placeholder) {
                showSubpanel('placeholder');
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (!mega.contains(event.target)) {
            closeMega();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMega();
        }
    });

    showSubpanel('placeholder');
}

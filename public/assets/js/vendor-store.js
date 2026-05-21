document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.querySelector('.vendor-store-nav-products-dropdown');
    if (!dropdown) {
        return;
    }

    const trigger = dropdown.querySelector('.vendor-store-products-trigger');
    const mainMenu = dropdown.querySelector('.vendor-store-products-menu');
    const CLOSE_DELAY = 280;
    let closeTimer = null;

    function clearCloseTimer() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
    }

    function openDropdown() {
        clearCloseTimer();
        dropdown.classList.add('is-open');
        positionSubmenus();
    }

    function scheduleClose() {
        clearCloseTimer();
        closeTimer = window.setTimeout(function () {
            dropdown.classList.remove('is-open');
            dropdown.querySelectorAll('.vendor-store-submenu-item.is-open').forEach(function (item) {
                item.classList.remove('is-open');
            });
        }, CLOSE_DELAY);
    }

    function positionSubmenu(item) {
        const submenu = item.querySelector(':scope > .dropdown-menu');
        if (!submenu) {
            return;
        }

        submenu.classList.remove('submenu-left');
        submenu.style.maxHeight = Math.min(window.innerHeight - 24, 500) + 'px';

        const rect = submenu.getBoundingClientRect();
        if (rect.right > window.innerWidth - 12) {
            submenu.classList.add('submenu-left');
        }

        const updated = submenu.getBoundingClientRect();
        if (updated.top < 12) {
            submenu.style.top = (12 - updated.top) + 'px';
        } else if (updated.bottom > window.innerHeight - 12) {
            submenu.style.top = (parseFloat(submenu.style.top || '0') - (updated.bottom - window.innerHeight + 12)) + 'px';
        }
    }

    function positionSubmenus() {
        dropdown.querySelectorAll('.vendor-store-submenu-item.has-children').forEach(positionSubmenu);
    }

    dropdown.addEventListener('mouseenter', openDropdown);
    dropdown.addEventListener('mouseleave', scheduleClose);

    if (trigger) {
        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            if (dropdown.classList.contains('is-open')) {
                scheduleClose();
            } else {
                openDropdown();
            }
        });
    }

    dropdown.querySelectorAll('.vendor-store-submenu-item.has-children').forEach(function (item) {
        let subCloseTimer = null;

        item.addEventListener('mouseenter', function () {
            clearCloseTimer();
            if (subCloseTimer) {
                clearTimeout(subCloseTimer);
                subCloseTimer = null;
            }

            dropdown.querySelectorAll('.vendor-store-submenu-item.is-open').forEach(function (openItem) {
                if (openItem !== item) {
                    openItem.classList.remove('is-open');
                }
            });

            item.classList.add('is-open');
            positionSubmenu(item);
        });

        item.addEventListener('mouseleave', function () {
            subCloseTimer = window.setTimeout(function () {
                item.classList.remove('is-open');
            }, CLOSE_DELAY);
        });

        const submenu = item.querySelector(':scope > .dropdown-menu');
        if (submenu) {
            submenu.addEventListener('mouseenter', function () {
                clearCloseTimer();
                if (subCloseTimer) {
                    clearTimeout(subCloseTimer);
                    subCloseTimer = null;
                }
                item.classList.add('is-open');
            });
        }
    });

    if (mainMenu) {
        mainMenu.addEventListener('mouseenter', clearCloseTimer);
    }

    document.addEventListener('click', function (event) {
        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('is-open');
            dropdown.querySelectorAll('.vendor-store-submenu-item.is-open').forEach(function (item) {
                item.classList.remove('is-open');
            });
        }
    });
});

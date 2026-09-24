(function (global) {
    function parseHashLink(link) {
        var href = (link && link.getAttribute('href')) || '';
        if (!href || href.charAt(0) !== '#') {
            return '';
        }

        return href.slice(1).trim();
    }

    function getScrollOffset() {
        var header = document.querySelector('.main-header, header.site-header, .navbar-fixed-top, #header, .header-area');
        var offset = 24;

        if (header) {
            offset += header.getBoundingClientRect().height;
        }

        return offset;
    }

    /**
     * Sidebar / in-page hash nav: scroll to the matching section and keep active state in sync.
     *
     * @param {string} linkSelector
     */
    function initProfileSectionNav(linkSelector) {
        var navLinks = document.querySelectorAll(linkSelector);
        if (!navLinks.length) {
            return;
        }

        var sectionById = new Map();
        var navScrollLock = false;
        var navScrollUnlockTimer = null;

        function setActiveNav(activeId) {
            navLinks.forEach(function (link) {
                var id = parseHashLink(link);
                link.classList.toggle('is-active', id === activeId);
            });
        }

        function scrollToSectionId(id) {
            if (!id) {
                return false;
            }

            var el = document.getElementById(id);
            if (!el) {
                return false;
            }

            navScrollLock = true;
            if (navScrollUnlockTimer) {
                clearTimeout(navScrollUnlockTimer);
            }

            var top = el.getBoundingClientRect().top + window.pageYOffset - getScrollOffset();
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
            setActiveNav(id);

            navScrollUnlockTimer = setTimeout(function () {
                navScrollLock = false;
            }, 900);

            if (global.history && global.history.replaceState) {
                global.history.replaceState(null, '', '#' + id);
            } else {
                global.location.hash = id;
            }

            return true;
        }

        navLinks.forEach(function (link) {
            var targetId = parseHashLink(link);
            if (!targetId) {
                return;
            }

            var section = document.getElementById(targetId);
            if (section && !sectionById.has(targetId)) {
                sectionById.set(targetId, section);
            }

            link.addEventListener('click', function (event) {
                var id = parseHashLink(link);
                if (!id) {
                    return;
                }

                event.preventDefault();
                scrollToSectionId(id);
            });
        });

        if (sectionById.size && 'IntersectionObserver' in global) {
            var observer = new IntersectionObserver(function (entries) {
                if (navScrollLock) {
                    return;
                }

                var best = null;
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    if (!best || entry.intersectionRatio > best.intersectionRatio) {
                        best = entry;
                    }
                });

                if (best && best.target.id) {
                    setActiveNav(best.target.id);
                }
            }, {
                rootMargin: '-20% 0px -60% 0px',
                threshold: [0, 0.1, 0.25, 0.5, 0.75, 1],
            });

            sectionById.forEach(function (el) {
                observer.observe(el);
            });
        }

        var initialHash = (global.location.hash || '').replace(/^#/, '').trim();
        if (initialHash && document.getElementById(initialHash)) {
            global.requestAnimationFrame(function () {
                scrollToSectionId(initialHash);
            });
        }
    }

    global.initProfileSectionNav = initProfileSectionNav;
})(window);

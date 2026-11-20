/**
 * Keeps the page's CSRF token current.
 *
 * Laravel issues a new token when someone logs in, and a new session (with a new token) when the
 * old one expires, so a page that stays open can end up posting an old token ("CSRF token
 * mismatch"). Every AJAX response from the site carries the current token in an X-CSRF-TOKEN
 * header (App\Http\Middleware\ShareFreshCsrfToken); this script copies it into
 * <meta name="csrf-token"> and every hidden _token field, and makes jQuery requests always send
 * the current one, even when the calling code read the token earlier.
 *
 * Load it in the <head>, before other scripts.
 */
(function (window, document) {
    'use strict';

    if (window.SnwCsrf) {
        return;
    }

    function currentToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    function setToken(token) {
        if (!token || typeof token !== 'string' || token === currentToken()) {
            return;
        }

        var metas = document.querySelectorAll('meta[name="csrf-token"]');
        for (var m = 0; m < metas.length; m++) {
            metas[m].setAttribute('content', token);
        }

        var inputs = document.querySelectorAll('input[name="_token"]');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].value = token;
        }

        if (window.axios && window.axios.defaults && window.axios.defaults.headers && window.axios.defaults.headers.common) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        }
    }

    function tokenFromHeaders(getHeader) {
        try {
            return getHeader('X-CSRF-TOKEN') || '';
        } catch (e) {
            return '';
        }
    }

    // fetch(): pick up the token from every response from this site
    if (typeof window.fetch === 'function') {
        var originalFetch = window.fetch;
        window.fetch = function () {
            return originalFetch.apply(this, arguments).then(function (response) {
                if (response && response.headers) {
                    setToken(tokenFromHeaders(function (name) { return response.headers.get(name); }));
                }
                return response;
            });
        };
    }

    function replaceTokenInBody(data, token) {
        if (typeof data === 'string') {
            return data.replace(/(^|&)_token=[^&]*/, '$1_token=' + encodeURIComponent(token));
        }

        if (typeof window.FormData !== 'undefined' && data instanceof window.FormData && data.has('_token')) {
            data.set('_token', token);
        }

        return data;
    }

    function hookJquery($) {
        if (!$ || !$.ajaxPrefilter || $.__snwCsrfHooked) {
            return;
        }
        $.__snwCsrfHooked = true;

        // Always send the current token, whatever token the calling code captured earlier
        $.ajaxPrefilter(function (options) {
            if (options.crossDomain) {
                return;
            }

            var token = currentToken();
            if (!token) {
                return;
            }

            if (options.headers) {
                for (var key in options.headers) {
                    if (Object.prototype.hasOwnProperty.call(options.headers, key) && key.toLowerCase() === 'x-csrf-token') {
                        options.headers[key] = token;
                    }
                }
            }

            options.data = replaceTokenInBody(options.data, token);
        });

        $(document).ajaxComplete(function (event, xhr) {
            if (xhr && typeof xhr.getResponseHeader === 'function') {
                setToken(tokenFromHeaders(function (name) { return xhr.getResponseHeader(name); }));
            }
        });
    }

    function hookAvailableJquery() {
        hookJquery(window.jQuery);
    }

    hookAvailableJquery();
    document.addEventListener('DOMContentLoaded', hookAvailableJquery);
    window.addEventListener('load', hookAvailableJquery);

    window.SnwCsrf = {
        token: currentToken,
        set: setToken,
        hookJquery: hookJquery,
        fromXhr: function (xhr) {
            if (xhr && typeof xhr.getResponseHeader === 'function') {
                setToken(tokenFromHeaders(function (name) { return xhr.getResponseHeader(name); }));
            }
        }
    };
})(window, document);

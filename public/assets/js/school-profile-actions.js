(function () {
    function notify(type, message) {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 4000,
            };
            toastr[type](message);
            return;
        }

        alert(message);
    }

    function requireAuth(pageRoot, actionLabel) {
        var loginUrl = pageRoot.dataset.loginUrl;
        if (loginUrl) {
            window.location.href = loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'redirect=' + encodeURIComponent(window.location.href);
            return;
        }

        notify('warning', 'Please log in to ' + actionLabel + '.');
    }

    async function postAction(url, pageRoot) {
        var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        var response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        var payload = await response.json().catch(function () {
            return {};
        });

        if (response.status === 401 || response.status === 419) {
            requireAuth(pageRoot, 'continue');
            return null;
        }

        if (!response.ok || payload.ok === false) {
            throw new Error(payload.message || 'Unable to complete this action.');
        }

        return payload;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var pageRoot = document.getElementById('schoolProfilePage') || document.getElementById('instituteProfilePage');
        if (!pageRoot) {
            return;
        }

        var isAuth = pageRoot.dataset.isAuth === '1';

        document.querySelectorAll('.js-sch-open-enquiry').forEach(function (btn) {
            btn.addEventListener('click', function (event) {
                event.preventDefault();
                if (!isAuth) {
                    requireAuth(pageRoot, 'send an enquiry');
                    return;
                }

                var modalEl = document.getElementById('schoolEnquiryModal');
                if (modalEl && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    return;
                }

                var contact = document.getElementById('sch-contact');
                contact?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        document.querySelectorAll('.js-sch-follow').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!isAuth) {
                    return requireAuth(pageRoot, 'follow this school');
                }

                var url = btn.dataset.url || pageRoot.dataset.followUrl;
                if (!url) {
                    return;
                }

                btn.disabled = true;
                try {
                    var payload = await postAction(url, pageRoot);
                    if (!payload) {
                        return;
                    }

                    var following = !!payload.following;
                    btn.classList.toggle('is-following', following);
                    var label = btn.querySelector('.js-sch-follow-label');
                    var icon = btn.querySelector('i');
                    if (label) {
                        label.textContent = following ? 'Following' : 'Follow School';
                    }
                    if (icon) {
                        icon.className = following ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                    }
                    notify('success', payload.message || 'Updated.');
                } catch (error) {
                    notify('error', error.message || 'Unable to update follow status.');
                } finally {
                    btn.disabled = false;
                }
            });
        });

        document.querySelectorAll('.js-sch-bookmark').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!isAuth) {
                    return requireAuth(pageRoot, 'bookmark this school');
                }

                var url = btn.dataset.url || pageRoot.dataset.bookmarkUrl;
                if (!url) {
                    return;
                }

                btn.disabled = true;
                try {
                    var payload = await postAction(url, pageRoot);
                    if (!payload) {
                        return;
                    }

                    var bookmarked = !!payload.bookmarked;
                    btn.classList.toggle('is-active', bookmarked);
                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.className = bookmarked ? 'fa-solid fa-bookmark' : 'fa-regular fa-bookmark';
                    }
                    notify('success', payload.message || 'Updated.');
                } catch (error) {
                    notify('error', error.message || 'Unable to update bookmark.');
                } finally {
                    btn.disabled = false;
                }
            });
        });

        document.querySelectorAll('.js-sch-compare').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!isAuth) {
                    return requireAuth(pageRoot, 'compare schools');
                }

                var url = btn.dataset.url || pageRoot.dataset.compareUrl;
                if (!url) {
                    return;
                }

                btn.disabled = true;
                try {
                    var payload = await postAction(url, pageRoot);
                    if (!payload) {
                        return;
                    }

                    btn.classList.toggle('is-active', !!payload.in_compare);
                    var label = btn.querySelector('.js-sch-compare-label');
                    if (label) {
                        label.textContent = payload.in_compare ? 'In compare list' : 'Compare';
                    }
                    notify('success', payload.message || 'Compare list updated.');
                    if (payload.in_compare && payload.compare_url && payload.compare_count >= 2) {
                        setTimeout(function () {
                            if (window.confirm('Open your compare list now?')) {
                                window.location.href = payload.compare_url;
                            }
                        }, 300);
                    }
                } catch (error) {
                    notify('error', error.message || 'Unable to update compare list.');
                } finally {
                    btn.disabled = false;
                }
            });
        });

        document.querySelectorAll('.js-sch-brochure').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!isAuth) {
                    return requireAuth(pageRoot, 'download the brochure');
                }

                var url = btn.dataset.url || pageRoot.dataset.brochureUrl;
                if (!url) {
                    notify('warning', 'Brochure is not available yet.');
                    return;
                }

                btn.disabled = true;
                try {
                    var payload = await postAction(url, pageRoot);
                    if (!payload) {
                        return;
                    }

                    if (payload.download_url) {
                        window.open(payload.download_url, '_blank', 'noopener');
                    }
                    notify('success', payload.message || 'Download started.');
                } catch (error) {
                    notify('error', error.message || 'Unable to download brochure.');
                } finally {
                    btn.disabled = false;
                }
            });
        });
    });
})();

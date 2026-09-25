(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var csrfToken = $('meta[name="csrf-token"]').attr('content');
    var routes = window.instPublicContentRoutes || {};

    function notify(type, message) {
        if (window.toastr) {
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3500,
            };
            toastr[type](message);
            return;
        }

        var alertEl = document.getElementById('instPublicPageAlert');
        if (!alertEl) {
            return;
        }

        alertEl.textContent = message;
        alertEl.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger');
        alertEl.classList.remove('d-none');
    }

    function clearFormErrors($form) {
        if (window.FormHelper && typeof window.FormHelper.clearFormErrors === 'function') {
            window.FormHelper.clearFormErrors($form);
            return;
        }

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.ajax-error').remove();
    }

    function renderFieldErrors($form, errors) {
        if (window.FormHelper && typeof window.FormHelper.renderFieldErrors === 'function') {
            return window.FormHelper.renderFieldErrors($form, errors);
        }

        return null;
    }

    function showValidationErrors($form, alertSelector, xhr) {
        var response = xhr.responseJSON || {};
        var messages = [];

        if (xhr.status === 422 && response.errors) {
            renderFieldErrors($form, response.errors);
            Object.keys(response.errors).forEach(function (key) {
                messages = messages.concat(response.errors[key]);
            });
        } else if (response.message) {
            messages.push(response.message);
        }

        if (!messages.length) {
            messages.push('Unable to save right now. Please try again.');
        }

        var summary = messages.length === 1
            ? messages[0]
            : 'Please fix the highlighted fields and try again.';

        notify('warning', summary);

        if (alertSelector) {
            var $alert = $(alertSelector);
            if ($alert.length) {
                $alert.removeClass('d-none alert-success alert-danger')
                    .addClass('alert-warning');

                if (messages.length === 1) {
                    $alert.text(messages[0]);
                } else {
                    $alert.html(messages.map(function (msg) {
                        return '<div>' + $('<div>').text(msg).html() + '</div>';
                    }).join(''));
                }
            }
        }
    }

    function setSubmitting($form, isSubmitting) {
        var $btn = $form.find('.js-inst-submit-btn').first();
        $btn.prop('disabled', isSubmitting);
        $btn.find('.btn-text').toggleClass('d-none', isSubmitting);
        $btn.find('.btn-loader').toggleClass('d-none', !isSubmitting);
    }

    function prependItem(listSelector, emptySelector, html) {
        var $list = $(listSelector);
        if (!$list.length) {
            return;
        }

        $(emptySelector).remove();
        $list.prepend(html);
    }

    function restoreEmpty(listSelector, itemSelector, emptySelector, emptyText) {
        var $list = $(listSelector);
        if ($list.find(itemSelector).length) {
            return;
        }

        $list.html('<p class="sch-manage-empty mb-0" id="' + emptySelector.replace('#', '') + '">' + emptyText + '</p>');
    }

    var publicPageSections = ['notices', 'classes', 'performers', 'achievements', 'books'];

    function sectionToggleSelector(section) {
        return '#' + section + '_section_enabled';
    }

    function syncPublicPageSection(section) {
        var $toggle = $(sectionToggleSelector(section));
        if (!$toggle.length) {
            return;
        }

        var enabled = $toggle.is(':checked');
        var $body = $('[data-section-body="' + section + '"]');

        $body.toggleClass('d-none', !enabled);
        $body.find('.js-section-field, .js-inst-submit-btn').prop('disabled', !enabled);
    }

    function bindForm(formSelector, url, listSelector, emptySelector, options) {
        options = options || {};
        var $form = $(formSelector);
        if (!$form.length || !url) {
            return;
        }

        var alertSelector = options.alertSelector || null;
        var ajaxValidationOnly = options.ajaxValidationOnly === true;
        var sectionKey = options.sectionKey || null;

        $form.on('submit', function (event) {
            event.preventDefault();

            if (sectionKey) {
                syncPublicPageSection(sectionKey);
                var $toggle = $(sectionToggleSelector(sectionKey));
                if (!$toggle.is(':checked')) {
                    notify('warning', 'Turn on “Show form” for this section to add an entry.');
                    return;
                }
            }

            if (!ajaxValidationOnly && !$form[0].reportValidity()) {
                notify('warning', 'Please fill in the required fields.');
                return;
            }

            clearFormErrors($form);
            if (alertSelector) {
                $(alertSelector).addClass('d-none').empty();
            }

            setSubmitting($form, true);

            var hasFile = $form.find('input[type="file"]').length > 0;
            var payload = hasFile ? new FormData($form[0]) : $form.serialize();
            var ajaxOptions = {
                url: url,
                method: 'POST',
                data: payload,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            };

            if (hasFile) {
                ajaxOptions.processData = false;
                ajaxOptions.contentType = false;
            }

            $.ajax(ajaxOptions)
                .done(function (response) {
                    notify('success', response.message || 'Saved successfully.');
                    if (alertSelector) {
                        $(alertSelector).removeClass('d-none alert-danger alert-warning')
                            .addClass('alert-success')
                            .text(response.message || 'Saved successfully.');
                    }
                    if (response.item_html) {
                        prependItem(listSelector, emptySelector, response.item_html);
                    }
                    $form[0].reset();
                    var expiresField = $form.find('input[name="expires_at"]');
                    if (expiresField.length) {
                        expiresField.attr('min', new Date().toISOString().slice(0, 10));
                    }
                })
                .fail(function (xhr) {
                    showValidationErrors($form, alertSelector, xhr);
                })
                .always(function () {
                    setSubmitting($form, false);
                });
        });
    }

    bindForm('#instNoticeForm', routes.notices, '#instNoticeList', '#instNoticeEmpty', {
        ajaxValidationOnly: true,
        alertSelector: '#instNoticeFormAlert',
        sectionKey: 'notices',
    });
    bindForm('#instClassForm', routes.classes, '#instClassList', '#instClassEmpty', {
        ajaxValidationOnly: true,
        alertSelector: '#instClassFormAlert',
        sectionKey: 'classes',
    });
    bindForm('#instPerformerForm', routes.performers, '#instPerformerList', '#instPerformerEmpty', {
        ajaxValidationOnly: true,
        alertSelector: '#instPerformerFormAlert',
        sectionKey: 'performers',
    });
    bindForm('#instAchievementForm', routes.achievements, '#instAchievementList', '#instAchievementEmpty', {
        ajaxValidationOnly: true,
        alertSelector: '#instAchievementFormAlert',
        sectionKey: 'achievements',
    });
    bindForm('#instBookForm', routes.books, '#instBookList', '#instBookEmpty', {
        ajaxValidationOnly: true,
        alertSelector: '#instBookFormAlert',
        sectionKey: 'books',
    });

    $('.js-public-page-section-toggle').on('change', function () {
        syncPublicPageSection($(this).data('section'));
    });

    publicPageSections.forEach(syncPublicPageSection);

    function bindDelete(selector, listSelector, itemSelector, emptySelector) {
        $(document).on('click', selector, function () {
            var $button = $(this);
            var url = $button.data('url');
            var $item = $button.closest('.sch-manage-item');

            if (!url || !window.confirm('Remove this item from your public page?')) {
                return;
            }

            $button.prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    _method: 'DELETE',
                },
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            })
                .done(function (response) {
                    notify('success', response.message || 'Removed successfully.');
                    $item.remove();
                    var emptyText = $(listSelector).data('empty-text') || 'No items yet.';
                    restoreEmpty(listSelector, itemSelector, emptySelector, emptyText);
                })
                .fail(function (xhr) {
                    $button.prop('disabled', false);
                    showValidationErrors($(), null, xhr);
                });
        });
    }

    bindDelete('.js-inst-notice-delete', '#instNoticeList', '.sch-manage-item', '#instNoticeEmpty');
    bindDelete('.js-inst-class-delete', '#instClassList', '.sch-manage-item', '#instClassEmpty');
    bindDelete('.js-inst-performer-delete', '#instPerformerList', '.sch-manage-item', '#instPerformerEmpty');
    bindDelete('.js-inst-achievement-delete', '#instAchievementList', '.sch-manage-item', '#instAchievementEmpty');
    bindDelete('.js-inst-book-delete', '#instBookList', '.sch-manage-item', '#instBookEmpty');

    document.querySelectorAll('.sch-portal-nav__link').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var href = link.getAttribute('href');
            if (!href || href.charAt(0) !== '#') {
                return;
            }

            event.preventDefault();
            var target = document.querySelector(href);
            target?.scrollIntoView({ behavior: 'smooth', block: 'start' });

            document.querySelectorAll('.sch-portal-nav__link').forEach(function (navLink) {
                navLink.classList.toggle('is-active', navLink === link);
            });
        });
    });
})(window.jQuery);

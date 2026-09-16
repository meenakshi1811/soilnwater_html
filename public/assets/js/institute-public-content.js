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

    function showValidationErrors(xhr) {
        var response = xhr.responseJSON || {};
        var messages = [];

        if (response.errors) {
            Object.keys(response.errors).forEach(function (key) {
                messages = messages.concat(response.errors[key]);
            });
        } else if (response.message) {
            messages.push(response.message);
        }

        if (!messages.length) {
            messages.push('Unable to save right now. Please try again.');
        }

        messages.forEach(function (message) {
            notify('warning', message);
        });
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

    function bindForm(formSelector, url, listSelector, emptySelector) {
        var $form = $(formSelector);
        if (!$form.length || !url) {
            return;
        }

        $form.on('submit', function (event) {
            event.preventDefault();

            if (!$form[0].reportValidity()) {
                notify('warning', 'Please fill in the required fields.');
                return;
            }

            setSubmitting($form, true);

            var hasFile = $form.find('input[type="file"]').length > 0;
            var payload = hasFile ? new FormData($form[0]) : $form.serialize();
            var ajaxOptions = {
                url: url,
                method: 'POST',
                data: payload,
                headers: csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {},
            };

            if (hasFile) {
                ajaxOptions.processData = false;
                ajaxOptions.contentType = false;
            }

            $.ajax(ajaxOptions)
                .done(function (response) {
                    notify('success', response.message || 'Saved successfully.');
                    if (response.item_html) {
                        prependItem(listSelector, emptySelector, response.item_html);
                    }
                    $form[0].reset();
                })
                .fail(showValidationErrors)
                .always(function () {
                    setSubmitting($form, false);
                });
        });
    }

    bindForm('#instNoticeForm', routes.notices, '#instNoticeList', '#instNoticeEmpty');
    bindForm('#instClassForm', routes.classes, '#instClassList', '#instClassEmpty');
    bindForm('#instPerformerForm', routes.performers, '#instPerformerList', '#instPerformerEmpty');
    bindForm('#instAchievementForm', routes.achievements, '#instAchievementList', '#instAchievementEmpty');
    bindForm('#instBookForm', routes.books, '#instBookList', '#instBookEmpty');

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
                    showValidationErrors(xhr);
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

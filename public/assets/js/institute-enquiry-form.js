/**
 * AJAX submit for school / institute public profile enquiry forms.
 */
(function () {
    function firstValidationMessage(payload) {
        if (!payload || !payload.errors) {
            return null;
        }

        var values = Object.values(payload.errors);
        if (!values.length) {
            return null;
        }

        var first = values[0];
        return Array.isArray(first) ? first[0] : first;
    }

    function notify(type, message) {
        if (typeof window.schoolProfileNotify === 'function') {
            window.schoolProfileNotify(type, message);
            return;
        }

        if (window.toastr && typeof window.toastr[type === 'success' ? 'success' : 'error'] === 'function') {
            window.toastr[type === 'success' ? 'success' : 'error'](message);
        }
    }

    function setSubmitting(submitBtn, btnText, btnSending, isSubmitting) {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = isSubmitting;
        if (btnText) {
            btnText.classList.toggle('d-none', isSubmitting);
        }
        if (btnSending) {
            btnSending.classList.toggle('d-none', !isSubmitting);
        }
    }

    function clearEnquiryFields(form) {
        ['subject', 'message'].forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (field) {
                field.value = '';
            }
        });
    }

    window.initInstituteEnquiryForm = function (options) {
        var form = options.form;
        if (!form) {
            return;
        }

        var enquiryUrl = options.enquiryUrl;
        var feedback = options.feedbackEl;
        var submitBtn = options.submitBtn || form.querySelector('[type="submit"]');
        var btnText = submitBtn?.querySelector('.js-enquiry-btn-text');
        var btnSending = submitBtn?.querySelector('.js-enquiry-btn-sending');
        var loginUrl = options.loginUrl;
        var csrf =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            form.querySelector('input[name="_token"]')?.value ||
            '';

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!enquiryUrl || !submitBtn) {
                return;
            }

            setSubmitting(submitBtn, btnText, btnSending, true);

            try {
                var response = await fetch(enquiryUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                var payload = await response.json().catch(function () {
                    return {};
                });

                if (response.status === 401 || response.status === 419) {
                    if (loginUrl) {
                        window.location.href = loginUrl + (loginUrl.indexOf('?') >= 0 ? '&' : '?') + 'redirect=' + encodeURIComponent(window.location.href);
                        return;
                    }
                    throw new Error('Please log in to send an enquiry.');
                }

                if (!response.ok || payload.ok === false) {
                    throw new Error(
                        firstValidationMessage(payload) || payload.message || 'Unable to send enquiry.'
                    );
                }

                notify('success', payload.message || 'Enquiry sent successfully.');
                clearEnquiryFields(form);

                var modalEl = form.closest('.modal');
                if (modalEl && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
            } catch (error) {
                notify('error', error.message || 'Unable to send enquiry.');
            } finally {
                setSubmitting(submitBtn, btnText, btnSending, false);
            }
        });
    };
})();

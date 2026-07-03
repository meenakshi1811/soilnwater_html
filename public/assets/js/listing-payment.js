(function () {
    var modalEl = document.getElementById('listingPaymentModal');
    if (!modalEl) {
        return;
    }

    var form = document.getElementById('listingPaymentForm');
    var typeInput = document.getElementById('listingPaymentType');
    var idInput = document.getElementById('listingPaymentId');
    var screenshotInput = document.getElementById('listingPaymentScreenshot');
    var previewWrap = document.getElementById('listingPaymentPreview');
    var previewImage = document.getElementById('listingPaymentPreviewImage');
    var fileNameLabel = document.getElementById('listingPaymentFileName');
    var amountWrap = document.getElementById('listingPaymentAmountWrap');
    var amountLabel = document.getElementById('listingPaymentAmount');
    var subtitle = document.getElementById('listingPaymentSubtitle');
    var alertBox = document.getElementById('listingPaymentAlert');
    var submitBtn = document.getElementById('listingPaymentSubmitBtn');

    var submitUrl = modalEl.getAttribute('data-submit-url');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    csrf = csrf ? csrf.getAttribute('content') : '';

    var redirectUrl = '';
    var modalInstance = null;

    function getModalInstance() {
        if (modalInstance) {
            return modalInstance;
        }
        if (window.bootstrap && window.bootstrap.Modal) {
            modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return modalInstance;
    }

    function formatInr(amount) {
        var value = Number(amount || 0);
        if (!isFinite(value)) {
            value = 0;
        }
        return '₹' + value.toFixed(2);
    }

    function showAlert(message, type) {
        if (!alertBox) {
            return;
        }
        alertBox.textContent = message;
        alertBox.className = 'alert alert-' + type;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        if (alertBox) {
            alertBox.classList.add('d-none');
        }
    }

    function resetForm() {
        if (form) {
            form.reset();
        }
        if (previewWrap) {
            previewWrap.classList.add('d-none');
        }
        if (fileNameLabel) {
            fileNameLabel.classList.add('d-none');
            fileNameLabel.textContent = '';
        }
        hideAlert();
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof';
        }
    }

    if (screenshotInput) {
        screenshotInput.addEventListener('change', function () {
            var file = screenshotInput.files && screenshotInput.files[0];
            if (!file) {
                if (previewWrap) previewWrap.classList.add('d-none');
                if (fileNameLabel) fileNameLabel.classList.add('d-none');
                return;
            }
            if (previewImage) previewImage.src = URL.createObjectURL(file);
            if (previewWrap) previewWrap.classList.remove('d-none');
            if (fileNameLabel) {
                fileNameLabel.textContent = file.name;
                fileNameLabel.classList.remove('d-none');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!screenshotInput || !screenshotInput.files || !screenshotInput.files.length) {
                showAlert('Please upload a payment screenshot.', 'danger');
                return;
            }

            var formData = new FormData(form);
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
            hideAlert();

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: formData
            }).then(function (response) {
                return response.json().then(function (payload) {
                    return { ok: response.ok, payload: payload };
                });
            }).then(function (result) {
                if (!result.ok) {
                    throw new Error((result.payload && result.payload.message) || 'Unable to submit payment proof.');
                }

                showAlert(result.payload.message || 'Payment proof submitted successfully.', 'success');
                submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Submitted';

                setTimeout(function () {
                    if (redirectUrl) {
                        window.location.assign(redirectUrl);
                    } else {
                        window.location.reload();
                    }
                }, 1400);
            }).catch(function (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Submit Payment Proof';
                showAlert(error.message || 'Something went wrong. Please try again.', 'danger');
            });
        });
    }

    window.ListingPayment = {
        open: function (options) {
            options = options || {};
            resetForm();

            if (typeInput) typeInput.value = options.listingType || '';
            if (idInput) idInput.value = options.listingId || '';
            redirectUrl = options.redirectUrl || '';

            var amount = Number(options.amount || 0);
            if (amountWrap && amountLabel) {
                if (amount > 0) {
                    amountLabel.textContent = formatInr(amount);
                    amountWrap.classList.remove('d-none');
                } else {
                    amountWrap.classList.add('d-none');
                }
            }

            if (subtitle) {
                var label = options.listingType === 'offer' ? 'offer' : 'ad';
                subtitle.textContent = 'Your ' + label + ' is saved. Scan & pay, then upload the screenshot for admin verification.';
            }

            var instance = getModalInstance();
            if (instance) {
                instance.show();
            }
        }
    };
})();

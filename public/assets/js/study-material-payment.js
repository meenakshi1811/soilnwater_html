(function () {
    var modalEl = document.getElementById('studyMaterialPaymentModal');
    if (!modalEl) {
        return;
    }

    var form = document.getElementById('studyMaterialPaymentForm');
    var idInput = document.getElementById('studyMaterialPaymentId');
    var titleEl = document.getElementById('studyMaterialPaymentTitle');
    var amountEl = document.getElementById('studyMaterialPaymentAmount');
    var fileInput = document.getElementById('studyMaterialPaymentScreenshot');
    var fileName = document.getElementById('studyMaterialPaymentFileName');
    var preview = document.getElementById('studyMaterialPaymentPreview');
    var previewImage = document.getElementById('studyMaterialPaymentPreviewImage');
    var alertBox = document.getElementById('studyMaterialPaymentAlert');
    var submitBtn = document.getElementById('studyMaterialPaymentSubmitBtn');
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

    function resetForm() {
        if (form) {
            form.reset();
        }
        if (preview) {
            preview.classList.add('d-none');
        }
        if (fileName) {
            fileName.classList.add('d-none');
            fileName.textContent = '';
        }
        if (alertBox) {
            alertBox.classList.add('d-none');
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Proceed to Payment';
        }
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (!file) {
                if (preview) {
                    preview.classList.add('d-none');
                }
                if (fileName) {
                    fileName.classList.add('d-none');
                }
                return;
            }
            if (fileName) {
                fileName.textContent = file.name;
                fileName.classList.remove('d-none');
            }
            if (preview && previewImage) {
                previewImage.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!fileInput || !fileInput.files || !fileInput.files.length) {
                if (alertBox) {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = 'Please upload a payment screenshot.';
                    alertBox.classList.remove('d-none');
                }
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
            if (alertBox) {
                alertBox.classList.add('d-none');
            }

            fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: new FormData(form),
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        throw new Error((result.payload && result.payload.message) || 'Unable to submit payment proof.');
                    }

                    var message = result.payload.message || 'Payment proof submitted successfully.';
                    if (window.toastr && typeof window.toastr.success === 'function') {
                        window.toastr.success(message);
                    } else if (alertBox) {
                        alertBox.className = 'alert alert-success';
                        alertBox.textContent = message;
                        alertBox.classList.remove('d-none');
                    }

                    setTimeout(function () {
                        if (redirectUrl) {
                            window.location.assign(redirectUrl);
                        } else {
                            window.location.reload();
                        }
                    }, 1400);
                })
                .catch(function (error) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Proceed to Payment';
                    if (alertBox) {
                        alertBox.className = 'alert alert-danger';
                        alertBox.textContent = error.message || 'Something went wrong. Please try again.';
                        alertBox.classList.remove('d-none');
                    }
                });
        });
    }

    window.StudyMaterialPayment = {
        open: function (options) {
            options = options || {};
            resetForm();

            if (idInput) {
                idInput.value = options.materialId || '';
            }
            if (titleEl) {
                titleEl.textContent = options.title ? 'Purchase: ' + options.title : 'Purchase Study Material';
            }
            if (amountEl) {
                amountEl.textContent = formatInr(options.amount);
            }
            redirectUrl = options.redirectUrl || '';

            var instance = getModalInstance();
            if (instance) {
                instance.show();
            }
        },
    };
})();

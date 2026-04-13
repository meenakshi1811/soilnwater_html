(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var OffersAdmin = {

        /* ── 1. Validator Methods ─────────────────────────────── */
        initValidatorMethods: function () {
            if ($.validator) {
                $.validator.addMethod('extension', function (value, element, param) {
                    return this.optional(element) || param.split('|').some(function (ext) {
                        return value.toLowerCase().endsWith('.' + ext);
                    });
                }, 'Invalid file type.');
            }
        },

        /* ── 2. Dynamic Subcategories ─────────────────────────── */
        loadSubcategories: function (categoryId, selectedSubId) {
            var $sub = $('#subcategorySelect');

            if (!categoryId) {
                $sub.html('<option value="">— Select a category first —</option>').prop('disabled', true);
                return;
            }

            $sub.html('<option value="">Loading…</option>').prop('disabled', true);

            $.get('/user/categories/' + categoryId + '/subcategories').done(function (data) {
                if (!data || data.length === 0) {
                    $sub.html('<option value="">No subcategories available</option>');
                } else {
                    $sub.html('<option value="">— Select a subcategory —</option>');
                    $.each(data, function (_, sub) {
                        $sub.append(
                            $('<option>', { value: sub.id, text: sub.name })
                        );
                    });
                    $sub.prop('disabled', false);

                    if (selectedSubId) {
                        $sub.val(String(selectedSubId));
                    }
                }
            }).fail(function () {
                $sub.html('<option value="">Failed to load subcategories</option>');
            });
        },

        /* ── 3. Banner Image Preview ──────────────────────────── */
        initBannerUpload: function () {
            function showPreview(file) {
                if (!file || !file.type.startsWith('image/')) return;
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#bannerPreview').attr('src', e.target.result);
                    $('#bannerPreviewWrap').removeClass('d-none');
                    $('#bannerPlaceholder').addClass('d-none');
                };
                reader.readAsDataURL(file);
            }

            $('#bannerImage').on('change', function () {
                if (this.files[0]) showPreview(this.files[0]);
            });

            $('#removeBannerBtn').on('click', function (e) {
                e.stopPropagation();
                $('#bannerImage').val('');
                $('#bannerPreview').attr('src', '#');
                $('#bannerPreviewWrap').addClass('d-none');
                $('#bannerPlaceholder').removeClass('d-none');
            });

            $('#bannerDropzone')
                .on('dragover', function (e) {
                    e.preventDefault();
                    $(this).addClass('drag-over');
                })
                .on('dragleave', function () {
                    $(this).removeClass('drag-over');
                })
                .on('drop', function (e) {
                    e.preventDefault();
                    $(this).removeClass('drag-over');
                    var file = e.originalEvent.dataTransfer.files[0];
                    if (file) {
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        $('#bannerImage')[0].files = dt.files;
                        showPreview(file);
                    }
                });
        },

        /* ── 4. Misc UI Bindings ──────────────────────────────── */
        bindUi: function () {
            var self = this;

            // Category → load subcategories
            $('#categorySelect').on('change', function () {
                self.loadSubcategories($(this).val(), '');
            });

            // Coupon code → auto uppercase
            $('#couponCode').on('input', function () {
                var pos = this.selectionStart;
                $(this).val($(this).val().toUpperCase());
                this.setSelectionRange(pos, pos);
            });

            // Description character counter
            $('#descCharCount').text($('#shortDescription').val().length);
            $('#shortDescription').on('input', function () {
                $('#descCharCount').text($(this).val().length);
            });
        },
        /* ── 5. Form (Ajax + Validation) ─────────────────────── */
        initForm: function () {
            var $form = $('#offerForm');
            var $btn = $('#offerSubmitBtn');
            var $text = $btn.find('.btn-text');
            var $loader = $btn.find('.btn-loader');

            function setButtonLoading(isLoading, shouldDisable) {
                $btn.prop('disabled', !!(isLoading && shouldDisable));
                $text.toggleClass('d-none', isLoading);
                $loader.toggleClass('d-none', !isLoading);

                if (isLoading) {
                    $loader.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Posting…');
                } else {
                    $loader.empty();
                }
            }

            $btn.off('click.offerLoader').on('click.offerLoader', function () {
                setButtonLoading(true, false);
            });

            $form.validate({
                rules: {
                    title: {
                        required: true,
                        minlength: 3,
                        maxlength: 255
                    },
                    discount_tag: {
                        required: true,
                        maxlength: 100
                    },
                    coupon_code: {
                        maxlength: 50
                    },
                    valid_until: {
                        date: true
                    },
                    banner_image: {
                        required: true,
                        extension: 'jpg|jpeg|png|webp'
                    },
                    short_description: {
                        maxlength: 300
                    }
                },
                messages: {
                    title: {
                        required: 'Please enter an offer title.',
                        minlength: 'Title must be at least 3 characters.',
                        maxlength: 'Title must not exceed 255 characters.'
                    },
                    discount_tag: {
                        required: 'Please enter a discount tag (e.g. 30% OFF).',
                        maxlength: 'Discount tag must not exceed 100 characters.'
                    },
                    banner_image: {
                        required: 'Please upload a banner image.',
                        extension: 'Only JPG, PNG, or WebP images are allowed.'
                    },
                    short_description: {
                        maxlength: 'Description must not exceed 300 characters.'
                    }
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function (element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                invalidHandler: function () {
                    setButtonLoading(false, false);
                },
                errorPlacement: function (error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function (form) {
                    setButtonLoading(true, true);

                    // Build FormData manually so file is included
                    var formData = new FormData(form);

                    $.ajax({
                        url: $(form).attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,   // ← required for file upload
                        contentType: false,   // ← required for file upload
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            FormHelper.showAlert($('#offerAlert'), 'success', response.message || 'Offer posted successfully.');

                            // Reset form
                            form.reset();
                            $('#subcategorySelect').html('<option value="">— Select a category first —</option>').prop('disabled', true);
                            $('#descCharCount').text('0');

                            // Reset banner preview
                            $('#bannerPreview').attr('src', '#');
                            $('#bannerPreviewWrap').addClass('d-none');
                            $('#bannerPlaceholder').removeClass('d-none');

                            // Reset validation states
                            $('#offerForm .is-valid').removeClass('is-valid');
                        },
                        error: function (xhr) {
                            var msg = 'Something went wrong. Please try again.';
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    msg = xhr.responseJSON.message;
                                }
                                // Show Laravel validation errors on fields
                                if (xhr.responseJSON.errors) {
                                    $.each(xhr.responseJSON.errors, function (field, errors) {
                                        var $field = $('[name="' + field + '"]');
                                        $field.addClass('is-invalid');
                                        $field.closest('.col-md-6, .col-12')
                                            .find('.invalid-feedback')
                                            .text(errors[0]);
                                    });
                                }
                            }
                            FormHelper.showAlert($('#offerAlert'), 'danger', msg);
                        },
                        complete: function () {
                            setButtonLoading(false, false);
                        }
                    });
                }
            });
        },

        /* ── 6. Init ──────────────────────────────────────────── */
        init: function () {
            if (!$('#offerForm').length) {
                return;
            }

            this.initValidatorMethods();
            this.initBannerUpload();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        OffersAdmin.init();
    });

    var MyOffersAdmin = {
        table: null,
        modal: null,

        initTable: function () {
            this.table = $('#myOffersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/user/offers/data'
                },
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'discount_tag', name: 'discount_tag' },
                    { data: 'coupon_code', name: 'coupon_code' },
                    { data: 'category_name', name: 'category_name', orderable: false, searchable: false },
                    { data: 'subcategory_name', name: 'subcategory_name', orderable: false, searchable: false },
                    { data: 'valid_until', name: 'valid_until' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[7, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('myOfferModal'));

            $(document).on('click', '.js-edit-offer', function () {
                var id = $(this).data('id');

                $('#myOfferForm')[0].reset();
                $('#myOfferForm').attr('action', '/user/offers/' + id);

                $.get('/user/offers/' + id, function (response) {
                    var offer = response.offer || {};
                    $('#myOfferTitle').val(offer.title || '');
                    $('#myOfferDiscountTag').val(offer.discount_tag || '');
                    $('#myOfferCouponCode').val(offer.coupon_code || '');
                    $('#myOfferValidUntil').val(offer.valid_until || '');
                    $('#myOfferShortDescription').val(offer.short_description || '');
                    $('#myOfferStatus').val(offer.status || 'inactive');

                    self.modal.show();
                }).fail(function () {
                    FormHelper.showAlert($('#myOfferAlert'), 'danger', 'Unable to load offer details.');
                });
            });

            $(document).on('click', '.js-delete-offer', function () {
                var id = $(this).data('id');
                if (!confirm('Delete this offer?')) {
                    return;
                }

                $.ajax({
                    url: '/user/offers/' + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#myOfferAlert'), 'success', response.message || 'Offer deleted.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to delete offer.';
                    FormHelper.showAlert($('#myOfferAlert'), 'danger', message);
                });
            });
        },

        initForm: function () {
            var self = this;
            var $form = $('#myOfferForm');
            var $button = $('#myOfferSubmitBtn');
            var $alert = $('#myOfferAlert');
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $form.validate({
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback d-block');
                    error.insertAfter(element);
                },
                highlight: function (element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element) {
                    $(element).removeClass('is-invalid');
                },
                rules: {
                    title: { required: true, minlength: 3, maxlength: 255 },
                    discount_tag: { required: true, maxlength: 255 },
                    coupon_code: { maxlength: 50 },
                    valid_until: { date: true },
                    short_description: { maxlength: 300 },
                    status: { required: true }
                },
                submitHandler: function () {
                    var formData = new FormData($form[0]);
                    formData.append('_method', 'PUT');

                    FormHelper.setButtonLoading($button, true, 'Updating...', 'Update Offer');

                    $.ajax({
                        url: $form.attr('action'),
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        }
                    }).done(function (response) {
                        FormHelper.showAlert($alert, 'success', response.message || 'Offer updated successfully.');
                        if (self.table) {
                            self.table.ajax.reload(null, false);
                        }
                        self.modal.hide();
                    }).fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            FormHelper.clearFormErrors($form);
                            FormHelper.renderFieldErrors($form, xhr.responseJSON.errors);
                            FormHelper.showAlert($alert, 'warning', 'Please fix the highlighted fields and try again.');
                            return;
                        }

                        var message = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : 'Unable to update offer.';
                        FormHelper.showAlert($alert, 'danger', message);
                    }).always(function () {
                        FormHelper.setButtonLoading($button, false, 'Updating...', 'Update Offer');
                    });
                }
            });
        },

        init: function () {
            if (!$('#myOffersTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
            this.initForm();
        }
    };

    $(function () {
        MyOffersAdmin.init();
    });

})(window.jQuery);

(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var OffersAdmin = {
        designer: {
            layers: [],
            activeId: null,
            drag: null,
            width: 1200,
            height: 600
        },
        currentBannerMode: 'upload',

        clamp: function (value, min, max) {
            return Math.max(min, Math.min(max, value));
        },

        ensureLayerWithinBounds: function (layer) {
            if (!layer) return;
            var maxX = this.designer.width - (layer.type === 'image' ? (layer.width || 20) : 20);
            var maxY = this.designer.height - (layer.type === 'image' ? (layer.height || 20) : 20);
            layer.x = this.clamp(layer.x || 0, 0, Math.max(0, maxX));
            layer.y = this.clamp(layer.y || 0, 0, Math.max(0, maxY));
        },

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
            var subcategoryEndpointBase = $('#offerForm').data('subcategory-url-base') || '/offers/categories';

            if (!categoryId) {
                $sub.html('<option value="">— Select a category first —</option>').prop('disabled', true);
                return;
            }

            $sub.html('<option value="">Loading…</option>').prop('disabled', true);

            $.get(subcategoryEndpointBase + '/' + categoryId + '/subcategories').done(function (data) {
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
            var self = this;
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
                if (this.files[0]) {
                    $('#generatedBannerData').val('');
                    showPreview(this.files[0]);
                }
            });

            $('#removeBannerBtn').on('click', function (e) {
                e.stopPropagation();
                $('#bannerImage').val('');
                $('#bannerPreview').attr('src', '#');
                $('#bannerPreviewWrap').addClass('d-none');
                $('#bannerPlaceholder').removeClass('d-none');
                self.renderDesignerStage();
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
                        $('#generatedBannerData').val('');
                        showPreview(file);
                    }
                });
        },

        syncBannerModeCards: function () {
            $('.banner-mode-option').each(function () {
                var $option = $(this);
                var isChecked = $option.find('.banner-mode-radio').is(':checked');
                $option.toggleClass('is-active', isChecked);
            });
        },

        applyBannerMode: function (mode) {
            this.currentBannerMode = mode === 'customize' ? 'customize' : 'upload';

            var isCustomize = this.currentBannerMode === 'customize';
            $('#bannerUploadWrap').toggleClass('d-none', isCustomize);
            $('#bannerDesignerWrap').toggleClass('d-none', !isCustomize);

            if (isCustomize) {
                $('#bannerImage').val('');
                $('#bannerPreview').attr('src', '#');
                $('#bannerPreviewWrap').addClass('d-none');
                $('#bannerPlaceholder').removeClass('d-none');
                this.renderDesignerStage();
                this.updateGeneratedBanner();
            } else {
                $('#generatedBannerData').val('');
            }

            this.syncBannerModeCards();
        },

        addTextLayer: function (text, options) {
            options = options || {};
            this.designer.layers.push({
                id: 'layer_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                type: 'text',
                text: text || 'New Text',
                x: options.x || 60,
                y: options.y || 60,
                fontSize: options.fontSize || 42,
                color: options.color || '#ffffff',
                align: options.align || 'left',
                fontFamily: options.fontFamily || 'Arial'
            });
            this.designer.activeId = this.designer.layers[this.designer.layers.length - 1].id;
            this.renderDesignerStage();
        },

        addImageLayer: function (src) {
            var self = this;
            var imgObj = new Image();
            var layer = {
                id: 'layer_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                type: 'image',
                src: src,
                imageObj: imgObj,
                x: 850,
                y: 300,
                width: 220,
                height: 220,
                aspectRatio: 1
            };

            imgObj.onload = function () {
                if (imgObj.naturalWidth > 0 && imgObj.naturalHeight > 0) {
                    layer.aspectRatio = imgObj.naturalWidth / imgObj.naturalHeight;
                    layer.height = Math.round(layer.width / layer.aspectRatio);
                    self.ensureLayerWithinBounds(layer);
                }
                self.renderDesignerStage();
            };
            imgObj.src = src;

            this.designer.layers.push(layer);
            this.designer.activeId = this.designer.layers[this.designer.layers.length - 1].id;
            this.renderDesignerStage();
        },

        findActiveLayer: function () {
            for (var i = 0; i < this.designer.layers.length; i++) {
                if (this.designer.layers[i].id === this.designer.activeId) {
                    return this.designer.layers[i];
                }
            }
            return null;
        },

        syncLayerControls: function () {
            var layer = this.findActiveLayer();
            var isText = !!(layer && layer.type === 'text');
            var isImage = !!(layer && layer.type === 'image');

            if (!isText) {
                $('#layerTextInput').val('');
                $('#layerFontSizeInput').val(42);
                $('#layerTextColorInput').val('#ffffff');
                $('#layerTextAlignInput').val('left');
                $('#layerFontFamilyInput').val('Arial');
            } else {
                $('#layerTextInput').val(layer.text || '');
                $('#layerFontSizeInput').val(layer.fontSize || 42);
                $('#layerTextColorInput').val(layer.color || '#ffffff');
                $('#layerTextAlignInput').val(layer.align || 'left');
                $('#layerFontFamilyInput').val(layer.fontFamily || 'Arial');
            }

            $('#layerTextInput, #layerFontSizeInput, #layerTextColorInput, #layerTextAlignInput, #layerFontFamilyInput')
                .prop('disabled', !isText);

            if (!isImage) {
                $('#layerImageWidthInput').val(220);
                $('#layerImageHeightInput').val(220);
                $('#layerImageScaleInput').val(18);
            } else {
                $('#layerImageWidthInput').val(Math.round(layer.width || 220));
                $('#layerImageHeightInput').val(Math.round(layer.height || 220));
                $('#layerImageScaleInput').val(Math.round(((layer.width || 220) / this.designer.width) * 100));
            }

            $('#layerImageWidthInput, #layerImageHeightInput, #layerImageScaleInput')
                .prop('disabled', !isImage);
        },

        renderDesignerStage: function () {
            var self = this;
            var $stage = $('#bannerDesignerStage');
            if (!$stage.length) return;

            $stage.css('background', $('#bannerBgColor').val() || '#2f7de1');
            $stage.empty();

            this.designer.layers.forEach(function (layer) {
                var $layer;
                if (layer.type === 'text') {
                    $layer = $('<div class="banner-designer-layer text-layer"></div>');
                    $layer.text(layer.text || '');
                    $layer.css({
                        left: (layer.x / self.designer.width * 100) + '%',
                        top: (layer.y / self.designer.height * 100) + '%',
                        color: layer.color,
                        fontSize: Math.max(10, layer.fontSize) + 'px',
                        textAlign: layer.align,
                        fontFamily: layer.fontFamily,
                        transform: 'translate(-0%, -0%)'
                    });
                } else {
                    $layer = $('<div class="banner-designer-layer image-layer"><img></div>');
                    $layer.find('img').attr('src', layer.src);
                    $layer.css({
                        left: (layer.x / self.designer.width * 100) + '%',
                        top: (layer.y / self.designer.height * 100) + '%',
                        width: (layer.width / self.designer.width * 100) + '%',
                        height: (layer.height / self.designer.height * 100) + '%'
                    });
                }

                $layer.attr('data-layer-id', layer.id);
                if (layer.id === self.designer.activeId) {
                    $layer.addClass('active');
                }
                $stage.append($layer);
            });

            this.syncLayerControls();
            if (!$('#bannerImage').val()) {
                this.updateGeneratedBanner();
            }
        },

        updateGeneratedBanner: function () {
            var self = this;
            var canvas = document.createElement('canvas');
            canvas.width = this.designer.width;
            canvas.height = this.designer.height;
            var ctx = canvas.getContext('2d');

            ctx.fillStyle = $('#bannerBgColor').val() || '#2f7de1';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            var drawNext = function (index) {
                if (index >= self.designer.layers.length) {
                    $('#generatedBannerData').val(canvas.toDataURL('image/png'));
                    return;
                }

                var layer = self.designer.layers[index];
                if (layer.type === 'text') {
                    ctx.textAlign = layer.align || 'left';
                    ctx.fillStyle = layer.color || '#ffffff';
                    ctx.shadowColor = 'rgba(0,0,0,0.4)';
                    ctx.shadowBlur = 4;
                    ctx.font = '700 ' + (layer.fontSize || 42) + 'px ' + (layer.fontFamily || 'Arial');
                    ctx.fillText((layer.text || '').substring(0, 80), layer.x, layer.y + (layer.fontSize || 42));
                    drawNext(index + 1);
                } else {
                    if (layer.imageObj && layer.imageObj.complete) {
                        ctx.drawImage(layer.imageObj, layer.x, layer.y, layer.width, layer.height);
                        drawNext(index + 1);
                        return;
                    }
                    var img = new Image();
                    img.onload = function () {
                        layer.imageObj = img;
                        ctx.drawImage(img, layer.x, layer.y, layer.width, layer.height);
                        drawNext(index + 1);
                    };
                    img.onerror = function () {
                        drawNext(index + 1);
                    };
                    img.src = layer.src;
                }
            };

            drawNext(0);
        },

        /* ── 4. Misc UI Bindings ──────────────────────────────── */
        bindUi: function () {
            var self = this;
            var syncTemplateSelectionUi = function () {
                $('.offer-template-card').removeClass('is-selected');
                $('input[name="selected_template"]:checked').each(function () {
                    $(this).closest('.offer-template-card').addClass('is-selected');
                });
            };
            var syncTemplateOverlay = function () {
                var title = $('#offerTitle').val().trim() || 'Offer Name';
                var discount = $('#discountTag').val().trim() || 'Discount %';
                var coupon = $('#couponCode').val().trim();
                $('.js-template-title').text(title);
                $('.js-template-discount').text(discount);
                $('.js-template-coupon').text('Coupon: ' + (coupon ? coupon.toUpperCase() : 'N/A'));
            };

            // Category → load subcategories
            $('#categorySelect').on('change', function () {
                self.loadSubcategories($(this).val(), '');
            });

            // Coupon code → auto uppercase
            $('#couponCode').on('input', function () {
                var pos = this.selectionStart;
                $(this).val($(this).val().toUpperCase());
                this.setSelectionRange(pos, pos);
                if (self.designer.layers[2] && self.designer.layers[2].type === 'text') {
                    self.designer.layers[2].text = 'Coupon: ' + ($(this).val() || 'N/A');
                }
                self.renderDesignerStage();
            });

            $('#offerTitle').on('input', function () {
                if (self.designer.layers[0] && self.designer.layers[0].type === 'text') {
                    self.designer.layers[0].text = $(this).val() || 'Offer Name';
                }
                self.renderDesignerStage();
            });

            $('#discountTag').on('input', function () {
                if (self.designer.layers[1] && self.designer.layers[1].type === 'text') {
                    self.designer.layers[1].text = $(this).val() || 'Discount %';
                }
                self.renderDesignerStage();
            });

            $('#bannerBgColor').on('input change', function () {
                self.renderDesignerStage();
            });

            $('input[name="banner_mode"]').on('change', function () {
                self.applyBannerMode($(this).val());
            });

            $('#addTextLayerBtn').on('click', function () {
                self.addTextLayer('New Text', { x: 80, y: 80, fontSize: 42 });
            });

            $('#bannerImageLayers').on('change', function () {
                var files = this.files ? Array.prototype.slice.call(this.files) : [];
                if (!files.length) {
                    return;
                }

                files.forEach(function (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        self.addImageLayer(e.target.result);
                    };
                    reader.readAsDataURL(file);
                });

                $(this).val('');
            });

            $('#removeSelectedLayerBtn').on('click', function () {
                if (!self.designer.activeId) return;
                self.designer.layers = self.designer.layers.filter(function (layer) {
                    return layer.id !== self.designer.activeId;
                });
                self.designer.activeId = null;
                self.renderDesignerStage();
            });

            $('#layerTextInput, #layerFontSizeInput, #layerTextColorInput, #layerTextAlignInput, #layerFontFamilyInput').on('input change', function () {
                var layer = self.findActiveLayer();
                if (!layer || layer.type !== 'text') return;

                layer.text = $('#layerTextInput').val();
                layer.fontSize = parseInt($('#layerFontSizeInput').val() || '42', 10);
                layer.color = $('#layerTextColorInput').val() || '#ffffff';
                layer.align = $('#layerTextAlignInput').val() || 'left';
                layer.fontFamily = $('#layerFontFamilyInput').val() || 'Arial';
                self.renderDesignerStage();
            });

            $('#layerImageWidthInput').on('input change', function () {
                var layer = self.findActiveLayer();
                if (!layer || layer.type !== 'image') return;

                var width = parseInt($(this).val() || layer.width || '220', 10);
                width = self.clamp(width, 40, self.designer.width);
                layer.width = width;

                if (layer.aspectRatio && layer.aspectRatio > 0) {
                    layer.height = Math.round(layer.width / layer.aspectRatio);
                }

                self.ensureLayerWithinBounds(layer);
                self.renderDesignerStage();
            });

            $('#layerImageHeightInput').on('input change', function () {
                var layer = self.findActiveLayer();
                if (!layer || layer.type !== 'image') return;

                var height = parseInt($(this).val() || layer.height || '220', 10);
                height = self.clamp(height, 40, self.designer.height);
                layer.height = height;

                if (layer.aspectRatio && layer.aspectRatio > 0) {
                    layer.width = Math.round(layer.height * layer.aspectRatio);
                    layer.width = self.clamp(layer.width, 40, self.designer.width);
                    layer.height = Math.round(layer.width / layer.aspectRatio);
                }

                self.ensureLayerWithinBounds(layer);
                self.renderDesignerStage();
            });

            $('#layerImageScaleInput').on('input change', function () {
                var layer = self.findActiveLayer();
                if (!layer || layer.type !== 'image') return;

                var percent = parseInt($(this).val() || '18', 10);
                var width = Math.round((percent / 100) * self.designer.width);
                layer.width = self.clamp(width, 40, self.designer.width);

                if (layer.aspectRatio && layer.aspectRatio > 0) {
                    layer.height = Math.round(layer.width / layer.aspectRatio);
                }

                self.ensureLayerWithinBounds(layer);
                self.renderDesignerStage();
            });

            $('#bannerDesignerStage').on('mousedown', '.banner-designer-layer', function (e) {
                e.preventDefault();
                var layerId = $(this).data('layer-id');
                self.designer.activeId = layerId;
                self.renderDesignerStage();

                var layer = self.findActiveLayer();
                if (!layer) return;

                var stageRect = document.getElementById('bannerDesignerStage').getBoundingClientRect();
                self.designer.drag = {
                    layerId: layerId,
                    startX: e.clientX,
                    startY: e.clientY,
                    origX: layer.x,
                    origY: layer.y,
                    scaleX: self.designer.width / stageRect.width,
                    scaleY: self.designer.height / stageRect.height
                };
            });

            $(document).on('mousemove', function (e) {
                if (!self.designer.drag) return;
                var layer = self.findActiveLayer();
                if (!layer) return;

                var dx = (e.clientX - self.designer.drag.startX) * self.designer.drag.scaleX;
                var dy = (e.clientY - self.designer.drag.startY) * self.designer.drag.scaleY;
                var maxX = self.designer.width - (layer.type === 'image' ? layer.width : 20);
                var maxY = self.designer.height - (layer.type === 'image' ? layer.height : 20);
                layer.x = self.clamp(self.designer.drag.origX + dx, 0, Math.max(0, maxX));
                layer.y = self.clamp(self.designer.drag.origY + dy, 0, Math.max(0, maxY));
                self.renderDesignerStage();
            }).on('mouseup', function () {
                self.designer.drag = null;
            });

            $('#bannerDesignerStage').on('wheel', '.banner-designer-layer.image-layer', function (e) {
                var event = e.originalEvent || e;
                var layerId = $(this).data('layer-id');
                var layer = self.designer.layers.find(function (item) { return item.id === layerId; });
                if (!layer || layer.type !== 'image') return;

                e.preventDefault();
                self.designer.activeId = layerId;

                var delta = event.deltaY < 0 ? 20 : -20;
                layer.width = self.clamp((layer.width || 220) + delta, 40, self.designer.width);
                if (layer.aspectRatio && layer.aspectRatio > 0) {
                    layer.height = Math.round(layer.width / layer.aspectRatio);
                }

                self.ensureLayerWithinBounds(layer);
                self.renderDesignerStage();
            });

            if (!self.designer.layers.length) {
                self.addTextLayer($('#offerTitle').val() || 'Offer Name', { x: 60, y: 70, fontSize: 56 });
                self.addTextLayer($('#discountTag').val() || 'Discount %', { x: 60, y: 170, fontSize: 46 });
                self.addTextLayer('Coupon: ' + ($('#couponCode').val() || 'N/A'), { x: 60, y: 255, fontSize: 32 });
            } else {
                self.renderDesignerStage();
            }

            self.applyBannerMode($('input[name="banner_mode"]:checked').val() || 'upload');

            // Description character counter
            $('#descCharCount').text($('#shortDescription').val().length);
            $('#shortDescription').on('input', function () {
                $('#descCharCount').text($(this).val().length);
            });
        },
        /* ── 5. Form (Ajax + Validation) ─────────────────────── */
        initForm: function () {
            var self = this;
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
                        required: function () {
                            return self.currentBannerMode === 'upload';
                        },
                        extension: 'jpg|jpeg|png|webp'
                    },
                    generated_banner_data: {
                        required: function () {
                            return self.currentBannerMode === 'customize';
                        }
                    },
                    short_description: {
                        maxlength: 300
                    },
                    accept_terms: {
                        required: true
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
                    generated_banner_data: {
                        required: 'Please customize and generate a banner.'
                    },
                    short_description: {
                        maxlength: 'Description must not exceed 300 characters.'
                    },
                    accept_terms: {
                        required: 'Please accept the offer terms and conditions.'
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

                    if (self.currentBannerMode === 'customize') {
                        self.updateGeneratedBanner();
                        $('#bannerImage').val('');
                    } else {
                        $('#generatedBannerData').val('');
                    }

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
                            $('#bannerImageLayers').val('');
                            $('input[name="banner_mode"][value="upload"]').prop('checked', true);
                            self.applyBannerMode('upload');
                            self.designer.layers = [];
                            self.designer.activeId = null;
                            self.addTextLayer($('#offerTitle').val() || 'Offer Name', { x: 60, y: 70, fontSize: 56 });
                            self.addTextLayer($('#discountTag').val() || 'Discount %', { x: 60, y: 170, fontSize: 46 });
                            self.addTextLayer('Coupon: ' + ($('#couponCode').val() || 'N/A'), { x: 60, y: 255, fontSize: 32 });

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
        canEdit: false,
        canDelete: false,
        canApprove: false,
        routes: {
            data: '/offers/data',
            showBase: '/dashboard/offers',
            updateBase: '/dashboard/offers',
            updateStatusTemplate: '/dashboard/offers/__ID__/update-offer-status',
            deleteBase: '/dashboard/offers'
        },

        initTable: function () {
            var $table = $('#myOffersTable');
            this.canEdit = $table.data('can-edit') === 1 || $table.data('can-edit') === '1';
            this.canDelete = $table.data('can-delete') === 1 || $table.data('can-delete') === '1';
            this.canApprove = $table.data('can-approve') === 1 || $table.data('can-approve') === '1';
            this.routes.data = $table.data('url') || this.routes.data;
            this.routes.showBase = $table.data('show-url-base') || this.routes.showBase;
            this.routes.updateBase = $table.data('update-url-base') || this.routes.updateBase;
            this.routes.updateStatusTemplate = $table.data('update-status-url-template') || this.routes.updateStatusTemplate;
            this.routes.deleteBase = $table.data('delete-url-base') || this.routes.deleteBase;

            this.table = $('#myOffersTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: {
                    url: this.routes.data
                },
                columns: [
                    { data: 'title', name: 'title' },
                    { data: 'created_by_name', name: 'created_by_name', orderable: false, searchable: false },
                    { data: 'banner_preview', name: 'banner_preview', orderable: false, searchable: false },
                    { data: 'discount_tag', name: 'discount_tag' },
                    { data: 'coupon_code', name: 'coupon_code' },
                    { data: 'category_name', name: 'category_name', orderable: false, searchable: false },
                    { data: 'subcategory_name', name: 'subcategory_name', orderable: false, searchable: false },
                    { data: 'valid_until', name: 'valid_until' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[9, 'desc']]
            });
        },

        bindUi: function () {
            var self = this;
            self.modal = new bootstrap.Modal(document.getElementById('myOfferModal'));

            $(document).on('click', '.js-edit-offer', function () {
                if (!self.canEdit) {
                    return;
                }
                var id = $(this).data('id');

                $('#myOfferForm')[0].reset();
                $('#myOfferForm').attr('action', self.routes.updateBase + '/' + id);

                $.get(self.routes.showBase + '/' + id, function (response) {
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
                if (!self.canDelete) {
                    return;
                }
                var id = $(this).data('id');
                if (!confirm('Delete this offer?')) {
                    return;
                }

                $.ajax({
                    url: self.routes.deleteBase + '/' + id,
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

            $(document).on('change', '.js-offer-status', function () {
                if (!self.canApprove) {
                    return;
                }

                var id = $(this).data('id');
                var selectedStatus = $(this).val();
                var statusUrl = self.routes.updateStatusTemplate.replace('__ID__', id);

                $.ajax({
                    url: statusUrl,
                    method: 'PUT',
                    data: {
                        status: selectedStatus
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function (response) {
                    FormHelper.showAlert($('#myOfferAlert'), 'success', response.message || 'Offer status updated.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update offer status.';
                    FormHelper.showAlert($('#myOfferAlert'), 'danger', message);
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
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

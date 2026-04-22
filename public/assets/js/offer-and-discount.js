(function ($) {
    if (!$ || !window.FormHelper) {
        return;
    }

    var OffersAdmin = {
        FIELD_LIMITS: {
            title: 120,
            discount: 100,
            coupon: 50,
            description: 300,
            layerText: 120
        },
        designer: {
            layers: [],
            activeId: null,
            drag: null,
            backgroundImageSrc: null,
            backgroundImageObj: null,
            width: 768,
            height: 1080
        },
        currentBannerMode: 'upload',

        updateFinalBannerPreview: function (src) {
            var hasSrc = !!src;
            $('#bannerFinalPreview').attr('src', hasSrc ? src : '#').toggleClass('d-none', !hasSrc);
            $('#bannerFinalPreviewPlaceholder').toggleClass('d-none', hasSrc);
        },

        updateUploadMeta: function (file) {
            if (!file || !file.type || !file.type.startsWith('image/')) {
                $('#bannerImageMeta').text('Uploaded image dimensions will appear here for quick validation.');
                return;
            }

            var img = new Image();
            img.onload = function () {
                $('#bannerImageMeta').text(
                    'Uploaded image: ' + img.naturalWidth + '×' + img.naturalHeight
                    + 'px. Recommended: 768×1080px (4:5) to avoid auto-padding.'
                );
            };
            img.src = URL.createObjectURL(file);
        },

        clamp: function (value, min, max) {
            return Math.max(min, Math.min(max, value));
        },

        truncateValue: function (value, limit) {
            if (typeof value !== 'string') return '';
            return value.length > limit ? value.substring(0, limit) : value;
        },

        updateCounter: function (selector, countSelector, limit) {
            var $field = $(selector);
            var value = $field.val() || '';
            var normalized = this.truncateValue(value, limit);
            if (normalized !== value) {
                $field.val(normalized);
            }
            $(countSelector).text(normalized.length);
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
            var subcategoryEndpointBase = $('#offerForm').data('subcategory-url-base') || '/dashboard/offers/categories';

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
                    self.updateFinalBannerPreview(e.target.result);
                };
                reader.readAsDataURL(file);
                self.updateUploadMeta(file);
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
                self.updateUploadMeta(null);
                self.updateFinalBannerPreview(null);
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
                this.updateUploadMeta(null);
                this.renderDesignerStage();
                this.updateGeneratedBanner();
            } else {
                $('#generatedBannerData').val('');
                this.updateFinalBannerPreview($('#bannerPreview').attr('src') !== '#' ? $('#bannerPreview').attr('src') : null);
            }

            this.syncBannerModeCards();
        },

        addTextLayer: function (text, options) {
            options = options || {};
            var layer = {
                id: 'layer_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                type: 'text',
                text: this.truncateValue(text || 'New Text', this.FIELD_LIMITS.layerText),
                x: options.x || 60,
                y: options.y || 60,
                width: options.width || Math.round(this.designer.width * 0.84),
                fontSize: options.fontSize || 42,
                fontWeight: options.fontWeight || '700',
                color: options.color || '#ffffff',
                align: options.align || 'left',
                fontFamily: options.fontFamily || 'Arial',
                sourceTag: options.sourceTag || 'text_layer',
                manuallyPositioned: !!options.manuallyPositioned
            };
            this.designer.layers.push(layer);
            this.designer.activeId = layer.id;
            this.renderDesignerStage();
        },

        maintainDefaultTextSpacing: function () {
            var orderedTags = ['offer_title', 'discount_tag', 'coupon_code'];
            var stackLayers = [];
            var i;

            for (i = 0; i < orderedTags.length; i++) {
                var layer = this.findLayerBySourceTag(orderedTags[i]);
                if (layer && layer.type === 'text' && !layer.manuallyPositioned) {
                    stackLayers.push(layer);
                }
            }

            if (!stackLayers.length) {
                return;
            }

            var nextY = 70;
            for (i = 0; i < stackLayers.length; i++) {
                var textLayer = stackLayers[i];
                var fontSize = Math.max(10, parseInt(textLayer.fontSize || 42, 10));
                var textHeight = Math.round(fontSize * 1.25);
                textLayer.y = nextY;
                nextY += textHeight + 22;
                this.ensureLayerWithinBounds(textLayer);
            }
        },

        findLayerBySourceTag: function (sourceTag) {
            for (var i = 0; i < this.designer.layers.length; i++) {
                if (this.designer.layers[i].sourceTag === sourceTag) {
                    return this.designer.layers[i];
                }
            }
            return null;
        },

        addImageLayer: function (src, options) {
            var self = this;
            options = options || {};
            var imgObj = new Image();
            var layer = {
                id: 'layer_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                type: 'image',
                src: src,
                imageObj: imgObj,
                x: options.x || 120,
                y: options.y || 240,
                width: options.width || 520,
                height: options.height || 520,
                aspectRatio: 1,
                sourceTag: options.sourceTag || 'image_layer'
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
            if (options.toBack) {
                this.designer.layers.pop();
                this.designer.layers.unshift(layer);
            }
            this.designer.activeId = layer.id;
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
                $('#layerTextCharCount').text('0');
                $('#layerFontSizeInput').val(42);
                $('#layerBoldInput').prop('checked', true);
                $('#layerTextColorInput').val('#ffffff');
                $('#layerTextAlignInput').val('left');
                $('#layerFontFamilyInput').val('Arial');
            } else {
                $('#layerTextInput').val(layer.text || '');
                $('#layerTextCharCount').text((layer.text || '').length);
                $('#layerFontSizeInput').val(layer.fontSize || 42);
                $('#layerBoldInput').prop('checked', (layer.fontWeight || '700') === '700');
                $('#layerTextColorInput').val(layer.color || '#ffffff');
                $('#layerTextAlignInput').val(layer.align || 'left');
                $('#layerFontFamilyInput').val(layer.fontFamily || 'Arial');
            }

            $('#layerTextInput, #layerFontSizeInput, #layerBoldInput, #layerTextColorInput, #layerTextAlignInput, #layerFontFamilyInput')
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
            this.maintainDefaultTextSpacing();
            var stageNode = $stage.get(0);
            var stageScale = 1;
            if (stageNode && stageNode.clientWidth) {
                stageScale = stageNode.clientWidth / this.designer.width;
            }

            $stage.css({
                backgroundColor: $('#bannerBgColor').val() || '#2f7de1',
                backgroundImage: this.designer.backgroundImageSrc ? 'url(' + this.designer.backgroundImageSrc + ')' : 'none',
                backgroundPosition: 'center',
                backgroundSize: 'contain',
                backgroundRepeat: 'no-repeat'
            });
            $stage.empty();

            this.designer.layers.forEach(function (layer) {
                var $layer;
                if (layer.type === 'text') {
                    $layer = $('<div class="banner-designer-layer text-layer"></div>');
                    $layer.text(layer.text || '');
                    $layer.css({
                        left: (layer.x / self.designer.width * 100) + '%',
                        top: (layer.y / self.designer.height * 100) + '%',
                        width: (Math.max(40, layer.width || 420) / self.designer.width * 100) + '%',
                        color: layer.color,
                        fontSize: Math.max(10, layer.fontSize) * stageScale + 'px',
                        fontWeight: layer.fontWeight || '700',
                        textAlign: layer.align,
                        fontFamily: layer.fontFamily,
                        whiteSpace: 'pre-wrap',
                        wordBreak: 'break-word',
                        overflowWrap: 'anywhere',
                        lineHeight: '1.2',
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

            var drawBackground = function () {
                ctx.fillStyle = $('#bannerBgColor').val() || '#2f7de1';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                if (!self.designer.backgroundImageSrc) {
                    return Promise.resolve();
                }

                return new Promise(function (resolve) {
                    var drawBgImage = function (img) {
                        var scale = Math.min(canvas.width / img.width, canvas.height / img.height);
                        var drawWidth = Math.max(1, Math.round(img.width * scale));
                        var drawHeight = Math.max(1, Math.round(img.height * scale));
                        var drawX = Math.round((canvas.width - drawWidth) / 2);
                        var drawY = Math.round((canvas.height - drawHeight) / 2);

                        ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
                    };

                    if (self.designer.backgroundImageObj && self.designer.backgroundImageObj.complete) {
                        drawBgImage(self.designer.backgroundImageObj);
                        resolve();
                        return;
                    }

                    var bgImg = new Image();
                    bgImg.onload = function () {
                        self.designer.backgroundImageObj = bgImg;
                        drawBgImage(bgImg);
                        resolve();
                    };
                    bgImg.onerror = function () {
                        resolve();
                    };
                    bgImg.src = self.designer.backgroundImageSrc;
                });
            };

            var drawNext = function (index) {
                if (index >= self.designer.layers.length) {
                    var dataUrl = canvas.toDataURL('image/png');
                    $('#generatedBannerData').val(dataUrl);
                    if (self.currentBannerMode === 'customize') {
                        self.updateFinalBannerPreview(dataUrl);
                    }
                    return;
                }

                var layer = self.designer.layers[index];
                if (layer.type === 'text') {
                    var fontSize = layer.fontSize || 42;
                    var maxWidth = Math.max(40, layer.width || Math.round(self.designer.width * 0.84));
                    var lineHeight = Math.round(fontSize * 1.2);

                    // Ensure line measurements match the actual draw font.
                    ctx.font = (layer.fontWeight || '700') + ' ' + fontSize + 'px ' + (layer.fontFamily || 'Arial');
                    var lines = self.wrapTextToLines(ctx, layer.text || '', maxWidth, true);
                    var baseX = layer.x;

                    ctx.textAlign = layer.align || 'left';
                    ctx.fillStyle = layer.color || '#ffffff';
                    ctx.shadowColor = 'rgba(0,0,0,0.4)';
                    ctx.shadowBlur = 4;
                    ctx.textBaseline = 'top';

                    if ((layer.align || 'left') === 'center') {
                        baseX = layer.x + (maxWidth / 2);
                    } else if ((layer.align || 'left') === 'right') {
                        baseX = layer.x + maxWidth;
                    }

                    lines.forEach(function (line, lineIndex) {
                        ctx.fillText(line, baseX, layer.y + (lineIndex * lineHeight));
                    });
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

            drawBackground().then(function () {
                drawNext(0);
            });
        },

        wrapTextToLines: function (ctx, text, maxWidth, allowWordBreak) {
            var safeText = this.truncateValue(text || '', this.FIELD_LIMITS.layerText);
            if (!safeText) return [''];

            var lines = [];
            var paragraphs = safeText.split('\n');
            var self = this;

            paragraphs.forEach(function (paragraph) {
                var words = paragraph.split(' ');
                var current = '';

                if (words.length === 1 && words[0] === '') {
                    lines.push('');
                    return;
                }

                words.forEach(function (word) {
                    var candidate = current ? (current + ' ' + word) : word;
                    if (ctx.measureText(candidate).width <= maxWidth) {
                        current = candidate;
                        return;
                    }

                    if (current) {
                        lines.push(current);
                        current = '';
                    }

                    if (allowWordBreak && ctx.measureText(word).width > maxWidth) {
                        var chunk = '';
                        word.split('').forEach(function (ch) {
                            var withChar = chunk + ch;
                            if (ctx.measureText(withChar).width > maxWidth && chunk) {
                                lines.push(chunk);
                                chunk = ch;
                            } else {
                                chunk = withChar;
                            }
                        });
                        current = chunk;
                    } else {
                        current = word;
                    }
                });

                if (current) {
                    lines.push(current);
                }
            });

            return lines.length ? lines : [self.truncateValue(safeText, 1)];
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
            var initialCategoryId = $('#categorySelect').val();
            var initialSubcategoryId = $('#subcategorySelect').data('selected-subcategory');
            if (initialCategoryId) {
                self.loadSubcategories(initialCategoryId, initialSubcategoryId || '');
            }

            // Coupon code → auto uppercase
            $('#couponCode').on('input', function () {
                var pos = this.selectionStart;
                $(this).val($(this).val().toUpperCase());
                self.updateCounter('#couponCode', '#couponCharCount', self.FIELD_LIMITS.coupon);
                this.setSelectionRange(pos, pos);
                var couponLayer = self.findLayerBySourceTag('coupon_code');
                if (couponLayer && couponLayer.type === 'text') {
                    couponLayer.text = self.truncateValue('Coupon: ' + ($(this).val() || 'N/A'), self.FIELD_LIMITS.layerText);
                }
                self.renderDesignerStage();
            });

            $('#offerTitle').on('input', function () {
                self.updateCounter('#offerTitle', '#titleCharCount', self.FIELD_LIMITS.title);
                var titleLayer = self.findLayerBySourceTag('offer_title');
                if (titleLayer && titleLayer.type === 'text') {
                    titleLayer.text = self.truncateValue($(this).val() || 'Offer Name', self.FIELD_LIMITS.layerText);
                }
                self.renderDesignerStage();
            });

            $('#discountTag').on('input', function () {
                self.updateCounter('#discountTag', '#discountCharCount', self.FIELD_LIMITS.discount);
                var discountLayer = self.findLayerBySourceTag('discount_tag');
                if (discountLayer && discountLayer.type === 'text') {
                    discountLayer.text = self.truncateValue($(this).val() || 'Discount %', self.FIELD_LIMITS.layerText);
                }
                self.renderDesignerStage();
            });

            $('#bannerBgColor').on('input change', function () {
                self.renderDesignerStage();
            });

            $('#bannerBgImage').on('change', function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file || !file.type || !file.type.startsWith('image/')) {
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (e) {
                    self.addImageLayer(e.target.result, {
                        x: 70,
                        y: 220,
                        width: 320,
                        height: 320,
                        sourceTag: 'background_upload',
                        toBack: true
                    });
                };
                reader.readAsDataURL(file);
                $(this).val('');
            });

            $('#removeBannerBgImageBtn').on('click', function () {
                self.designer.layers = self.designer.layers.filter(function (layer) {
                    return layer.sourceTag !== 'background_upload';
                });
                $('#bannerBgImage').val('');
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

            $('#layerTextInput, #layerFontSizeInput, #layerBoldInput, #layerTextColorInput, #layerTextAlignInput, #layerFontFamilyInput').on('input change', function () {
                var layer = self.findActiveLayer();
                if (!layer || layer.type !== 'text') return;

                self.updateCounter('#layerTextInput', '#layerTextCharCount', self.FIELD_LIMITS.layerText);
                layer.text = self.truncateValue($('#layerTextInput').val(), self.FIELD_LIMITS.layerText);
                layer.fontSize = parseInt($('#layerFontSizeInput').val() || '42', 10);
                layer.fontWeight = $('#layerBoldInput').is(':checked') ? '700' : '400';
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
                if (layer.type === 'text' && (Math.abs(dx) > 2 || Math.abs(dy) > 2)) {
                    layer.manuallyPositioned = true;
                }
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
                self.addTextLayer($('#offerTitle').val() || 'Offer Name', { x: 60, y: 70, fontSize: 56, sourceTag: 'offer_title' });
                self.addTextLayer($('#discountTag').val() || 'Discount %', { x: 60, y: 170, fontSize: 46, sourceTag: 'discount_tag' });
                self.addTextLayer('Coupon: ' + ($('#couponCode').val() || 'N/A'), { x: 60, y: 255, fontSize: 32, sourceTag: 'coupon_code' });
            } else {
                self.renderDesignerStage();
            }

            self.applyBannerMode($('input[name="banner_mode"]:checked').val() || 'upload');

            // Description character counter
            this.updateCounter('#offerTitle', '#titleCharCount', this.FIELD_LIMITS.title);
            this.updateCounter('#discountTag', '#discountCharCount', this.FIELD_LIMITS.discount);
            this.updateCounter('#couponCode', '#couponCharCount', this.FIELD_LIMITS.coupon);
            this.updateCounter('#shortDescription', '#descCharCount', this.FIELD_LIMITS.description);
            this.updateCounter('#layerTextInput', '#layerTextCharCount', this.FIELD_LIMITS.layerText);

            $('#shortDescription').on('input', function () {
                self.updateCounter('#shortDescription', '#descCharCount', self.FIELD_LIMITS.description);
            });
        },
        /* ── 5. Form (Ajax + Validation) ─────────────────────── */
        initForm: function () {
            var self = this;
            var $form = $('#offerForm');
            var $btn = $('#offerSubmitBtn');
            var $text = $btn.find('.btn-text');
            var $loader = $btn.find('.btn-loader');
            var isEditMode = String($form.data('is-edit')) === '1';

            function setButtonLoading(isLoading, shouldDisable) {
                $btn.prop('disabled', !!(isLoading && shouldDisable));
                $text.toggleClass('d-none', isLoading);
                $loader.toggleClass('d-none', !isLoading);

                if (isLoading) {
                    $loader.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + (isEditMode ? 'Updating…' : 'Posting…'));
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
                        maxlength: 120
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
                        required: !isEditMode
                    }
                },
                messages: {
                    title: {
                        required: 'Please enter an offer title.',
                        minlength: 'Title must be at least 3 characters.',
                        maxlength: 'Title must not exceed 120 characters.'
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
                            FormHelper.showToast('success', response.message || (isEditMode ? 'Offer updated successfully.' : 'Offer posted successfully.'));

                            if (isEditMode) {
                                window.location.href = '/dashboard/offers';
                                return;
                            }

                            // Reset form
                            form.reset();
                            $('#subcategorySelect').html('<option value="">— Select a category first —</option>').prop('disabled', true);
                            $('#descCharCount').text('0');
                            $('#titleCharCount').text('0');
                            $('#discountCharCount').text('0');
                            $('#couponCharCount').text('0');
                            $('#layerTextCharCount').text('0');

                            // Reset banner preview
                            $('#bannerPreview').attr('src', '#');
                            $('#bannerPreviewWrap').addClass('d-none');
                            $('#bannerPlaceholder').removeClass('d-none');
                            $('#bannerImageMeta').text('Uploaded image dimensions will appear here for quick validation.');
                            self.updateFinalBannerPreview(null);
                            $('#bannerImageLayers').val('');
                            $('#bannerBgImage').val('');
                            $('input[name="banner_mode"][value="upload"]').prop('checked', true);
                            self.applyBannerMode('upload');
                            self.designer.layers = [];
                            self.designer.activeId = null;
                            self.addTextLayer($('#offerTitle').val() || 'Offer Name', { x: 60, y: 70, fontSize: 56, sourceTag: 'offer_title' });
                            self.addTextLayer($('#discountTag').val() || 'Discount %', { x: 60, y: 170, fontSize: 46, sourceTag: 'discount_tag' });
                            self.addTextLayer('Coupon: ' + ($('#couponCode').val() || 'N/A'), { x: 60, y: 255, fontSize: 32, sourceTag: 'coupon_code' });

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
                            FormHelper.showToast('danger', msg);
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
        categories: [],
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

        normalizeDateInputValue: function (rawDate) {
            if (!rawDate) {
                return '';
            }

            if (typeof rawDate === 'string') {
                if (rawDate.length >= 10 && rawDate.indexOf('-') === 4) {
                    return rawDate.substring(0, 10);
                }
            }

            var parsedDate = new Date(rawDate);
            if (!isNaN(parsedDate.getTime())) {
                return parsedDate.toISOString().substring(0, 10);
            }

            return '';
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
            this.categories = $table.data('categories') || [];

            this.table = $('#myOffersTable').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                scrollX: true,
                ajax: {
                    url: this.routes.data,
                    data: function (d) {
                        d.category_id = $('#offersFilterCategory').val() || '';
                        d.subcategory_id = $('#offersFilterSubcategory').val() || '';
                        d.validity = $('#offersFilterValidity').val() || '';
                    }
                },
                columns: [
                    { data: 'title', name: 'title', className: 'offer-col-wrap' },
                    { data: 'created_by_name', name: 'created_by_name', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'banner_preview', name: 'banner_preview', orderable: false, searchable: false, className: 'text-center text-nowrap' },
                    { data: 'discount_tag', name: 'discount_tag', className: 'offer-col-wrap' },
                    { data: 'coupon_code', name: 'coupon_code', className: 'offer-col-wrap' },
                    { data: 'category_name', name: 'category_name', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'subcategory_name', name: 'subcategory_name', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'valid_until', name: 'valid_until', className: 'text-nowrap' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false, className: 'text-nowrap' },
                    { data: 'created_at', name: 'created_at', className: 'text-nowrap' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end text-nowrap' }
                ],
                order: [[9, 'desc']]
            });
        },

        getCategoryChildren: function (categoryId) {
            if (!categoryId) {
                return [];
            }

            for (var i = 0; i < this.categories.length; i++) {
                if (String(this.categories[i].id) === String(categoryId)) {
                    return this.categories[i].children || [];
                }
            }

            return [];
        },

        populateSubcategoryFilter: function (categoryId) {
            var $sub = $('#offersFilterSubcategory');
            var subcategories = this.getCategoryChildren(categoryId);

            $sub.empty().append('<option value="">All subcategories</option>');

            if (!subcategories.length) {
                $sub.prop('disabled', true);
                return;
            }

            for (var i = 0; i < subcategories.length; i++) {
                $sub.append(
                    $('<option>', { value: subcategories[i].id, text: subcategories[i].name })
                );
            }
            $sub.prop('disabled', false);
        },

        bindUi: function () {
            var self = this;

            $('#offersFilterCategory').on('change', function () {
                self.populateSubcategoryFilter($(this).val());
                if (self.table) {
                    self.table.ajax.reload();
                }
            });

            $('#offersFilterSubcategory, #offersFilterValidity').on('change', function () {
                if (self.table) {
                    self.table.ajax.reload();
                }
            });

            $('#myOfferRemoveBannerBtn').on('click', function (e) {
                e.stopPropagation();
                clearEditBannerSelection();
            });

            $('#myOfferBannerDropzone')
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
                        $('#myOfferBannerImage')[0].files = dt.files;
                        handleEditBannerFile(file);
                    }
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
                    FormHelper.showToast('success', response.message || 'Offer status updated.');
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                }).fail(function (xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'Unable to update offer status.';
                    FormHelper.showToast('danger', message);
                    if (self.table) {
                        self.table.ajax.reload(null, false);
                    }
                });
            });
        },

        init: function () {
            if (!$('#myOffersTable').length) {
                return;
            }

            this.initTable();
            this.bindUi();
        }
    };

    $(function () {
        MyOffersAdmin.init();
    });

})(window.jQuery);

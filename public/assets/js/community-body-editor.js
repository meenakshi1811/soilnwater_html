(function (window) {
    'use strict';

    const instances = {};
    const initPromises = {};

    const EDITOR_LANGUAGES = {
        en: { label: 'English', lang: 'en', dir: 'ltr' },
        hi: { label: 'Hinglish', lang: 'hi', dir: 'ltr' },
        hindi: { label: 'Hindi', lang: 'hi', dir: 'ltr' },
        ur: { label: 'Urdu', lang: 'ur', dir: 'rtl' },
        pa: { label: 'Punjabi', lang: 'pa', dir: 'ltr' },
        bn: { label: 'Bengali', lang: 'bn', dir: 'ltr' },
        mr: { label: 'Marathi', lang: 'mr', dir: 'ltr' },
        gu: { label: 'Gujarati', lang: 'gu', dir: 'ltr' },
        ta: { label: 'Tamil', lang: 'ta', dir: 'ltr' },
        te: { label: 'Telugu', lang: 'te', dir: 'ltr' },
    };

    const TRANSLITERATION_DEST_CODES = {
        hi: 'hi',
        ur: 'ur',
        pa: 'pa',
        bn: 'bn',
        mr: 'mr',
        gu: 'gu',
        ta: 'ta',
        te: 'te',
    };

    const SANSCRIPT_TARGETS = {
        hi: 'devanagari',
        mr: 'devanagari',
        bn: 'bengali',
        pa: 'gurmukhi',
        gu: 'gujarati',
        ta: 'tamil',
        te: 'telugu',
        ur: 'urdu',
    };

    function notify(message, type) {
        if (window.FormHelper && typeof window.FormHelper.showToast === 'function') {
            window.FormHelper.showToast(type || 'error', message);
            return;
        }

        if (window.toastr && typeof window.toastr[type === 'success' ? 'success' : 'error'] === 'function') {
            window.toastr[type === 'success' ? 'success' : 'error'](message);
            return;
        }

        window.alert(message);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function normalizeLanguage(code) {
        return EDITOR_LANGUAGES[code] ? code : 'en';
    }

    function getElement(id) {
        return id ? document.getElementById(id) : null;
    }

    function createUploadAdapterClass(imageUploadUrl, csrfToken) {
        return class UploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file.then(function (file) {
                    const data = new FormData();
                    data.append('upload', file);

                    return fetch(imageUploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            Accept: 'application/json',
                        },
                        body: data,
                    }).then(async function (response) {
                        const payload = await response.json();
                        if (!response.ok || !payload.url) {
                            throw new Error(payload.message || 'Unable to upload image.');
                        }

                        return { default: payload.url };
                    });
                });
            }

            abort() {}
        };
    }

    function uploadAttachment(file, attachmentUploadUrl, csrfToken) {
        const data = new FormData();
        data.append('upload', file);

        return fetch(attachmentUploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
            body: data,
        }).then(async function (response) {
            const payload = await response.json();
            if (!response.ok || !payload.url) {
                throw new Error(payload.message || 'Unable to upload file.');
            }

            return payload;
        });
    }

    function insertHtml(editor, html) {
        editor.model.change(function () {
            const viewFragment = editor.data.processor.toView(html);
            const modelFragment = editor.data.toModel(viewFragment);
            editor.model.insertContent(modelFragment);
        });
    }

    function createFileButtonPlugin(pluginName, buttonName, label, accept, onFile) {
        const CK = window.CKEDITOR || {};
        const PluginBase = CK.Plugin;
        const ButtonView = CK.ButtonView;

        if (typeof PluginBase !== 'function' || typeof ButtonView !== 'function') {
            return null;
        }

        return class extends PluginBase {
            static get pluginName() {
                return pluginName;
            }

            init() {
                const editor = this.editor;

                editor.ui.componentFactory.add(buttonName, function (locale) {
                    const button = new ButtonView(locale);
                    button.set({ label: label, withText: true, tooltip: true });

                    button.on('execute', function () {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = accept;
                        input.style.display = 'none';
                        document.body.appendChild(input);

                        input.addEventListener('change', function () {
                            const file = input.files && input.files[0];
                            input.remove();

                            if (!file) {
                                return;
                            }

                            onFile(editor, file).catch(function (error) {
                                notify(error.message || 'Unable to upload file.', 'error');
                            });
                        }, { once: true });

                        input.click();
                    });

                    return button;
                });
            }
        };
    }

    function createPromptButtonPlugin(pluginName, buttonName, label, onExecute) {
        const CK = window.CKEDITOR || {};
        const PluginBase = CK.Plugin;
        const ButtonView = CK.ButtonView;

        if (typeof PluginBase !== 'function' || typeof ButtonView !== 'function') {
            return null;
        }

        return class extends PluginBase {
            static get pluginName() {
                return pluginName;
            }

            init() {
                const editor = this.editor;

                editor.ui.componentFactory.add(buttonName, function (locale) {
                    const button = new ButtonView(locale);
                    button.set({ label: label, withText: true, tooltip: true });

                    button.on('execute', function () {
                        try {
                            onExecute(editor);
                        } catch (error) {
                            notify(error.message || 'Unable to insert content.', 'error');
                        }
                    });

                    return button;
                });
            }
        };
    }

    function imageTextFlowPlugin(editor) {
        let locking = false;

        editor.model.document.on('change:data', function () {
            if (locking) {
                return;
            }

            const differ = editor.model.document.differ;
            let imageNode = null;

            for (const change of differ.getChanges()) {
                if (change.type !== 'insert' || !change.position) {
                    continue;
                }

                const insertedNode = change.position.nodeAfter;
                if (!insertedNode) {
                    continue;
                }

                if (insertedNode.is('element', 'imageBlock')) {
                    imageNode = insertedNode;
                    break;
                }

                if (insertedNode.is('element', 'paragraph')) {
                    const previousSibling = insertedNode.previousSibling;
                    if (!previousSibling || !previousSibling.is('element', 'imageBlock')) {
                        continue;
                    }

                    const text = [...insertedNode.getChildren()]
                        .filter(function (child) { return child.is('$text'); })
                        .map(function (child) { return child.data; })
                        .join('');

                    if (text.trim() === '') {
                        imageNode = previousSibling;
                        break;
                    }
                }
            }

            if (!imageNode) {
                return;
            }

            locking = true;

            const applySelection = function (writer) {
                let targetParagraph = imageNode.nextSibling;

                if (!targetParagraph || !targetParagraph.is('element', 'paragraph')) {
                    targetParagraph = writer.createElement('paragraph');
                    writer.insert(targetParagraph, writer.createPositionAfter(imageNode));
                }

                writer.setSelection(targetParagraph, 'in');
            };

            if (typeof editor.model.enqueueChange === 'function') {
                editor.model.enqueueChange({ isUndoable: false }, applySelection);
            } else {
                editor.model.change(applySelection);
            }

            locking = false;
        });
    }

    function getExtraPlugins(config) {
        const UploadAdapterClass = createUploadAdapterClass(config.imageUploadUrl, config.csrfToken);

        function uploadAdapterPlugin(editor) {
            const fileRepository = editor.plugins.get('FileRepository');
            if (!fileRepository) {
                return;
            }

            fileRepository.createUploadAdapter = function (loader) {
                return new UploadAdapterClass(loader);
            };
        }

        const InsertDocumentPlugin = createFileButtonPlugin(
            'CommunityInsertDocument',
            'insertDocument',
            'Documents',
            '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip',
            function (editor, file) {
                return uploadAttachment(file, config.attachmentUploadUrl, config.csrfToken).then(function (payload) {
                    insertHtml(
                        editor,
                        '<p><a class="community-inline-document" href="' + escapeHtml(payload.url) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(payload.name || 'Download document') + '</a></p>'
                    );
                });
            }
        );

        const UploadVideoPlugin = createFileButtonPlugin(
            'CommunityUploadVideo',
            'uploadVideo',
            'Video',
            'video/*,.mp4,.webm,.mov,.avi,.mkv',
            function (editor, file) {
                return uploadAttachment(file, config.attachmentUploadUrl, config.csrfToken).then(function (payload) {
                    insertHtml(
                        editor,
                        '<figure class="media community-inline-video"><video controls preload="metadata" src="' + escapeHtml(payload.url) + '"></video></figure>'
                    );
                });
            }
        );

        return [uploadAdapterPlugin, imageTextFlowPlugin, InsertDocumentPlugin, UploadVideoPlugin].filter(Boolean);
    }

    function getEditorConfig(config) {
        const extraPlugins = getExtraPlugins(config);
        const editorConfig = {
            toolbar: {
                items: [
                    'heading', '|',
                    'fontFamily', 'fontSize', '|',
                    'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', '|',
                    'alignment', '|',
                    'outdent', 'indent', '|',
                    'bulletedList', 'numberedList', '|',
                    'uploadImage', '|',
                    'mediaEmbed', 'uploadVideo', '|',
                    'insertDocument', '|',
                    'link', 'horizontalLine', 'specialCharacters', '|',
                    'blockQuote', '|',
                    'insertTable', '|',
                    'removeFormat', '|',
                    'undo', 'redo',
                ],
                shouldNotGroupWhenFull: true,
            },
            fontFamily: {
                options: [
                    'default',
                    { title: 'Noto Sans Devanagari', model: 'Noto Sans Devanagari, Nirmala UI, Mangal, sans-serif' },
                    { title: 'Tiro Devanagari Hindi', model: 'Tiro Devanagari Hindi, Noto Sans Devanagari, serif' },
                    { title: 'Mangal', model: 'Mangal, Nirmala UI, sans-serif' },
                    { title: 'Arial', model: 'Arial, Helvetica, sans-serif' },
                    { title: 'Times New Roman', model: 'Times New Roman, Times, serif' },
                ],
                supportAllValues: true,
            },
            fontSize: {
                options: [9, 11, 13, 'default', 17, 19, 21, 24, 28, 32, 36, 48, 72],
                supportAllValues: true,
            },
            alignment: { options: ['left', 'center', 'right', 'justify'] },
            mediaEmbed: { previewsInData: true },
            htmlSupport: {
                allow: [
                    { name: 'div', classes: true, styles: true, attributes: true },
                    { name: 'p', classes: true, styles: true },
                    { name: 'figure', classes: ['media', 'community-inline-video'] },
                    { name: 'video', attributes: { controls: true, preload: true, src: true } },
                    { name: 'a', classes: ['community-inline-document'], attributes: { href: true, target: true, rel: true } },
                    { name: 'table', classes: true },
                    { name: 'span', classes: true, styles: true, attributes: true },
                    { name: 'strong', classes: true, styles: true },
                    { name: 'em', classes: true, styles: true },
                    { name: 'h2', styles: true },
                    { name: 'h3', styles: true },
                    { name: 'h4', styles: true },
                ],
            },
            image: {
                toolbar: [
                    'imageTextAlternative', 'toggleImageCaption', '|',
                    'imageStyle:inline', 'imageStyle:alignLeft', 'imageStyle:alignRight',
                    'imageStyle:alignCenter', 'imageStyle:block', 'imageStyle:side',
                ],
            },
            table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'] },
            removePlugins: [
                'AIAssistant', 'CKBox', 'CKFinder', 'EasyImage', 'MultiLevelList',
                'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments',
                'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination',
                'WProofreader', 'MathType', 'SlashCommand', 'Template', 'DocumentOutline',
                'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'CaseChange',
            ],
        };

        if (extraPlugins.length) {
            editorConfig.extraPlugins = extraPlugins;
        }

        return editorConfig;
    }

    function createTransliterationController(state, config) {
        function getActiveLanguage() {
            const select = getElement(config.languageSelectId);
            return select ? normalizeLanguage(select.value) : 'en';
        }

        function transliterateWord(word, destLangCode) {
            const sanscript = window.Sanscript;
            const target = SANSCRIPT_TARGETS[destLangCode];

            if (!target || !sanscript || typeof sanscript.t !== 'function' || !word) {
                return word;
            }

            try {
                const converted = sanscript.t(word, 'itrans', target, { syncope: true });
                return converted && converted !== word ? converted : word;
            } catch (error) {
                return word;
            }
        }

        function resetComposition() {
            state.phoneticBuffer = '';
            state.phoneticInsertRange = null;
        }

        function updateComposition(editor, destCode) {
            const converted = transliterateWord(state.phoneticBuffer, destCode) || state.phoneticBuffer;
            state.phoneticUpdating = true;

            editor.model.change(function (writer) {
                let insertPosition = editor.model.document.selection.getFirstPosition();

                if (state.phoneticInsertRange) {
                    insertPosition = state.phoneticInsertRange.start;
                    writer.remove(state.phoneticInsertRange);
                }

                writer.insertText(converted, insertPosition);
                const endPosition = insertPosition.getShiftedBy(converted.length);
                state.phoneticInsertRange = writer.createRange(insertPosition, endPosition);
                writer.setSelection(endPosition);
            });

            state.phoneticUpdating = false;
        }

        function commitComposition(editor, destCode, suffix) {
            if (!state.phoneticBuffer) {
                return false;
            }

            const converted = transliterateWord(state.phoneticBuffer, destCode) || state.phoneticBuffer;
            state.phoneticUpdating = true;

            editor.model.change(function (writer) {
                let insertPosition = editor.model.document.selection.getFirstPosition();

                if (state.phoneticInsertRange) {
                    insertPosition = state.phoneticInsertRange.start;
                    writer.remove(state.phoneticInsertRange);
                }

                writer.insertText(converted + suffix, insertPosition);
                writer.setSelection(insertPosition.getShiftedBy(converted.length + suffix.length));
            });

            state.phoneticUpdating = false;
            resetComposition();

            return true;
        }

        function detach(editor) {
            const activeEditor = editor || state.editor;

            if (activeEditor && activeEditor.editing && activeEditor.editing.view && state.keydownHandler) {
                activeEditor.editing.view.document.off('keydown', state.keydownHandler);
            }

            if (activeEditor && activeEditor.model && state.selectionHandler) {
                activeEditor.model.document.selection.off('change:range', state.selectionHandler);
            }

            state.keydownHandler = null;
            state.selectionHandler = null;
            state.editor = null;
            resetComposition();
        }

        function attach(editor) {
            detach(editor);
            state.editor = editor;

            state.selectionHandler = function () {
                if (state.phoneticUpdating || !state.phoneticBuffer || !state.phoneticInsertRange) {
                    return;
                }

                const selection = editor.model.document.selection;
                if (!selection.isCollapsed) {
                    resetComposition();
                    return;
                }

                if (!selection.focus.isEqual(state.phoneticInsertRange.end)) {
                    resetComposition();
                }
            };

            state.keydownHandler = function (event, data) {
                const domEvent = data.domEvent;
                if (!domEvent || domEvent.isComposing || domEvent.ctrlKey || domEvent.metaKey || domEvent.altKey) {
                    return;
                }

                const destCode = TRANSLITERATION_DEST_CODES[getActiveLanguage()];
                if (!destCode) {
                    return;
                }

                const key = domEvent.key;

                if (key === ' ' || key === 'Enter') {
                    if (state.phoneticBuffer) {
                        domEvent.preventDefault();
                        event.stop();
                        commitComposition(editor, destCode, key === 'Enter' ? '\n' : ' ');
                    }
                    return;
                }

                if (key === 'Backspace') {
                    if (!state.phoneticBuffer) {
                        return;
                    }

                    domEvent.preventDefault();
                    event.stop();
                    state.phoneticBuffer = state.phoneticBuffer.slice(0, -1);

                    if (state.phoneticBuffer === '') {
                        editor.model.change(function (writer) {
                            if (state.phoneticInsertRange) {
                                writer.remove(state.phoneticInsertRange);
                                writer.setSelection(state.phoneticInsertRange.start);
                            }
                        });
                        resetComposition();
                        return;
                    }

                    updateComposition(editor, destCode);
                    return;
                }

                if (/^[a-zA-Z]$/.test(key)) {
                    domEvent.preventDefault();
                    event.stop();
                    state.phoneticBuffer += key;
                    updateComposition(editor, destCode);
                }
            };

            editor.model.document.selection.on('change:range', state.selectionHandler);
            editor.editing.view.document.on('keydown', state.keydownHandler, { priority: 'highest' });
        }

        function sync(languageCode) {
            const language = normalizeLanguage(languageCode);
            const hint = getElement(config.transliterationHintId);
            const mount = getElement(config.mountId);
            const needsTransliteration = Boolean(TRANSLITERATION_DEST_CODES[language]);
            const languageLabel = (EDITOR_LANGUAGES[language] || {}).label || language;
            const isHindiWordMode = language === 'hindi';

            if (mount) {
                mount.classList.toggle('is-hindi-word-mode', isHindiWordMode);
            }

            if (hint) {
                if (needsTransliteration) {
                    hint.classList.remove('d-none');
                    hint.innerHTML = '<strong>' + languageLabel + ' typing mode is ON.</strong> Click in the editor and type English letters — they convert to ' + languageLabel + ' instantly as you type.';
                } else if (isHindiWordMode) {
                    hint.classList.remove('d-none');
                    hint.innerHTML = '<strong>Hindi mode is ON.</strong> Use your Windows Hindi keyboard (Win + Space) to type Hindi in the editor.';
                } else {
                    hint.classList.add('d-none');
                }
            }

            if (!state.instance) {
                resetComposition();
                return;
            }

            if (needsTransliteration) {
                attach(state.instance);
            } else {
                detach(state.instance);
            }
        }

        function applyLanguage(languageCode) {
            const language = normalizeLanguage(languageCode);
            const select = getElement(config.languageSelectId);
            const hidden = getElement(config.languageHiddenId);

            if (select) {
                select.value = language;
            }

            if (hidden) {
                hidden.value = language;
            }

            if (state.instance) {
                const root = state.instance.editing.view.getDomRoot();
                if (root) {
                    const languageMeta = EDITOR_LANGUAGES[language] || EDITOR_LANGUAGES.en;
                    root.setAttribute('lang', languageMeta.lang);
                    root.setAttribute('dir', languageMeta.dir || 'ltr');
                }
            }

            sync(language);
        }

        return {
            applyLanguage: applyLanguage,
            sync: sync,
            detach: detach,
        };
    }

    function init(config) {
        config = config || {};
        const instanceKey = config.instanceKey || 'default';

        if (instances[instanceKey]) {
            return Promise.resolve(instances[instanceKey]);
        }

        if (initPromises[instanceKey]) {
            return initPromises[instanceKey];
        }

        const textarea = getElement(config.textareaId);
        const mount = getElement(config.mountId);

        if (!textarea || !mount) {
            return Promise.resolve(null);
        }

        const EditorClass = (window.CKEDITOR && window.CKEDITOR.ClassicEditor) || window.ClassicEditor;

        if (!EditorClass || typeof EditorClass.create !== 'function') {
            notify('Rich text editor failed to load. Please refresh the page.', 'error');
            return Promise.resolve(null);
        }

        const transliterationState = {
            instance: null,
            editor: null,
            phoneticBuffer: '',
            phoneticInsertRange: null,
            phoneticUpdating: false,
            keydownHandler: null,
            selectionHandler: null,
        };

        const transliteration = createTransliterationController(transliterationState, config);

        initPromises[instanceKey] = new Promise(function (resolve) {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    EditorClass.create(textarea, getEditorConfig(config))
                        .then(function (editor) {
                            instances[instanceKey] = editor;
                            transliterationState.instance = editor;
                            mount.classList.add('is-editor-ready');
                            textarea.disabled = false;

                            transliteration.applyLanguage(config.initialLanguage || 'en');

                            if (window.SoilnWaterKrutiDev && typeof window.SoilnWaterKrutiDev.attachToEditor === 'function') {
                                window.SoilnWaterKrutiDev.attachToEditor(editor);
                            }

                            const languageSelect = getElement(config.languageSelectId);
                            if (languageSelect) {
                                languageSelect.addEventListener('change', function () {
                                    transliteration.applyLanguage(this.value);
                                    editor.editing.view.focus();
                                });
                            }

                            editor.model.document.on('change:data', function () {
                                textarea.value = editor.getData();
                            });

                            resolve(editor);
                        })
                        .catch(function (error) {
                            initPromises[instanceKey] = null;
                            console.error('Unable to load rich text editor.', error);
                            notify('Unable to load the rich text editor. Please refresh and try again.', 'error');
                            resolve(null);
                        });
                });
            });
        });

        return initPromises[instanceKey];
    }

    function syncToTextarea(instanceKey) {
        const editor = instances[instanceKey || 'default'];
        const config = window.communityBodyEditorConfigs && window.communityBodyEditorConfigs[instanceKey || 'default'];

        if (!editor || !config) {
            return;
        }

        const textarea = getElement(config.textareaId);
        if (textarea) {
            textarea.value = editor.getData();
        }
    }

    function destroy(instanceKey) {
        const key = instanceKey || 'default';
        const editor = instances[key];

        if (editor && typeof editor.destroy === 'function') {
            editor.destroy();
        }

        delete instances[key];
        delete initPromises[key];
    }

    window.CommunityBodyEditor = {
        init: init,
        syncToTextarea: syncToTextarea,
        destroy: destroy,
        get: function (instanceKey) {
            return instances[instanceKey || 'default'] || null;
        },
    };

    window.communityBodyEditorConfigs = window.communityBodyEditorConfigs || {};
})(window);

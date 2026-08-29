(function (root, factory) {
    const api = factory();
    root.SoilnWaterKrutiDev = api;
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }
}(typeof window !== 'undefined' ? window : globalThis, function () {
    const LEGACY_FONT_PATTERN = /kruti\s*dev|krutidev|devlys|walkman|chanakya/i;

    const MARKER_PATTERNS = [
        /\bgSa?\b/,
        /\bds\b/,
        /\bdh\b/,
        /\besa\b/,
        /\bvk/,
        /\bfd\b/,
        /\btks\b/,
        /\bog\b/,
        /\bge\b/,
        /\bdks\b/,
        /gS\]/,
        /[a-zA-Z]A(?:\s|$)/,
        /\bcM\+/,
        /\brqE/,
        /\bmlls\b/,
        /\bgksxk\b/,
        /\biwjk\b/,
        /\bvknfe/,
        /\btkrk\b/,
        /\bik;k\b/,
        /\blqukrs\b/,
        /\bekywe\b/,
        /\bcuek/,
        /\b,d\b/,
        /\bgS/,
        /\bD;k\b/,
        /\bugha\b/,
        /\bgksrk\b/,
        /\btaxy\b/,
    ];

    const ENGLISH_STOPWORDS = /\b(the|and|for|with|this|that|from|have|are|was|not|you|your|will|they|their|been|were|this|about)\b/gi;

    const ARRAY_ONE = [
        'ñ', 'Q+Z', 'sas', 'aa', ')Z', 'ZZ', '‘', '’', '“', '”',
        'å', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹',
        '¶+', 'd+', '[+k', '[+', 'x+', 'T+', 't+', 'M+', '<+', 'Q+', ';+', 'j+', 'u+',
        'Ùk', 'Ù', 'ä', '–', '—', 'é', '™', '=kk', 'f=k',
        'à', 'á', 'â', 'ã', 'ºz', 'º', 'í', '{k', '{', '=', '«',
        'Nî', 'Vî', 'Bî', 'Mî', '<î', '|', 'K', '}',
        'J', 'Vª', 'Mª', '<ªª', 'Nª', 'Ø', 'Ý', 'nzZ', 'æ', 'ç', 'Á', 'xz', '#', ':',
        'v‚', 'vks', 'vkS', 'vk', 'v', 'b±', 'Ã', 'bZ', 'b', 'm', 'Å', ',s', ',', '_',
        'ô', 'd', 'Dk', 'D', '[k', '[', 'x', 'Xk', 'X', 'Ä', '?k', '?', '³',
        'pkS', 'p', 'Pk', 'P', 'N', 't', 'Tk', 'T', '>', '÷', '¥',
        'ê', 'ë', 'V', 'B', 'ì', 'ï', 'M+', '<+', 'M', '<', '.k', '.',
        'r', 'Rk', 'R', 'Fk', 'F', ')', 'n', '/k', 'èk', '/', 'Ë', 'è', 'u', 'Uk', 'U',
        'i', 'Ik', 'I', 'Q', '¶', 'c', 'Ck', 'C', 'Hk', 'H', 'e', 'Ek', 'E',
        ';', '¸', 'j', 'y', 'Yk', 'Y', 'G', 'o', 'Ok', 'O',
        "'k", "'", '"k', '"', 'l', 'Lk', 'L', 'g',
        'È', 'z',
        'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Ö', 'Ø', 'Ù', 'Ük', 'Ü',
        '‚', 'ks', 'kS', 'k', 'h', 'q', 'w', '`', 's', 'S',
        'a', '¡', '%', 'W', '•', '·', '∙', '·', '~j', '~', '\\', '+', ' ः',
        '^', '*', 'Þ', 'ß', '(', '¼', '½', '¿', 'À', '¾', 'A', '-', '&', '&', 'Œ', ']', '~ ', '@',
    ];

    const ARRAY_TWO = [
        '॰', 'QZ+', 'sa', 'a', 'र्द्ध', 'Z', '"', '"', "'", "'",
        '०', '१', '२', '३', '४', '५', '६', '७', '८', '९',
        'फ़्', 'क़', 'ख़', 'ख़्', 'ग़', 'ज़्', 'ज़', 'ड़', 'ढ़', 'फ़', 'य़', 'ऱ', 'ऩ',
        'त्त', 'त्त्', 'क्त', 'दृ', 'कृ', 'न्न', 'न्न्', '=k', 'f=',
        'ह्न', 'ह्य', 'हृ', 'ह्म', 'ह्र', 'ह्', 'द्द', 'क्ष', 'क्ष्', 'त्र', 'त्र्',
        'छ्य', 'ट्य', 'ठ्य', 'ड्य', 'ढ्य', 'द्य', 'ज्ञ', 'द्व',
        'श्र', 'ट्र', 'ड्र', 'ढ्र', 'छ्र', 'क्र', 'फ्र', 'र्द्र', 'द्र', 'प्र', 'प्र', 'ग्र', 'रु', 'रू',
        'ऑ', 'ओ', 'औ', 'आ', 'अ', 'ईं', 'ई', 'ई', 'इ', 'उ', 'ऊ', 'ऐ', 'ए', 'ऋ',
        'क्क', 'क', 'क', 'क्', 'ख', 'ख्', 'ग', 'ग', 'ग्', 'घ', 'घ', 'घ्', 'ङ',
        'चै', 'च', 'च', 'च्', 'छ', 'ज', 'ज', 'ज्', 'झ', 'झ्', 'ञ',
        'ट्ट', 'ट्ठ', 'ट', 'ठ', 'ड्ड', 'ड्ढ', 'ड़', 'ढ़', 'ड', 'ढ', 'ण', 'ण्',
        'त', 'त', 'त्', 'थ', 'थ्', 'द्ध', 'द', 'ध', 'ध', 'ध्', 'ध्', 'ध्', 'न', 'न', 'न्',
        'प', 'प', 'प्', 'फ', 'फ्', 'ब', 'ब', 'ब्', 'भ', 'भ्', 'म', 'म', 'म्',
        'य', 'य्', 'र', 'ल', 'ल', 'ल्', 'ळ', 'व', 'व', 'व्',
        'श', 'श्', 'ष', 'ष्', 'स', 'स', 'स्', 'ह',
        'ीं', '्र',
        'द्द', 'ट्ट', 'ट्ठ', 'ड्ड', 'कृ', 'भ', '्य', 'ड्ढ', 'झ्', 'क्र', 'त्त्', 'श', 'श्',
        'ॉ', 'ो', 'ौ', 'ा', 'ी', 'ु', 'ू', 'ृ', 'े', 'ै',
        'ं', 'ँ', 'ः', 'ॅ', 'ऽ', 'ऽ', 'ऽ', 'ऽ', '्र', '्', '?', '़', ':',
        '‘', '’', '“', '”', ';', '(', ')', '{', '}', '=', '।', '.', '-', 'µ', '॰', ',', '् ', '/',
    ];

    function replaceAll(haystack, needle, replacement) {
        if (!needle || haystack.indexOf(needle) === -1) {
            return haystack;
        }

        return haystack.split(needle).join(replacement);
    }

    function stripTags(value) {
        return String(value || '')
            .replace(/<script[\s\S]*?<\/script>/gi, ' ')
            .replace(/<style[\s\S]*?<\/style>/gi, ' ')
            .replace(/<[^>]+>/g, ' ')
            .replace(/&nbsp;/gi, ' ')
            .replace(/&amp;/gi, '&')
            .replace(/&lt;/gi, '<')
            .replace(/&gt;/gi, '>')
            .replace(/&quot;/gi, '"')
            .replace(/&#39;/g, "'");
    }

    function looksLike(text, options) {
        const settings = options || {};
        const source = stripTags(text).trim();

        if (!source || source.length < 6) {
            return Boolean(settings.force && source);
        }

        const letters = source.match(/\p{L}/gu) || [];
        const devanagari = source.match(/\p{Script=Devanagari}/gu) || [];
        if (letters.length > 0 && (devanagari.length / letters.length) > 0.25) {
            return false;
        }

        const englishHits = (source.match(ENGLISH_STOPWORDS) || []).length;
        if (englishHits >= 3) {
            return false;
        }

        let hits = 0;
        MARKER_PATTERNS.forEach(function (pattern) {
            if (pattern.test(source)) {
                hits += 1;
            }
        });

        if (settings.force && hits >= 1) {
            return true;
        }

        if (hits >= 3) {
            return true;
        }

        if (hits >= 2 && source.length >= 16) {
            return true;
        }

        return hits >= 1 && source.length >= 36 && /\bgS/.test(source) && /\b(ds|dh|esa|vk|fd)\b/.test(source);
    }

    function convert(text) {
        let modified = String(text || '');
        if (!modified) {
            return modified;
        }

        ARRAY_ONE.forEach(function (symbol, index) {
            modified = replaceAll(modified, symbol, ARRAY_TWO[index]);
        });

        modified = replaceAll(modified, '±', 'Zं');
        modified = replaceAll(modified, 'Æ', 'र्f');

        let positionOfI = modified.indexOf('f');
        while (positionOfI !== -1) {
            const next = modified.charAt(positionOfI + 1);
            const search = 'f' + next;
            const replacement = next + 'ि';
            modified = modified.slice(0, positionOfI) + replacement + modified.slice(positionOfI + search.length);
            positionOfI = modified.indexOf('f', positionOfI + replacement.length);
        }

        modified = replaceAll(modified, 'Ç', 'fa');
        modified = replaceAll(modified, 'É', 'र्fa');

        let positionOfFa = modified.indexOf('fa');
        while (positionOfFa !== -1) {
            const next = modified.charAt(positionOfFa + 2);
            const search = 'fa' + next;
            const replacement = next + 'िं';
            modified = modified.slice(0, positionOfFa) + replacement + modified.slice(positionOfFa + search.length);
            positionOfFa = modified.indexOf('fa', positionOfFa + replacement.length);
        }

        modified = replaceAll(modified, 'Ê', 'ीZ');

        let positionOfWrongEe = modified.indexOf('ि्');
        while (positionOfWrongEe !== -1) {
            const consonant = modified.charAt(positionOfWrongEe + 2);
            const search = 'ि्' + consonant;
            const replacement = '्' + consonant + 'ि';
            modified = modified.slice(0, positionOfWrongEe) + replacement + modified.slice(positionOfWrongEe + search.length);
            positionOfWrongEe = modified.indexOf('ि्', positionOfWrongEe + 2);
        }

        const setOfMatras = 'अ आ इ ई उ ऊ ए ऐ ओ औ ा ि ी ु ू ृ े ै ो ौ ं : ँ ॅ';
        let positionOfR = modified.indexOf('Z');
        while (positionOfR > 0) {
            let probable = positionOfR - 1;
            let charAtProbable = modified.charAt(probable);
            while (probable > 0 && setOfMatras.indexOf(charAtProbable) !== -1) {
                probable -= 1;
                charAtProbable = modified.charAt(probable);
            }

            const chunk = modified.slice(probable, positionOfR);
            modified = modified.slice(0, probable) + 'र्' + chunk + modified.slice(positionOfR + 1);
            positionOfR = modified.indexOf('Z');
        }

        return modified;
    }

    function convertIfNeeded(text, options) {
        const source = String(text || '');
        if (!source) {
            return source;
        }

        if (!looksLike(source, options)) {
            return source;
        }

        if (source.indexOf('<') !== -1) {
            return convertHtml(source, { force: true });
        }

        return convert(source);
    }

    function convertHtml(html, options) {
        const source = String(html || '');
        if (!source) {
            return source;
        }

        const force = Boolean(options && options.force) || LEGACY_FONT_PATTERN.test(source);
        if (!force && !looksLike(source)) {
            return source;
        }

        if (typeof DOMParser === 'undefined') {
            return convert(stripTags(source));
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString('<div id="krutidev-root">' + source + '</div>', 'text/html');
        const root = doc.getElementById('krutidev-root') || doc.body;
        const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        const nodes = [];

        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }

        nodes.forEach(function (node) {
            if (!node.nodeValue || !node.nodeValue.trim()) {
                return;
            }

            const styledParent = node.parentElement;
            const fontFamily = styledParent ? (styledParent.getAttribute('style') || '') : '';
            const fromLegacyFont = LEGACY_FONT_PATTERN.test(fontFamily);
            if (force || fromLegacyFont || looksLike(node.nodeValue, { force: fromLegacyFont })) {
                node.nodeValue = convert(node.nodeValue);
            }
        });

        root.querySelectorAll('[style]').forEach(function (el) {
            el.setAttribute('style', (el.getAttribute('style') || '').replace(/font-family\s*:\s*[^;]+;?/gi, function (match) {
                return LEGACY_FONT_PATTERN.test(match) ? '' : match;
            }).trim());
        });

        return root.innerHTML;
    }

    function attachToInput(el) {
        if (!el || el.dataset.krutidevBound === '1') {
            return;
        }

        el.dataset.krutidevBound = '1';
        el.addEventListener('paste', function (event) {
            const pasted = event.clipboardData?.getData('text/plain') || '';
            if (!looksLike(pasted)) {
                return;
            }

            event.preventDefault();
            const converted = convert(pasted);
            const start = el.selectionStart ?? el.value.length;
            const end = el.selectionEnd ?? el.value.length;
            const next = el.value.slice(0, start) + converted + el.value.slice(end);
            el.value = next;
            const caret = start + converted.length;
            if (typeof el.setSelectionRange === 'function') {
                el.setSelectionRange(caret, caret);
            }
            el.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function plainToHtml(text) {
        return String(text || '')
            .split(/\n{2,}/)
            .map(function (block) {
                return '<p>' + escapeHtml(block).replace(/\n/g, '<br>') + '</p>';
            })
            .join('');
    }

    function attachToEditor(editor) {
        if (!editor || editor._krutidevPasteBound) {
            return;
        }

        editor._krutidevPasteBound = true;

        editor.editing.view.document.on('clipboardInput', function (evt, data) {
            if (editor.isReadOnly) {
                return;
            }

            const transfer = data.dataTransfer;
            if (!transfer) {
                return;
            }

            const html = transfer.getData('text/html') || '';
            const plain = transfer.getData('text/plain') || '';
            const force = LEGACY_FONT_PATTERN.test(html);
            let converted = '';

            if (html && (force || looksLike(html) || looksLike(plain))) {
                converted = convertHtml(html, { force: force || looksLike(plain) });
            } else if (looksLike(plain)) {
                converted = plainToHtml(convert(plain));
            }

            if (!converted || converted === html) {
                return;
            }

            try {
                data.content = editor.data.htmlProcessor.toView(converted);
                evt.stop();
            } catch (error) {
                console.warn('Unable to insert converted KrutiDev text.', error);
            }
        }, { priority: 'high' });
    }

    return {
        looksLike: looksLike,
        convert: convert,
        convertIfNeeded: convertIfNeeded,
        convertHtml: convertHtml,
        attachToInput: attachToInput,
        attachToEditor: attachToEditor,
    };
}));

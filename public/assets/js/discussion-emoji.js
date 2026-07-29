(function () {
    const RECENT_KEY = 'soilnwaterDiscussionRecentEmojis';
    const MAX_RECENT = 28;

    const CATEGORIES = [
        {
            id: 'smileys',
            icon: '😀',
            label: 'Smileys',
            emojis: [
                '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩',
                '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐',
                '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '😮‍💨', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷',
                '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐',
                '😕', '😟', '🙁', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱',
                '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩',
                '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾',
            ],
        },
        {
            id: 'people',
            icon: '👋',
            label: 'People',
            emojis: [
                '👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆',
                '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️',
                '💅', '🤳', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻', '👃', '🧠', '🫀', '🫁', '🦷', '🦴', '👀',
                '👁️', '👅', '👄', '👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👩', '🧓', '👴', '👵', '🙍',
                '🙎', '🙅', '🙆', '💁', '🙋', '🧏', '🙇', '🤦', '🤷', '👮', '🕵️', '💂', '🥷', '👷', '🤴', '👸',
                '👳', '👲', '🧕', '🤵', '👰', '🤰', '🤱', '👼', '🎅', '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜',
            ],
        },
        {
            id: 'nature',
            icon: '🐻',
            label: 'Nature',
            emojis: [
                '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵',
                '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗',
                '🐴', '🦄', '🐝', '🪱', '🐛', '🦋', '🐌', '🐞', '🐜', '🪰', '🪲', '🪳', '🦟', '🦗', '🕷️', '🕸️',
                '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳',
                '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🐃',
                '🌸', '💮', '🏵️', '🌹', '🥀', '🌺', '🌻', '🌼', '🌷', '🌱', '🪴', '🌲', '🌳', '🌴', '🌵', '🌾',
                '🌿', '☘️', '🍀', '🍁', '🍂', '🍃', '🌍', '🌎', '🌏', '🌑', '🌒', '🌓', '🌔', '🌕', '🌖', '🌗',
            ],
        },
        {
            id: 'food',
            icon: '🍕',
            label: 'Food',
            emojis: [
                '🍇', '🍈', '🍉', '🍊', '🍋', '🍌', '🍍', '🥭', '🍎', '🍏', '🍐', '🍑', '🍒', '🍓', '🫐', '🥝',
                '🍅', '🫒', '🥥', '🥑', '🍆', '🥔', '🥕', '🌽', '🌶️', '🫑', '🥒', '🥬', '🥦', '🧄', '🧅', '🍄',
                '🥜', '🌰', '🍞', '🥐', '🥖', '🫓', '🥨', '🥯', '🥞', '🧇', '🧀', '🍖', '🍗', '🥩', '🥓', '🍔',
                '🍟', '🍕', '🌭', '🥪', '🌮', '🌯', '🫔', '🥙', '🧆', '🥚', '🍳', '🥘', '🍲', '🫕', '🥣', '🥗',
                '🍿', '🧈', '🧂', '🥫', '🍱', '🍘', '🍙', '🍚', '🍛', '🍜', '🍝', '🍠', '🍢', '🍣', '🍤', '🍥',
                '🥮', '🍡', '🥟', '🥠', '🥡', '🦀', '🦞', '🦐', '🦑', '🦪', '🍦', '🍧', '🍨', '🍩', '🍪', '🎂',
                '🍰', '🧁', '🥧', '🍫', '🍬', '🍭', '🍮', '🍯', '🍼', '🥛', '☕', '🫖', '🍵', '🍶', '🍾', '🍷',
            ],
        },
        {
            id: 'activities',
            icon: '⚽',
            label: 'Activities',
            emojis: [
                '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍',
                '🏏', '🪃', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛼', '🛷', '⛸️', '🥌',
                '🎿', '⛷️', '🏂', '🪂', '🏋️', '🤼', '🤸', '⛹️', '🤺', '🤾', '🏌️', '🏇', '🧘', '🏄', '🏊', '🤽',
                '🚣', '🧗', '🚵', '🚴', '🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🎗️', '🎫', '🎟️', '🎪', '🤹', '🎭',
                '🩰', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🪘', '🎷', '🎺', '🪗', '🎸', '🪕', '🎻', '🎲',
                '♟️', '🎯', '🎳', '🎮', '🎰', '🧩', '🎈', '🎏', '🎀', '🎁', '🎊', '🎉', '🎎', '🏮', '🎐', '🧧',
            ],
        },
        {
            id: 'travel',
            icon: '🚗',
            label: 'Travel',
            emojis: [
                '🚗', '🚕', '🚙', '🚌', '🚎', '🏎️', '🚓', '🚑', '🚒', '🚐', '🛻', '🚚', '🚛', '🚜', '🦯', '🦽',
                '🦼', '🛴', '🚲', '🛵', '🏍️', '🛺', '🚨', '🚔', '🚍', '🚘', '🚖', '🚡', '🚠', '🚟', '🚃', '🚋',
                '🚞', '🚝', '🚄', '🚅', '🚈', '🚂', '🚆', '🚇', '🚊', '🚉', '✈️', '🛫', '🛬', '🛩️', '💺', '🛰️',
                '🚀', '🛸', '🚁', '🛶', '⛵', '🚤', '🛥️', '🛳️', '⛴️', '🚢', '⚓', '🪝', '⛽', '🚧', '🚦', '🚥',
                '🗺️', '🗿', '🗽', '🗼', '🏰', '🏯', '🏟️', '🎡', '🎢', '🎠', '⛲', '⛱️', '🏖️', '🏝️', '🏜️', '🌋',
                '⛰️', '🏔️', '🗻', '🏕️', '⛺', '🛖', '🏠', '🏡', '🏘️', '🏚️', '🏗️', '🏭', '🏢', '🏬', '🏣', '🏤',
            ],
        },
        {
            id: 'objects',
            icon: '💡',
            label: 'Objects',
            emojis: [
                '⌚', '📱', '📲', '💻', '⌨️', '🖥️', '🖨️', '🖱️', '🖲️', '🕹️', '🗜️', '💽', '💾', '💿', '📀', '📼',
                '📷', '📸', '📹', '🎥', '📽️', '🎞️', '📞', '☎️', '📟', '📠', '📺', '📻', '🎙️', '🎚️', '🎛️', '🧭',
                '⏱️', '⏲️', '⏰', '🕰️', '⌛', '⏳', '📡', '🔋', '🔌', '💡', '🔦', '🕯️', '🪔', '🧯', '🛢️', '💸',
                '💵', '💴', '💶', '💷', '🪙', '💰', '💳', '💎', '⚖️', '🪜', '🧰', '🪛', '🔧', '🔨', '⚒️', '🛠️',
                '⛏️', '🪚', '🔩', '⚙️', '🪤', '🧱', '⛓️', '🧲', '🔫', '💣', '🧨', '🪓', '🔪', '🗡️', '⚔️', '🛡️',
                '🚬', '⚰️', '🪦', '⚱️', '🏺', '🔮', '📿', '🧿', '💈', '⚗️', '🔭', '🔬', '🕳️', '🩹', '🩺', '💊',
            ],
        },
        {
            id: 'symbols',
            icon: '❤️',
            label: 'Symbols',
            emojis: [
                '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖',
                '💘', '💝', '💟', '☮️', '✝️', '☪️', '🕉️', '☸️', '✡️', '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈',
                '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓', '🆔', '⚛️', '🉑', '☢️', '☣️', '📴',
                '📳', '🈶', '🈚', '🈸', '🈺', '🈷️', '✴️', '🆚', '💮', '🉐', '㊙️', '㊗️', '🈴', '🈵', '🈹', '🈲',
                '🅰️', '🅱️', '🆎', '🆑', '🅾️', '🆘', '❌', '⭕', '🛑', '⛔', '📛', '🚫', '💯', '💢', '♨️', '🚷',
                '🚯', '🚳', '🚱', '🔞', '📵', '🚭', '❗', '❕', '❓', '❔', '‼️', '⁉️', '🔅', '🔆', '〽️', '⚠️',
                '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🌀', '💤',
                '🏧', '🚾', '♿', '🅿️', '🛗', '🈳', '🈂️', '🛂', '🛃', '🛄', '🛅', '🚹', '🚺', '🚼', '⚧️', '🚻',
            ],
        },
    ];

    function readRecent() {
        try {
            const stored = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');

            return Array.isArray(stored) ? stored.filter(Boolean).slice(0, MAX_RECENT) : [];
        } catch (error) {
            return [];
        }
    }

    function writeRecent(emoji) {
        const recent = readRecent().filter((item) => item !== emoji);
        recent.unshift(emoji);
        localStorage.setItem(RECENT_KEY, JSON.stringify(recent.slice(0, MAX_RECENT)));
    }

    function insertAtCursor(textarea, text, maxLength = null) {
        if (!textarea) {
            return;
        }

        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? textarea.value.length;
        const nextValue = `${textarea.value.slice(0, start)}${text}${textarea.value.slice(end)}`;

        if (maxLength && nextValue.length > maxLength) {
            return;
        }

        textarea.value = nextValue;
        const cursor = start + text.length;
        textarea.setSelectionRange(cursor, cursor);
        textarea.focus();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function initPicker(options = {}) {
        const trigger = options.trigger;
        const textarea = options.textarea;
        const panel = options.panel;
        const anchor = options.anchor || trigger?.closest('.discussion-widget__composer');

        if (!trigger || !textarea || !panel || !anchor) {
            return null;
        }

        let activeCategory = readRecent().length ? 'recent' : CATEGORIES[0].id;
        let searchQuery = '';
        let isOpen = false;

        const searchInput = panel.querySelector('.discussion-widget__emoji-search-input');
        const gridEl = panel.querySelector('.discussion-widget__emoji-grid');
        const tabsEl = panel.querySelector('.discussion-widget__emoji-tabs');
        const triggerIcon = trigger.querySelector('i');
        const defaultIconClass = triggerIcon?.className || 'fa-regular fa-face-smile';
        const activeIconClass = 'fa-regular fa-keyboard';

        function getRecentCategory() {
            const emojis = readRecent();

            return {
                id: 'recent',
                icon: '🕐',
                label: 'Recent',
                emojis,
            };
        }

        function getActiveEmojis() {
            if (searchQuery.trim()) {
                const query = searchQuery.trim().toLowerCase();
                const matching = CATEGORIES
                    .filter((category) => category.label.toLowerCase().includes(query))
                    .flatMap((category) => category.emojis);

                return [...new Set(matching)];
            }

            if (activeCategory === 'recent') {
                return getRecentCategory().emojis;
            }

            return CATEGORIES.find((category) => category.id === activeCategory)?.emojis || CATEGORIES[0].emojis;
        }

        function categoryLabelFor() {
            return '';
        }

        function renderTabs() {
            if (!tabsEl) {
                return;
            }

            const tabs = [getRecentCategory(), ...CATEGORIES];

            tabsEl.innerHTML = tabs.map((category) => {
                const active = activeCategory === category.id ? ' is-active' : '';
                const disabled = category.id === 'recent' && !category.emojis.length ? ' disabled' : '';

                return `<button type="button"
                                class="discussion-widget__emoji-tab${active}"
                                data-category="${category.id}"
                                title="${category.label}"
                                aria-label="${category.label}"${disabled}>
                    <span aria-hidden="true">${category.icon}</span>
                </button>`;
            }).join('');

            tabsEl.querySelectorAll('.discussion-widget__emoji-tab:not([disabled])').forEach((button) => {
                button.addEventListener('click', () => {
                    activeCategory = button.dataset.category;
                    searchQuery = searchInput?.value || '';
                    renderTabs();
                    renderGrid();
                });
            });
        }

        function renderGrid() {
            if (!gridEl) {
                return;
            }

            const emojis = getActiveEmojis();

            if (!emojis.length) {
                gridEl.innerHTML = '<p class="discussion-widget__emoji-empty">No emoji found.</p>';

                return;
            }

            gridEl.innerHTML = emojis.map((emoji) => {
                return `<button type="button"
                                class="discussion-widget__emoji-item"
                                data-emoji="${emoji}"
                                aria-label="Insert ${emoji}">
                    ${emoji}
                </button>`;
            }).join('');

            gridEl.querySelectorAll('.discussion-widget__emoji-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const emoji = button.dataset.emoji || '';
                    const maxLength = Number(textarea.getAttribute('maxlength')) || null;
                    insertAtCursor(textarea, emoji, maxLength);
                    writeRecent(emoji);
                    if (activeCategory === 'recent') {
                        renderGrid();
                    }
                });
            });
        }

        function open() {
            if (isOpen) {
                return;
            }

            isOpen = true;
            panel.hidden = false;
            trigger.classList.add('is-active');
            trigger.setAttribute('aria-expanded', 'true');
            if (triggerIcon) {
                triggerIcon.className = activeIconClass;
            }

            if (activeCategory === 'recent' && !readRecent().length) {
                activeCategory = CATEGORIES[0].id;
            }

            renderTabs();
            renderGrid();
        }

        function close() {
            if (!isOpen) {
                return;
            }

            isOpen = false;
            panel.hidden = true;
            trigger.classList.remove('is-active');
            trigger.setAttribute('aria-expanded', 'false');
            if (triggerIcon) {
                triggerIcon.className = defaultIconClass;
            }

            if (searchInput) {
                searchInput.value = '';
                searchQuery = '';
            }
        }

        function toggle() {
            if (isOpen) {
                close();
            } else {
                open();
            }
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            toggle();
        });

        searchInput?.addEventListener('input', () => {
            searchQuery = searchInput.value;
            renderGrid();
        });

        document.addEventListener('click', (event) => {
            if (!isOpen) {
                return;
            }

            if (anchor.contains(event.target) || panel.contains(event.target)) {
                return;
            }

            close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && isOpen) {
                close();
            }
        });

        return {
            open,
            close,
            toggle,
            isOpen: () => isOpen,
        };
    }

    window.soilnwaterDiscussionEmoji = {
        initPicker,
        insertAtCursor,
    };
})();

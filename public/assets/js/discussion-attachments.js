(function () {
    function config() {
        return window.soilnwaterDiscussion?.attachments || {};
    }

    function documentIcon(extension) {
        const map = config().documentIcons || {};
        const ext = String(extension || '').toLowerCase();

        return map[ext] || 'fa-file-lines';
    }

    function detectFileKind(file) {
        const mime = String(file?.type || '');
        const extension = String(file?.name || '').split('.').pop()?.toLowerCase() || '';

        if (mime.startsWith('video/') || ['mp4', 'webm', 'mov', 'avi'].includes(extension)) {
            return 'video';
        }

        if (mime.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            return 'image';
        }

        return 'document';
    }

    function kindIcon(kind, extension) {
        if (kind === 'video') {
            return 'fa-video';
        }

        if (kind === 'image') {
            return 'fa-image';
        }

        return documentIcon(extension);
    }

    function buildAttachmentsHtml(attachments, escapeHtml) {
        if (!attachments?.length) {
            return '';
        }

        const escape = typeof escapeHtml === 'function'
            ? escapeHtml
            : (value) => String(value ?? '');

        return `<div class="discussion-attachments">${attachments.map((attachment) => {
            const kind = attachment.kind || 'image';
            const icon = attachment.icon || kindIcon(kind, attachment.extension);
            const name = escape(attachment.name || 'Attachment');
            const url = escape(attachment.url || '#');
            const extension = String(attachment.extension || '').toUpperCase();

            if (kind === 'document') {
                return `<a class="discussion-attachments__document" href="${url}" target="_blank" rel="noopener">
                    <span class="discussion-attachments__document-icon"><i class="fa-solid ${icon}"></i></span>
                    <span class="discussion-attachments__document-meta">
                        <strong>${name}</strong>
                        <span>${extension ? `${escape(extension)} file` : 'Document'}</span>
                    </span>
                    <span class="discussion-attachments__type-badge"><i class="fa-solid fa-file-lines"></i></span>
                </a>`;
            }

            if (kind === 'video') {
                return `<div class="discussion-attachments__video-wrap">
                    <span class="discussion-attachments__type-badge"><i class="fa-solid fa-video"></i></span>
                    <video class="discussion-attachments__video" controls preload="metadata" src="${url}"></video>
                </div>`;
            }

            return `<div class="discussion-attachments__image-wrap">
                <span class="discussion-attachments__type-badge"><i class="fa-solid fa-image"></i></span>
                <a class="discussion-attachments__image-link" href="${url}" target="_blank" rel="noopener">
                    <img class="discussion-attachments__image" src="${url}" alt="${name}" loading="lazy">
                </a>
            </div>`;
        }).join('')}</div>`;
    }

    function createAttachmentPool() {
        return new DataTransfer();
    }

    function renderAttachmentPreview(pool, previewEl) {
        if (!previewEl) {
            return;
        }

        previewEl.innerHTML = '';
        previewEl.hidden = !pool.files.length;

        Array.from(pool.files).forEach((file, index) => {
            const kind = detectFileKind(file);
            const item = document.createElement('div');
            item.className = `discussion-media-preview__item discussion-media-preview__item--${kind}`;

            if (kind === 'video') {
                const video = document.createElement('video');
                video.src = URL.createObjectURL(file);
                video.muted = true;
                item.appendChild(video);
            } else if (kind === 'image') {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;
                item.appendChild(img);
            } else {
                const doc = document.createElement('div');
                doc.className = 'discussion-media-preview__document';
                const ext = file.name.split('.').pop()?.toLowerCase() || '';
                doc.innerHTML = `<span class="discussion-media-preview__document-icon"><i class="fa-solid ${documentIcon(ext)}"></i></span>
                    <span class="discussion-media-preview__document-name">${file.name}</span>`;
                item.appendChild(doc);
            }

            const badge = document.createElement('span');
            badge.className = 'discussion-media-preview__type-badge';
            badge.innerHTML = `<i class="fa-solid ${kindIcon(kind, file.name.split('.').pop())}"></i>`;
            item.appendChild(badge);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'discussion-media-preview__remove';
            remove.innerHTML = '&times;';
            remove.addEventListener('click', () => {
                const next = createAttachmentPool();
                Array.from(pool.files).forEach((existing, existingIndex) => {
                    if (existingIndex !== index) {
                        next.items.add(existing);
                    }
                });

                for (let removeIndex = pool.files.length - 1; removeIndex >= 0; removeIndex -= 1) {
                    pool.items.remove(removeIndex);
                }

                Array.from(next.files).forEach((existing) => pool.items.add(existing));
                renderAttachmentPreview(pool, previewEl);
            });
            item.appendChild(remove);
            previewEl.appendChild(item);
        });
    }

    function bindAttachmentPicker(options) {
        const {
            input,
            previewEl,
            pool = createAttachmentPool(),
            imageButton,
            videoButton,
            documentButton,
        } = options;

        if (!input || !previewEl) {
            return pool;
        }

        const accept = config();

        imageButton?.addEventListener('click', (event) => {
            event.preventDefault();
            input.accept = accept.acceptImages || 'image/*';
            input.click();
        });

        videoButton?.addEventListener('click', (event) => {
            event.preventDefault();
            input.accept = accept.acceptVideos || 'video/*';
            input.click();
        });

        documentButton?.addEventListener('click', (event) => {
            event.preventDefault();
            input.accept = accept.acceptDocuments || '.pdf,.doc,.docx';
            input.click();
        });

        input.addEventListener('change', () => {
            Array.from(input.files || []).forEach((file) => pool.items.add(file));
            input.value = '';
            renderAttachmentPreview(pool, previewEl);
        });

        return pool;
    }

    function appendPoolToFormData(formData, pool, fieldName = 'attachments[]') {
        Array.from(pool?.files || []).forEach((file) => {
            formData.append(fieldName, file);
        });
    }

    function clearAttachmentPool(pool, previewEl) {
        if (pool?.items) {
            for (let index = pool.files.length - 1; index >= 0; index -= 1) {
                pool.items.remove(index);
            }
        }

        if (previewEl) {
            previewEl.innerHTML = '';
            previewEl.hidden = true;
        }
    }

    window.soilnwaterDiscussionAttachments = {
        buildAttachmentsHtml,
        bindAttachmentPicker,
        createAttachmentPool,
        appendPoolToFormData,
        clearAttachmentPool,
        detectFileKind,
        documentIcon,
        kindIcon,
    };
})();

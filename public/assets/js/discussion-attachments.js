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

    function formatBytes(bytes) {
        const value = Number(bytes) || 0;

        if (value >= 1024 * 1024 * 1024) {
            return `${(value / (1024 * 1024 * 1024)).toFixed(1)} GB`;
        }

        if (value >= 1024 * 1024) {
            return `${Math.round(value / (1024 * 1024))} MB`;
        }

        if (value >= 1024) {
            return `${Math.round(value / 1024)} KB`;
        }

        return `${value} B`;
    }

    function maxBytesForKind(kind) {
        const limits = config().maxFileBytes || {};

        return limits[kind] || limits.document || (50 * 1024 * 1024);
    }

    function validateFileSize(file) {
        const kind = detectFileKind(file);
        const maxBytes = maxBytesForKind(kind);

        if (file.size <= maxBytes) {
            return { ok: true };
        }

        return {
            ok: false,
            message: `${file.name} is too large. Maximum size for ${kind} files is ${formatBytes(maxBytes)}.`,
        };
    }

    function showUploadProgress(container, label, percent) {
        if (!container) {
            return;
        }

        const safePercent = Math.max(0, Math.min(100, Number(percent) || 0));
        const labelEl = container.querySelector('.discussion-upload-progress__label');
        const percentEl = container.querySelector('.discussion-upload-progress__percent');
        const barEl = container.querySelector('.discussion-upload-progress__bar');

        container.hidden = false;
        container.classList.toggle('is-processing', safePercent >= 100 && /send|process|finish/i.test(String(label || '')));
        if (labelEl) {
            labelEl.textContent = label || 'Uploading…';
        }
        if (percentEl) {
            percentEl.textContent = `${safePercent}%`;
        }
        if (barEl) {
            barEl.style.width = `${safePercent}%`;
        }
    }

    function resetUploadProgress(container) {
        if (!container) {
            return;
        }

        const labelEl = container.querySelector('.discussion-upload-progress__label');
        const percentEl = container.querySelector('.discussion-upload-progress__percent');
        const barEl = container.querySelector('.discussion-upload-progress__bar');

        container.classList.remove('is-processing');
        if (labelEl) {
            labelEl.textContent = 'Uploading…';
        }
        if (percentEl) {
            percentEl.textContent = '0%';
        }
        if (barEl) {
            barEl.style.width = '0%';
        }
    }

    function hideUploadProgress(container) {
        if (!container) {
            return;
        }

        resetUploadProgress(container);
        container.hidden = true;
    }

    function uploadFormData(url, formData, options = {}) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            let settled = false;

            const finish = (callback, value) => {
                if (settled) {
                    return;
                }

                settled = true;
                callback(value);
            };

            xhr.open(options.method || 'POST', url, true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', options.csrfToken || '');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.timeout = Number(options.timeout) > 0 ? Number(options.timeout) : 180000;

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable || typeof options.onProgress !== 'function') {
                    return;
                }

                const percent = Math.min(95, Math.round((event.loaded / event.total) * 95));
                options.onProgress(percent, event.loaded, event.total, 'upload');
            });

            xhr.upload.addEventListener('load', () => {
                if (typeof options.onProgress === 'function') {
                    options.onProgress(96, 0, 0, 'processing');
                }
            });

            xhr.onload = () => {
                let data = {};

                try {
                    data = JSON.parse(xhr.responseText || '{}');
                } catch (error) {
                    data = {};
                }

                const contentType = String(xhr.getResponseHeader('Content-Type') || '').toLowerCase();
                const looksLikeHtml = String(xhr.responseText || '').trim().startsWith('<');

                if (xhr.status >= 200 && xhr.status < 300) {
                    if (looksLikeHtml && !contentType.includes('json')) {
                        finish(reject, new Error('Server returned an invalid response while sending your message. Please refresh and try again.'));
                        return;
                    }

                    if (!data || typeof data !== 'object' || !data.reply) {
                        const fallbackMessage = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                            || data.message
                            || 'Message could not be sent. Please try again.';
                        finish(reject, new Error(fallbackMessage));
                        return;
                    }

                    if (typeof options.onProgress === 'function') {
                        options.onProgress(100, 0, 0, 'done');
                    }

                    finish(resolve, data);
                    return;
                }

                if (xhr.status === 413 || (xhr.status === 0 && xhr.responseText === '')) {
                    finish(reject, new Error('The file is too large for the server. Try a smaller video or ask your admin to increase upload limits.'));
                    return;
                }

                const message = (data.errors ? Object.values(data.errors).flat().filter(Boolean).join(' ') : '')
                    || data.message
                    || (xhr.status >= 500
                        ? 'The server could not process this upload. Please try again.'
                        : 'Something went wrong while uploading.');
                finish(reject, new Error(message));
            };

            xhr.onerror = () => finish(reject, new Error('Network error while uploading. Please try again.'));
            xhr.onabort = () => finish(reject, new Error('Upload cancelled.'));
            xhr.ontimeout = () => finish(reject, new Error('Upload timed out. Check your connection and try again.'));
            xhr.addEventListener('loadend', () => {
                if (settled || xhr.status !== 0) {
                    return;
                }

                finish(reject, new Error('Connection lost while sending your message. Please try again.'));
            });
            xhr.send(formData);
        });
    }

    function buildPendingAttachmentsHtml(files, options = {}) {
        const escape = typeof options.escapeHtml === 'function'
            ? options.escapeHtml
            : (value) => String(value ?? '');
        const progress = Math.max(0, Math.min(100, Number(options.progress) || 0));
        const phase = options.phase || 'upload';
        const statusLabel = phase === 'processing'
            ? 'Sending'
            : phase === 'done'
                ? 'Sent'
                : 'Uploading';

        return `<div class="discussion-attachments">${Array.from(files || []).map((file) => {
            const kind = detectFileKind(file);
            const blobUrl = URL.createObjectURL(file);
            const name = escape(file.name || 'Attachment');

            if (kind === 'video') {
                return `<div class="discussion-attachments__video-wrap discussion-attachments__media-pending" data-blob-url="${blobUrl}">
                    <video class="discussion-attachments__video" src="${blobUrl}" muted playsinline preload="metadata"></video>
                    <div class="discussion-msg__upload-overlay" aria-hidden="true">
                        <div class="discussion-msg__upload-spinner"></div>
                        <span class="discussion-msg__upload-label">${statusLabel} · ${progress}%</span>
                        <div class="discussion-msg__upload-track"><div class="discussion-msg__upload-bar" style="width:${progress}%"></div></div>
                    </div>
                </div>`;
            }

            if (kind === 'image') {
                return `<div class="discussion-attachments__image-wrap discussion-attachments__media-pending" data-blob-url="${blobUrl}">
                    <img class="discussion-attachments__image" src="${blobUrl}" alt="${name}">
                    <div class="discussion-msg__upload-overlay" aria-hidden="true">
                        <div class="discussion-msg__upload-spinner"></div>
                        <span class="discussion-msg__upload-label">${statusLabel} · ${progress}%</span>
                        <div class="discussion-msg__upload-track"><div class="discussion-msg__upload-bar" style="width:${progress}%"></div></div>
                    </div>
                </div>`;
            }

            const ext = String(file.name || '').split('.').pop()?.toLowerCase() || '';
            return `<div class="discussion-attachments__document discussion-attachments__media-pending" data-blob-url="${blobUrl}">
                <span class="discussion-attachments__document-icon"><i class="fa-solid ${documentIcon(ext)}"></i></span>
                <span class="discussion-attachments__document-meta">
                    <strong>${name}</strong>
                    <span>${statusLabel} · ${progress}%</span>
                </span>
                <div class="discussion-msg__upload-track discussion-msg__upload-track--inline"><div class="discussion-msg__upload-bar" style="width:${progress}%"></div></div>
            </div>`;
        }).join('')}</div>`;
    }

    function revokePendingAttachmentUrls(container) {
        container?.querySelectorAll('[data-blob-url]').forEach((node) => {
            const url = node.getAttribute('data-blob-url');
            if (url) {
                URL.revokeObjectURL(url);
            }
        });
    }

    function updatePendingAttachmentProgress(container, progress, phase) {
        if (!container) {
            return;
        }

        const safeProgress = Math.max(0, Math.min(100, Number(progress) || 0));
        const statusLabel = phase === 'processing'
            ? 'Sending'
            : phase === 'done'
                ? 'Sent'
                : 'Uploading';

        container.querySelectorAll('.discussion-msg__upload-label').forEach((labelEl) => {
            labelEl.textContent = `${statusLabel} · ${safeProgress}%`;
        });
        container.querySelectorAll('.discussion-msg__upload-bar').forEach((barEl) => {
            barEl.style.width = `${safeProgress}%`;
        });
        container.classList.toggle('discussion-msg--upload-processing', phase === 'processing');
        container.classList.toggle('discussion-msg--upload-done', phase === 'done');
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
            onError,
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
            const maxAttachments = Number(config().maxAttachments || 4);
            const errors = [];

            Array.from(input.files || []).forEach((file) => {
                if (pool.files.length >= maxAttachments) {
                    errors.push(`You can attach up to ${maxAttachments} files per message.`);
                    return;
                }

                const sizeCheck = validateFileSize(file);
                if (!sizeCheck.ok) {
                    errors.push(sizeCheck.message);
                    return;
                }

                pool.items.add(file);
            });

            input.value = '';
            renderAttachmentPreview(pool, previewEl);

            if (errors.length && typeof onError === 'function') {
                onError(errors[0]);
            }
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
        validateFileSize,
        formatBytes,
        showUploadProgress,
        hideUploadProgress,
        uploadFormData,
        buildPendingAttachmentsHtml,
        updatePendingAttachmentProgress,
        revokePendingAttachmentUrls,
    };
})();

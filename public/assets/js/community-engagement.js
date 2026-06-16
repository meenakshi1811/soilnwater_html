(function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';
    }

    function notify(type, message) {
        if (window.toastr && typeof window.toastr[type] === 'function') {
            window.toastr[type](message);
            return;
        }

        alert(message);
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Request failed.');
        }

        return data;
    }

    document.addEventListener('click', async function (event) {
        const saveButton = event.target.closest('.js-community-save-post');
        if (saveButton) {
            event.preventDefault();
            saveButton.disabled = true;

            try {
                const response = await fetch(saveButton.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: new URLSearchParams({ _token: csrfToken() }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to save post.');
                }

                saveButton.classList.toggle('is-saved', Boolean(data.saved));
                saveButton.innerHTML = data.saved
                    ? '<i class="fa-solid fa-bookmark me-1"></i>Saved'
                    : '<i class="fa-regular fa-bookmark me-1"></i>Save';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to save post.');
            } finally {
                saveButton.disabled = false;
            }

            return;
        }

        const categoryButton = event.target.closest('.js-community-subscribe-category');
        if (categoryButton) {
            event.preventDefault();
            categoryButton.disabled = true;

            try {
                const data = await postJson(categoryButton.dataset.url, {
                    content_type: categoryButton.dataset.contentType,
                    category: categoryButton.dataset.category,
                });

                categoryButton.classList.toggle('is-subscribed', Boolean(data.subscribed));
                categoryButton.textContent = data.subscribed ? 'Subscribed to category' : 'Subscribe to category';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to update subscription.');
            } finally {
                categoryButton.disabled = false;
            }

            return;
        }

        const topicButton = event.target.closest('.js-community-follow-topic');
        if (topicButton) {
            event.preventDefault();
            topicButton.disabled = true;

            try {
                const data = await postJson(topicButton.dataset.url, {
                    topic: topicButton.dataset.topic,
                });

                topicButton.classList.toggle('is-following', Boolean(data.following));
                topicButton.textContent = data.following ? 'Following' : 'Follow topic';
                notify('success', data.message);
            } catch (error) {
                notify('error', error.message || 'Unable to update topic follow.');
            } finally {
                topicButton.disabled = false;
            }
        }
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('#communityPostReportForm');
        if (!form) {
            return;
        }

        event.preventDefault();

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: new FormData(form),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Unable to submit report.');
            }

            form.reset();
            const modalElement = form.closest('.modal');
            if (modalElement && window.bootstrap?.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
            }

            notify('success', data.message || 'Post reported successfully.');
        } catch (error) {
            notify('error', error.message || 'Unable to submit report.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
})();

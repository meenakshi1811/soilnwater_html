document.addEventListener('DOMContentLoaded', function () {
    const sortSelect = document.querySelector('.js-sm-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            this.closest('form')?.submit();
        });
    }

    document.querySelectorAll('.js-sm-bookmark').forEach(function (button) {
        button.addEventListener('click', async function (event) {
            event.preventDefault();

            const url = button.dataset.url;
            if (!url) {
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            button.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Bookmark request failed');
                }

                const data = await response.json();
                const saved = Boolean(data.saved ?? data.bookmarked);
                button.classList.toggle('is-saved', saved);

                const icon = button.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-solid', saved);
                    icon.classList.toggle('fa-regular', !saved);
                }
            } catch (error) {
                console.error(error);
            } finally {
                button.disabled = false;
            }
        });
    });
});

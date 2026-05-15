(function () {
    var sectionIndex = document.querySelectorAll('.vendor-section-block').length;
    var container = document.getElementById('sectionsContainer');
    var template = document.getElementById('sectionTemplate');

    document.getElementById('addSectionBtn')?.addEventListener('click', function () {
        if (!template || !container) return;
        var html = template.innerHTML.replace(/__INDEX__/g, sectionIndex++);
        var wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        container.appendChild(wrap.firstElementChild);
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.js-remove-section')) {
            var block = e.target.closest('.vendor-section-block');
            var del = block.querySelector('.section-delete-flag');
            var idInput = block.querySelector('input[name*="[id]"]');
            if (idInput && del) {
                del.value = '1';
                block.style.display = 'none';
            } else {
                block.remove();
            }
        }

        if (e.target.closest('.js-remove-slide')) {
            var btn = e.target.closest('.js-remove-slide');
            var id = btn.dataset.id;
            if (!confirm('Remove this banner slide?')) return;
            fetch('/vendor/banner-slides/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).then(function () {
                btn.closest('.vendor-banner-thumb')?.remove();
            });
        }
    });

    document.getElementById('bannerSlidesInput')?.addEventListener('change', function () {
        if (this.files.length) {
            this.closest('label')?.querySelector('p')?.textContent = this.files.length + ' file(s) selected';
        }
    });
})();

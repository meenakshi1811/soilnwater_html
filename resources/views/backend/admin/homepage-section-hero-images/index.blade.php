@extends('backend.layouts.app')

@section('title', 'Section Banner Images')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    .section-hero-card {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .section-hero-card__preview {
        height: 120px;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .section-hero-card__preview::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(255,255,255,0.92) 0%, rgba(255,255,255,0.55) 55%, rgba(255,255,255,0.35) 100%);
    }
    .section-hero-card__body {
        padding: 1rem 1.1rem 1.15rem;
    }
</style>
@endpush

@section('content')
<div class="admin-panel ems-page">
    <div class="ems-hero mb-4">
        <p class="ems-kicker mb-1">Homepage only</p>
        <h2 class="admin-title mb-1">Section Banner Images</h2>
        <p class="mb-0 text-secondary">Background images for each homepage section header (Featured Businesses, Popular Near You, Offers, etc.). Upload a new image or reset to the built-in default.</p>
    </div>

    <div class="row g-3">
        @foreach($sections as $section)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="section-hero-card h-100" data-section-key="{{ $section['key'] }}">
                    <div
                        class="section-hero-card__preview js-hero-preview"
                        style="background-image: url('{{ $section['image_url'] ?: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=800&q=80' }}')"
                    ></div>
                    <div class="section-hero-card__body">
                        <h3 class="h6 mb-2">{{ $section['label'] }}</h3>
                        <p class="small text-muted mb-3">
                            @if($section['has_custom'])
                                Custom banner active
                            @else
                                Using default (upload or run seeder)
                            @endif
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="btn btn-sm btn-primary mb-0">
                                <i class="fa-solid fa-upload me-1"></i> Upload
                                <input type="file" class="d-none js-hero-file" accept="image/jpeg,image/png,image/webp">
                            </label>
                            @if($section['has_custom'])
                                <button type="button" class="btn btn-sm btn-outline-secondary js-hero-reset">
                                    Reset default
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const updateUrl = @json(url('/admin/homepage-section-heroes'));
    const destroyUrl = @json(url('/admin/homepage-section-heroes'));

    document.querySelectorAll('.section-hero-card').forEach(function (card) {
        const key = card.dataset.sectionKey;
        const preview = card.querySelector('.js-hero-preview');
        const fileInput = card.querySelector('.js-hero-file');
        const resetBtn = card.querySelector('.js-hero-reset');

        fileInput?.addEventListener('change', function () {
            const file = fileInput.files?.[0];
            if (!file) return;

            const form = new FormData();
            form.append('image', file);
            form.append('_token', csrf);

            fetch(updateUrl + '/' + encodeURIComponent(key), {
                method: 'POST',
                body: form,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data }; }); })
                .then(function ({ ok, data }) {
                    if (!ok) throw new Error(data.message || 'Upload failed');
                    if (data.image_url) preview.style.backgroundImage = "url('" + data.image_url + "')";
                    toastr.success(data.message || 'Saved');
                    fileInput.value = '';
                    if (!resetBtn) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'btn btn-sm btn-outline-secondary js-hero-reset';
                        btn.textContent = 'Reset default';
                        btn.addEventListener('click', onReset);
                        card.querySelector('.d-flex')?.appendChild(btn);
                    }
                })
                .catch(function (err) {
                    toastr.error(err.message || 'Upload failed');
                    fileInput.value = '';
                });
        });

        function onReset() {
            if (!confirm('Remove custom banner for this section?')) return;

            fetch(destroyUrl + '/' + encodeURIComponent(key), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data }; }); })
                .then(function ({ ok, data }) {
                    if (!ok) throw new Error(data.message || 'Reset failed');
                    toastr.success(data.message || 'Reset');
                    location.reload();
                })
                .catch(function (err) { toastr.error(err.message || 'Reset failed'); });
        }

        resetBtn?.addEventListener('click', onReset);
    });
})();
</script>
@endpush

@php
    $bookPages = $post->bookPages();
@endphp

@if($bookPages !== [])
    @once
        @push('styles')
        <style>
            .community-book-reader {
                margin: 0 auto;
                max-width: 920px;
            }

            .community-book-shell {
                background: linear-gradient(135deg, #f8f4ec 0%, #efe6d7 100%);
                border: 1px solid #d8c9ad;
                border-radius: 18px;
                box-shadow: 0 18px 40px rgba(67, 47, 24, 0.12);
                overflow: hidden;
                padding: 1.25rem;
            }

            .community-book-page {
                background: #fffdf8;
                border: 1px solid #eadfce;
                border-radius: 12px;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.65);
                display: none;
                margin: 0 auto;
                max-width: 760px;
                min-height: 420px;
                padding: 2.5rem 2.75rem;
            }

            .community-book-page.is-active {
                display: block;
            }

            .community-book-page-number {
                color: #8b7355;
                font-size: .85rem;
                font-weight: 700;
                letter-spacing: .08em;
                margin-bottom: 1.25rem;
                text-transform: uppercase;
            }

            .community-book-page-content {
                color: #2f2a24;
                font-size: 1.05rem;
                line-height: 1.85;
            }

            .community-book-page-content img {
                height: auto;
                max-width: 100%;
            }

            .community-book-controls {
                align-items: center;
                display: flex;
                gap: .75rem;
                justify-content: center;
                margin-top: 1.25rem;
            }

            .community-book-status {
                color: #6b5b45;
                font-weight: 600;
                min-width: 120px;
                text-align: center;
            }

            @media (max-width: 767.98px) {
                .community-book-page {
                    min-height: 320px;
                    padding: 1.5rem 1.25rem;
                }
            }
        </style>
        @endpush
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-book-reader]').forEach(function (reader) {
                    const pages = reader.querySelectorAll('[data-book-page]');
                    const prevBtn = reader.querySelector('[data-book-prev]');
                    const nextBtn = reader.querySelector('[data-book-next]');
                    const status = reader.querySelector('[data-book-status]');
                    let current = 0;

                    function render() {
                        pages.forEach(function (page, index) {
                            page.classList.toggle('is-active', index === current);
                        });

                        if (status) {
                            status.textContent = 'Page ' + (current + 1) + ' of ' + pages.length;
                        }

                        if (prevBtn) {
                            prevBtn.disabled = current === 0;
                        }

                        if (nextBtn) {
                            nextBtn.disabled = current >= pages.length - 1;
                        }
                    }

                    prevBtn?.addEventListener('click', function () {
                        if (current > 0) {
                            current -= 1;
                            render();
                        }
                    });

                    nextBtn?.addEventListener('click', function () {
                        if (current < pages.length - 1) {
                            current += 1;
                            render();
                        }
                    });

                    render();
                });
            });
        </script>
        @endpush
    @endonce

    <div class="community-book-reader" data-book-reader>
        <div class="community-book-shell">
            @foreach($bookPages as $index => $page)
                <article class="community-book-page{{ $index === 0 ? ' is-active' : '' }}" data-book-page>
                    <div class="community-book-page-number">Page {{ $index + 1 }}</div>
                    <div class="community-book-page-content" data-community-body-protected lang="{{ $page['language'] ?? 'en' }}">{!! $page['content'] !!}</div>
                </article>
            @endforeach
        </div>

        @if(count($bookPages) > 1)
            <div class="community-book-controls">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-book-prev>
                    <i class="fa-solid fa-chevron-left me-1"></i> Previous page
                </button>
                <span class="community-book-status" data-book-status>Page 1 of {{ count($bookPages) }}</span>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-book-next>
                    Next page <i class="fa-solid fa-chevron-right ms-1"></i>
                </button>
            </div>
        @endif
    </div>
@endif

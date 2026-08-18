@php
    $shareUrl = $shareUrl ?? (isset($post) ? $post->shareUrl() : '');
    $shareLabel = $shareLabel ?? (isset($post) ? $post->title : 'Community post');
@endphp

@once
    @push('styles')
    <style>
        .community-banner-action {
            align-items: center;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 999px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 0.88rem;
            font-weight: 700;
            gap: 0.45rem;
            line-height: 1.2;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .community-banner-action:hover,
        .community-banner-action:focus {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .community-banner-action--icon {
            height: 2.5rem;
            justify-content: center;
            min-width: 2.5rem;
            padding: 0.5rem;
            width: 2.5rem;
        }

        .community-banner-action--icon i {
            margin: 0;
        }

        .community-engagement-panel {
            width: 100%;
        }

        .community-engagement-panel__title {
            margin-bottom: 1rem;
        }

        .community-engagement-stats {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            margin-bottom: 1.15rem;
            width: 100%;
        }

        .community-engagement-stat {
            align-items: center;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            border: 1px solid rgba(15, 47, 85, 0.1);
            border-radius: 1rem;
            color: #0f2744;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            justify-content: center;
            min-height: 5.25rem;
            padding: 0.9rem 0.75rem;
            text-align: center;
        }

        .community-engagement-stat i {
            color: #1b6ca8;
            font-size: 1.25rem;
        }

        .community-engagement-stat__value {
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }

        .community-engagement-actions {
            width: 100%;
        }

        .community-engagement-icon-btn {
            align-items: center;
            display: inline-flex;
            gap: 0.35rem;
            justify-content: center;
            min-width: 2.75rem;
        }

        .community-engagement-icon-btn .reaction-label {
            display: none;
        }

        @media (max-width: 575.98px) {
            .community-engagement-stats {
                grid-template-columns: 1fr;
            }
        }

        .community-share-modal {
            border: 0;
            border-radius: 16px;
        }

        .community-share-qr-wrap {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.85rem;
            text-align: center;
        }

        .community-share-qr {
            border-radius: 12px;
            height: auto;
            width: min(100%, 220px);
        }

        .community-share-actions {
            display: grid;
            gap: 0.6rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .community-share-btn {
            align-items: center;
            border-radius: 12px;
            color: #fff !important;
            display: flex;
            font-size: 0.86rem;
            font-weight: 600;
            gap: 0.45rem;
            justify-content: center;
            padding: 0.6rem 0.5rem;
            text-decoration: none;
        }

        .community-share-btn.share-whatsapp { background: #1ba74a; }
        .community-share-btn.share-facebook { background: #1877f2; }
        .community-share-btn.share-instagram { background: linear-gradient(135deg, #f58529, #dd2a7b 45%, #8134af); }

        .community-share-inline {
            border: 1px solid #dbe9ff;
            border-radius: 0.9rem;
            background: linear-gradient(180deg, #f8fbff 0%, #f0f7ff 100%);
            overflow: hidden;
        }

        .community-share-inline-head {
            border-bottom: 1px solid #dbe9ff;
            padding: 0.8rem 0.95rem;
        }

        .community-share-inline-title {
            color: #173d67;
            font-size: 0.96rem;
            font-weight: 700;
            margin-bottom: 0.15rem;
        }

        .community-share-inline-body {
            align-items: center;
            display: flex;
            gap: 0.9rem;
            padding: 0.85rem 0.95rem 0.95rem;
        }

        .community-share-inline-qr {
            background: #fff;
            border: 1px solid #deebff;
            border-radius: 0.6rem;
            height: 112px;
            padding: 0.25rem;
            width: 112px;
        }

        @media (max-width: 767.98px) {
            .community-share-inline-body {
                align-items: flex-start;
                flex-direction: column;
            }

            .community-share-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        window.communitySharePopulate = window.communitySharePopulate || function communitySharePopulate(shareUrl, shareLabel) {
            const normalizedUrl = window.soilnwaterNormalizeShareUrl
                ? window.soilnwaterNormalizeShareUrl(shareUrl)
                : shareUrl;
            const label = shareLabel || 'Community post';
            const qrBase = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=';
            const whatsappText = 'Check out this community post: ' + normalizedUrl;
            const facebookUrl = window.soilnwaterFacebookShareUrl
                ? window.soilnwaterFacebookShareUrl(normalizedUrl)
                : 'https://www.facebook.com/sharer/sharer.php?display=popup&u=' + encodeURIComponent(normalizedUrl);

            const titleEl = document.getElementById('communityShareModalLabel');
            const subtitleEl = document.getElementById('communityShareModalSubtitle');
            const qrEl = document.getElementById('communityShareModalQr');
            const urlEl = document.getElementById('communityShareModalUrl');
            const whatsappEl = document.getElementById('communityShareModalWhatsapp');
            const facebookEl = document.getElementById('communityShareModalFacebook');
            const instagramEl = document.getElementById('communityShareModalInstagram');

            if (titleEl) {
                titleEl.textContent = 'Share: ' + label;
            }

            if (subtitleEl) {
                subtitleEl.textContent = 'Send this story using QR code or social channels.';
            }

            if (qrEl) {
                qrEl.src = qrBase + encodeURIComponent(normalizedUrl);
                qrEl.alt = 'QR code for ' + label;
            }

            if (urlEl) {
                urlEl.value = normalizedUrl;
            }

            if (whatsappEl) {
                whatsappEl.href = 'https://wa.me/?text=' + encodeURIComponent(whatsappText);
            }

            if (facebookEl) {
                facebookEl.href = facebookUrl;
            }

            if (instagramEl) {
                instagramEl.href = 'https://www.instagram.com/?url=' + encodeURIComponent(normalizedUrl);
            }
        };

        document.addEventListener('DOMContentLoaded', function () {
            const shareModal = document.getElementById('communityShareModal');

            function trackCommunityShare(trigger) {
                const trackUrl = trigger?.dataset?.shareTrackUrl;

                if (!trackUrl) {
                    return;
                }

                fetch(trackUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: new URLSearchParams({
                        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    }),
                }).catch(function () {});
            }

            if (shareModal) {
                shareModal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;

                    if (!trigger) {
                        return;
                    }

                    trackCommunityShare(trigger);

                    window.communitySharePopulate(
                        trigger.dataset.shareUrl || '',
                        trigger.dataset.shareTitle || 'Community post'
                    );
                });
            }

            document.querySelectorAll('.community-share-btn, [data-copy-community-share-link]').forEach(function (element) {
                element.addEventListener('click', function () {
                    const panel = element.closest('#communitySharePanel, .community-share-inline');
                    const trackUrl = panel?.dataset?.shareTrackUrl
                        || document.querySelector('.js-community-share-trigger[data-share-track-url]')?.dataset?.shareTrackUrl;

                    if (trackUrl) {
                        fetch(trackUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            },
                            body: new URLSearchParams({
                                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            }),
                        }).catch(function () {});
                    }
                });
            });

            document.querySelectorAll('[data-copy-community-share-link]').forEach(function (button) {
                button.addEventListener('click', async function () {
                    const targetId = button.getAttribute('data-copy-community-share-link');
                    const input = targetId ? document.getElementById(targetId) : null;

                    if (!input) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(input.value);
                        const original = button.textContent;
                        button.textContent = 'Copied';
                        window.setTimeout(function () {
                            button.textContent = original;
                        }, 1600);
                    } catch (error) {
                        input.select();
                        document.execCommand('copy');
                    }
                });
            });
        });
    </script>
    @endpush
@endonce

@if(!empty($showTrigger) && filled($shareUrl))
    <button
        type="button"
        class="community-banner-action{{ !empty($iconOnly) ? ' community-banner-action--icon' : '' }} js-community-share-trigger"
        data-bs-toggle="modal"
        data-bs-target="#communityShareModal"
        data-share-url="{{ $shareUrl }}"
        data-share-title="{{ $shareLabel }}"
        data-share-track-url="{{ isset($post) ? route('community.share.track', $post) : '' }}"
        title="Share"
        aria-label="Share"
    >
        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
        @unless(!empty($iconOnly))
            Scan &amp; Share
        @endunless
    </button>
@endif

@if(!empty($showCardTrigger) && filled($shareUrl))
    <button
        type="button"
        class="community-post-card__share-btn js-community-share-trigger"
        data-bs-toggle="modal"
        data-bs-target="#communityShareModal"
        data-share-url="{{ $shareUrl }}"
        data-share-title="{{ $shareLabel }}"
        data-share-track-url="{{ isset($post) ? route('community.share.track', $post) : '' }}"
        aria-label="Share {{ $shareLabel }}"
        title="Share this post"
    >
        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
        <span>Share</span>
    </button>
@endif

@if(!empty($showInline) && filled($shareUrl))
    <div class="community-share-inline mt-4" id="communitySharePanel" data-share-track-url="{{ isset($post) ? route('community.share.track', $post) : '' }}">
        <div class="community-share-inline-head">
            <h4 class="community-share-inline-title mb-0">Share this post</h4>
            <p class="text-muted small mb-0">Send this story using QR code or social channels.</p>
        </div>
        <div class="community-share-inline-body">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=224x224&data={{ urlencode($shareUrl) }}"
                alt="QR code for {{ $shareLabel }}"
                class="community-share-inline-qr"
                loading="lazy"
            >
            <div class="flex-grow-1 w-100">
                <label for="communityShareInlineUrl" class="form-label small text-muted mb-1">Post link</label>
                <div class="input-group input-group-sm mb-2">
                    <input id="communityShareInlineUrl" type="text" class="form-control" readonly value="{{ $shareUrl }}">
                    <button class="btn btn-outline-secondary" type="button" data-copy-community-share-link="communityShareInlineUrl">Copy</button>
                </div>
                <div class="community-share-actions">
                    <a href="https://wa.me/?text={{ urlencode('Check out this community post: '.$shareUrl) }}" target="_blank" rel="noopener" class="community-share-btn share-whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?display=popup&amp;u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="community-share-btn share-facebook"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a href="https://www.instagram.com/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="community-share-btn share-instagram"><i class="fa-brands fa-instagram"></i><span>Instagram</span></a>
                </div>
            </div>
        </div>
    </div>
@endif

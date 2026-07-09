@php
    $premiumPrompt = session('premium_upgrade_prompt');
@endphp

@if ($premiumPrompt)
    <div class="modal fade premium-upgrade-modal" id="premiumUpgradeModal" tabindex="-1" aria-labelledby="premiumUpgradeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="premium-upgrade-shell type-{{ $premiumPrompt['color'] }}">
                        <div class="premium-upgrade-hero">
                            <button type="button" class="premium-upgrade-close" data-bs-dismiss="modal" aria-label="Close">
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                            <div class="premium-upgrade-badge">
                                <i class="fa-solid fa-crown"></i>
                                {{ $premiumPrompt['singular'] }} Premium
                            </div>

                            <h2 class="premium-upgrade-title" id="premiumUpgradeModalLabel">
                                <i class="fa-solid {{ $premiumPrompt['icon'] }} me-2" style="color: var(--premium-accent);"></i>
                                {{ $premiumPrompt['headline'] }}
                            </h2>
                            <p class="premium-upgrade-subtitle">{{ $premiumPrompt['subtitle'] }}</p>
                        </div>

                        <div class="premium-upgrade-body">
                            <div class="premium-upgrade-grid">
                                @foreach ($premiumPrompt['highlights'] as $highlight)
                                    <div class="premium-upgrade-feature">
                                        <span class="premium-upgrade-feature-icon">
                                            <i class="fa-solid {{ $highlight['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <h4>{{ $highlight['title'] }}</h4>
                                            <p>{{ $highlight['text'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="premium-upgrade-price-card">
                                <div>
                                    <span class="premium-upgrade-price-label">One-time membership</span>
                                    <span class="premium-upgrade-price-amount">{{ $premiumPrompt['formatted_amount'] }}</span>
                                </div>
                                <p class="premium-upgrade-price-note">Secure payment confirmation and admin approval included.</p>
                            </div>

                            <div class="premium-upgrade-actions">
                                <a href="{{ $premiumPrompt['upgrade_url'] }}" class="btn-premium-upgrade">
                                    <i class="fa-solid fa-crown"></i>
                                    Become a Premium Member
                                </a>
                                <button type="button" class="btn btn-premium-later" data-bs-dismiss="modal">
                                    Maybe later
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

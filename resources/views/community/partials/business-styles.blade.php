<style>
    .business-hero-strip {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 45%, #fff7ed 100%);
        border: 1px solid rgba(217, 119, 6, 0.18);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        padding: 1rem 1.25rem;
    }
    .business-hero-strip__item { min-width: 140px; }
    .business-hero-strip__label {
        color: #78716c;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }
    .business-hero-strip__value { color: #1c1917; font-weight: 600; }
    .business-section-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .business-section-panel__header {
        align-items: center;
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .business-section-panel__header i { color: #d97706; }
    .business-pill {
        background: #fffbeb;
        border: 1px solid rgba(217, 119, 6, 0.22);
        border-radius: 999px;
        color: #b45309;
        display: inline-block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
    }
    .business-pill--segment { background: #eff6ff; border-color: rgba(37, 99, 235, 0.2); color: #1d4ed8; }
    .business-pill--challenge { background: #fef2f2; border-color: rgba(220, 38, 38, 0.18); color: #b91c1c; }
    .business-meta-item {
        background: #fafaf9;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.75rem;
        height: 100%;
        padding: 0.85rem 1rem;
    }
    .business-meta-item__label {
        color: #78716c;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .business-gallery-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
    .business-gallery-card {
        background: #fafaf9;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        color: inherit;
        display: block;
        overflow: hidden;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .business-gallery-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }
    .business-gallery-card img { display: block; height: 180px; object-fit: cover; width: 100%; }
    .business-gallery-card__label {
        color: #334155;
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.75rem;
    }
    .business-ask-panel__lead { color: #0f172a; font-size: 1.05rem; font-weight: 600; line-height: 1.5; }
    .business-resource-card {
        background: #fafaf9;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.85rem;
        height: 100%;
        padding: 1rem;
    }
    .business-resource-card__title { color: #92400e; font-size: 0.95rem; font-weight: 700; margin-bottom: 0.5rem; }
    .business-resource-text { color: #334155; font-size: 0.92rem; line-height: 1.55; white-space: pre-line; }
    .business-contact-panel {
        background: #fff;
        border: 1px solid rgba(217, 119, 6, 0.15);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .business-contact-panel__header {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .business-contact-panel__kicker {
        color: #d97706;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .business-contact-panel__stat {
        background: #fffbeb;
        border: 1px solid rgba(217, 119, 6, 0.18);
        border-radius: 999px;
        color: #78716c;
        font-size: 0.82rem;
        padding: 0.35rem 0.85rem;
        text-align: center;
    }
    .business-contact-panel__stat strong { color: #92400e; display: block; font-size: 1.1rem; }
    .business-author-strip {
        align-items: center;
        background: linear-gradient(135deg, #fffbeb 0%, #fff 100%);
        border: 1px solid rgba(217, 119, 6, 0.12);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.25rem;
    }
    .business-author-strip__avatar {
        align-items: center;
        background: #d97706;
        border-radius: 999px;
        color: #fff;
        display: flex;
        height: 44px;
        justify-content: center;
        width: 44px;
    }
    .business-author-strip__label {
        color: #78716c;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .business-card-badge {
        background: #fffbeb;
        border: 1px solid rgba(217, 119, 6, 0.2);
        border-radius: 999px;
        color: #92400e;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
    }
</style>

<style>
    .scf-hero-strip {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 45%, #f8fafc 100%);
        border: 1px solid rgba(5, 150, 105, 0.18);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        padding: 1rem 1.25rem;
    }
    .scf-hero-strip__item { min-width: 140px; }
    .scf-hero-strip__label {
        color: #64748b;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }
    .scf-hero-strip__value { color: #0f172a; font-weight: 600; }
    .scf-section-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .scf-section-panel__header {
        align-items: center;
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .scf-section-panel__header i { color: #059669; }
    .scf-pill {
        background: #ecfdf5;
        border: 1px solid rgba(5, 150, 105, 0.2);
        border-radius: 999px;
        color: #047857;
        display: inline-block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
    }
    .scf-pill--wisdom {
        background: #eff6ff;
        border-color: rgba(37, 99, 235, 0.2);
        color: #1d4ed8;
    }
    .scf-meta-item {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.75rem;
        height: 100%;
        padding: 0.85rem 1rem;
    }
    .scf-meta-item__label {
        color: #64748b;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .scf-key-lessons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .scf-key-lesson {
        background: linear-gradient(90deg, #ecfdf5 0%, #fff 100%);
        border-left: 4px solid #059669;
        border-radius: 0.65rem;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.5;
        padding: 0.85rem 1rem;
    }
    .scf-advice-panel {
        background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 55%, #fff 100%);
        border: 1px solid rgba(234, 88, 12, 0.2);
        border-radius: 1rem;
        padding: 1.5rem;
    }
    .scf-advice-panel__kicker {
        color: #c2410c;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }
    .scf-advice-panel__quote {
        color: #1c1917;
        font-size: 1.15rem;
        font-weight: 600;
        line-height: 1.65;
        margin: 0;
        white-space: pre-line;
    }
    .scf-achievement-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        height: 100%;
        padding: 1rem;
    }
    .scf-achievement-card__image {
        height: 72px;
        object-fit: cover;
        width: 72px;
    }
    .scf-achievement-card__icon {
        align-items: center;
        background: #ecfdf5;
        border-radius: 0.75rem;
        color: #059669;
        display: flex;
        font-size: 1.5rem;
        height: 72px;
        justify-content: center;
        width: 72px;
    }
    .scf-achievement-card__year {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .scf-heritage-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.85rem;
        height: 100%;
        padding: 1rem;
    }
    .scf-heritage-card__title {
        color: #047857;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .scf-heritage-card__text {
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.55;
        margin: 0;
        white-space: pre-line;
    }
    .scf-digital-legacy-banner {
        align-items: flex-start;
        background: linear-gradient(135deg, #1e3a5f 0%, #0f766e 100%);
        border-radius: 1rem;
        color: #fff;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        justify-content: space-between;
        padding: 1.25rem 1.5rem;
    }
    .scf-digital-legacy-banner__title {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .scf-digital-legacy-banner__benefits {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .scf-digital-legacy-banner__benefits li {
        background: rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.3rem 0.7rem;
    }
    .scf-ask-panel__lead {
        color: #0f172a;
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.5;
    }
    .scf-certificate-link {
        align-items: center;
        color: #047857;
        display: inline-flex;
        font-size: 0.85rem;
        font-weight: 600;
        gap: 0.35rem;
        text-decoration: none;
    }
    .scf-certificate-link:hover { color: #065f46; }
</style>

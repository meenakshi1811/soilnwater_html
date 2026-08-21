<style>
    .awareness-hero-strip {
        background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 45%, #f0fdf4 100%);
        border: 1px solid rgba(37, 99, 235, 0.15);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        padding: 1rem 1.25rem;
    }
    .awareness-hero-strip__item {
        min-width: 140px;
    }
    .awareness-hero-strip__label {
        color: #64748b;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }
    .awareness-hero-strip__value {
        color: #1e293b;
        font-weight: 600;
    }
    .awareness-section-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .awareness-section-panel__header {
        align-items: center;
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .awareness-section-panel__header i {
        color: #2563eb;
    }
    .awareness-audience-pill {
        background: #eff6ff;
        border: 1px solid rgba(37, 99, 235, 0.2);
        border-radius: 999px;
        color: #1d4ed8;
        display: inline-block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
    }
    .awareness-meta-item {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.75rem;
        height: 100%;
        padding: 0.85rem 1rem;
    }
    .awareness-meta-item__label {
        color: #64748b;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .awareness-infographic-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
    .awareness-infographic-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        color: inherit;
        display: block;
        overflow: hidden;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .awareness-infographic-card:hover {
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }
    .awareness-infographic-card img {
        display: block;
        height: 180px;
        object-fit: cover;
        width: 100%;
    }
    .awareness-infographic-card__label,
    .awareness-document-link {
        color: #334155;
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.75rem;
    }
    .awareness-document-link {
        align-items: center;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        display: flex;
        gap: 0.65rem;
        text-decoration: none;
    }
    .awareness-document-link i {
        color: #dc2626;
        font-size: 1.1rem;
    }
    .awareness-cta-panel__lead {
        color: #0f172a;
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.5;
    }
    .awareness-impact-strip {
        background: linear-gradient(135deg, #ecfdf5 0%, #eff6ff 100%);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 2rem;
        justify-content: space-around;
        padding: 1.25rem 1.5rem;
    }
    .awareness-impact-strip__item {
        min-width: 120px;
        text-align: center;
    }
    .awareness-impact-strip__value {
        color: #047857;
        display: block;
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .awareness-impact-strip__label {
        color: #64748b;
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.25rem;
        text-transform: uppercase;
    }
    .awareness-engagement-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .awareness-engagement-panel__header {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .awareness-engagement-panel__kicker {
        color: #2563eb;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .awareness-engagement-panel__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1rem;
    }
    .awareness-engagement-panel__stat {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 999px;
        color: #475569;
        font-size: 0.82rem;
        padding: 0.35rem 0.85rem;
    }
    .awareness-engagement-panel__stat strong {
        color: #0f172a;
    }
    .awareness-engagement-panel__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    .awareness-engagement-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.9rem;
        display: flex;
        gap: 0.85rem;
        padding: 1rem;
    }
    .awareness-engagement-card--wide {
        grid-column: 1 / -1;
    }
    .awareness-engagement-card__icon {
        align-items: center;
        border-radius: 0.75rem;
        color: #fff;
        display: flex;
        flex-shrink: 0;
        font-size: 1.1rem;
        height: 2.75rem;
        justify-content: center;
        width: 2.75rem;
    }
    .awareness-engagement-card__icon--support {
        background: linear-gradient(135deg, #059669, #10b981);
    }
    .awareness-engagement-card__icon--pledge {
        background: linear-gradient(135deg, #d97706, #f59e0b);
    }
    .awareness-engagement-card__icon--volunteer {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
    }
    .awareness-engagement-card__body {
        flex: 1;
        min-width: 0;
    }
    .awareness-pledge-counts__row {
        align-items: center;
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        display: flex;
        font-size: 0.82rem;
        gap: 0.75rem;
        justify-content: space-between;
        padding: 0.25rem 0;
    }
    .awareness-author-strip {
        align-items: center;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem 1.25rem;
    }
    .awareness-author-strip__avatar {
        align-items: center;
        background: #eff6ff;
        border-radius: 999px;
        color: #2563eb;
        display: flex;
        height: 2.75rem;
        justify-content: center;
        width: 2.75rem;
    }
    .awareness-author-strip__label {
        color: #64748b;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .awareness-author-strip__body {
        flex: 1;
        min-width: 180px;
    }
    .awareness-engagement-feedback {
        font-size: 0.9rem;
    }
    .awareness-location-layout {
        margin-top: 0;
    }
    .awareness-location-map {
        min-height: 280px;
    }
    .awareness-location-map iframe {
        border: 0;
        height: 100%;
        width: 100%;
    }
    @media (max-width: 991.98px) {
        .awareness-location-map {
            margin-top: 0.5rem;
            min-height: 240px;
        }
    }
    @media (max-width: 767.98px) {
        .awareness-engagement-panel__header {
            flex-direction: column;
        }
        .awareness-impact-strip__value {
            font-size: 1.45rem;
        }
    }
</style>

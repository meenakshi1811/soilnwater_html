    .env-show-overview {
        background: linear-gradient(135deg, #0f4c4a 0%, #0f766e 45%, #14b8a6 100%);
        border-radius: 18px;
        color: #fff;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 14px 32px rgba(15, 118, 110, 0.2);
    }
    .env-show-overview__kicker {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.92;
        margin-bottom: 0.35rem;
    }
    .env-show-overview__title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    .env-show-overview__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .env-show-chip {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .env-show-chip--flagship {
        background: rgba(255, 255, 255, 0.24);
        border-color: rgba(255, 255, 255, 0.35);
    }
    .env-flagship-banner {
        background: linear-gradient(90deg, #f0fdfa 0%, #ecfeff 55%, #ccfbf1 100%);
        border: 1px solid rgba(15, 118, 110, 0.22);
        border-radius: 14px;
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
    }
    .env-flagship-banner--water {
        border-color: rgba(14, 116, 144, 0.28);
        background: linear-gradient(90deg, #ecfeff 0%, #f0fdfa 55%, #ecfeff 100%);
    }
    .env-flagship-banner--issue {
        border-color: rgba(220, 38, 38, 0.22);
        background: linear-gradient(90deg, #fef2f2 0%, #fff7ed 55%, #fef2f2 100%);
    }
    .env-impact-strip {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .env-impact-strip__item {
        background: #fff;
        border: 1px solid rgba(15, 118, 110, 0.16);
        border-radius: 14px;
        padding: 1rem;
        text-align: center;
    }
    .env-impact-strip__value {
        color: #0f766e;
        display: block;
        font-size: 1.15rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    .env-impact-strip__label {
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .env-capability-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .env-capability-pill {
        align-items: center;
        background: #fff;
        border: 1px solid #99f6e4;
        border-radius: 999px;
        color: #0f766e;
        display: inline-flex;
        font-size: 0.8rem;
        font-weight: 600;
        gap: 0.35rem;
        padding: 0.4rem 0.8rem;
    }
    .env-capability-pill.is-disabled {
        border-color: #e5e7eb;
        color: #9ca3af;
        opacity: 0.75;
    }
    .env-gallery-category {
        margin-bottom: 1rem;
    }
    .env-gallery-category__label {
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }
    .env-community-panel {
        background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
        border-color: rgba(15, 118, 110, 0.14);
    }
    .env-community-panel .report-community-panel__kicker {
        color: #0f766e;
    }
    .env-engagement-panel {
        background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
        border: 1px solid rgba(15, 118, 110, 0.16);
        border-radius: 18px;
        padding: 1.5rem;
    }
    .env-engagement-panel__header {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1.25rem;
    }
    .env-engagement-panel__kicker {
        color: #0f766e;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .env-engagement-panel__stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .env-engagement-panel__stat {
        background: #fff;
        border: 1px solid #ccfbf1;
        border-radius: 12px;
        font-size: 0.78rem;
        padding: 0.5rem 0.85rem;
        text-align: center;
    }
    .env-engagement-panel__stat strong {
        color: #0f766e;
        display: block;
        font-size: 1.05rem;
    }
    .env-engagement-panel__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    }
    .env-engagement-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        display: flex;
        gap: 0.85rem;
        padding: 1rem;
    }
    .env-engagement-card--wide {
        grid-column: 1 / -1;
    }
    .env-engagement-card__icon {
        align-items: center;
        border-radius: 12px;
        display: flex;
        flex-shrink: 0;
        font-size: 1.1rem;
        height: 44px;
        justify-content: center;
        width: 44px;
    }
    .env-engagement-card__icon--support { background: #ccfbf1; color: #0f766e; }
    .env-engagement-card__icon--follow { background: #e0f2fe; color: #0369a1; }
    .env-engagement-card__icon--volunteer { background: #dcfce7; color: #15803d; }
    .env-engagement-feedback {
        border-radius: 12px;
    }

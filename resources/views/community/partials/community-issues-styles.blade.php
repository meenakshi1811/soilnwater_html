    .ci-show-overview {
        background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 55%, #dc2626 100%);
        border-radius: 18px;
        color: #fff;
        padding: 1.35rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    .ci-show-overview__kicker {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.88;
        margin-bottom: 0.35rem;
    }
    .ci-show-overview__title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    .ci-show-overview__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .ci-show-chip {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 0.35rem 0.8rem;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .ci-show-chip--severity-emergency,
    .ci-show-chip--severity-critical { background: rgba(0, 0, 0, 0.22); }
    .ci-campaign-banner {
        background: linear-gradient(90deg, #ecfdf5 0%, #f0fdf4 100%);
        border: 1px solid rgba(22, 163, 74, 0.22);
        border-radius: 14px;
        padding: 1rem 1.15rem;
        margin-bottom: 1.5rem;
    }
    .ci-status-stepper {
        display: flex;
        gap: 0.5rem;
        overflow-x: auto;
        padding-bottom: 0.25rem;
        margin-bottom: 1rem;
    }
    .ci-status-step {
        flex: 0 0 auto;
        min-width: 118px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.65rem 0.75rem;
        background: #fff;
        text-align: center;
    }
    .ci-status-step.is-complete {
        border-color: rgba(22, 163, 74, 0.35);
        background: #f0fdf4;
    }
    .ci-status-step.is-current {
        border-color: #16a34a;
        background: #16a34a;
        color: #fff;
        box-shadow: 0 8px 20px rgba(22, 163, 74, 0.18);
    }
    .ci-status-step__label {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .ci-capability-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .ci-capability-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
    }
    .ci-capability-pill.is-on {
        border-color: rgba(22, 163, 74, 0.35);
        background: #ecfdf5;
        color: #166534;
    }
    .ci-capability-pill.is-off {
        opacity: 0.55;
    }
    .ci-hub-link-card {
        border: 1px dashed rgba(185, 28, 28, 0.35);
        border-radius: 14px;
        padding: 1rem 1.15rem;
        background: #fffafb;
        margin-bottom: 1.5rem;
    }
    .community-issues-community-panel {
        background: linear-gradient(180deg, #fff7f7 0%, #ffffff 100%);
        border-color: rgba(185, 28, 28, 0.14);
    }
    .community-issues-community-panel .report-community-panel__kicker {
        color: #b91c1c;
    }

    .community-issues-evidence-rail__gallery {
        display: grid;
        gap: 0.5rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .community-issues-evidence-rail__thumb {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: block;
        overflow: hidden;
    }

    .community-issues-evidence-rail__thumb img {
        display: block;
        height: 72px;
        object-fit: cover;
        width: 100%;
    }

    .community-issues-community-panel--rail {
        background: linear-gradient(180deg, #fff7f7 0%, #ffffff 100%);
        border: 1px solid rgba(185, 28, 28, 0.12);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(15, 47, 85, 0.05);
        margin: 0 0 0.85rem;
        padding: 1rem 1rem 1.1rem;
    }

    .community-issues-community-panel__rail-head {
        display: block;
        margin-bottom: 1rem;
    }

    .community-issues-community-panel--rail .report-community-panel__kicker {
        display: block;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        line-height: 1.35;
        margin: 0 0 0.45rem;
    }

    .community-issues-community-panel--rail .community-detail-card__title {
        color: #0f2f55;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.3;
        margin-bottom: 0.35rem;
    }

    .community-issues-community-panel--rail .community-issues-community-panel__rail-head p {
        font-size: 0.82rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    .community-issues-community-panel__rail-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .community-issues-community-panel__rail-stats .report-community-panel__stat {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        color: #475569;
        font-size: 0.74rem;
        padding: 0.3rem 0.65rem;
    }

    .community-issues-community-panel--rail .report-community-panel__grid,
    .community-issues-community-panel--rail .report-community-panel__grid--rail {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        grid-template-columns: none;
    }

    .community-issues-community-panel--rail .report-community-action-card {
        align-items: flex-start;
        flex-direction: row;
        gap: 0.75rem;
        height: auto;
        min-height: 0;
        padding: 0.85rem;
    }

    .community-issues-community-panel--rail .report-community-action-card__icon {
        flex-shrink: 0;
        height: 40px;
        width: 40px;
    }

    .community-issues-community-panel--rail .report-community-action-card__body {
        min-width: 0;
    }

    .community-issues-community-panel--rail .report-community-action-card h5 {
        font-size: 0.88rem;
        line-height: 1.3;
        margin-bottom: 0.25rem;
    }

    .community-issues-community-panel--rail .report-community-action-card p {
        font-size: 0.78rem;
        line-height: 1.4;
        margin-bottom: 0.65rem;
    }

    .community-issues-community-panel--rail .report-community-action-card .btn {
        font-size: 0.78rem;
        white-space: normal;
    }

    .ci-show-overview__chips--sidebar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .ci-show-overview__chips--sidebar .ci-show-chip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        color: #0f172a;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 0.3rem 0.65rem;
    }

    .ci-show-overview__chips--sidebar .ci-show-chip--severity-emergency,
    .ci-show-overview__chips--sidebar .ci-show-chip--severity-critical {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

        flex-direction: column;
        overflow-x: visible;
        margin-bottom: 0.85rem;
    }

    .ci-status-stepper--sidebar .ci-status-step {
        min-width: 0;
        text-align: left;
    }

    .community-issues-sidebar-timeline__label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.45rem;
        text-transform: uppercase;
    }

    .community-issues-sidebar-timeline__item {
        align-items: flex-start;
        color: #334155;
        display: flex;
        font-size: 0.84rem;
        gap: 0.45rem;
        margin-bottom: 0.45rem;
    }

    .community-issues-sidebar-timeline__item i {
        color: #16a34a;
        margin-top: 0.2rem;
    }

    .ci-capability-grid--sidebar {
        gap: 0.4rem;
    }

    .ci-capability-grid--sidebar .ci-capability-pill {
        font-size: 0.74rem;
        padding: 0.35rem 0.55rem;
    }


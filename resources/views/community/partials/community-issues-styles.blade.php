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


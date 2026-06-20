<style>
    .cc-hero-strip {
        background: linear-gradient(135deg, #fff7ed 0%, #fefce8 45%, #f0fdf4 100%);
        border: 1px solid rgba(234, 88, 12, 0.15);
        border-radius: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        padding: 1rem 1.25rem;
    }
    .cc-hero-strip__item {
        min-width: 120px;
    }
    .cc-hero-strip__label {
        color: #64748b;
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }
    .cc-hero-strip__value {
        color: #1e293b;
        font-weight: 600;
    }
    .cc-section-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .cc-section-panel__header {
        align-items: center;
        display: flex;
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .cc-section-panel__header i {
        color: #ea580c;
        font-size: 1.1rem;
    }
    .cc-section-panel__header h4 {
        font-size: 1.15rem;
        margin-bottom: 0;
    }
    .cc-child-spotlight {
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-left: 4px solid #f59e0b;
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
    }
    .cc-child-spotlight__name {
        color: #92400e;
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }
    .cc-pill {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 999px;
        color: #334155;
        display: inline-block;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.35rem 0.75rem;
    }
    .cc-pill--theme {
        background: #ecfdf5;
        border-color: rgba(16, 185, 129, 0.25);
        color: #047857;
    }
    .cc-pill--talent {
        background: #eff6ff;
        border-color: rgba(59, 130, 246, 0.25);
        color: #1d4ed8;
    }
    .cc-art-frame {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        overflow: hidden;
        padding: 0.75rem;
        text-align: center;
    }
    .cc-art-frame img {
        border-radius: 0.75rem;
        max-height: 560px;
        object-fit: contain;
        width: 100%;
    }
    .cc-gallery-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    .cc-gallery-grid__item {
        aspect-ratio: 1;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .cc-gallery-grid__item img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }
    .cc-quiz-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.85rem;
        padding: 1rem 1.15rem;
    }
    .cc-quiz-option {
        align-items: center;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.1);
        border-radius: 0.65rem;
        cursor: pointer;
        display: flex;
        gap: 0.65rem;
        margin-bottom: 0.5rem;
        padding: 0.65rem 0.85rem;
        transition: border-color 0.15s ease, background 0.15s ease;
    }
    .cc-quiz-option:hover {
        border-color: rgba(234, 88, 12, 0.35);
    }
    .cc-quiz-option.is-correct {
        background: #ecfdf5;
        border-color: #10b981;
    }
    .cc-quiz-option.is-incorrect {
        background: #fef2f2;
        border-color: #f87171;
    }
    .cc-certificate-card {
        align-items: center;
        background: #fffbeb;
        border: 1px dashed rgba(245, 158, 11, 0.45);
        border-radius: 0.85rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        padding: 1rem 1.15rem;
    }
    .cc-meta-grid .cc-meta-item {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 0.75rem;
        height: 100%;
        padding: 0.85rem 1rem;
    }
    .cc-meta-item__label {
        color: #64748b;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    .cc-reaction-preview .badge {
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.45rem 0.7rem;
    }
</style>

<style>
    .story-moral-takeaway {
        border-left: 4px solid #ffc107;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }
    .story-moral-takeaway__quote {
        font-size: 1.1rem;
        font-style: italic;
        color: #495057;
        padding: 1rem 1.25rem;
        border-left: 3px solid rgba(255, 193, 7, 0.65);
        background: rgba(255, 255, 255, 0.75);
        border-radius: 0.5rem;
    }
    .story-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0.75rem;
    }
    .story-gallery-grid__item {
        display: block;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #f8fafc;
    }
    .story-gallery-grid__item img {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
    }
    .story-audio-player {
        border-left: 4px solid #0dcaf0;
        background: linear-gradient(180deg, #f8fdff 0%, #ffffff 100%);
    }
    .story-rating-panel {
        border-left: 4px solid #ffc107;
        background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }
    .story-rating-summary__score {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }
    .story-rating-summary__stars {
        color: #ffc107;
        letter-spacing: 0.08rem;
    }
    .story-meta-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }
    .story-meta-pill {
        font-weight: 500;
        padding: 0.35rem 0.65rem;
    }
    .story-meta-pill--audience {
        border-color: rgba(102, 16, 242, 0.25) !important;
        background: #f8f5ff !important;
    }
    .story-meta-pill--theme {
        border-color: rgba(13, 110, 253, 0.25) !important;
        background: #f8fbff !important;
    }
    .community-story-badge--most-read { border: 1px solid rgba(25, 135, 84, 0.25); }
    .community-story-badge--most-shared { border: 1px solid rgba(13, 202, 240, 0.25); }
    .community-story-badge--most-inspiring { border: 1px solid rgba(255, 193, 7, 0.35); }
    .community-story-badge--community-favorite { border: 1px solid rgba(102, 16, 242, 0.25); }
    .story-achievements-panel {
        border-left: 4px solid #198754;
        background: linear-gradient(180deg, #f8fff9 0%, #ffffff 100%);
    }
    .story-achievement-item {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.5rem;
        padding: 0.65rem 0.75rem;
        background: #fff;
        height: 100%;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .story-achievement-item.is-earned {
        border-color: rgba(25, 135, 84, 0.35);
        background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);
        color: #146c43;
    }
    .story-achievement-item.is-pending {
        color: #64748b;
        background: #f8fafc;
    }
    .story-achievement-item__icon {
        display: block;
        font-size: 1rem;
        margin-bottom: 0.25rem;
    }
    .story-rating-star {
        min-width: 2.25rem;
    }
    .story-rating-input .btn-warning,
    .story-rating-input .btn-outline-warning {
        border-radius: 999px;
    }
    .story-portal-card.about-box,
    .story-portal-card.chart-card {
        border-left: 4px solid #6610f2;
    }
</style>

<style>
    .about-page--news-detail {
        --news-green: #2e7d32;
        --news-green-soft: #e8f5e9;
        --news-ink: #10253f;
        --news-muted: #5f7083;
        --news-line: rgba(16, 37, 63, 0.08);
        --news-paper: #ffffff;
        --news-bg: #eef2f6;
        background:
            radial-gradient(ellipse 70% 40% at 100% 0%, rgba(42, 107, 79, 0.09), transparent 55%),
            radial-gradient(ellipse 55% 35% at 0% 15%, rgba(23, 105, 165, 0.1), transparent 50%),
            linear-gradient(180deg, #e4ebf3 0%, var(--news-bg) 38%, #e9eef4 100%);
    }

    .about-page--news-detail .community-article-hero {
        margin-bottom: 0;
    }

    .news-detail-header {
        background: var(--news-paper);
        border-bottom: 1px solid var(--news-line);
        padding: 1.25rem 0 1.75rem;
    }

    .news-detail-header__container {
        margin: 0 auto;
        max-width: min(1320px, calc(100vw - 32px));
    }

    .news-detail-breadcrumb {
        color: var(--news-muted);
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .news-detail-breadcrumb a {
        color: var(--news-muted);
        text-decoration: none;
    }

    .news-detail-breadcrumb a:hover {
        color: var(--news-green);
    }

    .news-detail-breadcrumb span {
        margin: 0 0.35rem;
    }

    .news-detail-badge {
        background: var(--news-green-soft);
        border-radius: 4px;
        color: var(--news-green);
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.75rem;
        padding: 0.25rem 0.6rem;
        text-transform: uppercase;
    }

    .news-detail-badge--breaking {
        background: #fdecea;
        color: #c62828;
    }

    .news-detail-title {
        color: var(--news-ink);
        font-family: Fraunces, Georgia, "Times New Roman", serif;
        font-size: clamp(1.75rem, 3.5vw, 2.5rem);
        font-weight: 700;
        line-height: 1.25;
        margin-bottom: 0.85rem;
    }

    .news-detail-deck {
        color: #334155;
        font-family: "Source Sans 3", "Segoe UI", sans-serif;
        font-size: clamp(1.05rem, 2vw, 1.2rem);
        line-height: 1.6;
        margin-bottom: 1.25rem;
        max-width: 900px;
    }

    .news-detail-byline {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
    }

    .news-detail-author {
        align-items: center;
        display: flex;
        gap: 0.65rem;
    }

    .news-detail-author__avatar,
    .news-detail-author__initials {
        align-items: center;
        background: var(--news-green-soft);
        border-radius: 50%;
        color: var(--news-green);
        display: inline-flex;
        flex-shrink: 0;
        font-size: 0.9rem;
        font-weight: 700;
        height: 42px;
        justify-content: center;
        overflow: hidden;
        width: 42px;
    }

    .news-detail-author__avatar img {
        height: 100%;
        object-fit: cover;
        width: 100%;
    }

    .news-detail-author__name {
        color: var(--news-ink);
        font-weight: 600;
        margin: 0;
    }

    .news-detail-author__name a {
        color: inherit;
        text-decoration: none;
    }

    .news-detail-author__name a:hover {
        color: var(--news-green);
    }

    .news-detail-meta {
        align-items: center;
        color: var(--news-muted);
        display: flex;
        flex-wrap: wrap;
        font-size: 0.88rem;
        gap: 0.5rem 1rem;
    }

    .news-detail-meta i {
        color: var(--news-green);
        margin-right: 0.25rem;
    }

    .news-detail-shell {
        padding: 1.5rem 0 3rem;
    }

    .news-detail-shell__container {
        margin: 0 auto;
        max-width: min(1320px, calc(100vw - 32px));
    }

    .news-detail-shell__grid {
        align-items: start;
        display: grid;
        gap: 1.75rem;
        grid-template-columns: minmax(0, 1fr) 320px;
    }

    .news-detail-card {
        background: var(--news-paper);
        border: 1px solid var(--news-line);
        border-radius: 12px;
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .news-detail-card__head {
        border-bottom: 1px solid var(--news-line);
        color: var(--news-ink);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        padding: 0.85rem 1.15rem;
        text-transform: uppercase;
    }

    .news-detail-card__body {
        padding: 1rem 1.15rem;
    }

    .news-detail-kv {
        display: grid;
        gap: 0.65rem;
    }

    .news-detail-kv__row {
        display: grid;
        gap: 0.15rem;
        grid-template-columns: 110px 1fr;
    }

    .news-detail-kv__label {
        color: var(--news-muted);
        font-size: 0.82rem;
    }

    .news-detail-kv__value {
        color: var(--news-ink);
        font-size: 0.88rem;
        font-weight: 600;
    }

    .news-detail-hero {
        display: none;
    }

    .news-detail-body {
        display: none;
    }

    .about-page--news-detail .community-article-shell {
        margin-bottom: 0;
    }

    .about-page--news-detail .news-detail-highlights {
        background: var(--news-green-soft);
        border-left: 4px solid var(--news-green);
        border-radius: 0 10px 10px 0;
        margin-bottom: 1.5rem;
        padding: 1.15rem 1.25rem;
    }

    .news-detail-highlights h4 {
        color: var(--news-green);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .news-detail-highlights ul {
        margin: 0;
        padding-left: 1.2rem;
    }

    .news-detail-highlights li {
        margin-bottom: 0.4rem;
    }

    .news-detail-quote {
        background: #f0faf2;
        border-left: 4px solid var(--news-green);
        border-radius: 0 10px 10px 0;
        margin: 1.5rem 0;
        padding: 1.15rem 1.25rem;
    }

    .news-detail-quote blockquote {
        font-size: 1.05rem;
        font-style: italic;
        margin: 0 0 0.5rem;
    }

    .news-detail-quote cite {
        color: var(--news-muted);
        font-size: 0.88rem;
        font-style: normal;
    }

    .news-detail-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .news-detail-tag {
        background: var(--news-green-soft);
        border: 1px solid #c8e6c9;
        border-radius: 999px;
        color: var(--news-green);
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.3rem 0.75rem;
        text-decoration: none;
    }

    .news-detail-share {
        align-items: center;
        border-top: 1px solid var(--news-line);
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        margin-bottom: 1.5rem;
        padding-top: 1rem;
    }

    .news-detail-share__label {
        color: var(--news-muted);
        font-size: 0.88rem;
        font-weight: 600;
        margin-right: 0.25rem;
    }

    .news-detail-share__btn {
        align-items: center;
        background: #f8fafc;
        border: 1px solid var(--news-line);
        border-radius: 8px;
        color: var(--news-ink);
        display: inline-flex;
        font-size: 0.85rem;
        gap: 0.35rem;
        padding: 0.4rem 0.75rem;
        text-decoration: none;
        transition: background 0.15s ease;
    }

    .news-detail-share__btn:hover {
        background: var(--news-green-soft);
        color: var(--news-green);
    }

    .news-detail-related-item {
        align-items: start;
        border-bottom: 1px solid var(--news-line);
        display: flex;
        gap: 0.75rem;
        padding: 0.85rem 0;
        text-decoration: none;
    }

    .news-detail-related-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .news-detail-related-item__thumb {
        border-radius: 6px;
        flex-shrink: 0;
        height: 56px;
        object-fit: cover;
        width: 72px;
    }

    .news-detail-related-item__title {
        color: var(--news-ink);
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0 0 0.25rem;
    }

    .news-detail-related-item__meta {
        color: var(--news-muted);
        font-size: 0.78rem;
    }

    .news-detail-trending-item {
        align-items: start;
        border-bottom: 1px solid var(--news-line);
        display: flex;
        gap: 0.65rem;
        padding: 0.75rem 0;
        text-decoration: none;
    }

    .news-detail-trending-item:last-child {
        border-bottom: 0;
    }

    .news-detail-trending-item__num {
        align-items: center;
        background: var(--news-green-soft);
        border-radius: 6px;
        color: var(--news-green);
        display: inline-flex;
        flex-shrink: 0;
        font-size: 0.82rem;
        font-weight: 700;
        height: 28px;
        justify-content: center;
        width: 28px;
    }

    .news-detail-trending-item__title {
        color: var(--news-ink);
        font-size: 0.86rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0;
    }

    .news-detail-trending-item__views {
        color: var(--news-muted);
        font-size: 0.76rem;
        margin-top: 0.2rem;
    }

    .news-detail-author-card {
        text-align: center;
    }

    .news-detail-author-card__avatar {
        border-radius: 50%;
        height: 72px;
        margin: 0 auto 0.75rem;
        object-fit: cover;
        width: 72px;
    }

    .news-detail-author-card__initials {
        align-items: center;
        background: var(--news-green-soft);
        border-radius: 50%;
        color: var(--news-green);
        display: inline-flex;
        font-size: 1.5rem;
        font-weight: 700;
        height: 72px;
        justify-content: center;
        margin: 0 auto 0.75rem;
        width: 72px;
    }

    .news-detail-author-card__name {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .news-detail-author-card__stats {
        color: var(--news-muted);
        display: flex;
        font-size: 0.82rem;
        gap: 1rem;
        justify-content: center;
        margin: 0.75rem 0 1rem;
    }

    .news-detail-socials {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: center;
    }

    .news-detail-socials a {
        align-items: center;
        background: #1877f2;
        border-radius: 50%;
        color: #fff;
        display: inline-flex;
        height: 36px;
        justify-content: center;
        text-decoration: none;
        width: 36px;
    }

    .news-detail-socials a.news-detail-socials__x { background: #000; }
    .news-detail-socials a.news-detail-socials__ig { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
    .news-detail-socials a.news-detail-socials__yt { background: #ff0000; }
    .news-detail-socials a.news-detail-socials__in { background: #0a66c2; }

    .news-detail-also-like {
        margin-top: 2rem;
    }

    .news-detail-also-like h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .news-detail-also-like__grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }

    .news-detail-also-like__card {
        background: var(--news-paper);
        border: 1px solid var(--news-line);
        border-radius: 10px;
        overflow: hidden;
        text-decoration: none;
        transition: box-shadow 0.2s ease;
    }

    .news-detail-also-like__card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .news-detail-also-like__card img {
        display: block;
        height: 140px;
        object-fit: cover;
        width: 100%;
    }

    .news-detail-also-like__card-body {
        padding: 0.85rem;
    }

    .news-detail-also-like__category {
        color: var(--news-green);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .news-detail-also-like__title {
        color: var(--news-ink);
        font-size: 0.9rem;
        font-weight: 600;
        line-height: 1.4;
        margin: 0.35rem 0 0;
    }

    .about-page--news-detail .about-inner {
        background: transparent;
        max-width: none;
        padding: 0 clamp(16px, 2vw, 32px) 40px;
        top: -20px;
    }

    .about-page--news-detail .about-inner .sec {
        padding: 0;
    }

    .about-page--news-detail .community-engagement-panel {
        background: var(--news-paper);
        border: 1px solid var(--news-line);
        border-radius: 12px;
    }

    .about-page--news-detail .about-box {
        background: var(--news-paper);
        border: 1px solid var(--news-line);
        border-radius: 12px;
    }

    @@media (max-width: 991.98px) {
        .news-detail-shell__grid {
            grid-template-columns: 1fr;
        }
    }
</style>

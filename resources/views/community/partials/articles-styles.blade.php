<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,550;9..144,700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .about-page--articles {
        --article-ink: #10253f;
        --article-muted: #5f7083;
        --article-accent: #1769a5;
        --article-accent-soft: rgba(23, 105, 165, 0.11);
        --article-earth: #2a6b4f;
        --article-surface: #edf2f7;
        --article-paper: #ffffff;
        --article-line: rgba(16, 37, 63, 0.08);
        background:
            radial-gradient(ellipse 70% 40% at 100% 0%, rgba(42, 107, 79, 0.09), transparent 55%),
            radial-gradient(ellipse 55% 35% at 0% 15%, rgba(23, 105, 165, 0.1), transparent 50%),
            linear-gradient(180deg, #e4ebf3 0%, var(--article-surface) 38%, #e9eef4 100%);
        font-family: "Source Sans 3", "Segoe UI", sans-serif;
    }

    .about-page--articles .about-banner.community-article-hero {
        background: linear-gradient(150deg, #0a243d 0%, #12486a 48%, #1a6148 100%);
        overflow: hidden;
        padding: clamp(52px, 7.5vw, 96px) 20px clamp(56px, 7vw, 88px);
        position: relative;
        text-align: left;
    }

    .about-page--articles .community-article-hero.has-cover::before {
        background:
            linear-gradient(180deg, rgba(6, 22, 38, 0.78) 0%, rgba(6, 22, 38, 0.5) 42%, rgba(6, 22, 38, 0.88) 100%),
            var(--article-cover) center / cover no-repeat;
        content: "";
        inset: 0;
        position: absolute;
        transform: scale(1.03);
        z-index: 0;
    }

    .about-page--articles .community-article-hero::after {
        animation: articleHeroSheen 1.15s ease both;
        background:
            radial-gradient(circle at 14% 18%, rgba(255, 255, 255, 0.18), transparent 40%),
            linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.04) 50%, transparent 100%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
        z-index: 1;
    }

    .about-page--articles .community-article-hero__inner {
        margin: 0 auto;
        max-width: min(860px, calc(100vw - 48px));
        position: relative;
        width: 100%;
        z-index: 2;
    }

    .about-page--articles .community-post-back-wrap {
        margin: 0 0 1.35rem;
        max-width: none;
        text-align: left;
    }

    .about-page--articles .community-post-back {
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        margin-bottom: 0;
        padding: 0.42rem 0.95rem;
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }

    .about-page--articles .community-post-back:hover {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.32);
        text-decoration: none;
        transform: translateX(-2px);
    }

    .about-page--articles .community-article-hero__kicker {
        animation: articleRise 0.7s ease both;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        margin-bottom: 0.9rem;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-hero__kicker span {
        color: #9ad4ff;
    }

    .about-page--articles .community-article-hero h1 {
        animation: articleRise 0.75s ease 0.05s both;
        font-family: Fraunces, Georgia, serif;
        font-optical-sizing: auto;
        font-size: clamp(2.05rem, 5vw, 3.5rem);
        font-weight: 700;
        letter-spacing: -0.025em;
        line-height: 1.12;
        margin-bottom: 1.05rem;
        max-width: 16ch;
        text-wrap: balance;
    }

    .about-page--articles .community-article-hero__deck {
        animation: articleRise 0.75s ease 0.1s both;
        color: rgba(255, 255, 255, 0.9);
        font-size: clamp(1.05rem, 2vw, 1.22rem);
        line-height: 1.55;
        margin: 0 0 1.4rem;
        max-width: 38rem;
    }

    .about-page--articles .community-article-hero__byline {
        animation: articleRise 0.75s ease 0.14s both;
        align-items: center;
        color: rgba(255, 255, 255, 0.86);
        display: flex;
        flex-wrap: wrap;
        font-size: 0.95rem;
        gap: 0.35rem 0.65rem;
        margin: 0 0 1.4rem;
    }

    .about-page--articles .community-article-hero__byline a {
        color: #fff;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 0.15em;
    }

    .about-page--articles .community-article-hero__byline-sep {
        color: rgba(255, 255, 255, 0.4);
    }

    .about-page--articles .community-article-hero__actions {
        animation: articleRise 0.75s ease 0.18s both;
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        justify-content: flex-start !important;
        width: 100%;
    }

    .about-page--articles .community-post-banner-tags {
        display: none;
    }

    .about-page--articles .about-inner {
        gap: 0;
        max-width: min(1080px, calc(100vw - 28px));
        padding: 0 14px 56px;
        position: relative;
        top: -40px;
        z-index: 3;
    }

    .about-page--articles .about-inner > .sec {
        background: transparent;
        border: 0;
        box-shadow: none;
        padding: 0;
    }

    .about-page--articles .community-article-shell {
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 1.5rem;
        box-shadow:
            0 1px 2px rgba(16, 37, 63, 0.04),
            0 22px 56px rgba(16, 37, 63, 0.1);
        overflow: hidden;
    }

    .about-page--articles .community-article-meta {
        background: linear-gradient(180deg, #f6f9fc 0%, #ffffff 100%);
        border-bottom: 1px solid var(--article-line);
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        padding: 1rem 1.25rem;
    }

    .about-page--articles .community-article-meta__item {
        background: #fff;
        border: 1px solid var(--article-line);
        border-radius: 999px;
        display: inline-flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
        padding: 0.55rem 0.95rem;
    }

    .about-page--articles .community-article-meta__label {
        color: var(--article-muted);
        display: block;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-meta__value {
        color: var(--article-ink);
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.25;
    }

    .about-page--articles .community-article-cover {
        display: block;
        max-height: 480px;
        object-fit: cover;
        width: 100%;
    }

    .about-page--articles .community-article-cover-wrap {
        border-bottom: 1px solid var(--article-line);
        margin: 0;
        overflow: hidden;
    }

    .about-page--articles .community-featured-gallery--article {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
    }

    .about-page--articles .community-article-reading {
        margin: 0 auto;
        max-width: 44rem;
        padding: clamp(1.6rem, 4vw, 2.85rem) clamp(1.2rem, 4vw, 2.4rem) clamp(2.1rem, 5vw, 3.1rem);
    }

    .about-page--articles .community-article-reading__lead {
        border-left: 3px solid var(--article-accent);
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: clamp(1.15rem, 2.2vw, 1.32rem);
        font-weight: 550;
        line-height: 1.55;
        margin: 0 0 1.75rem;
        padding-left: 1rem;
    }

    .about-page--articles .community-post-body--article {
        color: #1e293b;
        font-size: 1.1rem;
        line-height: 1.88;
    }

    .about-page--articles .community-post-body--article:not([dir="rtl"]) > p:first-of-type::first-letter {
        color: var(--article-ink);
        float: left;
        font-family: Fraunces, Georgia, serif;
        font-size: 3.35rem;
        font-weight: 700;
        line-height: 0.85;
        margin: 0.12em 0.12em 0 0;
        padding-right: 0.05em;
    }

    .about-page--articles .community-post-body--article > p {
        margin-bottom: 1.2rem;
    }

    .about-page--articles .community-post-body--article > h2,
    .about-page--articles .community-post-body--article > h3,
    .about-page--articles .community-post-body--article > h4 {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.25;
        margin: 2.15rem 0 0.9rem;
    }

    .about-page--articles .community-post-body--article > h2 {
        font-size: 1.7rem;
    }

    .about-page--articles .community-post-body--article > h3 {
        font-size: 1.38rem;
    }

    .about-page--articles .community-post-body--article > blockquote {
        background: linear-gradient(135deg, rgba(23, 105, 165, 0.06), rgba(42, 107, 79, 0.07));
        border: 0;
        border-left: 4px solid var(--article-earth);
        border-radius: 0 1rem 1rem 0;
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: 1.14rem;
        font-style: italic;
        margin: 1.85rem 0;
        padding: 1.15rem 1.4rem;
    }

    .about-page--articles .community-post-body--article a {
        color: var(--article-accent);
        font-weight: 600;
        text-decoration-thickness: 1px;
        text-underline-offset: 0.18em;
    }

    .about-page--articles .community-article-score-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.3rem;
    }

    .about-page--articles .community-article-score-row .badge {
        border-radius: 999px;
        font-size: 0.76rem;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
    }

    .about-page--articles .community-article-tags {
        border-top: 1px solid var(--article-line);
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 2.1rem;
        padding-top: 1.4rem;
    }

    .about-page--articles .community-article-tag {
        background: var(--article-accent-soft);
        border-radius: 999px;
        color: #0f4c75;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.35rem 0.8rem;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .about-page--articles .community-article-tag:hover {
        background: rgba(23, 105, 165, 0.2);
        color: #0a3a5c;
    }

    .about-page--articles .community-article-author-card {
        align-items: center;
        background:
            radial-gradient(circle at 0% 0%, rgba(23, 105, 165, 0.08), transparent 45%),
            linear-gradient(135deg, #f3f8fc 0%, #f3faf6 100%);
        border: 1px solid var(--article-line);
        border-radius: 1.2rem;
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding: 1.2rem 1.3rem;
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    .about-page--articles .community-article-author-card:hover {
        box-shadow: 0 10px 28px rgba(16, 37, 63, 0.08);
        transform: translateY(-1px);
    }

    .about-page--articles .community-article-author-card__avatar,
    .about-page--articles .community-article-author-card__initials {
        align-items: center;
        background: linear-gradient(135deg, #dbeafe 0%, #d1fae5 100%);
        border-radius: 50%;
        color: #0f4c75;
        display: inline-flex;
        flex-shrink: 0;
        font-family: Fraunces, Georgia, serif;
        font-size: 1.25rem;
        font-weight: 700;
        height: 68px;
        justify-content: center;
        object-fit: cover;
        overflow: hidden;
        width: 68px;
    }

    .about-page--articles .community-article-author-card__label {
        color: var(--article-muted);
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-author-card__name {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.18rem;
        font-weight: 700;
        margin: 0;
    }

    .about-page--articles .community-article-author-card__name a {
        color: inherit;
        text-decoration: none;
    }

    .about-page--articles .community-article-author-card__name a:hover {
        color: var(--article-accent);
    }

    .about-page--articles .community-article-author-card__bio {
        color: var(--article-muted);
        font-size: 0.9rem;
        margin: 0.3rem 0 0;
    }

    .about-page--articles .community-engagement-panel,
    .about-page--articles #comments-discussion.about-box {
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 1.25rem;
        box-shadow: 0 10px 30px rgba(16, 37, 63, 0.06);
        margin-top: 1.25rem !important;
        padding: 1.25rem 1.35rem !important;
    }

    .about-page--articles .community-engagement-panel__title,
    .about-page--articles #comments-discussion > h4 {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .about-page--articles .community-engagement-stats {
        gap: 0.65rem;
    }

    .about-page--articles .community-engagement-stat {
        background: linear-gradient(180deg, #f7fafc 0%, #ffffff 100%);
        border: 1px solid var(--article-line);
        border-radius: 0.95rem;
        padding: 0.85rem 0.95rem;
    }

    .about-page--articles .community-detail-card {
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 1.2rem;
        box-shadow: 0 10px 28px rgba(16, 37, 63, 0.06);
    }

    .about-page--articles .community-detail-card__title {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.12rem;
    }

    .about-page--articles .community-detail-item,
    .about-page--articles .community-detail-list__row {
        background: linear-gradient(180deg, #f8fbfd 0%, #ffffff 100%);
        border-color: var(--article-line);
    }

    .about-page--articles .community-detail-item__value,
    .about-page--articles .community-detail-list__row dd {
        color: #1e293b;
        font-weight: 500;
    }

    @@keyframes articleRise {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @@keyframes articleHeroSheen {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @@media (max-width: 575.98px) {
        .about-page--articles .about-inner {
            top: -24px;
        }

        .about-page--articles .community-article-hero h1 {
            max-width: none;
        }

        .about-page--articles .community-article-meta {
            padding: 0.85rem 0.95rem;
        }

        .about-page--articles .community-article-meta__item {
            flex: 1 1 calc(50% - 0.55rem);
        }

        .about-page--articles .community-post-body--article:not([dir="rtl"]) > p:first-of-type::first-letter {
            font-size: 2.75rem;
        }
    }

    @@media (prefers-reduced-motion: reduce) {
        .about-page--articles .community-article-hero::after,
        .about-page--articles .community-article-hero__kicker,
        .about-page--articles .community-article-hero h1,
        .about-page--articles .community-article-hero__deck,
        .about-page--articles .community-article-hero__byline,
        .about-page--articles .community-article-hero__actions,
        .about-page--articles .community-article-author-card {
            animation: none;
            transition: none;
        }
    }
</style>

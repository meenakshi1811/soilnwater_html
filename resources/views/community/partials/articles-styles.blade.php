<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,550;9..144,700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .about-page--articles {
        --article-ink: #0f2744;
        --article-muted: #5b6b7c;
        --article-accent: #1b6ca8;
        --article-accent-soft: rgba(27, 108, 168, 0.12);
        --article-earth: #2d6a4f;
        --article-surface: #f3f6f9;
        --article-paper: #ffffff;
        background:
            radial-gradient(ellipse 80% 50% at 100% -10%, rgba(45, 106, 79, 0.08), transparent 55%),
            radial-gradient(ellipse 70% 45% at -5% 20%, rgba(27, 108, 168, 0.1), transparent 50%),
            linear-gradient(180deg, #e8eef4 0%, var(--article-surface) 40%, #eef2f6 100%);
        font-family: "Source Sans 3", "Segoe UI", sans-serif;
    }

    .about-page--articles .about-banner.community-article-hero {
        background: linear-gradient(145deg, #0c2a45 0%, #134e6f 42%, #1a6b4a 100%);
        overflow: hidden;
        padding: clamp(48px, 7vw, 88px) 20px clamp(40px, 5vw, 64px);
        position: relative;
        text-align: left;
    }

    .about-page--articles .community-article-hero.has-cover::before {
        background:
            linear-gradient(180deg, rgba(8, 28, 48, 0.72) 0%, rgba(8, 28, 48, 0.55) 45%, rgba(8, 28, 48, 0.82) 100%),
            var(--article-cover) center / cover no-repeat;
        content: "";
        inset: 0;
        position: absolute;
        transform: scale(1.02);
        z-index: 0;
    }

    .about-page--articles .community-article-hero::after {
        animation: articleHeroSheen 1.1s ease both;
        background: radial-gradient(circle at 18% 20%, rgba(255, 255, 255, 0.16), transparent 42%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
        z-index: 1;
    }

    .about-page--articles .community-article-hero__inner {
        margin: 0 auto;
        max-width: min(920px, calc(100vw - 48px));
        position: relative;
        z-index: 2;
        width: 100%;
    }

    .about-page--articles .community-post-back-wrap {
        margin: 0 0 1.25rem;
        max-width: none;
        text-align: left;
    }

    .about-page--articles .community-post-back {
        backdrop-filter: blur(6px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        margin-bottom: 0;
        padding: 0.4rem 0.9rem;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .about-page--articles .community-post-back:hover {
        background: rgba(255, 255, 255, 0.18);
        text-decoration: none;
        transform: translateX(-2px);
    }

    .about-page--articles .community-article-hero__kicker {
        animation: articleRise 0.7s ease both;
        color: rgba(255, 255, 255, 0.78);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        margin-bottom: 0.85rem;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-hero__kicker span {
        color: #9ad4ff;
    }

    .about-page--articles .community-article-hero h1 {
        animation: articleRise 0.75s ease 0.06s both;
        font-family: Fraunces, Georgia, serif;
        font-optical-sizing: auto;
        font-size: clamp(2rem, 4.6vw, 3.35rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.15;
        margin-bottom: 1rem;
        max-width: 18ch;
    }

    .about-page--articles .community-article-hero__deck {
        animation: articleRise 0.75s ease 0.12s both;
        color: rgba(255, 255, 255, 0.88);
        font-size: clamp(1.05rem, 2vw, 1.2rem);
        line-height: 1.55;
        margin: 0 0 1.35rem;
        max-width: 42rem;
    }

    .about-page--articles .community-article-hero__byline {
        animation: articleRise 0.75s ease 0.16s both;
        align-items: center;
        color: rgba(255, 255, 255, 0.86);
        display: flex;
        flex-wrap: wrap;
        font-size: 0.95rem;
        gap: 0.35rem 0.65rem;
        margin: 0 0 1.35rem;
    }

    .about-page--articles .community-article-hero__byline a {
        color: #fff;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 0.15em;
    }

    .about-page--articles .community-article-hero__byline-sep {
        color: rgba(255, 255, 255, 0.45);
    }

    .about-page--articles .community-article-hero__actions {
        animation: articleRise 0.75s ease 0.2s both;
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
        max-width: min(1120px, calc(100vw - 32px));
        padding: 0 16px 48px;
        position: relative;
        top: -28px;
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
        border: 1px solid rgba(15, 47, 85, 0.08);
        border-radius: 1.35rem;
        box-shadow:
            0 1px 2px rgba(15, 47, 85, 0.04),
            0 18px 48px rgba(15, 47, 85, 0.08);
        overflow: hidden;
    }

    .about-page--articles .community-article-meta {
        align-items: stretch;
        background: linear-gradient(180deg, #f7fafc 0%, #ffffff 100%);
        border-bottom: 1px solid rgba(15, 47, 85, 0.07);
        display: grid;
        gap: 0;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .about-page--articles .community-article-meta__item {
        border-right: 1px solid rgba(15, 47, 85, 0.07);
        min-width: 0;
        padding: 1.1rem 1.25rem;
    }

    .about-page--articles .community-article-meta__item:last-child {
        border-right: 0;
    }

    .about-page--articles .community-article-meta__label {
        color: var(--article-muted);
        display: block;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        margin-bottom: 0.3rem;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-meta__value {
        color: var(--article-ink);
        font-size: 0.98rem;
        font-weight: 600;
        line-height: 1.35;
    }

    .about-page--articles .community-article-cover {
        display: block;
        max-height: 460px;
        object-fit: cover;
        width: 100%;
    }

    .about-page--articles .community-article-cover-wrap {
        border-bottom: 1px solid rgba(15, 47, 85, 0.06);
        margin: 0;
        overflow: hidden;
    }

    .about-page--articles .community-article-reading {
        margin: 0 auto;
        max-width: 46rem;
        padding: clamp(1.5rem, 4vw, 2.75rem) clamp(1.25rem, 4vw, 2.5rem) clamp(2rem, 5vw, 3rem);
    }

    .about-page--articles .community-article-reading__lead {
        border-left: 3px solid var(--article-accent);
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: clamp(1.15rem, 2.2vw, 1.35rem);
        font-weight: 550;
        line-height: 1.55;
        margin: 0 0 1.75rem;
        padding-left: 1rem;
    }

    .about-page--articles .community-post-body--article {
        color: #1e293b;
        font-size: 1.08rem;
        line-height: 1.85;
    }

    .about-page--articles .community-post-body--article > p {
        margin-bottom: 1.15rem;
    }

    .about-page--articles .community-post-body--article > h2,
    .about-page--articles .community-post-body--article > h3,
    .about-page--articles .community-post-body--article > h4 {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.25;
        margin: 2rem 0 0.85rem;
    }

    .about-page--articles .community-post-body--article > h2 {
        font-size: 1.65rem;
    }

    .about-page--articles .community-post-body--article > h3 {
        font-size: 1.35rem;
    }

    .about-page--articles .community-post-body--article > blockquote {
        background: linear-gradient(135deg, rgba(27, 108, 168, 0.06), rgba(45, 106, 79, 0.06));
        border: 0;
        border-left: 4px solid var(--article-earth);
        border-radius: 0 0.85rem 0.85rem 0;
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: 1.12rem;
        font-style: italic;
        margin: 1.75rem 0;
        padding: 1.1rem 1.35rem;
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
        margin-bottom: 1.25rem;
    }

    .about-page--articles .community-article-score-row .badge {
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 0.4rem 0.75rem;
    }

    .about-page--articles .community-article-tags {
        border-top: 1px solid rgba(15, 47, 85, 0.08);
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 2rem;
        padding-top: 1.35rem;
    }

    .about-page--articles .community-article-tag {
        background: var(--article-accent-soft);
        border-radius: 999px;
        color: #0f4c75;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.35rem 0.8rem;
        text-decoration: none;
    }

    .about-page--articles .community-article-tag:hover {
        background: rgba(27, 108, 168, 0.2);
        color: #0a3a5c;
    }

    .about-page--articles .community-article-author-card {
        align-items: center;
        background: linear-gradient(135deg, #f0f7fb 0%, #f4faf6 100%);
        border: 1px solid rgba(15, 47, 85, 0.08);
        border-radius: 1.1rem;
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding: 1.15rem 1.25rem;
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
        height: 64px;
        justify-content: center;
        object-fit: cover;
        overflow: hidden;
        width: 64px;
    }

    .about-page--articles .community-article-author-card__label {
        color: var(--article-muted);
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }

    .about-page--articles .community-article-author-card__name {
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.15rem;
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
        margin: 0.25rem 0 0;
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

    @@media (max-width: 991.98px) {
        .about-page--articles .community-article-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .about-page--articles .community-article-meta__item:nth-child(2n) {
            border-right: 0;
        }

        .about-page--articles .community-article-meta__item:nth-child(-n+2) {
            border-bottom: 1px solid rgba(15, 47, 85, 0.07);
        }
    }

    @@media (max-width: 575.98px) {
        .about-page--articles .about-inner {
            top: -18px;
        }

        .about-page--articles .community-article-hero h1 {
            max-width: none;
        }

        .about-page--articles .community-article-meta {
            grid-template-columns: 1fr;
        }

        .about-page--articles .community-article-meta__item {
            border-bottom: 1px solid rgba(15, 47, 85, 0.07);
            border-right: 0;
        }

        .about-page--articles .community-article-meta__item:last-child {
            border-bottom: 0;
        }
    }

    @@media (prefers-reduced-motion: reduce) {
        .about-page--articles .community-article-hero::after,
        .about-page--articles .community-article-hero__kicker,
        .about-page--articles .community-article-hero h1,
        .about-page--articles .community-article-hero__deck,
        .about-page--articles .community-article-hero__byline,
        .about-page--articles .community-article-hero__actions {
            animation: none;
        }
    }
</style>

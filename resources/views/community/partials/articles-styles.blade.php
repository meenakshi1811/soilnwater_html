<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,550;9..144,700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .about-page--community-post, .about-page--articles {
        --article-ink: #10253f;
        --article-muted: #5f7083;
        --article-accent: #1769a5;
        --article-accent-soft: rgba(23, 105, 165, 0.11);
        --article-earth: #2a6b4f;
        --article-surface: #edf2f7;
        --article-paper: #ffffff;
        --article-line: rgba(16, 37, 63, 0.08);
        --article-text-scale: 1;
        --post-text-base: 1rem;
        background:
            radial-gradient(ellipse 70% 40% at 100% 0%, rgba(42, 107, 79, 0.09), transparent 55%),
            radial-gradient(ellipse 55% 35% at 0% 15%, rgba(23, 105, 165, 0.1), transparent 50%),
            linear-gradient(180deg, #e4ebf3 0%, var(--article-surface) 38%, #e9eef4 100%);
        font-family: "Source Sans 3", "Segoe UI", sans-serif;
        width: 100%;
    }

    .about-page--community-post .about-banner.community-article-hero, .about-page--articles .about-banner.community-article-hero{
        background: linear-gradient(150deg, #0a243d 0%, #12486a 48%, #1a6148 100%);
        overflow: hidden;
        padding: clamp(28px, 4.5vw, 52px) clamp(16px, 2vw, 32px) clamp(32px, 4.5vw, 48px);
        position: relative;
        text-align: left;
    }

    .about-page--community-post .community-article-hero.has-cover::before, .about-page--articles .community-article-hero.has-cover::before{
        background:
            linear-gradient(180deg, rgba(6, 22, 38, 0.78) 0%, rgba(6, 22, 38, 0.5) 42%, rgba(6, 22, 38, 0.88) 100%),
            var(--article-cover) center / cover no-repeat;
        content: "";
        inset: 0;
        position: absolute;
        transform: scale(1.03);
        z-index: 0;
    }

    .about-page--community-post .community-article-hero::after, .about-page--articles .community-article-hero::after{
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

    .about-page--community-post .community-article-hero__inner, .about-page--articles .community-article-hero__inner{
        box-sizing: border-box;
        margin: 0;
        max-width: none;
        padding: 0 clamp(16px, 2vw, 32px);
        position: relative;
        width: 100%;
        z-index: 2;
    }

    .about-page--community-post .community-post-back-wrap, .about-page--articles .community-post-back-wrap{
        margin: 0 0 0.85rem;
        max-width: none;
        text-align: left;
    }

    .about-page--community-post .community-post-back, .about-page--articles .community-post-back{
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        margin-bottom: 0;
        padding: 0.42rem 0.95rem;
        transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    }

    .about-page--community-post .community-post-back:hover, .about-page--articles .community-post-back:hover{
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.32);
        text-decoration: none;
        transform: translateX(-2px);
    }

    .about-page--community-post .community-article-hero__kicker, .about-page--articles .community-article-hero__kicker{
        animation: articleRise 0.7s ease both;
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        margin-bottom: 0.55rem;
        text-transform: uppercase;
    }

    .about-page--community-post .community-article-hero__kicker span, .about-page--articles .community-article-hero__kicker span{
        color: #9ad4ff;
    }

    .about-page--community-post .community-article-hero h1, .about-page--articles .community-article-hero h1{
        animation: articleRise 0.75s ease 0.05s both;
        font-family: Fraunces, Georgia, serif;
        font-optical-sizing: auto;
        font-size: clamp(1.55rem, 3.6vw, 2.15rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.65rem;
        max-width: none;
        text-wrap: balance;
    }

    .about-page--community-post .community-article-hero__deck, .about-page--articles .community-article-hero__deck{
        animation: articleRise 0.75s ease 0.1s both;
        color: rgba(255, 255, 255, 0.9);
        font-size: clamp(0.92rem, 1.6vw, 1.02rem);
        line-height: 1.5;
        margin: 0 0 0.75rem;
        max-width: none;
    }

    .about-page--community-post .community-article-hero__byline, .about-page--articles .community-article-hero__byline{
        animation: articleRise 0.75s ease 0.14s both;
        align-items: center;
        color: rgba(255, 255, 255, 0.86);
        display: flex;
        flex-wrap: wrap;
        font-size: 0.84rem;
        gap: 0.3rem 0.5rem;
        margin: 0 0 0.85rem;
    }

    .about-page--community-post .community-article-hero__byline a, .about-page--articles .community-article-hero__byline a{
        color: #fff;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 0.15em;
    }

    .about-page--community-post .community-article-hero__byline-sep, .about-page--articles .community-article-hero__byline-sep{
        color: rgba(255, 255, 255, 0.4);
    }

    .about-page--community-post .community-article-hero__actions, .about-page--articles .community-article-hero__actions{
        animation: articleRise 0.75s ease 0.18s both;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        justify-content: flex-start !important;
        width: 100%;
    }

    .about-page--community-post .community-post-banner-tags, .about-page--articles .community-post-banner-tags{
        display: none;
    }

    .about-page--community-post .awareness-hero-strip, .about-page--articles .awareness-hero-strip{
        display: none;
    }

    .about-page--community-post .about-inner, .about-page--articles .about-inner{
        box-sizing: border-box;
        gap: 0;
        margin: 0;
        max-width: none;
        padding: 0 clamp(16px, 2vw, 32px) 40px;
        position: relative;
        top: -20px;
        width: 100%;
        z-index: 3;
    }

    .about-page--community-post .about-inner > .sec, .about-page--articles .about-inner > .sec{
        background: transparent;
        border: 0;
        box-shadow: none;
        padding: 0;
    }

    .about-page--community-post .community-article-shell, .about-page--articles .community-article-shell{
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 1rem;
        box-shadow: 0 6px 24px rgba(16, 37, 63, 0.07);
        overflow: hidden;
        width: 100%;
    }

    .about-page--community-post .community-article-shell__top, .about-page--articles .community-article-shell__top{
        align-items: center;
        background: #f8fafc;
        border-bottom: 1px solid var(--article-line);
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem 0.65rem;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
    }

    .about-page--community-post .community-article-meta, .about-page--articles .community-article-meta{
        align-items: center;
        display: flex;
        flex: 1 1 auto;
        flex-wrap: wrap;
        gap: 0.35rem;
        min-width: 0;
        padding: 0;
    }

    .about-page--community-post .community-article-meta__chip, .about-page--articles .community-article-meta__chip{
        align-items: center;
        background: #fff;
        border: 1px solid var(--article-line);
        border-radius: 999px;
        color: var(--article-ink);
        display: inline-flex;
        font-size: 0.76rem;
        font-weight: 600;
        gap: 0.35rem;
        line-height: 1.2;
        padding: 0.28rem 0.6rem;
        white-space: nowrap;
    }

    .about-page--community-post .community-article-meta__chip i, .about-page--articles .community-article-meta__chip i{
        color: var(--article-accent);
        font-size: 0.68rem;
        width: 0.75rem;
    }

    .about-page--community-post .community-article-font-size, .about-page--articles .community-article-font-size{
        align-items: center;
        background: #fff;
        border: 1px solid var(--article-line);
        border-radius: 999px;
        display: inline-flex;
        flex-shrink: 0;
        gap: 0.1rem;
        margin-left: auto;
        padding: 0.2rem 0.3rem 0.2rem 0.45rem;
    }

    .about-page--community-post .community-article-font-size__icon, .about-page--articles .community-article-font-size__icon{
        color: var(--article-muted);
        font-size: 0.72rem;
        margin-right: 0.1rem;
    }

    .about-page--community-post .community-article-font-size__btn, .about-page--articles .community-article-font-size__btn{
        align-items: center;
        background: transparent;
        border: 0;
        border-radius: 999px;
        color: var(--article-ink);
        cursor: pointer;
        display: inline-flex;
        font-family: "Source Sans 3", "Segoe UI", sans-serif;
        font-size: 0.78rem;
        font-weight: 700;
        height: 1.65rem;
        justify-content: center;
        line-height: 1;
        min-width: 1.65rem;
        padding: 0 0.35rem;
        transition: background 0.2s ease, color 0.2s ease, opacity 0.2s ease;
    }

    .about-page--community-post .community-article-font-size__btn:hover:not(:disabled), .about-page--articles .community-article-font-size__btn:hover:not(:disabled){
        background: var(--article-accent-soft);
        color: var(--article-accent);
    }

    .about-page--community-post .community-article-font-size__btn.is-active, .about-page--articles .community-article-font-size__btn.is-active{
        background: var(--article-accent);
        color: #fff;
    }

    .about-page--community-post .community-article-font-size__btn:disabled, .about-page--articles .community-article-font-size__btn:disabled{
        cursor: not-allowed;
        opacity: 0.4;
    }

    .about-page--community-post .community-article-cover, .about-page--articles .community-article-cover{
        display: block;
        max-height: 360px;
        object-fit: cover;
        width: 100%;
    }

    .about-page--community-post .community-article-cover-wrap, .about-page--articles .community-article-cover-wrap{
        border-bottom: 1px solid var(--article-line);
        margin: 0;
        overflow: hidden;
    }

    .about-page--community-post .community-featured-gallery--article, .about-page--articles .community-featured-gallery--article{
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
    }

    .about-page--community-post .community-article-reading, .about-page--articles .community-article-reading{
        margin: 0 auto;
        max-width: none;
        padding: 1rem 1.25rem 1.25rem;
        width: 100%;
    }

    .about-page--community-post .community-article-reading__lead, .about-page--articles .community-article-reading__lead{
        border-left: 3px solid var(--article-accent);
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: clamp(1.15rem, 2.2vw, 1.32rem);
        font-weight: 550;
        line-height: 1.55;
        margin: 0 0 1.75rem;
        padding-left: 1rem;
    }

    .about-page--community-post [data-community-font-target],
    .about-page--articles [data-community-font-target] {
        transform-origin: top center;
    }

    .about-page--community-post .community-post-body--scalable,
    .about-page--articles .community-post-body--scalable,
    .about-page--community-post [data-community-body-protected],
    .about-page--articles [data-community-body-protected] {
        font-size: calc(var(--post-text-base, 1rem) * var(--article-text-scale, 1));
    }

    .about-page--community-post .community-post-body--poetry.community-post-body--scalable,
    .about-page--articles .community-post-body--poetry.community-post-body--scalable {
        --post-text-base: 1.08rem;
    }

    .about-page--community-post.is-text-scaled .community-post-body--scalable [style*="font-size"],
    .about-page--articles.is-text-scaled .community-post-body--scalable [style*="font-size"],
    .about-page--community-post.is-text-scaled .community-post-body--article [style*="font-size"],
    .about-page--articles.is-text-scaled .community-post-body--article [style*="font-size"],
    .about-page--community-post.is-text-scaled [data-community-body-protected] [style*="font-size"],
    .about-page--articles.is-text-scaled [data-community-body-protected] [style*="font-size"] {
        font-size: inherit !important;
    }

    .about-page--community-post .community-post-body--scalable p,
    .about-page--articles .community-post-body--scalable p {
        margin-bottom: 0.95rem;
    }

    .about-page--community-post .community-post-body--article,
    .about-page--articles .community-post-body--article {
        color: #1e293b;
        font-size: calc(var(--post-text-base, 1rem) * var(--article-text-scale, 1));
        line-height: 1.72;
    }

    .about-page--community-post .community-post-body--article p, .about-page--articles .community-post-body--article p{
        margin-bottom: 0.95rem;
    }

    .about-page--community-post .community-post-body--article h2, .about-page--articles .community-post-body--article h2,
    .about-page--community-post .community-post-body--article h3, .about-page--articles .community-post-body--article h3,
    .about-page--community-post .community-post-body--article h4, .about-page--articles .community-post-body--article h4{
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-weight: 700;
        letter-spacing: -0.015em;
        line-height: 1.28;
        margin: 1.5rem 0 0.65rem;
    }

    .about-page--community-post .community-post-body--article h2, .about-page--articles .community-post-body--article h2{
        font-size: calc(1.4rem * var(--article-text-scale));
    }

    .about-page--community-post .community-post-body--article h3, .about-page--articles .community-post-body--article h3{
        font-size: calc(1.2rem * var(--article-text-scale));
    }

    .about-page--community-post .community-post-body--article h4, .about-page--articles .community-post-body--article h4{
        font-size: calc(1.05rem * var(--article-text-scale));
    }

    .about-page--community-post .community-post-body--article blockquote, .about-page--articles .community-post-body--article blockquote{
        background: linear-gradient(135deg, rgba(23, 105, 165, 0.05), rgba(42, 107, 79, 0.06));
        border: 0;
        border-left: 3px solid var(--article-earth);
        border-radius: 0 0.75rem 0.75rem 0;
        color: #334155;
        font-family: Fraunces, Georgia, serif;
        font-size: calc(1.02rem * var(--article-text-scale));
        font-style: italic;
        margin: 1.25rem 0;
        padding: 0.85rem 1rem;
    }

    .about-page--community-post .community-post-body--article a, .about-page--articles .community-post-body--article a{
        color: var(--article-accent);
        font-weight: 600;
        text-decoration-thickness: 1px;
        text-underline-offset: 0.18em;
    }

    .about-page--community-post .community-article-score-row, .about-page--articles .community-article-score-row{
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.85rem;
    }

    .about-page--community-post .community-article-score-row .badge, .about-page--articles .community-article-score-row .badge{
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.28rem 0.6rem;
    }

    .about-page--community-post .community-article-tags, .about-page--articles .community-article-tags, .about-page--news-detail .community-article-tags{
        align-items: flex-start;
        border-top: 1px solid var(--article-line);
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 1.25rem;
        padding-top: 0.85rem;
        row-gap: 0.55rem;
    }

    .about-page--community-post .community-article-tag, .about-page--articles .community-article-tag, .about-page--news-detail .community-article-tag{
        align-items: center;
        background: var(--article-accent-soft);
        border-radius: 999px;
        color: #0f4c75;
        display: inline-flex;
        font-size: 0.74rem;
        font-weight: 600;
        gap: 0.2rem;
        max-width: min(100%, 18rem);
        min-width: 0;
        padding: 0.25rem 0.6rem;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .about-page--community-post .community-article-tag__text, .about-page--articles .community-article-tag__text, .about-page--news-detail .community-article-tag__text{
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .about-page--community-post .community-article-tag i, .about-page--articles .community-article-tag i, .about-page--news-detail .community-article-tag i{
        flex-shrink: 0;
        font-size: 0.62rem;
        opacity: 0.75;
    }

    .about-page--community-post .community-article-tag:hover, .about-page--articles .community-article-tag:hover, .about-page--news-detail .community-article-tag:hover{
        background: rgba(23, 105, 165, 0.2);
        color: #0a3a5c;
    }

    .about-page--community-post .community-article-author-card, .about-page--articles .community-article-author-card, .about-page--news-detail .community-article-author-card{
        align-items: center;
        background: #f8fafc;
        border: 1px solid var(--article-line);
        border-radius: 0.85rem;
        display: flex;
        gap: 0.75rem;
        margin-top: 1.15rem;
        padding: 0.75rem 0.85rem;
    }

    .about-page--community-post .community-article-author-card__avatar, .about-page--articles .community-article-author-card__avatar, .about-page--news-detail .community-article-author-card__avatar,
    .about-page--community-post .community-article-author-card__initials, .about-page--articles .community-article-author-card__initials, .about-page--news-detail .community-article-author-card__initials{
        align-items: center;
        background: linear-gradient(135deg, #dbeafe 0%, #d1fae5 100%);
        border-radius: 50%;
        color: #0f4c75;
        display: inline-flex;
        flex-shrink: 0;
        font-family: Fraunces, Georgia, serif;
        font-size: 0.95rem;
        font-weight: 700;
        height: 44px;
        justify-content: center;
        object-fit: cover;
        overflow: hidden;
        width: 44px;
    }

    .about-page--community-post .community-article-author-card__body, .about-page--articles .community-article-author-card__body, .about-page--news-detail .community-article-author-card__body{
        min-width: 0;
    }

    .about-page--community-post .community-article-author-card__name, .about-page--articles .community-article-author-card__name, .about-page--news-detail .community-article-author-card__name{
        align-items: center;
        color: var(--article-ink);
        display: flex;
        flex-wrap: wrap;
        font-family: Fraunces, Georgia, serif;
        font-size: 0.98rem;
        font-weight: 700;
        gap: 0.35rem;
        margin: 0;
    }

    .about-page--community-post .community-article-author-card__name-icon, .about-page--articles .community-article-author-card__name-icon, .about-page--news-detail .community-article-author-card__name-icon{
        color: var(--article-accent);
        font-size: 0.72rem;
    }

    .about-page--community-post .community-article-author-card__name a, .about-page--articles .community-article-author-card__name a, .about-page--news-detail .community-article-author-card__name a{
        color: inherit;
        text-decoration: none;
    }

    .about-page--community-post .community-article-author-card__name a:hover, .about-page--articles .community-article-author-card__name a:hover, .about-page--news-detail .community-article-author-card__name a:hover{
        color: var(--article-accent);
    }

    .about-page--community-post .community-article-author-card__bio, .about-page--articles .community-article-author-card__bio, .about-page--news-detail .community-article-author-card__bio{
        color: var(--article-muted);
        font-size: 0.8rem;
        line-height: 1.45;
        margin: 0.2rem 0 0;
    }

    .about-page--community-post .community-engagement-panel,, .about-page--articles .community-engagement-panel,
    .about-page--community-post #comments-discussion.about-box, .about-page--articles #comments-discussion.about-box{
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 0.85rem;
        box-shadow: 0 4px 16px rgba(16, 37, 63, 0.05);
        margin-top: 0.85rem !important;
        padding: 0.75rem 0.85rem !important;
    }

    .about-page--community-post .community-engagement-panel__title,, .about-page--articles .community-engagement-panel__title,
    .about-page--community-post #comments-discussion > h4, .about-page--articles #comments-discussion > h4{
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .about-page--community-post .community-engagement-panel--article .community-engagement-stats, .about-page--articles .community-engagement-panel--article .community-engagement-stats{
        gap: 0.35rem;
        margin-bottom: 0.55rem;
    }

    .about-page--community-post .community-engagement-panel--article .community-engagement-stat, .about-page--articles .community-engagement-panel--article .community-engagement-stat{
        border-radius: 999px;
        padding: 0.35rem 0.6rem;
    }

    .about-page--community-post .community-engagement-panel--article .community-engagement-actions, .about-page--articles .community-engagement-panel--article .community-engagement-actions{
        margin-bottom: 0 !important;
    }

    .about-page--community-post .community-detail-card, .about-page--articles .community-detail-card{
        background: var(--article-paper);
        border: 1px solid var(--article-line);
        border-radius: 1.2rem;
        box-shadow: 0 10px 28px rgba(16, 37, 63, 0.06);
    }

    .about-page--community-post .community-detail-card__title, .about-page--articles .community-detail-card__title{
        color: var(--article-ink);
        font-family: Fraunces, Georgia, serif;
        font-size: 1.12rem;
    }

    .about-page--community-post .community-detail-item,, .about-page--articles .community-detail-item,
    .about-page--community-post .community-detail-list__row, .about-page--articles .community-detail-list__row{
        background: linear-gradient(180deg, #f8fbfd 0%, #ffffff 100%);
        border-color: var(--article-line);
    }

    .about-page--community-post .community-detail-item__value,, .about-page--articles .community-detail-item__value,
    .about-page--community-post .community-detail-list__row dd, .about-page--articles .community-detail-list__row dd{
        color: #1e293b;
        font-weight: 500;
    }

    .about-page--community-post .community-article-reading .about-box, .about-page--articles .community-article-reading .about-box{
        border: 1px solid var(--article-line);
        border-radius: 0.85rem;
        box-shadow: none;
        margin-top: 1rem;
        padding: 0.85rem 0.95rem;
    }

    .about-page--community-post .community-article-reading .about-box > h4, .about-page--articles .community-article-reading .about-box > h4{
        font-size: 0.95rem;
        margin-bottom: 0.65rem;
    }

    .about-page--community-post .community-article-reading .about-box > h5, .about-page--articles .community-article-reading .about-box > h5{
        font-size: 0.88rem;
    }

    .about-page--community-post .poetry-reading-card__kicker, .about-page--articles .poetry-reading-card__kicker{
        font-size: 0.68rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
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
        .about-page--community-post .about-inner, .about-page--articles .about-inner{
            padding: 0 12px 32px;
            top: -14px;
        }

        .about-page--community-post .community-article-shell__top, .about-page--articles .community-article-shell__top{
            padding: 0.45rem 0.6rem;
        }

        .about-page--community-post .community-article-meta__chip, .about-page--articles .community-article-meta__chip{
            font-size: 0.72rem;
            padding: 0.24rem 0.5rem;
        }

        .about-page--community-post .community-article-reading, .about-page--articles .community-article-reading{
            padding: 0.85rem 0.9rem 1rem;
        }
    }

    @@media (prefers-reduced-motion: reduce) {
        .about-page--community-post .community-article-hero::after,, .about-page--articles .community-article-hero::after,
        .about-page--community-post .community-article-hero__kicker,, .about-page--articles .community-article-hero__kicker,
        .about-page--community-post .community-article-hero h1,, .about-page--articles .community-article-hero h1,
        .about-page--community-post .community-article-hero__deck,, .about-page--articles .community-article-hero__deck,
        .about-page--community-post .community-article-hero__byline,, .about-page--articles .community-article-hero__byline,
        .about-page--community-post .community-article-hero__actions,, .about-page--articles .community-article-hero__actions,
        .about-page--community-post .community-article-author-card, .about-page--articles .community-article-author-card{
            animation: none;
            transition: none;
        }
    }
</style>

    .community-portal-nav {
        margin-bottom: 0.85rem;
        width: 100%;
    }

    .community-portal-nav__back {
        align-items: center;
        color: #1b5e20;
        display: inline-flex;
        font-size: 0.88rem;
        font-weight: 700;
        gap: 0.45rem;
        margin-bottom: 0.7rem;
        text-decoration: none;
        transition: color 0.15s ease, transform 0.15s ease;
    }

    .community-portal-nav__back:hover {
        color: #2e7d32;
        text-decoration: none;
        transform: translateX(-2px);
    }

    .community-portal-nav--on-dark .community-portal-nav__back {
        color: rgba(255, 255, 255, 0.92);
    }

    .community-portal-nav--on-dark .community-portal-nav__back:hover {
        color: #fff;
    }

    .community-portal-nav__breadcrumb {
        color: #5f7083;
        font-size: 0.85rem;
        margin-bottom: 0.65rem;
    }

    .community-portal-nav__breadcrumb a {
        color: #5f7083;
        text-decoration: none;
    }

    .community-portal-nav__breadcrumb a:hover {
        color: #2e7d32;
    }

    .community-portal-nav__breadcrumb span[aria-hidden="true"] {
        margin: 0 0.35rem;
    }

    .community-portal-nav__links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .community-portal-nav__link {
        align-items: center;
        background: #f4f8f5;
        border: 1px solid rgba(46, 125, 50, 0.14);
        border-radius: 999px;
        color: #1b5e20;
        display: inline-flex;
        font-size: 0.82rem;
        font-weight: 700;
        gap: 0.4rem;
        padding: 0.38rem 0.8rem;
        text-decoration: none;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .community-portal-nav__link:hover {
        background: #e8f5e9;
        border-color: rgba(46, 125, 50, 0.28);
        color: #1b5e20;
        text-decoration: none;
    }

    .community-portal-nav--on-dark .community-portal-nav__breadcrumb,
    .community-portal-nav--on-dark .community-portal-nav__breadcrumb a {
        color: rgba(255, 255, 255, 0.82);
    }

    .community-portal-nav--on-dark .community-portal-nav__breadcrumb a:hover {
        color: #fff;
    }

    .community-portal-nav--on-dark .community-portal-nav__link {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.24);
        color: #fff;
    }

    .community-portal-nav--on-dark .community-portal-nav__link:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.34);
        color: #fff;
    }

    .community-news-main__header--listing,
    .community-news-main__header--detail {
        flex-direction: column;
        align-items: stretch;
    }

    .community-news-main__header-row {
        align-items: flex-start;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        width: 100%;
    }

    .community-article-hero .community-portal-nav {
        box-sizing: border-box;
        margin-bottom: 1rem;
        max-width: none;
        padding: 0 clamp(16px, 2vw, 32px);
        width: 100%;
    }

    .community-hero .community-portal-nav {
        margin-bottom: 1.15rem;
    }

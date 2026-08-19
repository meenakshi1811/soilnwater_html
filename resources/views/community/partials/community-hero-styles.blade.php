    .community-hero {
        background:
            linear-gradient(90deg, rgba(8, 42, 48, 0.78) 0%, rgba(12, 58, 52, 0.58) 48%, rgba(10, 48, 44, 0.42) 100%),
            url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        color: #fff;
        padding: clamp(36px, 4.5vw, 56px) clamp(16px, 2.5vw, 40px) 32px;
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    .community-hero::before {
        display: none;
    }

    .community-hero__inner {
        margin: 0 auto;
        max-width: none;
        position: relative;
        width: 100%;
        z-index: 1;
    }

    .community-hero__top {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem 2rem;
        justify-content: space-between;
    }

    .community-hero__copy {
        flex: 1 1 320px;
        min-width: 0;
    }

    .community-hero__profile {
        align-items: center;
        display: flex;
        gap: 1.25rem;
        margin-bottom: 0.5rem;
    }

    .community-hero__avatar,
    .community-author-avatar.community-hero__avatar {
        align-items: center;
        background: rgba(255, 255, 255, 0.18);
        border: 3px solid rgba(255, 255, 255, 0.35);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 1.5rem;
        font-weight: 700;
        height: 5rem;
        justify-content: center;
        overflow: hidden;
        width: 5rem;
    }

    .community-hero__avatar.community-author-avatar--image {
        background: rgba(255, 255, 255, 0.12);
    }

    .community-hero__eyebrow {
        display: none;
    }

    .community-hero__title {
        font-size: clamp(2.1rem, 4vw, 3.15rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin-bottom: 0.45rem;
        text-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
    }

    .community-hero__subtitle {
        color: rgba(255, 255, 255, 0.92);
        font-size: 1rem;
        line-height: 1.6;
        margin: 0;
        max-width: 560px;
    }

    .community-hero__stats {
        display: grid;
        flex: 0 1 520px;
        gap: 1rem 1.25rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        min-width: min(100%, 420px);
        padding-top: 0.35rem;
    }

    .community-hero__stat-block {
        color: #fff;
        text-align: left;
    }

    .community-hero__stat-block i {
        display: block;
        font-size: 1.05rem;
        margin-bottom: 0.35rem;
        opacity: 0.92;
    }

    .community-hero__stat-value {
        display: block;
        font-size: clamp(1.35rem, 2vw, 1.7rem);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
    }

    .community-hero__stat-label {
        color: rgba(255, 255, 255, 0.82);
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.35;
        margin-top: 0.2rem;
    }

    .community-hero__actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.7rem;
        margin-top: 1.35rem;
    }

    .community-hero__ghost {
        align-items: center;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.55);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 0.88rem;
        font-weight: 650;
        gap: 0.4rem;
        padding: 0.55rem 1rem;
        text-decoration: none;
    }

    .community-hero__ghost:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
    }

    .community-hero__stat {
        align-items: center;
        background: #163a6b;
        border: 0;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 0.86rem;
        font-weight: 700;
        gap: 0.4rem;
        padding: 0.55rem 0.95rem;
    }

    @media (max-width: 991.98px) {
        .community-hero__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

@extends('frontend.layouts.app')

@php
    $authorSeoName = isset($activeAuthor) ? ($activeAuthor->name ?? $activeAuthor->full_name ?? 'Community author') : null;
    $hubSeoTitle = $authorSeoName
        ? $authorSeoName."'s Posts | SoilnWater Community"
        : (($activeType && isset($types[$activeType]))
            ? $types[$activeType]['label'].' | SoilnWater Community'
            : 'Community Hub | SoilnWater');
    $hubSeoDescription = $authorSeoName
        ? 'Browse published stories, reports, news, and community updates from '.$authorSeoName.' on SoilnWater.'
        : (($activeType && isset($types[$activeType]))
            ? $types[$activeType]['description'].' Explore '.$types[$activeType]['label'].' posts on SoilnWater Community.'
            : 'Discover stories, reports, news, poetry, biography, and local voices from the SoilnWater Community Hub.');
    $hubSeoUrl = isset($activeAuthor)
        ? ($activeType
            ? route('community.authors.show', ['uniqueName' => $activeAuthor->authorUniqueName(), 'type' => $activeType])
            : route('community.authors.show', $activeAuthor->authorUniqueName()))
        : ($activeType ? route('community.index', ['type' => $activeType]) : route('community.index'));
    $hubSeoKeywords = $authorSeoName
        ? 'SoilnWater community, '.$authorSeoName.', community posts, local stories'
        : (($activeType && isset($types[$activeType]))
            ? 'SoilnWater community, '.$types[$activeType]['label'].', local stories, community hub'
            : 'SoilnWater community, community hub, local stories, reports, news, poetry');
    $communityFilterPillColors = \App\Support\CommunityContentTaxonomy::pillColors();
    $communityFilterPillFallbackColor = \App\Support\CommunityContentTaxonomy::pillColorFallback();
@endphp

@section('meta_title', $hubSeoTitle)
@section('meta_description', $hubSeoDescription)
@section('meta_url', $hubSeoUrl)
@section('meta_canonical', $hubSeoUrl)
@section('meta_keywords', $hubSeoKeywords)
@section('meta_image', asset('assets/images/logo_soilnwater.webp'))

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/community-hub-nav.css') }}?v={{ file_exists(public_path('assets/css/community-hub-nav.css')) ? filemtime(public_path('assets/css/community-hub-nav.css')) : time() }}">
@if(\App\Support\CommunityContentTaxonomy::shouldUsePortalListing($activeType ?? '', $activeHub ?? null, isset($activeAuthor)))
<link rel="stylesheet" href="{{ asset('assets/css/community-news-portal.css') }}?v={{ file_exists(public_path('assets/css/community-news-portal.css')) ? filemtime(public_path('assets/css/community-news-portal.css')) : time() }}">
@endif
<style>
    @include('community.partials.community-portal-nav-styles')
    .community-hub {
        background: #f4f7fb;
    }

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

    .community-hub-sections-bar {
        background: #f4f7fb;
        margin: 0 auto;
        max-width: none;
        padding: 1.15rem clamp(16px, 2.5vw, 40px) 0.35rem;
        width: 100%;
    }

    .community-hub-nav--sections-only {
        margin-bottom: 0;
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

    .community-btn-create {
        align-items: center;
        background: #1f9d45;
        border: 0;
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 0.92rem;
        font-weight: 700;
        gap: 0.45rem;
        padding: 0.62rem 1.15rem;
        text-decoration: none;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .community-btn-create:hover {
        background: #18863a;
        color: #fff;
        transform: translateY(-1px);
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

    .community-shell {
        margin: 0 auto;
        max-width: none;
        padding: 1.5rem clamp(16px, 2.5vw, 40px) 3rem;
        width: 100%;
    }

    .community-hub-layout {
        align-items: start;
        display: grid;
        gap: 1.5rem;
        grid-template-columns: minmax(0, 1fr);
        margin-top: 0.25rem;
    }

    .community-hub-feed {
        min-width: 0;
    }

    @media (min-width: 1100px) {
        .community-hub-layout {
            grid-template-columns: minmax(0, 1fr) 300px;
        }
    }

    @media (max-width: 991.98px) {
        .community-hero__stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .community-toolbar {
        background: linear-gradient(135deg, #0f2f55 0%, #174a7a 55%, #1a5c42 100%);
        border: none;
        border-radius: 18px;
        box-shadow: 0 14px 36px rgba(15, 47, 85, 0.22);
        margin-bottom: 1.25rem;
        overflow: hidden;
        padding: 1.25rem 1.35rem 1.35rem;
        position: relative;
    }

    .community-toolbar::before {
        background: radial-gradient(circle at 92% 8%, rgba(255, 255, 255, 0.12), transparent 42%);
        content: "";
        inset: 0;
        pointer-events: none;
        position: absolute;
    }

    .community-toolbar__head {
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.9rem;
        position: relative;
        z-index: 1;
    }

    .community-toolbar__title {
        color: #fff;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        margin: 0;
    }

    .community-toolbar__hint {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.86rem;
        margin: 0;
    }

    .community-filter-scroll {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        position: relative;
        z-index: 1;
    }

    .community-filter-pill {
        --pill-color: #78909c;
        backdrop-filter: blur(6px);
        background: color-mix(in srgb, var(--pill-color) 58%, transparent);
        border: 1px solid color-mix(in srgb, var(--pill-color) 72%, #ffffff 28%);
        border-radius: 999px;
        box-shadow: 0 2px 8px color-mix(in srgb, var(--pill-color) 24%, transparent);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 148px;
        min-height: 40px;
        white-space: nowrap;
        text-align: center;
        font-size: 0.83rem;
        font-weight: 600;
        padding: 0.46rem 1.05rem;
        text-decoration: none;
        text-shadow: 0 1px 1px rgba(15, 47, 85, 0.18);
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .community-filter-pill:hover {
        background: color-mix(in srgb, var(--pill-color) 74%, transparent);
        border-color: color-mix(in srgb, var(--pill-color) 82%, #ffffff 18%);
        box-shadow: 0 4px 14px color-mix(in srgb, var(--pill-color) 34%, transparent);
        color: #fff;
        transform: translateY(-1px);
    }

    .community-filter-pill.is-active {
        background: color-mix(in srgb, var(--pill-color) 18%, #ffffff);
        border-color: color-mix(in srgb, var(--pill-color) 42%, #ffffff);
        box-shadow: 0 6px 18px color-mix(in srgb, var(--pill-color) 28%, transparent);
        color: color-mix(in srgb, var(--pill-color) 82%, #0f2f55);
        font-weight: 700;
        text-shadow: none;
        transform: none;
    }

    .community-filter-pill.is-active:hover {
        background: color-mix(in srgb, var(--pill-color) 24%, #ffffff);
        border-color: color-mix(in srgb, var(--pill-color) 48%, #ffffff);
        box-shadow: 0 8px 22px color-mix(in srgb, var(--pill-color) 32%, transparent);
        color: color-mix(in srgb, var(--pill-color) 88%, #0f2f55);
        transform: none;
    }

    .community-type-panel {
        --type-color: #1f66b4;
        background: linear-gradient(180deg, #f5f9fd 0%, #ffffff 100%);
        border: 1px solid color-mix(in srgb, var(--type-color) 35%, #c8d9ea);
        border-left: 4px solid var(--type-color);
        border-radius: 14px;
        margin-bottom: 1.25rem;
        padding: 1rem 1.1rem;
    }

    .community-type-panel__title {
        color: #0f2f55;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .community-type-panel__text {
        color: #4a6480;
        font-size: 0.92rem;
        line-height: 1.6;
        margin-bottom: 0.5rem;
    }

    .community-type-panel__categories {
        align-items: flex-start;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.35rem;
    }

    .community-type-panel__categories > strong {
        color: #0f2f55;
        flex: 0 0 100%;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        margin-bottom: 0.15rem;
        text-transform: uppercase;
    }

    .community-category-chip {
        align-items: center;
        background: linear-gradient(180deg, #f3f8fd 0%, #eaf2fa 100%);
        border: 1px solid #b8cfe6;
        border-radius: 10px;
        display: inline-flex;
        gap: 0.45rem;
        padding: 0.35rem 0.55rem 0.35rem 0.7rem;
    }

    .community-category-chip__label {
        color: #1a4570;
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .community-category-chip .btn-outline-success {
        border-color: #2e7d32;
        color: #2e7d32;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
    }

    .community-category-chip .btn-outline-success:hover {
        background: #2e7d32;
        border-color: #2e7d32;
        color: #fff;
    }

    .community-category-chip .btn-success {
        background: linear-gradient(135deg, #256b2a, #2e7d32);
        border-color: transparent;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
    }

    .community-post-card {
        --type-color: #dce6f3;
        background: #fff;
        border: 1px solid #dbe6f2;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(18, 57, 95, 0.05);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .community-post-card:hover {
        border-color: var(--type-color);
        box-shadow: 0 16px 34px color-mix(in srgb, var(--type-color) 22%, rgba(18, 57, 95, 0.12));
        transform: translateY(-3px);
    }

    .community-post-card__media-link {
        display: block;
        overflow: hidden;
        position: relative;
        text-decoration: none;
    }

    .community-post-card__image,
    .community-post-card__placeholder {
        aspect-ratio: 16 / 9;
        display: block;
        width: 100%;
    }

    .community-post-card__image {
        object-fit: cover;
        transition: transform 0.35s ease;
    }

    .community-post-card:hover .community-post-card__image {
        transform: scale(1.04);
    }

    .community-post-card__placeholder {
        align-items: center;
        background: linear-gradient(135deg, #e8f0f8, #d8e8f4);
        color: #5f7f9d;
        display: flex;
        font-size: 2rem;
        justify-content: center;
    }

    .community-post-card__media-overlay {
        background: linear-gradient(180deg, transparent 35%, rgba(15, 47, 85, 0.55) 100%);
        inset: 0;
        pointer-events: none;
        position: absolute;
    }

    .community-post-card__badges {
        bottom: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        left: 0.75rem;
        position: absolute;
        right: 0.75rem;
        z-index: 1;
    }

    .community-post-card__badge {
        backdrop-filter: blur(6px);
        background: color-mix(in srgb, var(--type-color) 14%, rgba(255, 255, 255, 0.92));
        border: 1px solid color-mix(in srgb, var(--type-color) 45%, #ffffff);
        border-radius: 999px;
        color: color-mix(in srgb, var(--type-color) 72%, #12395f);
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.28rem 0.65rem;
    }

    .community-post-card__badge--section {
        background: color-mix(in srgb, var(--type-color) 92%, #000000);
        border-color: transparent;
        color: #fff;
    }

    .community-post-card__badge--promotion {
        background: color-mix(in srgb, var(--type-color) 78%, #1f66b4);
        border-color: transparent;
        color: #fff;
    }

    .community-post-card__badge--trust-score,
    .community-post-card__badge--report-status {
        background: color-mix(in srgb, var(--type-color) 18%, rgba(255, 255, 255, 0.94));
        border-color: color-mix(in srgb, var(--type-color) 55%, #ffffff);
        color: color-mix(in srgb, var(--type-color) 68%, #0f2f55);
    }

    .community-post-card--highlighted {
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.55), 0 14px 28px rgba(15, 47, 85, 0.12);
    }

    .community-post-card__video-badge {
        align-items: center;
        background: color-mix(in srgb, var(--type-color) 82%, rgba(15, 47, 85, 0.9));
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        font-size: 0.85rem;
        height: 2rem;
        justify-content: center;
        position: absolute;
        right: 0.75rem;
        top: 0.75rem;
        width: 2rem;
        z-index: 1;
    }

    .community-post-card__audio-badge {
        background: color-mix(in srgb, var(--type-color) 85%, rgba(13, 110, 122, 0.9));
        bottom: 0.75rem;
        top: auto;
    }

    .community-post-card__tag {
        align-items: center;
        background: color-mix(in srgb, var(--type-color) 12%, #ffffff);
        border: 1px solid color-mix(in srgb, var(--type-color) 48%, #dce6f3);
        border-radius: 999px;
        color: color-mix(in srgb, var(--type-color) 70%, #12395f);
        display: inline-flex;
        font-size: 0.72rem;
        font-weight: 600;
        gap: 0.3rem;
        padding: 0.22rem 0.6rem;
    }

    .community-post-card__tag--solid {
        background: color-mix(in srgb, var(--type-color) 90%, #000000);
        border-color: transparent;
        color: #fff;
    }

    .community-post-card__tag--emphasis {
        background: color-mix(in srgb, var(--type-color) 22%, #ffffff);
        border-color: color-mix(in srgb, var(--type-color) 65%, #dce6f3);
        color: color-mix(in srgb, var(--type-color) 78%, #0f2f55);
        font-weight: 700;
    }

    .community-post-card__tag--rating {
        background: color-mix(in srgb, var(--type-color) 16%, #fff8e7);
        border-color: color-mix(in srgb, var(--type-color) 40%, #f3d9a4);
        color: color-mix(in srgb, var(--type-color) 45%, #92400e);
    }

    .poetry-card-badge {
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
    }

    .poetry-card-badge--rating {
        background: rgba(255, 193, 7, 0.18);
        color: #92400e;
    }

    .community-post-card__body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.65rem;
        padding: 1rem 1.05rem 1.05rem;
    }

    .community-post-card__title {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.35;
        margin: 0;
    }

    .community-post-card__title a {
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: #12395f;
        display: -webkit-box;
        overflow: hidden;
        text-decoration: none;
    }

    .community-post-card__title a:hover {
        color: color-mix(in srgb, var(--type-color) 55%, #12395f);
    }

    .community-post-card__excerpt {
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        color: #4f6780;
        display: -webkit-box;
        font-size: 0.86rem;
        line-height: 1.55;
        margin: 0;
        overflow: hidden;
    }

    .community-post-card__location {
        align-items: center;
        color: #6c849c;
        display: flex;
        font-size: 0.8rem;
        gap: 0.35rem;
        margin: 0;
    }

    .community-post-card__footer {
        align-items: flex-end;
        border-top: 0;
        display: flex;
        gap: 0.65rem;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 0.45rem;
    }

    .community-post-card__byline {
        color: #6c849c;
        font-size: 0.78rem;
        line-height: 1.4;
        margin: 0;
        min-width: 0;
    }

    .community-post-card__byline .community-post-card__author-name {
        color: #24527a;
        font-weight: 700;
        text-decoration: none;
    }

    .community-post-card__author {
        align-items: center;
        display: flex;
        gap: 0.65rem;
        min-width: 0;
    }

    .community-post-card__avatar,
    .community-author-avatar.community-post-card__avatar {
        align-items: center;
        background: linear-gradient(135deg, #1f66b4, #2e7d32);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.72rem;
        font-weight: 700;
        height: 2.2rem;
        justify-content: center;
        overflow: hidden;
        width: 2.2rem;
    }

    .community-author-avatar--image {
        background: #e2e8f0;
        object-fit: cover;
    }

    .community-post-card__author-meta {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }

    .community-post-card__author-name {
        color: #24527a;
        font-size: 0.84rem;
        font-weight: 700;
        overflow: hidden;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .community-post-card__author-meta time {
        color: #8aa0b5;
        font-size: 0.76rem;
    }

    .community-post-card__stats {
        align-items: center;
        color: #6c849c;
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        font-size: 0.78rem;
        gap: 0.45rem;
        justify-content: flex-end;
    }

    .community-post-card__save {
        background: transparent;
        border: 0;
        color: #475569;
        cursor: pointer;
        padding: 0;
    }

    .community-post-card__save.is-saved {
        color: #12824e;
    }

    .community-post-card__badge--score {
        font-weight: 700;
    }

    .community-score-badge--trending,
    .community-post-card__badge--score.community-score-badge--trending {
        background: color-mix(in srgb, var(--type-color) 18%, #fff4e5) !important;
        border-color: color-mix(in srgb, var(--type-color) 35%, #f3d9a4) !important;
        color: color-mix(in srgb, var(--type-color) 35%, #b45309) !important;
    }

    .community-score-badge--editors-choice,
    .community-post-card__badge--score.community-score-badge--editors-choice {
        background: color-mix(in srgb, var(--type-color) 18%, #eef2ff) !important;
        border-color: color-mix(in srgb, var(--type-color) 35%, #c7d2fe) !important;
        color: color-mix(in srgb, var(--type-color) 35%, #4338ca) !important;
    }

    .community-score-badge--most-read,
    .community-post-card__badge--score.community-score-badge--most-read {
        background: color-mix(in srgb, var(--type-color) 18%, #ecfeff) !important;
        border-color: color-mix(in srgb, var(--type-color) 35%, #a5f3fc) !important;
        color: color-mix(in srgb, var(--type-color) 35%, #0e7490) !important;
    }

    .community-score-badge--featured,
    .community-post-card__badge--score.community-score-badge--featured {
        background: color-mix(in srgb, var(--type-color) 18%, #ecfdf3) !important;
        border-color: color-mix(in srgb, var(--type-color) 35%, #a7f3d0) !important;
        color: color-mix(in srgb, var(--type-color) 35%, #047857) !important;
    }

    .community-score-badge--community-pick,
    .community-post-card__badge--score.community-score-badge--community-pick {
        background: color-mix(in srgb, var(--type-color) 18%, #fdf2f8) !important;
        border-color: color-mix(in srgb, var(--type-color) 35%, #fbcfe8) !important;
        color: color-mix(in srgb, var(--type-color) 35%, #be185d) !important;
    }

    .community-empty-state {
        align-items: center;
        background: #fff;
        border: 1px dashed #c9d9ea;
        border-radius: 16px;
        display: flex;
        gap: 1rem;
        padding: 2rem 1.25rem;
        text-align: left;
    }

    .community-empty-state__icon {
        align-items: center;
        background: #eef6ff;
        border-radius: 14px;
        color: #1f66b4;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 1.35rem;
        height: 3.25rem;
        justify-content: center;
        width: 3.25rem;
    }

    .community-empty-state__title {
        color: #12395f;
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .community-empty-state__text {
        color: #6c849c;
        font-size: 0.92rem;
        line-height: 1.6;
    }

    .community-pagination-wrap {
        margin-top: 1.5rem;
        text-align: center;
    }

    .community-pagination-summary {
        color: #6c849c;
        font-size: 0.88rem;
        margin: 0;
    }

    .community-pagination-loading {
        color: #2e7d32;
        font-size: 0.88rem;
        font-weight: 600;
        margin: 0.35rem 0 0;
    }

    .community-scroll-sentinel {
        height: 1px;
        width: 100%;
    }

    @media (min-width: 1200px) {
        .community-post-card__title {
            font-size: 0.92rem;
        }

        .community-post-card__excerpt {
            -webkit-line-clamp: 2;
            font-size: 0.82rem;
        }

        .community-post-card__body {
            padding: 0.85rem 0.9rem 0.95rem;
        }

        .community-post-card__avatar,
        .community-author-avatar.community-post-card__avatar {
            height: 2rem;
            width: 2rem;
        }
    }

    @media (max-width: 767.98px) {
        .community-shell {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .community-toolbar,
        .community-type-panel {
            border-radius: 12px;
        }

        .community-empty-state {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endpush

@section('content')
@php
    $authorName = isset($activeAuthor) ? ($activeAuthor->name ?? $activeAuthor->full_name ?? 'Community author') : null;
    $sectionRoute = isset($activeAuthor) ? 'community.authors.show' : 'community.index';
    $sectionRouteParams = isset($activeAuthor) ? ['uniqueName' => $activeAuthor->authorUniqueName()] : [];
    $emptyMessage = isset($activeAuthor)
        ? 'No posts found for this author yet. Try another section or check back later.'
        : 'No posts found for this section yet. Try another category or create the first post.';
    $usePortalListing = \App\Support\CommunityContentTaxonomy::shouldUsePortalListing($activeType ?? '', $activeHub ?? null, isset($activeAuthor));
@endphp

<div class="community-hub {{ $usePortalListing ? 'community-hub--news' : '' }}">
    @include('community.partials.community-hero', [
        'posts' => $posts,
        'hubStats' => $hubStats ?? [],
        'activeAuthor' => $activeAuthor ?? null,
        'authorName' => $authorName,
        'activeType' => $activeType ?? '',
        'activeHub' => $activeHub ?? null,
    ])

    @if (!isset($activeAuthor))
        @include('community.partials.community-hub-sections-nav', [
            'hubSections' => $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections(),
            'activeHub' => $activeHub ?? null,
            'sectionRoute' => $sectionRoute,
            'sectionRouteParams' => $sectionRouteParams,
        ])
    @endif

    @if($usePortalListing)
        @php
            $portalScope = \App\Support\CommunityContentTaxonomy::resolvePortalScope($activeType ?? '', $activeHub ?? null);
        @endphp
        @include('community.partials.news-portal', [
            'posts' => $posts,
            'types' => $types,
            'hubSections' => $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections(),
            'contentPortal' => $contentPortal ?? $newsPortal ?? [],
            'portalKey' => $portalKey ?? $portalScope['portal_key'],
            'activeType' => $activeType ?? '',
            'activeHub' => $activeHub ?? null,
            'activeCategory' => $activeCategory ?? '',
            'engagement' => $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []],
            'emptyMessage' => $emptyMessage,
        ])
    @else
    <div class="community-shell">
        @include('community.partials.hub-navigation', [
            'hubSections' => $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections(),
            'types' => $types,
            'activeHub' => $activeHub ?? null,
            'activeType' => $activeType,
            'activeCategory' => $activeCategory ?? '',
            'engagement' => $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []],
            'sectionRoute' => $sectionRoute,
            'sectionRouteParams' => $sectionRouteParams,
            'hideSectionCards' => true,
        ])

        @php
            $resolvedHubSections = $hubSections ?? \App\Support\CommunityContentTaxonomy::hubSections();
            $feedHubKey = ($activeHub && isset($resolvedHubSections[$activeHub]))
                ? $activeHub
                : array_key_first($resolvedHubSections);
            $feedHub = $feedHubKey ? ($resolvedHubSections[$feedHubKey] ?? null) : null;
        @endphp
        <div class="community-hub-layout">
            <div class="community-hub-feed">
                <div class="community-hub-posts-heading">
                    <h2>
                        @if ($activeCategory && $activeType && isset($types[$activeType]))
                            Latest {{ $types[$activeType]['label'] }}: {{ $activeCategory }}
                        @elseif ($activeType && isset($types[$activeType]))
                            Latest {{ $types[$activeType]['label'] }} Posts
                        @elseif ($feedHub)
                            Latest {{ $feedHub['label'] }} Posts
                        @else
                            Latest community posts
                        @endif
                    </h2>
                    @if ($feedHubKey)
                        <a href="{{ route($sectionRoute, array_merge($sectionRouteParams, ['hub' => $feedHubKey])) }}" class="community-hub-posts-heading__view-all">
                            View all posts <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    @endif
                </div>

                @if(isset($activeAuthor))
                    @include('community.partials.author-questions', [
                        'author' => $activeAuthor,
                        'answeredQuestions' => $answeredAuthorQuestions ?? collect(),
                    ])
                @endif

                <div
                    id="communityPostsGrid"
                    class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4"
                    data-next-page-url="{{ $posts->nextPageUrl() }}"
                >
                    @include('community.partials.post-cards', [
                        'posts' => $posts,
                        'emptyMessage' => $emptyMessage,
                        'engagement' => $engagement ?? ['saved_post_ids' => [], 'subscribed_categories' => [], 'followed_topics' => []],
                    ])
                </div>

                <div class="community-pagination-wrap" id="communityPaginationState">
                    @if ($posts->total() > 0)
                        <p class="community-pagination-summary" id="communitySummaryText">
                            Showing 1 to {{ $posts->lastItem() }} of {{ $posts->total() }} results
                        </p>
                    @endif
                    <p class="community-pagination-loading d-none" id="communityLoadingText">Loading more posts…</p>
                </div>

                <div id="communityScrollSentinel" class="community-scroll-sentinel" aria-hidden="true"></div>
            </div>

            @include('community.partials.hub-sidebar', [
                'popularTopics' => $popularTopics ?? collect(),
                'topContributors' => $topContributors ?? collect(),
            ])
        </div>

        @include('community.partials.hub-highlights')
    </div>
    @endif
</div>

@include('community.partials.share-modal')
@include('community.partials.toastr-assets')
@endsection

@push('scripts')
<script>
    (function () {
        const isNewsLayout = @json(\App\Support\CommunityContentTaxonomy::shouldUsePortalListing($activeType ?? '', $activeHub ?? null, isset($activeAuthor)));
        const postsGrid = document.getElementById(isNewsLayout ? 'communityNewsList' : 'communityPostsGrid');
        const loadingText = document.getElementById('communityLoadingText');
        const summaryText = document.getElementById('communitySummaryText');
        const scrollSentinel = document.getElementById('communityScrollSentinel');
        const loadMoreBtn = document.getElementById('communityNewsLoadMore');
        let nextPageUrl = postsGrid ? (postsGrid.dataset.nextPageUrl || '') : '';
        let isLoading = false;

        function setLoadingState(show) {
            if (!loadingText) return;
            loadingText.classList.toggle('d-none', !show);
        }

        async function loadNextCommunityPage() {
            if (!nextPageUrl || isLoading || !postsGrid) return;

            isLoading = true;
            setLoadingState(true);

            try {
                const response = await fetch(nextPageUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load more posts');
                }

                const payload = await response.json();
                const emptyState = postsGrid.querySelector(isNewsLayout ? '.community-news-empty' : '.community-empty-state');

                if (emptyState) {
                    emptyState.remove();
                }

                if (payload.html) {
                    postsGrid.insertAdjacentHTML('beforeend', payload.html);
                }

                nextPageUrl = payload.next_page_url || '';
                postsGrid.dataset.nextPageUrl = nextPageUrl;

                if (!nextPageUrl && loadMoreBtn) {
                    loadMoreBtn.classList.add('d-none');
                }

                if (summaryText && payload.total > 0) {
                    summaryText.textContent = `Showing 1 to ${payload.loaded_to} of ${payload.total} results`;
                    summaryText.classList.remove('d-none');
                }
            } catch (error) {
                console.error(error);
            } finally {
                isLoading = false;
                setLoadingState(false);
            }
        }

        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', loadNextCommunityPage);
        }

        if (scrollSentinel && postsGrid && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadNextCommunityPage();
                    }
                });
            }, {
                rootMargin: '300px 0px',
            });

            observer.observe(scrollSentinel);
        } else {
            window.addEventListener('scroll', function () {
                if (!nextPageUrl || isLoading || !scrollSentinel) return;

                const sentinelTop = scrollSentinel.getBoundingClientRect().top;
                if (sentinelTop <= window.innerHeight + 300) {
                    loadNextCommunityPage();
                }
            }, { passive: true });
        }
    })();
</script>
<script src="{{ asset('assets/js/community-engagement.js') }}?v={{ now()->timestamp }}"></script>
@endpush

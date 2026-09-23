@extends('layouts.app')

@section('title', $settings->get('site_name', ''))
@section('meta_description', $settings->get('purpose_statement', ''))
@section('canonical_url', url('/') . '/')

@section('content')
    <x-hero
        :eyebrow="$settings->get('home_hero_eyebrow', '')"
        :title="$settings->get('home_hero_title', '')"
        :description="$settings->get('home_hero_description', '')"
        :cta-label="$settings->get('home_hero_cta', 'About Us')"
        :cta-url="url($settings->get('home_hero_cta_url', '/about-us/'))"
        :background-image="$heroImage"
    />

    <section class="action-cards section-pad" aria-label="{{ $settings->get('home_actions_aria', '') }}">
        <div class="container action-cards__grid">
            @foreach ($settings->get('home_actions', []) as $card)
                <x-cta-card
                    :title="$card['title'] ?? ''"
                    :description="$card['description'] ?? ''"
                    :label="$card['label'] ?? ''"
                    :url="url($card['url'] ?? '/')"
                    :image-url="$card['image_url']"
                    :image-alt="$card['image_alt']"
                    :icon="['volunteer', 'donation', 'partner'][$loop->index] ?? 'volunteer'"
                />
            @endforeach
        </div>
    </section>

    <section class="projects-section section-pad" aria-labelledby="projects-title">
        <div class="container">
            <x-section-heading
                id="projects-title"
                :eyebrow="$settings->get('home_projects_eyebrow', '')"
                :title="$settings->get('home_projects_title', '')"
                :description="$settings->get('home_projects_description', '')"
                centered
            />
            <div class="project-grid">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
            @if ($projects->isEmpty())
                <p class="empty-state">{{ $settings->get('home_empty_projects', '') }}</p>
            @endif
        </div>
    </section>

    <x-cta-banner
        :text="$settings->get('home_main_cta', '')"
        :cta-label="$settings->get('home_main_cta_label', '')"
        :cta-url="url($settings->get('home_main_cta_url', '/'))"
        :background-image="$mainCtaImage"
    />

    <section class="campaign-section section-pad" aria-labelledby="campaign-title">
        <div class="container campaign-section__inner">
            
            @if ($campaignImage)
                <div class="campaign-section__media">
                    <img class="campaign-section__image" src="{{ \Illuminate\Support\Facades\Storage::disk($campaignImage->disk)->url($campaignImage->path) }}" alt="{{ $campaignImage->alt_text ?: $campaignImage->caption ?: ($pinkChaiPage?->title ?? '') }}" loading="lazy">
                </div>
            @endif
            <div class="campaign-section__content">
                @if ($pinkChaiPage)
                    <p class="eyebrow">{{ $settings->get('home_campaign_eyebrow', '') }}</p>
                    <h2 id="campaign-title">{{ $pinkChaiPage->title }}</h2>
                    <p>{{ $pinkChaiPage->excerpt }}</p>
                    <a class="button button--primary" href="{{ url($settings->get('home_campaign_cta_url', '/donate/')) }}">{{ $settings->get('home_campaign_cta', 'Donate Now') }}</a>
                @endif
            </div>
        </div>
    </section>

    <section class="gallery-section section-pad" aria-labelledby="gallery-title">
        <div class="container">
            <x-section-heading id="gallery-title" :eyebrow="$settings->get('home_gallery_eyebrow', '')" :title="$settings->get('home_gallery_title', '')" centered />
            @if ($gallery->isNotEmpty())
                <x-image-gallery :images="$gallery" />
            @endif
        </div>
    </section>

    <section class="blog-intro section-pad" aria-labelledby="blog-title">
        <div class="container blog-intro__inner">
            <div class="blog-intro__feature">
                <p class="eyebrow">{{ $settings->get('home_blog_eyebrow', '') }}</p>
                <h2 id="blog-title">{{ $settings->get('home_blog_title', '') }}</h2>
                @foreach ($settings->get('home_blog_paragraphs', []) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                <a class="button button--primary" href="{{ url($settings->get('home_blog_cta_url', '/blog/')) }}">{{ $settings->get('home_blog_cta', '') }}</a>
            </div>
            @if ($blogPosts->isEmpty())
                <p class="empty-state blog-intro__empty">{{ $settings->get('home_empty_blog', '') }}</p>
            @else
                <div class="project-grid blog-intro__posts">
                    @foreach ($blogPosts as $post)
                        <article class="project-card">
                            @if ($post->ogImage)
                                <img class="project-card__image" src="{{ \Illuminate\Support\Facades\Storage::disk($post->ogImage->disk)->url($post->ogImage->path) }}" alt="{{ $post->ogImage->alt_text ?: $post->title }}" loading="lazy">
                            @endif
                            <div class="project-card__content"><h3>{{ $post->title }}</h3>@if ($post->excerpt)<p>{{ $post->excerpt }}</p>@endif</div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection

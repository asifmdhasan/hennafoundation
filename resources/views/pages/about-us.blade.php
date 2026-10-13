@extends('layouts.app')

@section('title', $seoTitle)
@section('meta_description', $metaDescription ?? '')
@section('canonical_url', $canonicalUrl)
@section('og_title', $ogTitle)
@section('og_description', $ogDescription ?? '')
@if ($ogImageUrl)
    @section('og_image', $ogImageUrl)
@endif
@section('content')
    @if ($content['hero']['visible'])
        <section
            class="about-hero{{ $content['hero']['overlay'] ? ' about-hero--overlay' : '' }}"
            @if ($content['hero']['image'])
                style="--about-hero-image: url('{{ $content['hero']['image']['url'] }}')"
            @endif
            aria-labelledby="about-page-title"
        >
            <div class="container about-hero__inner">
                <nav class="about-hero__breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}">Home</a>
                    <span aria-hidden="true">/</span>
                    <span>{{ $content['hero']['breadcrumb_title'] ?: $page->title }}</span>
                </nav>
                <h1 id="about-page-title">{{ $page->title }}</h1>
                @if (filled($content['hero']['text']))
                    <p class="about-hero__text">{{ $content['hero']['text'] }}</p>
                @endif
            </div>
        </section>
    @else
        <header class="about-page-title container">
            <h1 id="about-page-title">{{ $page->title }}</h1>
        </header>
    @endif

    @if ($content['inspiration']['visible'])
        <section class="about-section about-inspiration section-pad" aria-labelledby="about-inspiration-title">
            <div class="container about-inspiration__layout">
                @if ($content['inspiration']['images']->isNotEmpty())
                    <div class="about-inspiration__gallery image-gallery">
                        @foreach ($content['inspiration']['images'] as $image)
                            <figure class="image-gallery__item">
                                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" loading="lazy" decoding="async">
                            </figure>
                        @endforeach
                    </div>
                @endif

                <div class="about-inspiration__copy">
                    @if (filled($content['inspiration']['eyebrow']))
                        <p class="eyebrow">{{ $content['inspiration']['eyebrow'] }}</p>
                    @endif
                    @if (filled($content['inspiration']['heading']))
                        <h2 id="about-inspiration-title">{{ $content['inspiration']['heading'] }}</h2>
                    @endif
                    @foreach ($content['inspiration']['paragraphs'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @if ($content['inspiration']['button_visible'] && filled($content['inspiration']['button_label']) && filled($content['inspiration']['button_url']))
                        <a class="button button--primary" href="{{ $content['inspiration']['button_url'] }}">{{ $content['inspiration']['button_label'] }}</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if ($content['patron']['visible'] && (filled($content['patron']['name']) || filled($content['patron']['designation']) || count($content['patron']['biography']) || $content['patron']['image']))
        <section class="about-section about-patron section-pad" aria-labelledby="about-patron-title">
            <div class="container about-patron__layout">
                @if ($content['patron']['image'])
                    <div class="about-patron__media">
                        <img src="{{ $content['patron']['image']['url'] }}" alt="{{ $content['patron']['image']['alt'] }}" loading="lazy" decoding="async">
                    </div>
                @endif
                <div class="about-patron__copy">
                    @if (filled($content['patron']['eyebrow']))
                        <p class="eyebrow">{{ $content['patron']['eyebrow'] }}</p>
                    @endif
                    @if (filled($content['patron']['name']))
                        <h2 id="about-patron-title">{{ $content['patron']['name'] }}</h2>
                    @endif
                    @if (filled($content['patron']['designation']))
                        <p class="about-patron__designation">{{ $content['patron']['designation'] }}</p>
                    @endif
                    @foreach ($content['patron']['biography'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($content['trustees']['visible'])
        <section class="about-section about-trustees section-pad" aria-labelledby="about-trustees-title">
            <div class="container">
                <header class="section-heading section-heading--centered">
                    @if (filled($content['trustees']['eyebrow']))
                        <p class="eyebrow">{{ $content['trustees']['eyebrow'] }}</p>
                    @endif
                    @if (filled($content['trustees']['heading']))
                        <h2 id="about-trustees-title">{{ $content['trustees']['heading'] }}</h2>
                    @endif
                </header>
                @if (count($content['trustees']['items']))
                    <div class="about-trustees__grid">
                        @foreach ($content['trustees']['items'] as $trustee)
                            <article class="about-trustee">
                                @if ($trustee['image'])
                                    <img class="about-trustee__image" src="{{ $trustee['image']['url'] }}" alt="{{ $trustee['image']['alt'] }}" loading="lazy" decoding="async">
                                @endif
                                @if (filled($trustee['name'] ?? null))
                                    <h3>{{ $trustee['name'] }}</h3>
                                @endif
                                @if (filled($trustee['designation'] ?? null))
                                    <p class="about-trustee__designation">{{ $trustee['designation'] }}</p>
                                @endif
                                @if (count($trustee['social_links'] ?? []))
                                    <ul class="about-trustee__social" aria-label="Profile links">
                                        @foreach ($trustee['social_links'] as $link)
                                            <li><a href="{{ $link['url'] }}" aria-label="{{ $link['label'] }}" title="{{ $link['label'] }}" target="_blank" rel="noopener noreferrer">{{ $link['symbol'] }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($content['goals']['visible'])
        <section class="about-section about-goals section-pad" aria-labelledby="about-goals-title">
            <div class="container">
                <header class="section-heading section-heading--centered">
                    @if (filled($content['goals']['eyebrow']))
                        <p class="eyebrow">{{ $content['goals']['eyebrow'] }}</p>
                    @endif
                    @if (filled($content['goals']['heading']))
                        <h2 id="about-goals-title">{{ $content['goals']['heading'] }}</h2>
                    @endif
                    @if (filled($content['goals']['intro']))
                        <p class="section-heading__description">{{ $content['goals']['intro'] }}</p>
                    @endif
                </header>
                @if (count($content['goals']['items']))
                    <div class="about-goals__grid">
                        @foreach ($content['goals']['items'] as $goal)
                            <article class="about-goal">
                                @if (! empty($goal['icon_image']))
                                    <img class="about-goal__icon-image" src="{{ $goal['icon_image']['url'] }}" alt="" aria-hidden="true" loading="lazy" decoding="async">
                                @elseif (filled($goal['icon'] ?? null))
                                    <span class="about-goal__icon" aria-hidden="true">{{ $goal['icon'] }}</span>
                                @endif
                                @if (filled($goal['title'] ?? null))
                                    <h3>{{ $goal['title'] }}</h3>
                                @endif
                                @if (filled($goal['description'] ?? null))
                                    <p>{{ $goal['description'] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    @if ($content['heart']['visible'] && (filled($content['heart']['heading']) || count($content['heart']['paragraphs'])))
        <section class="about-section about-heart section-pad" aria-labelledby="about-heart-title">
            <div class="container about-heart__container">
                @if (filled($content['heart']['eyebrow']))
                    <p class="eyebrow">{{ $content['heart']['eyebrow'] }}</p>
                @endif
                @if (filled($content['heart']['heading']))
                    <h2 id="about-heart-title">{{ $content['heart']['heading'] }}</h2>
                @endif
                @foreach ($content['heart']['paragraphs'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </section>
    @endif

    @if ($content['plan']['visible'])
        <section class="about-section about-plan section-pad" aria-labelledby="about-plan-title">
            <div class="container">
                @if (filled($content['plan']['eyebrow']))
                    <p class="eyebrow">{{ $content['plan']['eyebrow'] }}</p>
                @endif
                @if (filled($content['plan']['heading']))
                    <h2 id="about-plan-title">{{ $content['plan']['heading'] }}</h2>
                @endif
                @if (filled($content['plan']['intro']))
                    <p class="about-plan__intro">{{ $content['plan']['intro'] }}</p>
                @endif
                @if (count($content['plan']['items']))
                    <ol class="about-plan__list">
                        @foreach ($content['plan']['items'] as $item)
                            <li>
                                @if (filled($item['title'] ?? null))
                                    <h3>{{ $item['title'] }}</h3>
                                @endif
                                @if (filled($item['description'] ?? null))
                                    <p>{{ $item['description'] }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>
    @endif

    @if ($content['charity']['visible'] && (filled($content['charity']['heading']) || filled($content['charity']['content']) || filled($content['charity']['number'])))
        <section class="about-section about-charity section-pad">
            <div class="container about-charity__container">
                @if (filled($content['charity']['heading']))
                    <h2>{{ $content['charity']['heading'] }}</h2>
                @endif
                @if (filled($content['charity']['content']))
                    <p>{{ $content['charity']['content'] }}</p>
                @endif
                @if (filled($content['charity']['number']))
                    <p class="about-charity__number">
                        @if (filled($settings->get('footer_charity_label')))
                            <span>{{ $settings->get('footer_charity_label') }}:</span>
                        @endif
                        {{ $content['charity']['number'] }}
                    </p>
                @endif
            </div>
        </section>
    @endif
@endsection

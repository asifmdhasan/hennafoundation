@props(['eyebrow', 'title', 'description', 'ctaLabel', 'ctaUrl', 'backgroundImage'])

<section class="hero" aria-labelledby="home-title" @if ($backgroundImage) style="--hero-image: url('{{ $backgroundImage }}')" @endif>
    <div class="hero__content container">
        <p class="eyebrow hero__eyebrow">{{ $eyebrow }}</p>
        <h1 id="home-title">{{ $title }}</h1>
        <p class="hero__description">{{ $description }}</p>
        <a class="button button--primary" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
</section>

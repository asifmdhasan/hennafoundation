@props(['text', 'ctaLabel', 'ctaUrl', 'backgroundImage'])

<section class="cta-banner" @if ($backgroundImage) style="--banner-image: url('{{ $backgroundImage }}')" @endif aria-label="Get involved">
    <div class="cta-banner__inner container">
        <h2>{{ $text }}</h2>
        <a class="button button--light" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
    </div>
</section>

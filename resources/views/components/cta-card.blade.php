@props(['title', 'description', 'label', 'url', 'imageUrl' => null, 'imageAlt' => '', 'icon' => 'volunteer'])

<article class="cta-card">
    @if ($imageUrl)
        <img class="cta-card__image" src="{{ $imageUrl }}" alt="{{ $imageAlt }}" loading="lazy">
    @else
        <span class="cta-card__icon" aria-hidden="true">
            @if ($icon === 'volunteer')
                <svg viewBox="0 0 48 48"><circle cx="22" cy="17" r="6"/><path d="M10 38v-5c0-5 5-8 12-8s12 3 12 8v5H10Z"/><circle cx="36" cy="13" r="7"/><path d="M36 9v8M32 13h8"/></svg>
            @elseif ($icon === 'donation')
                <svg viewBox="0 0 48 48"><circle cx="24" cy="13" r="9"/><path d="M24 8v10m-3-7c0-2 6-2 6 0s-6 2-6 4 6 2 6 0M7 31h9l5 3h11l8-5c2-1 4 2 2 3l-8 7H20l-5-3H7zM7 30v9"/></svg>
            @else
                <svg viewBox="0 0 48 48"><path d="m6 18 8-7 9 2 3 3 4-2 11 5-8 9-8-2-5 4-7-5-7-2z"/><path d="m15 13 8 6c2 2 5-1 3-3l-3-3m-8 12-4 4m9-2-3 4m8-5-3 4m8-9 5 2M6 18l-3 11 8 2m34-13 3 10-9 2"/></svg>
            @endif
        </span>
    @endif
    <div class="cta-card__copy">
        <h2>{{ $title }}</h2>
        <p>{{ $description }}</p>
        <a class="text-link" href="{{ $url }}">{{ $label }} <span aria-hidden="true">→</span></a>
    </div>
</article>

@props(['id' => null, 'eyebrow' => null, 'title', 'description' => null, 'centered' => false])

<div {{ $attributes->class(['section-heading', 'section-heading--centered' => $centered]) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif
    <h2 @if ($id) id="{{ $id }}" @endif>{{ $title }}</h2>
    @if ($description)
        <p class="section-heading__description">{{ $description }}</p>
    @endif
</div>

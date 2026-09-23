@props(['images' => []])

<div class="image-gallery">
    @foreach ($images as $index => $image)
        <figure class="image-gallery__item">
            <img
                src="{{ $image['url'] }}"
                width="640"
                height="480"
                alt="{{ $image['alt'] }}"
                loading="lazy"
                decoding="async"
            >
            @if ($image['caption'])
                <figcaption>{{ $image['caption'] }}</figcaption>
            @endif
        </figure>
    @endforeach
</div>

@props(['project'])

<article class="project-card">
    @if (!empty($project['image_url']))
        <img
            class="project-card__image"
            src="{{ $project['image_url'] }}"
            width="768"
            height="512"
            alt="{{ $project['image_alt'] ?? 'Image for '.$project['title'] }}"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="project-card__image project-card__image--placeholder" aria-hidden="true"></div>
    @endif
    <div class="project-card__content">
        <h3>{{ $project['title'] }}</h3>
        @if (!empty($project['summary']))
            <p>{{ $project['summary'] }}</p>
        @endif
    </div>
</article>
